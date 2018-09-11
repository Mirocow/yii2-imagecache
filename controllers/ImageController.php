<?php

namespace mirocow\imagecache\controllers;

use Yii;
use yii\helpers\FileHelper;

class ImageController extends \yii\web\Controller
{

    public function actionGet($filename = '', $preset = 'original')
    {

        $webrootPath = Yii::getAlias('@webroot');

        $filename = Yii::getAlias($this->module->cachePath . '/original/' . $filename);

        $targetPath = \Yii::$app->image->createPath($filename, $preset, false, \Yii::$app->request->get('nocache'));

        $mimeType = FileHelper::getMimeTypeByExtension($targetPath);

        if (strpos($targetPath, $webrootPath) !== false) {
            $targetPath = substr($targetPath, strlen($webrootPath));
        }

        $response = \Yii::$app->response;
        $response->format = yii\web\Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', $mimeType);

        return $this->redirect($targetPath);

    }

}