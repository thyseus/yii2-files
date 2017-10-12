<?php

namespace thyseus\files\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "file".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $offer_id
 *
 * @property Offer $offer
 */
class File extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'files';
    }

    public function __toString()
    {
        return $this->filename_path;
    }

    /**
     * Returns an link that downloads the file.
     * @param bool $raw 
     * @param string $caption optional: the caption for the link.
     * @return string
     */
    public function downloadLink($raw = false, $caption = false)
    {
        if (!$caption) {
            $innerHtml = '<span class="glyphicon glyphicon-download" aria-hidden="true"></span> ' . Yii::t('app', 'Download');
        } else {
            $innerHtml = $caption;
        }
        return Html::a($innerHtml, $this->downloadUrl($raw), ['data-pjax' => '0']);
    }

    public function downloadUrl($raw = false)
    {
        return Url::to(['//files/file/download', 'id' => $this->id, 'raw' => $raw]);
    }

    public function isImage()
    {
        return strpos($this->mimetype, 'image') !== false;
    }

    /**
     * Crop the image to the dimensions given in FileWebModule->crop_target_{width/height} using Imagine.
     * Thanks to http://www.yiiframework.com/wiki/859/how-to-resize-an-image-proportionally/
     */
    public function crop() {
        $imagine  = \yii\imagine\Image::getImagine();

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
            $startX = ( $width - $widthR ) / 2;
        }

        if ($heightR < $height) {
            $startY = ( $height - $heightR ) / 2;
        }

        $preserve
            ->paste($resized_image, new \Imagine\Image\Point($startX, $startY))
            ->save($this->filename_path, ['quality' => 100]);
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => date('Y-m-d G:i:s'),
            ],
            [
                'class' => BlameableBehavior::className(),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['public', 'status'], 'default', 'value' => 0],
            [['position'], 'default', 'value' => 1000],
            [['download_count'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 0],
            [['public', 'position', 'status', 'download_count'], 'integer'],
            [['filename_path', 'filename_user', 'model', 'target_id', 'target_url', 'mimetype'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('files', '#'),
            'created_by' => Yii::t('files', 'created by'),
            'updated_by' => Yii::t('files', 'updated by'),
            'created_at' => Yii::t('files', 'created at'),
            'updated_at' => Yii::t('files', 'updated at'),
            'public' => Yii::t('files', 'public'),
            'model' => Yii::t('files', 'model'),
            'target_id' => Yii::t('files', 'Target'),
            'filename_path' => Yii::t('files', 'filename_path'),
            'filename_user' => Yii::t('files', 'filename_user'),
            'mimetype' => Yii::t('files', 'File format'),
            'position' => Yii::t('files', 'Position'),
            'download_count' => Yii::t('files', 'Downloads'),
            'tags' => Yii::t('files', 'Tags'),
            'shared_with' => Yii::t('files', 'Shared with'),
            'display_shared_files' => Yii::t('files', 'Uploaded by'),
        ];
    }

    /**
     * Ensure that files are also removed physically from the hard drive when the option
     * is set in the module configuration
     */
    public function afterDelete()
    {
        if (Yii::$app->getModule('files')->deletePhysically) {
            unlink($this->filename_path);
        }

        parent::afterDelete();
    }

    /**
     * Deserialize shared_with column
     */
    public function afterFind()
    {
        $this->shared_with = explode(', ', $this->shared_with);
       return parent::afterFind();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(Yii::$app->getModule('files')->userModelClass, ['id' => 'created_by']);
    }

    /**
     * identifierAttribute is necessary e.g. for cases where the target model gets referenced by slug
     * @return \yii\db\ActiveQuery
     */
    public function getTarget()
    {
        if (!$this->model) {
            return null;
        }

        $targetClass = $this->model;

        $target = new $targetClass;

        $identifier_attribute = 'id';

        if (method_exists($target, 'identifierAttribute'))
            $identifier_attribute = $target->identifierAttribute();

        return $this->hasOne($targetClass::className(), [$identifier_attribute => 'target_id']);
    }
}
