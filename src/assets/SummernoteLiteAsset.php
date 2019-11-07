<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteLiteAsset extends AssetBundle
{
    public $sourcePath = '@kilyakus/widget/redactor/assets/dist';

    public $css = [
        'summernote-lite.css',
    ];

    public $js = [
        'summernote-lite.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'phpnt\fontAwesome\FontAwesomeAsset',
    ];
}