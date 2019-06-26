<?php

namespace mirocow\imagecache\components\handlers;

use Intervention\Image\Image;
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
    /**
     * @var array 
     */
    public $config = ['driver' => 'imagick'];
    
    /**
     * @var array
     */
    public $presets = [];

    /**
     * @var string
     */
    public $targetPath;

    /**
     * @var callable|null
     */
    public $callback;

    /**
     * @param string $srcPath
     * @param string $targetFile
     * @return \ImageManager
     */
    public function runHandler(string $srcPath, string $targetFile)
    {
        $manager = new ImageManager($this->config);

        /** @var Image $handle */
        $handle = $manager->make($srcPath);

        if (isset($this->presets['actions']['image_watermark_path']) && isset($this->presets['actions']['image_watermark'])) {
            $handle->insert(Yii::getAlias($this->presets['actions']['image_watermark_path']) . DIRECTORY_SEPARATOR . $this->presets['actions']['image_watermark']);
        }

        if ($this->callback instanceof \Closure || is_callable($this->callback)) {
            call_user_func($this->callback, $handle, $this->presets);
        }

        if (isset($this->presets['actions'])) {
            foreach ($this->presets['actions'] as $action => $params) {
                if(method_exists($handle, $action)) {
                    call_user_func_array([$handle, $action], $params);
                }
            }
        }

        if(isset($this->presets['actions']['image_convert'])){
            $handle->encode($this->presets['actions']['image_convert']);
        }

        if(isset($this->presets['actions']['jpeg_quality'])){
            $quality = $this->presets['actions']['jpeg_quality'];
        }
        if(isset($this->presets['actions']['png_compression'])){
            $quality = $this->presets['actions']['png_compression'];
        }
        if(empty($quality)){
            $quality = 60;
        }

        $handle->save($targetFile, $quality);

    }
}