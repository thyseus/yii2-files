<?php

namespace thyseus\files\models;

/**
 * Trait CropTrait
 *
 * Used in thyseus\files\models\File.php
 *
 * @package thyseus\files\models
 */
trait CropTrait
{
    /**
     * Crop the image to the dimensions given in FileWebModule->crop_target_{width/height} using Imagine.
     * Thanks to http://www.yiiframework.com/wiki/859/how-to-resize-an-image-proportionally/
     */
    public function crop()
    {
        $imagine = \yii\imagine\Image::getImagine();

        $width = Yii::$app->getModule('files')->crop_target_width;
        $height = Yii::$app->getModule('files')->crop_target_height;

        $image = $imagine->open($this->filename_path);

        $size = new \Imagine\Image\Box($width, $height);
        $resized_image = $image->thumbnail($size, \Imagine\Image\ImageInterface::THUMBNAIL_INSET);

        $sizeR = $resized_image->getSize();
        $widthR = $sizeR->getWidth();
        $heightR = $sizeR->getHeight();
        $preserve = $imagine->create($size);

        $startX = $startY = 0;

        if ($widthR < $width) {
            $startX = ($width - $widthR) / 2;
        }

        if ($heightR < $height) {
            $startY = ($height - $heightR) / 2;
        }

        $preserve
            ->paste($resized_image, new \Imagine\Image\Point($startX, $startY))
            ->save($this->filename_path, ['quality' => 100]);
    }

}