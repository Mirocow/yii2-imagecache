# yii2-imagecache

# Install

# Configuration

## Nginx

```
server {
   location ~ /images/\w+/.*?\.(png|jpg|jpeg|gif) {
       # Redirect everything that isn't a real file to index.php
       try_files $uri $uri/ /index.php$is_args$args;
   }
}
```

## Yii2 config

```php
return [
    'components' => [
        'urlManager' => [
            'baseUrl' => 'http://site.com',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            //'cache' => 'cache',
            'rules' => [
                'GET /images/<preset:\w+>/<filename:.*?>' => 'mirocow/imagecache',
            ],
        ],
        'image' => [
            'class' => 'mirocow\imagecache\components\Image',
            'presets' => [
		        '200x200' => [
		            'cachePath'=>'@webroot/images/200x200',
		            'actions'=>[
	                'image_x' => 200,
	                'image_y' => 200,
	                'image_ratio_crop' => true,
	                'image_resize' => true,
	                //'image_convert' => 'png',
	            ],
            ],
        ],
    ],
    'modules' => [
        'imagecache' => [
            'class' => 'mirocow\imagecache\Module',
        ],
    ],
];
```

### Presets

### Libraries

* https://www.verot.net/php_class_upload_samples.htm - has handler
* http://image.intervention.io - has handler
* https://phpimageworkshop.com/
* http://nielse63.github.io/php-image-cache/#usage
* http://glide.thephpleague.com/
* https://github.com/claviska/SimpleImage
* https://imagine.readthedocs.io/en/latest/index.html
* https://kosinix.github.io/grafika/
* https://github.com/Gregwar/Image - has handler
* https://github.com/Treinetic/ImageArtist