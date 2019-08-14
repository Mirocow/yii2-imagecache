<?php

namespace mirocow\imagecache\components\handlers;

use mirocow\imagecache\contracts\handlerInterface;
use yii\base\Exception;

/**
 * Class classUploadHadler
 * @package mirocow\imagecache\components\handlers
 * @see https://github.com/verot/class.upload.php
 * @see https://www.verot.net/php_class_upload_samples.htm
 */
class classUploadHandler implements handlerInterface
{
    /**
     * @var array 
     */
    public $presets = [];

    /**
     * @var string
     */
    public $targetPath;

    /**
     * @var string 
     */
    public $max_filesize = '52428800';

    /**
     * @var callable|null
     */
    public $callback;

    /**
     * @param string $srcPath
     * @param string $targetFile
     */
    public function runHandler(string $srcPath, string $targetFile)
    {
        if (isset($this->presets['actions']['image_increase']) && $this->presets['actions']['image_increase'] === false) {
            $size = self::getSize($originalFile);
            if ($size[0] < $this->presets['actions']['image_x'] || $size[1] < $this->presets['actions']['image_y']) {
                $this->presets['actions']['image_resize'] = false;
            }
        }

        $handle = new \Verot\Upload\upload($srcPath);
        $handle->file_safe_name = false;
        $handle->file_overwrite = true;
        $handle->file_auto_rename = false;

        if (isset($this->presets['actions']['image_watermark_path']) && isset($this->presets['actions']['image_watermark'])) {
            $this->presets['actions']['image_watermark'] = Yii::getAlias($this->presets['actions']['image_watermark_path']) . DIRECTORY_SEPARATOR . $this->presets['actions']['image_watermark'];
        }

        if ($this->targetPath) {
            if (isset($this->presets['actions'])) {
                foreach ($this->presets['actions'] as $action => $params) {
                    $handle->{$action} = $params;
                }
            }

            $handle->file_max_size_raw = trim($this->max_filesize);
            $handle->file_max_size = $handle->getsize($handle->file_max_size_raw);

            if ($this->callback instanceof \Closure || is_callable($this->callback)) {
                call_user_func($this->callback, $handle, $this->presets);
            }
            
            $handle->process($this->targetPath);
            if ($handle->processed) {
                @rename($handle->file_dst_pathname, $targetFile);
            } else {
                throw new Exception($handle->error);
            }
        }
    }

    /**
     * @param $file
     * @return array
     */
    private static function getSize($file)
    {
        $cmd = "identify -format \"%w|%h|%k\" " . escapeshellarg($file) . " 2>&1";
        $returnVal = 0;
        $output = array();
        exec($cmd, $output, $returnVal);
        if ($returnVal == 0 && count($output) == 1) {
            return explode('|', $output[0]);
        }
    }
}
