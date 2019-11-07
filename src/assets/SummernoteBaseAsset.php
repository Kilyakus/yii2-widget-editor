<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteBaseAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/themes/base';

        $this->css[] = 'css/base-summernote.css';
        $this->css[] = 'css/base-codemirror.css';

        $this->js[] = 'js/base-summernote.js';
        $this->js[] = 'js/base-codemirror.js';

        parent::init();
    }
}
