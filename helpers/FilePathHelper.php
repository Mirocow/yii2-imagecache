<?php

namespace mirocow\imagecache\helpers;

class FilePathHelper
{
    /**
     * Generate obsolute path
     * @param string $filePath (Examples: @web/file, @web/upload)
     * @return string
     */
    public static function getAbsolutePath($filePath)
    {
        $filePath = \Yii::getAlias($filePath);

        $info = pathinfo($filePath);

        if(!isset($info['dirname'])){
            return false;
        }

        $dirname = $info['dirname'];

        if(!isset($info['extension'])){
            return false;
        }

        $extension = $info['extension'];

        while(strpos($info['filename'], '.')) {
            $info = pathinfo($info['filename']);
        }

        if(!self::isMD5($info['filename'])){
            $fileName = md5($filePath) . '.' . $extension;
        } else {
            $fileName = $info['filename'] . '.' . $extension;
        }

        $pathFile =  substr($fileName, 1, 2) . '/' . substr($fileName, 4, 2);

        if (!is_dir($dirname . DIRECTORY_SEPARATOR . $pathFile)){
            mkdir($dirname . DIRECTORY_SEPARATOR . $pathFile, 0775, true);
        }

        return $dirname . DIRECTORY_SEPARATOR . $pathFile . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $storageUrl Путь к сохраняемому файлу можно алиас (пример: @web/file, @web/upload)
     * @return bool|string
     */
    public static function getRelativePath($filePath, $storageAlias = '@frontend/web')
    {
        $absolutePath = self::getAbsolutePath($filePath);
        $webUrl = \Yii::getAlias($storageAlias);
        return ltrim(str_replace($webUrl, '', $absolutePath), DIRECTORY_SEPARATOR);
    }

    /**
     * @param $fileName
     * @return bool
     */
    protected static function isMD5($fileName)
    {
        if(strlen($fileName) == 32 && preg_match('~[a-z0-9_]~', $fileName)){
            return true;
        }
        return false;
    }

}