<?php

namespace app\modules\imageapi\controllers;

use Yii;

class ImageController extends \yii\web\Controller
{

    public function actionGet($preset, $filename = '')
    {
        $standart_path = Yii::getAlias('@webroot/i/cat/photo/original');

        $file_path = $standart_path . '/' . $filename;

        $path = Yii::$app->image->createUrl($preset, $file_path);

        return $this->redirect($path);

    }

}