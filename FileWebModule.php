<?php

namespace thyseus\files;

use Yii;
use yii\i18n\PhpMessageSource;

/**
 * File module definition class
 */
class FileWebModule extends \yii\base\Module
{
    public $version = '0.1.0-dev';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'thyseus\files\controllers';

    public $defaultRoute = 'files\files\index';

    /**
     * @var string The class of the User Model inside the application this module is attached to
     */
    public $userModelClass = 'app\models\User';

    /**
     * @var boolean Should the file also be deleted physically on removal ?
     */
    public $deletePhysically = false;

    /**
     * @var string Url to upload files.
     */
    public $uploadUrl = ['/files/file/upload'];

    /**
     * @var string Physical directory where the upload files should be saved. Make sure the folder exists.
     * by default (value null) the files get saved outside of the web/ directory for security reasons.
     * This default is Yii::$app->basePath . '/uploads'
     * If you want ALL files to be public accessible, you can change the path here.
     */
    public $uploadPath = null;

    /**
     * @var int Which target size should the cropped images be? Set both values to null to disable cropping.
     */
    public $crop_target_width = 128;
    public $crop_target_height = 128;

    /**
     * @var array options for the cropper. Make sure to keep 'modal' => false, since we do not use the
     * modal feature of the demicropper.
     */
    public $cropperOptions = [
        'modal' => false,
    ];


    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        'files/update/<id>' => 'files/files/update',
        'files/delete/<id>' => 'files/files/delete',
        'files/<id>' => 'files/files/view',
        'files/index' => 'files/files/index',
        'files/create' => 'files/files/create',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->uploadPath) {
            $this->uploadPath = Yii::$app->basePath . '/uploads';
        }

        if (!isset(Yii::$app->get('i18n')->translations['files*'])) {
            Yii::$app->get('i18n')->translations['files*'] = [
                'class' => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US'
            ];
        }
        parent::init();
    }
}
