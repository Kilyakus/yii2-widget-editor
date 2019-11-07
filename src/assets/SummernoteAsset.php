<?php
namespace kilyakus\widget\redactor\assets;

use Yii;
use yii\web\AssetBundle;

class SummernoteAsset extends AssetBundle
{
    const THEME_BS3 = 'bs3';
    const THEME_BS4 = 'bs4';
    const THEME_LITE = 'lite';

    public $sourcePath = '@kilyakus/widget/redactor/assets/dist';

    public function define($theme, $i18n = true)
    {
        if ($theme == self::THEME_BS4) {

            $this->css[] = 'summernote-bs4.css';
            $this->js[] = 'summernote-bs4.js';

        } else if ($theme == self::THEME_BS3) {

            $this->css[] = 'summernote.css';
            $this->js[] = 'summernote.js';

        } else if ($theme == self::THEME_LITE) {

            $this->css[] = 'summernote-lite.css';
            $this->js[] = 'summernote-lite.js';

        }

        if ($i18n && Yii::$app->language != 'en') {
            $lang = Yii::$app->language . '-' . strtoupper(Yii::$app->language);
            $this->js[] = 'lang/summernote-' . $lang . '.js';
        }

        parent::init();
    }

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'kilyakus\flaticons\FlatIconsAsset',
        'kilyakus\fontawesome\FontAwesomeAsset',
    ];
}