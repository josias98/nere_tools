<?php
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

$opts = getopt('', ['from:', 'to:', 'dry-run']);
$from = $opts['from'] ?? '';
$to = $opts['to'] ?? '';
$dryRun = array_key_exists('dry-run', $opts);

if ($from === '' || $to === '') {
    fwrite(STDERR, "Usage: php scripts/db-url-replace.php --from=http://old --to=https://new [--dry-run]\n");
    exit(1);
}

require_once dirname(__DIR__) . '/wp-load.php';

global $wpdb;

$tables = $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($wpdb->prefix) . '%'));
$changedRows = 0;

function nere_replace_value($value, $from, $to, &$changed)
{
    if (!is_string($value)) {
        return $value;
    }

    $unserialized = @unserialize($value);
    if ($unserialized !== false || $value === 'b:0;') {
        $nestedChanged = false;
        $replaced = nere_replace_nested($unserialized, $from, $to, $nestedChanged);
        if ($nestedChanged) {
            $changed = true;
            return serialize($replaced);
        }
    }

    $replaced = str_replace($from, $to, $value);
    if ($replaced !== $value) {
        $changed = true;
    }

    return $replaced;
}

function nere_replace_nested($value, $from, $to, &$changed)
{
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = nere_replace_nested($item, $from, $to, $changed);
        }
        return $value;
    }

    if (is_object($value)) {
        foreach ($value as $key => $item) {
            $value->$key = nere_replace_nested($item, $from, $to, $changed);
        }
        return $value;
    }

    if (is_string($value)) {
        $replaced = str_replace($from, $to, $value);
        if ($replaced !== $value) {
            $changed = true;
        }
        return $replaced;
    }

    return $value;
}

foreach ($tables as $table) {
    $primary = $wpdb->get_var("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    if (!$primary) {
        continue;
    }

    $columns = $wpdb->get_results("SHOW COLUMNS FROM `$table`");
    $textColumns = array_values(array_filter($columns, function ($column) {
        return preg_match('/char|text|blob/i', $column->Type);
    }));

    if (!$textColumns) {
        continue;
    }

    $primaryColumn = $columns[0]->Field;
    foreach ($columns as $column) {
        if ($column->Key === 'PRI') {
            $primaryColumn = $column->Field;
            break;
        }
    }

    $rows = $wpdb->get_results("SELECT * FROM `$table`", ARRAY_A);
    foreach ($rows as $row) {
        $updates = [];
        foreach ($textColumns as $column) {
            $field = $column->Field;
            if (!array_key_exists($field, $row) || $row[$field] === null) {
                continue;
            }

            $changed = false;
            $value = nere_replace_value($row[$field], $from, $to, $changed);
            if ($changed) {
                $updates[$field] = $value;
            }
        }

        if ($updates) {
            $changedRows++;
            if (!$dryRun) {
                $wpdb->update($table, $updates, [$primaryColumn => $row[$primaryColumn]]);
            }
        }
    }
}

echo ($dryRun ? 'Would update ' : 'Updated ') . $changedRows . " rows.\n";
