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