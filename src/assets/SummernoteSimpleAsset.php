<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteSimpleAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/lib/simple';

        $this->css[] = 'simple-summernote.css';

        parent::init();
    }
}
