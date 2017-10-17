<?php

namespace thyseus\files;

use Yii;
use yii\i18n\PhpMessageSource;

/**
 * File module definition class
 */
class FileWebModule extends \yii\base\Module
{
    public $version = '0.3.0';

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

    /**
     * @var string Callback that defines which users choosable to share files with.
     *
     * For example, to allow only username foo and bar, do this:
     *
     * 'shareableUsersCallback' => function ($users) {
     *    return array_filter($users, function ($user) {
     *      return !in_array($user->username, ['foo', 'bar']); // or !$user->isAdmin()
     *    });
     *  },
     *
     */
    public $shareableUsersCallback = null;

    /**
     * @var array fill this array to let users tag their file with some of these options. For example:
     *
     * 'possibleTags' => [
     *      'logo' => 'Logo',
     *      'screenshot' => 'Screenshot',
     * ],
     * The key is stored in the database, the value is translated via Yii::t('app', as caption for the Tag.
     */
    public $possibleTags = [];

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
