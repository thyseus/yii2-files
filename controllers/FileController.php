<?php

namespace thyseus\files\controllers;

use thyseus\files\models\File;
use thyseus\files\models\FileSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;


/**
 * FileController implements the CRUD actions for File model.
 */
class FileController extends Controller
{
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
                        'actions' => ['index', 'view', 'delete', 'upload', 'download', 'protect', 'publish'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Files of the currently logged in user.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Publishes a File (sets public to true)
     * @return mixed
     */
    public function actionPublish($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->user->id != $model->created_by && !Yii::$app->user->can('admin'))
            throw new ForbiddenHttpException;

        if ($model->updateAttributes(['public' => 1]))
            Yii::$app->getSession()->setFlash('success', Yii::t('files', 'File is now public'));
        else
            Yii::$app->getSession()->setFlash('error', Yii::t('files', 'File could not be made public'));

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the File model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return File the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = File::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('files', 'The requested file does not exist.'));
        }
    }

    /**
     * Protects a File (sets public to false)
     * @return mixed
     */
    public function actionProtect($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->user->id != $model->created_by && !Yii::$app->user->can('admin'))
            throw new ForbiddenHttpException;

        if ($model->updateAttributes(['public' => 0]))
            Yii::$app->getSession()->setFlash('success', Yii::t('files', 'File is now protected'));
        else
            Yii::$app->getSession()->setFlash('error', Yii::t('files', 'File could not be protected'));

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Checks permission and downloads the requested file, if possible.
     * Set $raw to false to get the raw file content rather than a download.
     *   * @return mixed
     */
    public function actionDownload($id, $raw = false)
    {
        $model = $this->findModel($id);

        if (!$model->public)
            if (Yii::$app->user->id != $model->created_by && !Yii::$app->user->can('admin'))
                throw new ForbiddenHttpException;

        header("Content-Type: $model->mimetype");

        if (!$raw)
            header("Content-Disposition: attachment; filename=\"$model->filename_user\"");

        if (!file_exists($model->filename_path))
            throw new yii\web\NotFoundHttpException;

        echo readfile($model->filename_path);
    }

    /**
     * Endpoint to receive an uploaded file.
     * @return mixed
     */
    public function actionUpload()
    {
        if (empty($_FILES['files'])) {
            echo json_encode(['error' => Yii::t('files', 'No files found for upload.')]);
            return;
        }

        $files = $_FILES['files'];
        $success = false;
        $paths = [];

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
                        ],
                    ]);

                    $success = $file->save();
                    $paths[] = $target;
                }
            }
        }

        if ($success === true) {
            $output = [];
        } else {
            $output = ['error' => Yii::t('files', 'Error while uploading files. Please contact the system administrator.')];

            if (YII_DEBUG)
                $output['error'] .= error_get_last() . (isset($file) ? $file->getErrors() : '');

            foreach ($paths as $file) {
                unlink($file);
            }
        }

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

        if (Yii::$app->user->id != $file->created_by && !Yii::$app->user->can('admin'))
            throw new ForbiddenHttpException;

        return $this->render('view', [
            'model' => $file,
        ]);
    }

    /**
     * Deletes an existing File model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $file = $this->findModel($id);

        if (Yii::$app->user->id == $file->created_by || Yii::$app->user->can('admin'))
            $file->delete();
        else
            throw new ForbiddenHttpException;

        return $this->redirect(Yii::$app->request->referrer);
    }
}
