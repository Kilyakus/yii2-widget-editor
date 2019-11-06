<?php
namespace kilyakus\widget\redactor\assets;

class EmojiAsset extends BaseAsset
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/lib/emoji');
        $this->setupAssets('js', ['js/summernote-emoji']);
        parent::init();
    }
}
