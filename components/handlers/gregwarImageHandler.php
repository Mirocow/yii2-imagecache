<?php

namespace mirocow\imagecache\components\handlers;

use mirocow\imagecache\contracts\handlerInterface;
use Gregwar\Image\Image;

/**
 * Class gregwarImageHandler
 * @package mirocow\imagecache\components\handlers
 * @see https://github.com/Gregwar/Image
 */
class gregwarImageHandler implements handlerInterface
{
    public $preset;
    public $targetPath;

    /**
     * @param string $srcPath
     * @param string $targetFile
     * @return \ImageManager
     */
    public function runHandler(string $srcPath, string $targetFile)
    {
        /** @var Image $manager */
        $manager = Image::open($this->config);

        if (isset($this->preset['actions']['image_watermark_path']) && isset($this->preset['actions']['image_watermark'])) {
            $watermark = Image::open(Yii::getAlias($this->preset['actions']['image_watermark_path']) . DIRECTORY_SEPARATOR . $this->preset['actions']['image_watermark']);

            $manager->merge($watermark, $manager->width()-$watermark->width(),
                $manager->height()-$watermark->height());
        }

        if (isset($this->preset['actions'])) {
            foreach ($this->preset['actions'] as $action => $params) {
                call_user_func_array([$image, $action], $params);
            }
        }

        $image->save($targetFile);

    }
}