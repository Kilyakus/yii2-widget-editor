<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class CodeMirrorAsset extends AssetBundle
{
    /**
     * @inherit
     */
    public $css = [
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.css',
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/theme/monokai.css',
    ];

    /**
     * @inherit
     */
    public $js = [
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.js',
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/xml/xml.js',
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/2.36.0/formatting.js',
    ];

    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];

    public $depends = [
        'yii\web\YiiAsset',
        'kartik\editors\assets\CodemirrorAsset'
    ];
}