<?php

namespace App\Service;

use App\Service\FileService;

/**
 * Class to work with files via ftp
 *
 * @author k.gorbunov
 */
class FtpService
{

    private static $ftpSrv = '192.168.1.250';
    private static $ftpUsr = 'anonymous';
    private static $ftpPass = 'anonymous';
    private static $skipSize = 10;   //if file less than $skipSize, don't upload it to FTP 
    protected static $ftpConn;

    public static function setConn()
    {
        if (self::$ftpConn = ftp_connect(self::$ftpSrv)) {
            return TRUE;
        }

        echo "Не удалось установить соединение с " . self::$ftpSrv . PHP_EOL;
        return FALSE;
    }

    public static function login($verbose = 0)
    {

        if (empty(self::$ftpConn)) {
            self::setConn();
        }

        if ($result = ftp_login(self::$ftpConn, self::$ftpUsr, self::$ftpPass)) {
            if ($verbose) {
                echo "Connected as " . self::$ftpUsr . "@" . self::$ftpSrv . PHP_EOL;
            }
            //switch to passive mode (https://stackoverflow.com/questions/40720260/php-ftp-put-fails)
            if (!$result = ftp_pasv(self::$ftpConn, true)) {
                echo "Unable switch to passive mode" . PHP_EOL;
            }
        } else {
            echo "Couldn't connect as " . self::$ftpUsr . "@" . self::$ftpSrv . PHP_EOL;
        }

        return $result;
    }

    public static function put($dstFile, $srcFile, $verbose = 0)
    {
        if (empty(self::$ftpConn)) {
            self::setConn();
        }
        
        if (ftp_put(self::$ftpConn, $dstFile, $srcFile, FTP_ASCII)) {
            if ($verbose) {
                echo "File $dstFile uploaded successfully" . PHP_EOL;
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function close()
    {
        if (!empty(self::$ftpConn)) {
            ftp_close(self::$ftpConn);
        }
    }

    public static function fileExists($remoteFile, $verbose = 0)
    {
        $fSize = ftp_size(self::$ftpConn, $remoteFile);
        if ($fSize !== -1) {
            if ($verbose) {
                echo "File {$remoteFile} exists on remote server, size = {$fSize}" . PHP_EOL;
            }
            return TRUE;
        } else {

            return FALSE;
        }
    }

    public static function uploadFiles($dstFolder, $srcFolder, $verbose = 0)
    {
        $files = FileService::getFileList($srcFolder);
        $sentDir = $srcFolder . 'sent/';

        if (!is_dir($sentDir)) {
            // dir doesn't exist, make it
            mkdir($sentDir, 0777, TRUE);
        }

        foreach ($files as $file) {
            if (is_dir($srcFolder . $file)) {
                continue;
            }

            //check filesize, skip if <= 10 bytes
            $fsize = filesize ( $srcFolder . $file );
            
            if ($fsize <= self::$skipSize) {
                echo "SIZE OF FILE " . $srcFolder . $file . " = $fsize bytes (less than " . (self::$skipSize +1) . " bytes, skip)" . PHP_EOL;
                continue;
            }

            if (self::put($dstFolder . $file, $srcFolder . $file, $verbose)) {
                //check by ftp_nlist for control
                if ($success = self::fileExists($dstFolder . $file, $verbose)) {
                    //if success, change status in table and delete src file
                    rename($srcFolder . $file, $sentDir . $file);
                }
            }
        }
    }
}
