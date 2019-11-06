<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteBs3Asset extends AssetBundle
{
    /**
     * @inherit
     */
    public $sourcePath = '@kilyakus/widget/redactor/assets/dist';

    /**
     * @inherit
     */
    public $css = [
        'summernote.css',
    ];

    /**
     * @inherit
     */
    public $js = [
        'summernote.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'phpnt\fontAwesome\FontAwesomeAsset',
    ];
}