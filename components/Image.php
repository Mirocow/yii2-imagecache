<?php

namespace mirocow\imagecache\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\web\NotFoundHttpException;

/**
 * Class Image
 * @package app\modules\imageapi\components
 *
 * @see https://www.verot.net/php_class_upload_samples.htm
 */
class Image extends Component
{

    public $presets = [
        'original' => [
            'cachePath' => '@webroot/images/original',
        ],
    ];
    
    public $allowedImageExtensions = ['*.*'];
    
    private static $_matrix = null;

    public function init()
    {
        self::$_matrix = [
            "й" => "i",
            "ц" => "c",
            "у" => "u",
            "к" => "k",
            "е" => "e",
            "н" => "n",
            "г" => "g",
            "ш" => "sh",
            "щ" => "shch",
            "з" => "z",
            "х" => "h",
            "ъ" => "",
            "ф" => "f",
            "ы" => "y",
            "в" => "v",
            "а" => "a",
            "п" => "p",
            "р" => "r",
            "о" => "o",
            "л" => "l",
            "д" => "d",
            "ж" => "zh",
            "э" => "e",
            "ё" => "e",
            "я" => "ya",
            "ч" => "ch",
            "с" => "s",
            "м" => "m",
            "и" => "i",
            "т" => "t",
            "ь" => "",
            "б" => "b",
            "ю" => "yu",
            "Й" => "I",
            "Ц" => "C",
            "У" => "U",
            "К" => "K",
            "Е" => "E",
            "Н" => "N",
            "Г" => "G",
            "Ш" => "SH",
            "Щ" => "SHCH",
            "З" => "Z",
            "Х" => "X",
            "Ъ" => "",
            "Ф" => "F",
            "Ы" => "Y",
            "В" => "V",
            "А" => "A",
            "П" => "P",
            "Р" => "R",
            "О" => "O",
            "Л" => "L",
            "Д" => "D",
            "Ж" => "ZH",
            "Э" => "E",
            "Ё" => "E",
            "Я" => "YA",
            "Ч" => "CH",
            "С" => "S",
            "М" => "M",
            "И" => "I",
            "Т" => "T",
            "Ь" => "",
            "Б" => "B",
            "Ю" => "YU",
            "«" => "",
            "»" => "",
            " " => "-",
            "\"" => "",
            "\." => "",
            "–" => "-",
            "\," => "",
            "\(" => "",
            "\)" => "",
            "\?" => "",
            "\!" => "",
            "\:" => "",
            '#' => '',
            '№' => '',
            ' - ' => '-',
            '/' => '-',
            '  ' => '-',
        ];

        if(!isset($this->presets['original'])){
            $this->presets['original'] = [
                'cachePath' => '@webroot/images/original',
            ];
        }
        
        parent::init();
    }

    /**
     * Basic function for create a derived image from a file using a preset declared in
     * configuration
     *
     * @param $file origin image file to create the derived image
     * @param $presetName Name of the preset declared in configuration under presets array
     * @see https://www.verot.net/php_class_upload_samples.htm
     * in the way:
     * <pre>
     *    '640x480' => [
     *      'cachePath' => '@webroot/images/640x480',
     *      'actions' => ['image_x' => 640, 'image_y' => 480, 'image_ratio_crop' => true],
     *    ],
     * </pre>
     *
     * @return the URL to the cached derived image (if it does not exist it'll be generated
     *   transparently)
     */
    public function createUrl($file, $presetName = 'original')
    {
        if(!$file){
            throw new NotFoundHttpException(Yii::t('app',  'File {name} not foud', ['name' => $file]));
        }

        $webrootPath = Yii::getAlias('@webroot');

        $targetPath = $this->createPath($webrootPath . $file, $presetName, true);

        if (strpos($targetPath, $webrootPath) !== false) {
            $targetPath = substr($targetPath, strlen($webrootPath));
        }

        return $targetPath;
    }

    /**
     * Basic function for create a derived image from a file using a preset declared in
     * configuration
     *
     * @param $file origin image file to create the derived image
     * @param $presetName Name of the preset declared in configuration under presets array
     * @see https://www.verot.net/php_class_upload_samples.htm
     * in the way:
     * <pre>
     *    '640x480' => [
     *      'cachePath' => '@webroot/images/640x480',
     *      'actions' => ['image_x' => 640, 'image_y' => 480, 'image_ratio_crop' => true],
     *    ],
     * </pre>
     *
     * @return the path to the cached derived image (if it does not exist it'll be generated
     *   transparently)
     */
    public function createPath($file, $presetName = 'original', $onlyReturnPath = false)
    {

        if (!isset($this->presets[$presetName])) {
            return false;
        }

        if (!file_exists($file)) {
            //return false;
            $file = Yii::getAlias('@vendor/mirocow/yii2-imagecache/assets/no_image_available.png');
        }

        $preset = $this->presets[$presetName];

        $originalFile = $this->createImage($file);

        if (isset($preset)) {

            $basename = basename($originalFile);
            $targetPath = Yii::getAlias($preset['cachePath']);

            if (isset($preset['actions']['image_convert'])) {
                $basename = pathinfo($basename, PATHINFO_FILENAME) . '.' . $preset['actions']['image_convert'];
            }

            $targetFile = $targetPath . '/' . $basename;

            if ($onlyReturnPath && file_exists($originalFile)) {

                return $targetFile;

            } else {

                if (isset($preset['actions'])) {

                    if (isset($preset['actions']['image_increase']) && $preset['actions']['image_increase'] === false) {
                        $size = self::getSize($originalFile);
                        if ($size[0] < $preset['actions']['image_x'] || $size[1] < $preset['actions']['image_y']) {
                            $preset['actions']['image_resize'] = false;
                        }
                    }

                    $this->createHandle($preset, $originalFile, $targetPath);
                } else {
                    copy($originalFile, $targetFile);
                }

                if (file_exists($targetFile)) {
                    chmod($targetFile, 0666);
                    return $targetFile;
                } else {
                    return false;
                }
            }

        } else {
            return false;
        }
    }

    /**
     * @param $presetName
     * @param $file
     * @param array $options
     * @return string
     */
    public function createAbsoluteUrl($file, $presetName = 'original', $options = array())
    {
        return Yii::$app->request->getHostInfo() . $this->createUrl($file, $presetName, $options);
    }

    /**
     * @param $preset
     * @param $srcPath
     * @param string $targetPath
     * @return object
     */
    protected function createHandle($preset, $srcPath, $targetPath = '')
    {
        
        $handle = new \upload($srcPath);
        $handle->file_safe_name = false;
        $handle->file_overwrite = true;
        $handle->file_auto_rename = false;

        if (isset($preset['actions']['image_watermark_path']) && isset($preset['actions']['image_watermark'])) {
            $preset['actions']['image_watermark'] = Yii::getAlias($preset['actions']['image_watermark_path']) . DIRECTORY_SEPARATOR . $preset['actions']['image_watermark'];
        }

        if (isset($preset['actions'])) {
            foreach ($preset['actions'] as $action => $params) {
                $handle->$action = $params;
            }
        }

        if ($targetPath) {
            $handle->process($targetPath);
            $this->createIgnoreFile($targetPath);
            if ($handle->processed) {
                $handle->file_dst_pathname;
            } else {
                throw new Exception($handle->error);
            }
        }

        return $handle;

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

    /**
     * @param $text
     * @param bool $toLowCase
     * @param int $maxlength
     * @return false|string
     */
    private static function cyrillicToLatin(
        $text,
        $toLowCase = true,
        $maxlength = 100
    )
    {
        $text = implode(array_slice(explode('<br>',
            wordwrap(trim(strip_tags(html_entity_decode($text))),
                $maxlength, '<br>', false)), 0, 1));

        foreach (self::$_matrix as $from => $to) {
            $text = mb_eregi_replace($from, $to, $text);
        }

        // Optionally convert to lower case.
        if ($toLowCase) {
            $text = strtolower($text);
        }

        return $text;
    }

    /**
     * @param $source
     * @return bool|string
     */
    private function createImage($source)
    {
        if (!file_exists($source)) {
            return false;
        }

        $file_name = basename($source);

        $preset = $this->presets['original'];

        if (!isset($preset['cachePath'])) {
            return false;
        }

        $file_info = pathinfo($file_name);

        if (!(isset($file_info['filename']) && isset($file_info['extension']))) {
            return false;
        }

        $file_name = self::cyrillicToLatin($file_info['filename']);
        $file_name = str_replace(array(' ', '-'), array('_', '_'), $file_name);
        $file_name = preg_replace('/[^A-Za-z0-9_]/', '', $file_name);
        $extension = strtolower($file_info['extension']);

        if (file_exists($file_info['dirname'] . '/' . $file_name . '.' . $extension)) {
            $file_name = $file_name . '-' . time();
        }

        $targetPath = Yii::getAlias($preset['cachePath']);
        $targetFile = $targetPath . '/' . $file_name . '.' . $extension;

        if(!file_exists($targetFile)) {
            if (!file_exists($targetPath)) {
                mkdir($targetPath, 0777, true);
                $this->createIgnoreFile($targetPath);
            }

            copy($source, $targetFile);
            chmod($targetFile, 0666);
        }

        return $targetFile;
    }

    /**
     * @param $targetPath
     */
    private function createIgnoreFile($targetPath)
    {
        $ignoreData = "*\n!.gitignore";
        file_put_contents($targetPath.'/.gitignore', $ignoreData);
    }

}