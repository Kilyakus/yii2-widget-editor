<?php
namespace kilyakus\widget\redactor\controllers;

/*
    How to use:

    add this to your config:

----------------------------------------------------------------------------

    'redactor' => [
        'class' => 'kilyakus\widget\redactor\RedactorModule',
    ],

----------------------------------------------------------------------------

    and use "/redactor/upload/image" url for upload images, or add

----------------------------------------------------------------------------

    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'images (or upload as more like)' => [
                'class' => 'kilyakus\widget\redactor\actions\UploadAction',
            ],
        ];
    }

----------------------------------------------------------------------------

    to your controller.




    Or just use:

----------------------------------------------------------------------------

    extends \kilyakus\widget\redactor\controllers\RedactorController

----------------------------------------------------------------------------

    if u like crutches.

*/

class UploadController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'images' => [
                'class' => 'kilyakus\widget\redactor\actions\UploadAction',
            ],
        ];
    }
}