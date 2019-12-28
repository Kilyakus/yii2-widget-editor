<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class CodeMirrorAsset extends AssetBundle
{
    public $css = [
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/5.48.4/codemirror.css',
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/5.48.4/theme/monokai.css',
    ];

    public $js = [
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/5.48.4/codemirror.js',
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/5.48.4/mode/xml/xml.js',
        '//cdnjs.cloudflare.com/ajax/libs/codemirror/2.36.0/formatting.js',
    ];

    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];

    public $depends = [
        'yii\web\JqueryAsset'
    ];
}