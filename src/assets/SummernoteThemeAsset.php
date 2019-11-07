<?php
namespace kilyakus\widget\redactor\assets;

use yii\web\AssetBundle;

class SummernoteThemeAsset extends AssetBundle
{
	const THEME_BS3 = 'bs3';
    const THEME_BS4 = 'bs4';
    const THEME_LITE = 'lite';

    public function init()
    {
        $this->sourcePath = __DIR__ . '/themes';

        $this->css[] = 'addons/summernote-addon.css';
        $this->css[] = 'addons/codemirror-addon.css';

        $this->js[] = 'addons/summernote-addon.js';
        $this->js[] = 'addons/codemirror-addon.js';
    }

    public function define($theme, $scheme = null)
    {
        if ($theme == self::THEME_BS4) {

            $this->css[] = 'bs4/summernote-bs4.css';

        } else if ($theme == self::THEME_BS3 || $theme == self::THEME_LITE) {

            $this->css[] = 'bs3/summernote-bs3.css';

        }

        if($scheme && $theme != $scheme){

	        $this->css[] = 'schemes/summernote-' . $theme . '-' . $scheme . '.css';

	    }

        parent::init();
    }
}
