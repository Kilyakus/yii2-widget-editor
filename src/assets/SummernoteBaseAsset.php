<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteBaseAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/lib/base';

        $this->css[] = 'css/base-summernote.css';
        $this->css[] = 'css/base-codemirror.css';

        parent::init();
    }
}
