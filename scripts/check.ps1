$ErrorActionPreference = "Stop"

$files = @(
    "local/wp-config.local.php",
    "wp-content/themes/financity-child/functions.php",
    "wp-content/themes/financity-child/header.php",
    "wp-content/themes/financity-child/footer.php",
    "wp-content/themes/financity-child/single.php"
)

foreach ($file in $files) {
    php -l $file
}

Write-Host "PHP syntax checks passed."
