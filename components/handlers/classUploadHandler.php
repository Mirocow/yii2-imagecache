<?php

namespace mirocow\imagecache\components\handlers;

use mirocow\imagecache\contracts\handlerInterface;

/**
 * Class classUploadHadler
 * @package mirocow\imagecache\components\handlers
 * @see https://github.com/verot/class.upload.php
 * @see https://www.verot.net/php_class_upload_samples.htm
 */
class classUploadHandler implements handlerInterface
{
    public $preset;
    public $targetPath;

    /**
     * @param string $srcPath
     * @param string $targetFile
     */
    public function runHandler(string $srcPath, string $targetFile)
    {
        $handle = new \upload($srcPath);
        $handle->file_safe_name = false;
        $handle->file_overwrite = true;
        $handle->file_auto_rename = false;

        if (isset($this->preset['actions']['image_watermark_path']) && isset($this->preset['actions']['image_watermark'])) {
            $this->preset['actions']['image_watermark'] = Yii::getAlias($this->preset['actions']['image_watermark_path']) . DIRECTORY_SEPARATOR . $this->preset['actions']['image_watermark'];
        }

        if (isset($this->preset['actions'])) {
            foreach ($this->preset['actions'] as $action => $params) {
                $handle->$action = $params;
            }
        }

        if ($this->targetPath) {
            $handle->process($this->targetPath);
            if ($handle->processed) {
                @rename($handle->file_dst_pathname, $targetFile);
            } else {
                throw new Exception($handle->error);
            }
        }
    }
}