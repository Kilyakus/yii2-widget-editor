<?php
namespace kilyakus\widget\redactor\assets;

use kilyakus\widgets\AssetBundle;

class BaseAsset extends AssetBundle
{
    protected function setAssetFile($ext, $file)
    {
        $this->$ext[] = YII_DEBUG ? "{$file}.{$ext}" : "{$file}.min.{$ext}";
        return $this;
    }
}
