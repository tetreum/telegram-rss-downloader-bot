<?php

namespace App;

class Utils {
    /**
     * Creates the given folder path
     * @param string $path
     * @param bool $recursive
     */
    public static function createFolder ($path, $recursive = false)
    {
        $oldmask = umask(0);
        mkdir($path, 0775, $recursive);
        umask($oldmask);
    }
}