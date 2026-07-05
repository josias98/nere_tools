<?php

namespace WPSynchro\Files;

/**
 * Filter class for use during file population
 *
 */
class PopulateFileListFilterIterator extends \FilterIterator
{
    public static $FILTERS;
    public static $common;
    public static $file_excludes;

    public function accept(): bool
    {
        $file = $this->current()->getPathname();
        $file = self::$common->fixPath($file);
        $filename = $this->current()->getFilename();

        if (in_array($file, self::$file_excludes)) {
            return false;
        }

        foreach (self::$FILTERS as $filter) {
            if (strpos($file, $filter) > -1) {
                return false;
            }
        }
        return true;
    }
}
