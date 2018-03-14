<?php

namespace thyseus\files\models;

use app\models\User;
use thyseus\files\events\ShareWithUserEvent;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
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
    const EVENT_BEFORE_SHARE_WITH_USER = 'before_share_with_user';
    const EVENT_AFTER_SHARE_WITH_USER = 'after_share_with_user';

    /**
     * @var $content in order to generate the md5 sum of the file the content is temporarily saved here
     */
    public $content;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{files}}';
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
        return Url::to(['//files/file/download', 'id' => $this->slug, 'raw' => $raw], true);
    }

    public function isImage()
    {
        return strpos($this->mimetype, 'image') !== false;
    }

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

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => date('Y-m-d G:i:s'),
            ],
            [
                'class' => BlameableBehavior::className(),
            ],
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'filename_user',
                'ensureUnique' => true,
                'immutable' => true,
            ],
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

            [['tags', 'content'], 'safe'],
            [['public', 'position', 'status', 'download_count', 'created_by'], 'integer'],
            [['filename_path', 'filename_user', 'model', 'target_id', 'target_url', 'mimetype'], 'string'],
            [['checksum'], 'required'],
            [['checksum'], 'string', 'max' => 32],
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
            'target_url' => Yii::t('files', 'Target Url'),
            'filename_path' => Yii::t('files', 'filename_path'),
            'filename_user' => Yii::t('files', 'filename_user'),
            'mimetype' => Yii::t('files', 'File format'),
            'position' => Yii::t('files', 'Position'),
            'download_count' => Yii::t('files', 'Downloads'),
            'tags' => Yii::t('files', 'Tags'),
            'shared_with' => Yii::t('files', 'Shared with'),
            'display_shared_files' => Yii::t('files', 'Uploaded by'),
            'checksum' => Yii::t('files', 'Checksum'),
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

    public function addShareWith($username)
    {
        $recipient = User::find()->where(['username' => $username])->one();

        if (!$recipient) {
            throw new NotFoundHttpException(Yii::t('files', 'User can not be found'));
        }

        $sharedWith = $this->shared_with;
        $sharedWith[] = $username;
        $sharedWith = array_unique($sharedWith);

        $this->updateAttributes(['shared_with' => implode(', ', $sharedWith)]);

        $event = new ShareWithUserEvent;
        $event->sharedFrom = Yii::$app->user->identity;
        $event->sharedWith = $recipient;
        $event->sharedFile = $this;
        $event->add = 1;
        $this->trigger(self::EVENT_AFTER_SHARE_WITH_USER, $event);
    }

    public function removeShareWith($username)
    {
        $recipient = User::find()->where(['username' => $username])->one();

        if (!$recipient) {
            throw new NotFoundHttpException(Yii::t('files', 'User can not be found'));
        }

        $sharedWith = $this->shared_with;

        if (($key = array_search($username, $sharedWith)) !== false) {
            unset($sharedWith[$key]);
        }

        $sharedWith = array_unique($sharedWith);

        $this->updateAttributes(['shared_with' => implode(', ', $sharedWith)]);

        $event = new ShareWithUserEvent;
        $event->sharedFrom = Yii::$app->user->identity;
        $event->sharedWith = $recipient;
        $event->sharedFile = $this;
        $event->add = 0;
        $this->trigger(self::EVENT_AFTER_SHARE_WITH_USER, $event);
    }

    /**
     * Deserialize shared_with column
     */
    public function afterFind()
    {
        $this->shared_with = explode(', ', $this->shared_with);
        $this->tags = explode(', ', $this->tags);
        return parent::afterFind();
    }

    public function beforeValidate()
    {
        $this->handleSerializableFields();
        $this->createChecksum();
        return parent::beforeValidate();
    }

    public function getTagsFormatted()
    {
        if (!is_array($this->tags)) {
            $this->tags = explode(', ', $this->tags);
        }

        $output = '';
        foreach ($this->tags as $tag) {
            $output .= Yii::t('app', Yii::$app->getModule('files')->possibleTags[$tag] ?? $tag) . ', ';
        }

        if ($output) {
            $output = substr($output, 0, -2);
        }

        return $output;
    }


    /**
     * @return bool if the file is valid; always true if the check is being skipped
     */
    public function proofChecksum()
    {
        if (Yii::$app->getModule('files')->skipChecksumIntegrity) {
            return true;
        }

        return $this->checksum == md5(file_get_contents($this->filename_path));
    }

    protected function createChecksum()
    {
        $this->checksum = md5($this->content);
    }

    protected function handleSerializableFields()
    {
        if (is_array($this->shared_with)) {
            $this->shared_with = implode(', ', $this->shared_with);
        }

        if (is_array($this->tags)) {
            $this->tags = implode(', ', $this->tags);
        }

    }

    /**
     * serialize shared_with column
     */
    public function beforeDelete()
    {
        $this->handleSerializableFields();

        return parent::beforeDelete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(Yii::$app->getModule('files')->userModelClass, ['id' => 'created_by']);
    }

    /**
     * Take all tags that are defined in Yii::$app->getModule('files')->possibleTags and translate the values.
     * @return array the translated tags
     */
    public static function possibleTagsTranslated()
    {
        $tags = [];
        foreach (Yii::$app->getModule('files')->possibleTags as $key => $value) {
            $tags[$key] = Yii::t('app', $value);
        }

        return $tags;
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

        if (method_exists($target, 'identifierAttribute')) {
            $identifier_attribute = $target->identifierAttribute();
        }

        return $this->hasOne($targetClass::className(), [$identifier_attribute => 'target_id']);
    }

    public function isDeleteable()
    {
        $allowDeletion = Yii::$app->getModule('files')->allowDeletion;

        if ($allowDeletion === true) {
            return true;
        }

        if ($allowDeletion === false) {
            return false;
        }

        if (is_callable($allowDeletion)) {
            return call_user_func($allowDeletion, $this) === true;
        }

        return $allowDeletion;
    }

    /**
     * Checks the mimeType of the $file against the list in the [[mimeTypes]] property.
     * borrowed from: https://github.com/yiisoft/yii2/blob/master/framework/validators/FileValidator.php#L479
     *
     * @param string $tempName
     * @return bool whether the $file mimeType is allowed
     * @throws \yii\base\InvalidConfigException
     */
    public static function validateMimeType($tempName, $mimeTypes)
    {
        $fileMimeType = FileHelper::getMimeType($tempName);

        foreach ($mimeTypes as $mimeType) {
            if ($mimeType === $fileMimeType) {
                return true;
            }

            $regexp =  '/^' . str_replace('\*', '.*', preg_quote($mimeType, '/')) . '$/';

            if (strpos($mimeType, '*') !== false && preg_match($regexp, $fileMimeType)) {
                return true;
            }
        }
        return false;
    }
}
