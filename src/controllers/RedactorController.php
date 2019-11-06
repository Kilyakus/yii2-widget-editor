<?php
namespace kilyakus\widget\redactor\controllers;

/*
    Just use:

    extends \kilyakus\widget\redactor\controllers\RedactorController

    or add

    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'upload' => [
                'class' => 'kilyakus\widget\redactor\actions\UploadAction',
            ],
        ];
    }

    to your controller.

*/

class RedactorController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'upload' => [
                'class' => 'kilyakus\widget\redactor\actions\UploadAction',
            ],
        ];
    }
}