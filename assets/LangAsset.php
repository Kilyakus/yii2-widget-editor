<?php
/**
 * User: Vladimir Baranov <phpnt@yandex.ru>
 * Git: <https://github.com/phpnt>
 * VK: <https://vk.com/phpnt>
 * Date: 26.01.2019
 * Time: 18:33
 */

namespace kilyakus\widget\redactor\assets;

use Yii;
use yii\web\AssetBundle;

class LangAsset extends AssetBundle
{
    /**
     * @inherit
     */
    public $sourcePath = '@kilyakus/widget/redactor/assets/dist/lang';

    public function init()
    {
        parent::init();

        if (Yii::$app->language != 'en') {
            $lang = Yii::$app->language . '-' . strtoupper(Yii::$app->language);
            $this->js = [
                'summernote-' . $lang . '.js'
            ];
        }
    }

    public $depends = [
        'yii\web\YiiAsset',
    ];
}