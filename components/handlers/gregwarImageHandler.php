<?php

namespace mirocow\imagecache\components\handlers;

use mirocow\imagecache\contracts\handlerInterface;
use Gregwar\Image\Image;
use yii\base\Exception;

/**
 * Class gregwarImageHandler
 * @package mirocow\imagecache\components\handlers
 * @see https://github.com/Gregwar/Image
 */
class gregwarImageHandler implements handlerInterface
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
        throw new Exception('Not yet implemented');
    }
}