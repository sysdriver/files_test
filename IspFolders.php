<?php
namespace App\Models;

/**
 * Description of IspFolders
 *
 * @author k.gorbunov
 */
class IspFolders
{

    public static function getFolders($root='ISP')
    {
        $folders = [
            //abon info
            "/$root/abonents/",
            //pay info
            "/$root/pay/bank-pay",
            //dictionary info
            "/$root/dictionaries/gates/",
            //phone connections info
            "/$root/connections",
        ];

        return $folders;
    }

    public static function makeAllFolders($root='ISP')
    {
        $rootFiles = dirname(dirname( __DIR__ )) . '/files';
        $folders = self::getFolders($root);
        
        foreach ($folders as $k => $folder) {
            $dir = $rootFiles . $folder;
            if (!is_dir($dir)) {    
                // dir doesn't exist, make it
                if(!mkdir($dir, 0777, TRUE)) {
                    die("Can't create directory...");
                }
            }
        }
    }
}
