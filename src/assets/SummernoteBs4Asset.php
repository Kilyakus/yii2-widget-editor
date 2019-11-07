<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteBs4Asset extends AssetBundle
{
    public $sourcePath = '@kilyakus/widget/redactor/assets/dist';

    public $css = [
        'summernote-bs4.css',
    ];

    public $js = [
        'summernote-bs4.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'kilyakus\fontawesome\FontAwesomeAsset',
    ];
}
