<?php

namespace mirocow\imagecache\components\handlers;

use mirocow\imagecache\contracts\handlerInterface;
use Intervention\Image\ImageManager;

/**
 * Class interventionImageHandler
 * @package mirocow\imagecache\components\handlers
 * @see http://image.intervention.io
 * @see https://github.com/Intervention/image
 */
class interventionImageHandler implements handlerInterface
{
    public $config = ['driver' => 'imagick'];
    public $preset;
    public $targetPath;

    /**
     * @param string $srcPath
     * @param string $targetFile
     * @return \ImageManager
     */
    public function runHandler(string $srcPath, string $targetFile)
    {
        $manager = new ImageManager($this->config);

        $image = $manager->make($srcPath);

        if (isset($this->preset['actions']['image_watermark_path']) && isset($this->preset['actions']['image_watermark'])) {
            $image->insert(Yii::getAlias($this->preset['actions']['image_watermark_path']) . DIRECTORY_SEPARATOR . $this->preset['actions']['image_watermark']);
        }

        if(isset($this->preset['actions']['image_convert'])){
            if(isset($this->preset['actions']['jpeg_quality'])){
                $quality = $this->preset['actions']['jpeg_quality'];
                unset($this->preset['actions']['jpeg_quality']);
            }
            if(isset($this->preset['actions']['png_compression'])){
                $quality = $this->preset['actions']['png_compression'];
                unset($this->preset['actions']['png_compression']);
            }
            if(empty($quality)){
                $quality = 60;
            }
            $image->encode($this->preset['actions']['image_convert'], $quality);
            unset($this->preset['actions']['image_convert']);
        }

        if (isset($this->preset['actions'])) {
            foreach ($this->preset['actions'] as $action => $params) {
                call_user_func_array([$image, $action], $params);
            }
        }

        $image->save($targetFile);

    }
}