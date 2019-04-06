<?php

namespace mirocow\imagecache\components;

use mirocow\imagecache\contracts\handlerInterface;
use mirocow\imagecache\helpers\FilePathHelper;
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
    /**
     * @var bool 
     */
    public $disable = false;

    /**
     * @var bool
     */
    public $useOriginalName = true;

    /**
     * @var array
     */
    public $presets = [
        'original' => [
            'cachePath' => '@webroot/images/original',
        ],
    ];

    /**
     * @var array
     */
    public $allowedImageExtensions = ['*'];

    /**
     * @var string
     */
    public $webrootPath;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $chmodDir = 0777;

    /**
     * @var int
     */
    public $chmodFile = 0666;

    /**
     * @var null
     */
    private static $_matrix = null;

    /**
     * @inheritDoc
     */
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
     * @param $path
     * @return $this
     */
    public function setWebRootPath($path)
    {
        $this->webrootPath = $path;

        return $this;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        if(!$this->host) {
            if (!Yii::$app->request->isConsoleRequest) {
                $this->setHost(Yii::$app->request->getHostInfo());
            }
        }

        return $this->host;
    }

    /**
     * Basic function for create a derived image from a file using a preset declared in
     * configuration
     *
     * @param $file origin image file to create the derived image
     * @param $presetName Name of the preset declared in configuration under presets array
     * @see https://www.verot.net/php_class_upload_samples.htm
     * @example:
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
            $file = Yii::getAlias('@vendor/mirocow/yii2-imagecache/assets/no_image_available.png');
        }

        if(!$this->webrootPath) {
            $this->webrootPath = '@webroot';
        }

        $this->webrootPath = Yii::getAlias($this->webrootPath);

        // If absolute url
        $urlInfo = parse_url($file);
        if(!empty($urlInfo['path'])){
            $file = $urlInfo['path'];
        }

        $pathToFile = $this->webrootPath . $file;

        if(!file_exists($pathToFile)){
            if(file_exists($file)){
                $pathToFile = $file;
            }
        }

        $targetPath = $this->createPath($pathToFile, $presetName, true);

        if (strpos($targetPath, $this->webrootPath) !== false) {
            $targetPath = substr($targetPath, strlen($this->webrootPath));
        }

        return $targetPath;
    }

    /**
     * Basic function for create a derived image from a file using a preset declared in
     * configuration
     *
     * @param $file origin image file to create the derived image
     * @param $presetName Name of the preset declared in configuration under presets array
     * @param $force Without image cache
     * @see https://www.verot.net/php_class_upload_samples.htm
     * @example:
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
    public function createPath($file, $presetName = 'original', $onlyReturnPath = false, $force = false)
    {
        if($this->disable){
            return $file;
        }

        if (!isset($this->presets[$presetName])) {
            return false;
        }

        $preset = $this->presets[$presetName];

        if (isset($preset['actions']['image_convert'])) {
            $pathinfo = pathinfo($file);
            $extension = pathinfo($pathinfo['filename'], PATHINFO_EXTENSION);

            // Prepare origin file
            if(!empty($extension) && $this->isAllowedToConvertExtension($extension) && $extension <> $preset['actions']['image_convert']) {
                $file = str_replace('.' . $preset['actions']['image_convert'], '', $file);
            }
        }

        $originalFile = $this->createOriginImage($file);

        if (!file_exists($originalFile)) {
            $originalFile = Yii::getAlias('@vendor/mirocow/yii2-imagecache/assets/no_image_available.png');
        }

        if ($preset) {

            $basename = basename($originalFile);
            $targetPath = Yii::getAlias($preset['cachePath']);
            $targetFile = $targetPath . '/' . $basename;

            // Add new extension
            if (isset($preset['actions']['image_convert'])) {
                $pathinfo = pathinfo($basename);
                if($pathinfo['extension'] <> $preset['actions']['image_convert']) {
                    $targetFile = $targetPath . DIRECTORY_SEPARATOR . $basename . '.' . $preset['actions']['image_convert'];
                }
            }

            if (!$force && $onlyReturnPath && file_exists($originalFile)) {

                return $targetFile;

            } else {

                if (isset($preset['actions'])) {

                    if (isset($preset['actions']['image_increase']) && $preset['actions']['image_increase'] === false) {
                        $size = self::getSize($originalFile);
                        if ($size[0] < $preset['actions']['image_x'] || $size[1] < $preset['actions']['image_y']) {
                            $preset['actions']['image_resize'] = false;
                        }
                    }

                    $this->runHandler($preset, $originalFile, $targetFile, $targetPath);
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
     * @see https://www.verot.net/php_class_upload_samples.htm
     * @example:
     * <pre>
     *    '640x480' => [
     *      'cachePath' => '@webroot/images/640x480',
     *      'actions' => ['image_x' => 640, 'image_y' => 480, 'image_ratio_crop' => true],
     *    ],
     * </pre>
     */
    public function createAbsoluteUrl($file, $presetName = 'original')
    {
        return $this->getHost() . $this->createUrl($file, $presetName);
    }

    /**
     * @param $preset
     * @param $srcPath
     * @param $targetFile
     * @param string $targetPath
     * @return mixed
     * @throws \Exception
     */
    protected function runHandler($preset, $srcPath, $targetFile, $targetPath = '')
    {
        if(empty($preset['hadler'])){
            $preset['hadler'] = \mirocow\imagecache\components\handlers\classUploadHandler::class;
        }

        /** @var handlerInterface $handler */
        $handler = new $preset['hadler'];

        if(!($handler instanceof handlerInterface)){
            throw new \Exception();
        }

        if(!file_exists($targetPath)){
            mkdir($targetPath, (int) $this->chmodDir, true);
        }

        $handler->preset = $preset;
        $handler->targetPath = $targetPath;

        $handler->runHandler($srcPath, $targetFile);
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
            $text = preg_replace('~'.$from.'~iu', $to, $text);
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
     * @throws Exception
     */
    private function createOriginImage($source)
    {
        if(!file_exists($source)){
            $source = FilePathHelper::getAbsolutePath($source);
        }

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

        $extension = strtolower($file_info[ 'extension' ]);

        if($this->allowedImageExtensions <> '*') {
            if (!in_array($extension, $this->allowedImageExtensions)) {
                throw new Exception('This extension is not allowed');
            }
        }

        $targetPath = Yii::getAlias($preset['cachePath']);

        if($this->useOriginalName) {
            $file_name = self::cyrillicToLatin($file_info[ 'filename' ]);
            $file_name = str_replace([' ', '-'], ['_', '_'], $file_name);
            $file_name = preg_replace('/[^A-Za-z0-9_]/', '', $file_name);
        } else {
            throw new Exception('Not yet implemented');
        }

        if (file_exists($file_info[ 'dirname' ].'/'.$file_name.'.'.$extension)) {
            $file_name = $file_name.'-'.time();
        }

        $targetFile = $targetPath . '/' . $file_name . '.' . $extension;
        $targetFile = FilePathHelper::getAbsolutePath($targetFile);
        
        if(!file_exists($targetFile)) {
            $targetPath = dirname($targetFile);
            if (!file_exists($targetPath)) {
                mkdir($targetPath, (int) $this->chmodDir, true);
            }

            copy($source, $targetFile);
            chmod($targetFile, (int) $this->chmodFile);
        }

        return $targetFile;
    }

    /**
     * @param $extension
     *
     * @return bool
     */
    private function isAllowedToConvertExtension($extension)
    {
        return in_array($extension, [
            'png',
            'jpg',
            'jpeg',
        ]);
    }

}
