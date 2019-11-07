<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class EmojiAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist/emoji';

        $this->js[] = 'js/summernote-emoji.js';

        parent::init();
    }
}
