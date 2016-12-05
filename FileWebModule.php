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

    public $uploadPath = null;


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
        /**
         * @var string Physical directory where the upload files should be saved. Make sure the folder exists.
         * by default the files get saved outside of the web/ directory for security reasons. If you want the files to
         * be public accessible, you can change the path here.
         */
        if(!$this->uploadPath)
            $this->uploadPath = Yii::$app->basePath. '/uploads';

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
