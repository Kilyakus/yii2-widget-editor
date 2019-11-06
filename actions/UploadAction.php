<?php
namespace kilyakus\widget\redactor\actions;

use Yii;
use yii\base\Action;
use yii\web\UploadedFile;
use yii\web\Response;
use kilyakus\imageprocessor\Image;

class UploadAction extends Action
{
    public function run($dir = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fileInstance = UploadedFile::getInstanceByName('file');
        if ($fileInstance) {
            $file = Image::upload($fileInstance, $dir);
            if($file) {
                return $this->getResponse($file);
            }
        }
        return ['error' => 'Unable to save image file'];
    }

    private function getResponse($fileName)
    {
        return [
            'filelink' => $fileName,
            'filename' => basename($fileName)
        ];
    }
}