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

    public function downloadLink()
    {
        return Html::a(Yii::t('app', 'Download'),
            $this->downloadUrl(),
            ['data-pjax' => '0']);
    }

    public function downloadUrl()
    {
        return Url::to(['//files/file/download', 'id' => $this->id]);
    }

    public function isImage()
    {
        return strpos($this->mimetype, 'image') !== false;
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
            [['public'], 'default', 'value' => 0],
            [['public'], 'integer'],
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
        ];
    }

    public function afterDelete()
    {
        if (Yii::$app->getModule('files')->deletePhysically)
            unlink($this->filename_path);

        parent::afterDelete();
    }


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
        $targetClass = $this->model;

        $target = new $targetClass;

        $identifier_attribute = 'id';

        if (method_exists($target, 'identifierAttribute'))
            $identifier_attribute = $target->identifierAttribute();

        return $this->hasOne($targetClass::className(), [$identifier_attribute => 'target_id']);
    }
}
