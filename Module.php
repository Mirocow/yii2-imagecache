<?php

namespace mirocow\imagecache;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'mirocow\imagecache\controllers';

    public $cachePath = '@webroot/images';
}