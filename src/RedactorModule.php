<?php
namespace kilyakus\widget\redactor;

use yii\base\Module as BaseModule;

class RedactorModule extends BaseModule
{
    public $urlPrefix = 'redactor';

    public $dbConnection = 'db';

    public function getDb()
    {
        return \Yii::$app->get($this->dbConnection);
    }
}