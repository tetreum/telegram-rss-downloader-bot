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

    /**
    * Recursive rmdir
    * @param string $path
    */
    public static function rmdir($path) {
        $dir = new \DirectoryIterator($path);

        foreach ($dir as $file) {
            if ($file->isDot()) {continue;}
            $file->isDir() ? self::rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($path);
    }
}
