<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteBs3Asset extends AssetBundle
{
    public $sourcePath = '@kilyakus/widget/redactor/assets/dist';

    public $css = [
        'summernote.css',
    ];

    public $js = [
        'summernote.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'kilyakus\fontawesome\FontAwesomeAsset',
    ];
}