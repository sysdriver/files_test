<?php

namespace App\Service;

/**
 * Description of FileService
 *
 * @author k.gorbunov
 */
class FileService
{
    //put your code here
    public static function makeFoldersRecursive($filePath)
    {
        $dir = dirname($filePath);
        return !is_dir($dir) ? mkdir($dir, 0777, TRUE) : '';
    }
    
    public static function getFileList($folder)
    {
        $files = [];
        if ($handle = opendir($folder)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $files[] = $entry;
                }
            }

            closedir($handle);
        }
        
        return $files;
    }
}
