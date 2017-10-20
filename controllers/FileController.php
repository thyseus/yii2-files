<?php

namespace thyseus\files\controllers;

use app\models\User;
use thyseus\files\events\FileUploadEvent;
use thyseus\files\events\ShareWithUserEvent;
use thyseus\files\FileWebModule;
use thyseus\files\models\File;
use thyseus\files\models\FileSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * FileController implements all actions for the yii2-files module.
 */
class FileController extends Controller
{
    // Event definitions:
    const EVENT_BEFORE_PROTECT = 'before_protect';
    const EVENT_AFTER_PROTECT = 'after_protect';

    const EVENT_BEFORE_UPLOAD = 'before_upload';
    const EVENT_AFTER_UPLOAD = 'after_upload';

    const EVENT_BEFORE_DELETE = 'before_upload';
    const EVENT_AFTER_DELETE = 'after_upload';

    const EVENT_BEFORE_PUBLISH = 'before_publish';
    const EVENT_AFTER_PUBLISH = 'after_publish';

    const EVENT_BEFORE_CROP = 'before_crop';
    const EVENT_AFTER_CROP = 'after_crop';

    const EVENT_BEFORE_SHARE_WITH_USER = 'before_share_with_user';
    const EVENT_AFTER_SHARE_WITH_USER = 'after_share_with_user';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['download'], // Files are potentially public
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'delete', 'upload', 'upload-raw',
                            'download', 'protect', 'publish', 'crop', 'move', 'share-with-user'],
                        'roles' => ['@'],
                    ],
                ],
            ],

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'share_with_user' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Receive raw data and write to file. Usually used with the cropping function.
     * Thanks to drew010´s answer on
     * http://stackoverflow.com/questions/11511511/how-to-save-a-png-image-server-side-from-a-base64-data-string
     *
     * @param $id id of the file to be written into
     * @throws BadRequestHttpException if raw-data is not set properly
     * @throws ForbiddenHttpException if currently logged in user is not the owner of the file
     */
    public function actionUploadRaw($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->user->id != $model->created_by && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException;
        }

        if (!isset($_POST['raw-data'])) {
            throw new BadRequestHttpException('Raw data is not given properly');
        }

        file_put_contents($model->filename_path, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['raw-data'])));
    }

    /**
     * Finds the File model based on its slug or its id.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id (slug or auto increment value)
     * @return File the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = File::findOne(['slug' => $id])) !== null) {
            return $model;
        } else if (($model = File::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('files', 'The requested file does not exist.'));
        }
    }

    /**
     * Changes the position of a file by direction (up or down)
     * @return mixed
     */
    public function actionMove($id, $dir, $inc = 1)
    {
        $model = $this->findModel($id);

        if ($dir == 'up') {
            $inc = -1 * abs($inc);
        }

        $model->updateCounters(['position' => $inc]);

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Lists all Files of the currently logged in user.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Yii::$app->user->setReturnUrl(['//files/file/index']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Allow users to crop their uploaded images. Uses https://github.com/demisang/yii2-cropper
     * @return mixed
     */
    public function actionCrop($id, $x = null, $y = null, $width = null, $height = null)
    {
        $this->trigger(self::EVENT_BEFORE_CROP);

        $model = $this->findModel($id);

        if (Yii::$app->user->id != $model->created_by && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException;
        }

        $this->trigger(self::EVENT_BEFORE_CROP);

        return $this->render('crop', ['model' => $model]);
    }

    /**
     * Publishes a File (sets public to true)
     * @return mixed
     */
    public function actionPublish($id)
    {
        $this->trigger(self::EVENT_BEFORE_PUBLISH);

        $model = $this->findModel($id);

        if (Yii::$app->user->id != $model->created_by && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException;
        }

        if ($model->updateAttributes(['public' => 1])) {
            Yii::$app->getSession()->setFlash('success',
                Yii::t('files', 'File is now public'));
        } else {
            Yii::$app->getSession()->setFlash('error',
                Yii::t('files', 'File could not be made public'));
        }

        $this->trigger(self::EVENT_AFTER_PUBLISH);

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Protects a File (sets public to false)
     * @return mixed
     */
    public function actionProtect($id)
    {
        $this->trigger(self::EVENT_BEFORE_PROTECT);

        $model = $this->findModel($id);

        if (Yii::$app->user->id != $model->created_by && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException;
        }

        if ($model->updateAttributes(['public' => 0])) {
            Yii::$app->getSession()->setFlash('success', Yii::t('files', 'File is now protected'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('files', 'File could not be protected'));
        }

        $this->trigger(self::EVENT_AFTER_PROTECT);

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Checks permission and downloads the requested file, if possible.
     * Set $raw to false to get the raw file content rather than a download.
     * Increments the download_count of the requested file by one, if valid.
     * @return mixed
     */
    public function actionDownload(string $id, bool $raw = false)
    {
        $model = $this->findModel($id);

        if (!$this->checkAccessPermission($model)) {
            throw new ForbiddenHttpException;
        }

        header("Content-Type: $model->mimetype");

        if (!file_exists($model->filename_path)) {
            throw new NotFoundHttpException;
        }

        if (!$raw) {
            header("Content-Disposition: attachment; filename=\"$model->filename_user\"");
        }

        echo readfile($model->filename_path);

        $model->updateCounters(['download_count' => 1]);
    }

    /**
     * Check if the file can be downloaded by the requesting user.
     * @param $model the model to check
     */
    public function checkAccessPermission($model)
    {
        if ($model->public) {
            return true;
        }

        if (!Yii::$app->user->isGuest && in_array(Yii::$app->user->identity->username, $model->shared_with)) {
            return true;
        }

        if ($model->created_by == Yii::$app->user->id) {
            return true;
        }

        if (Yii::$app->user->can('admin')) {
            return true;
        }

        return false;
    }

    /**
     * When called via GET request, this will render the file upload form.
     * When called via POST request, this is the endpoint to receive an uploaded file.
     * @var $public boolean Should the uploaded file be marked as public? Defaults to false.
     * @return mixed
     */
    public function actionUpload($public = false)
    {
        if (Yii::$app->request->isGet) {
            return $this->render('upload');
        }

        $this->trigger(self::EVENT_BEFORE_UPLOAD);

        if (empty($_FILES['files'])) {
            echo json_encode(['error' => Yii::t('files', 'No files found for upload.')]);
            return;
        }

        $files = $_FILES['files'];
        $success = false;
        $paths = [];
        $fileModels = [];

        foreach ($_FILES as $i => $file) {
            if ($file['error'] == UPLOAD_ERR_OK) {
                $ext = explode('.', basename($file['name']));
                $target = Yii::$app->getModule('files')->uploadPath . '/' . md5(uniqid()) . "." . array_pop($ext);

                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $file = Yii::createObject([
                        'class' => File::className(),
                        'attributes' => [
                            'filename_user' => $file['name'],
                            'created_by' => Yii::$app->user->id,
                            'filename_path' => $target,
                            'mimetype' => $file['type'],
                            'model' => isset($_POST['model']) ? $_POST['model'] : '',
                            'target_id' => isset($_POST['target_id']) ? $_POST['target_id'] : '',
                            'target_url' => isset($_POST['target_url']) ? $_POST['target_url'] : '',
                            'public' => ((isset($_POST['public']) && $_POST['public']) || $public) ? 1 : 0,
                        ],
                    ]);

                    $success = $file->save();

                    if ($file->isImage() && $this->module->crop_target_width && $this->module->crop_target_height) {
                        $file->crop();
                    }

                    $paths[] = $target;
                    $fileModels[] = $file;
                }
            }
        }

        if ($success === true) {
            $output = [];
        } else {
            $output = ['error' => Yii::t('files', 'Error while uploading files. Please contact the system administrator.')];

            if (YII_DEBUG)
                $output['error'] .= error_get_last() . (isset($file) ? json_encode($file->getErrors()) : '');

            foreach ($paths as $file) {
                unlink($file);
            }
        }

        $event = new FileUploadEvent;
        $event->postData = Yii::$app->request->post();
        $event->files = $fileModels;
        $this->trigger(self::EVENT_AFTER_UPLOAD, $event);

        echo json_encode($output);
    }

    /**
     * Displays a single File model.
     * @param string $id
     * @param string $language
     * @return mixed
     */
    public function actionView($id)
    {
        $file = $this->findModel($id);

        if (!$this->checkAccessPermission($file)) {
            throw new ForbiddenHttpException;
        }

        if (Yii::$app->request->post()) {
            if ($file->load(Yii::$app->request->post()) && $file->save()) {
                Yii::$app->getSession()->setFlash('success',
                    Yii::t('files', 'Tags have been updated'));
                $file->refresh();
            }
        }

        Yii::$app->user->setReturnUrl(['//files/file/view', 'id' => $id]);

        return $this->render('view', [
            'model' => $file,
            'users' => $this->determineShareableUsers(),
        ]);
    }

    /**
     * Which users should be able to be choosen when selecting files to share with.
     * @see FileWebModule shareableUsersCallback
     */
    public function determineShareableUsers() {
        if (is_callable(Yii::$app->getModule('files')->shareableUsersCallback)) {
            return call_user_func(Yii::$app->getModule('files')->shareableUsersCallback);
        } else {
            return ArrayHelper::map(
                \app\Models\User::find()
                    ->where(['!=', 'id', Yii::$app->user->id])
                    ->all(), 'username', 'username');
        }
    }

    /**
     * Share a file with an specific user.
     * The file id is provided by the GET param $file_id, while
     * the user is provided by the POST param user, since it is provided
     * by a drop down list.
     * @param $file_id the file that should be shared with the user
     * @param $add shall the user be added (1) or removed (!= 1) from the shared list
     * @throws ForbiddenHttpException
     */
    public function actionShareWithUser(string $file_id, bool $add, string $username = null)
    {
        $this->trigger(self::EVENT_BEFORE_SHARE_WITH_USER);

        $post = Yii::$app->request->post();

        $file = $this->findModel($file_id);

        if (Yii::$app->user->id != $file->created_by && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException;
        }

        if (!$username) {
            $username = $post['username'];
        }

        $recipient = User::find()->where(['username' => $username])->one();

        if (!$recipient) {
            throw new NotFoundHttpException(Yii::t('files', 'User can not be found'));
        }

        if ($add == 1) {
            $file->addShareWith($username);

            Yii::$app->getSession()->setFlash('success',
                Yii::t('files', 'File has been shared with {username}.', [
                'username' => $username,
            ]));
        } else {
            $file->removeShareWith($username);

            Yii::$app->getSession()->setFlash('success',
                Yii::t('files', 'File is no longer shared with {username}.', [
                    'username' => $username,
                ]));
        }

        $event = new ShareWithUserEvent;
        $event->sharedFrom = Yii::$app->user->identity;
        $event->sharedWith = $recipient;
        $event->sharedFile = $file;
        $event->add = $add;
        $this->trigger(self::EVENT_AFTER_SHARE_WITH_USER, $event);

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Deletes an existing File model.
     * If deletion is successful, the browser will be redirected to the referrer.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->trigger(self::EVENT_BEFORE_DELETE);

        $file = $this->findModel($id);

        if (Yii::$app->user->id == $file->created_by || Yii::$app->user->can('admin')) {
            $file->delete();
        } else {
            throw new ForbiddenHttpException;
        }

        $this->trigger(self::EVENT_AFTER_DELETE);

        return $this->redirect(Yii::$app->request->referrer);
    }
}
