<?php

namespace Framework3\Filesystem;

class DirectoryReader
{
    /**
     * @param string $dir
     *
     * @return array|false
     */
    public function scan(string $dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        return scandir($dir);
    }

    /**
     * @param string $dir
     *
     * @return bool
     */
    public function isDir(string $dir)
    {
        return is_dir($dir);
    }
}