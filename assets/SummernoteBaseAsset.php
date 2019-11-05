<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteBaseAsset extends AssetBundle
{
    public $depends = [
        'kartik\editors\assets\CodemirrorFormatterAsset'
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/lib/base';

        $this->css[] = 'css/base-summernote.css';

        parent::init();
    }
}
