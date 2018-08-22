<?php

namespace mirocow\imagecache\controllers;

use Yii;

class ImageController extends \yii\web\Controller
{

    public function actionGet($filename = '', $preset = 'original')
    {
        $webrootPath = Yii::getAlias('@webroot');

        $filename = Yii::getAlias($this->module->cachePath . '/original/' . $filename);

        $targetPath = \Yii::$app->image->createPath($filename, $preset, false, \Yii::$app->request->get('nocache'));

        if (strpos($targetPath, $webrootPath) !== false) {
            $targetPath = substr($targetPath, strlen($webrootPath));
        }

        return $this->redirect($targetPath);

    }

}