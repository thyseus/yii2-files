<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Sitecontent */

$this->title = $model->filename_user;
$this->params['breadcrumbs'][] = ['label' => Yii::t('files', 'Files'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$owner = $model->created_by == Yii::$app->user->id;
?>
<div class="file-view">

    <div class="row">
        <div class="row-lg-12">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">

            <?php
            echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => 'Target',
                        'visible' => $model->target !== null,
                        'format' => 'html',
                        'value' => function ($data) {
                            if ($data->target) {
                                $identifierAttribute = 'id';

                                if (method_exists($data->target, 'identifierAttribute'))
                                    $identifierAttribute = $data->target->identifierAttribute();

                                return Html::a($data->target->$identifierAttribute, $data->target_url);
                            }
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'created_by',
                        'value' => $model->owner->username,
                    ],
                    'mimetype',
                    'filename_user',
                    [
                        'attribute' => 'model',
                        'visible' => Yii::$app->user->can('admin'),
                    ],
                    [
                        'attribute' => 'filename_path',
                        'visible' => Yii::$app->user->can('admin'),
                    ],
                    'status',
                    [
                        'attribute' => 'public',
                        'value' => $model->public ? Yii::t('files', 'Yes') : Yii::t('files', 'No'),
                    ],
                    [
                        'format' => 'html',
                        'attribute' => 'target_url',
                        'value' => $model->target_url ? Html::a($model->target_url, $model->target_url) : null,
                    ]
                ]
            ]);
            ?>
        </div>
        <div class="col-md-6">
            <?php if ($model->isImage()): ?>
                <img src="<?= $model->downloadUrl(); ?>" alt="image"/>
                <br>
                <?= Html::a(Yii::t('files', 'Crop Image'), ['crop', 'id' => $model->id]); ?>
            <?php endif ?>

            <br>

            <?= $model->downloadLink(); ?>

            <br>

            <?= Html::a(Yii::t('files', 'Back to file overview'), ['index'], ['class' => 'btn btn-primary']); ?>

            <br>

            <?php if ($owner) { ?>
            <?php if ($model->public) { ?>
                <div class="alert alert-warning"><p> <?= Yii::t('files', 'File is public'); ?>. </p></div>

                <br>

                <?= Html::a(Yii::t('files', 'Make protected'), ['//files/file/protect', 'id' => $model->id], ['class' => 'btn btn-primary']); ?>

            <?php } else { ?>
                <div class="alert alert-warning"><p><?= Yii::t('files', 'File is protected'); ?>.</p></div>

                <?= $this->render('_shared_with', ['model' => $model]); ?>

                <br>

                <?= Html::a(Yii::t('files', 'Make public'), ['//files/file/publish', 'id' => $model->id], ['class' => 'btn btn-primary']); ?>

            <?php } ?>

            <hr>

            <?= Html::a(Yii::t('files', 'Remove file'), ['/files/file/delete', 'id' => $model->id],
                ['class' => 'btn btn-danger', 'data-confirm' => 'Are you sure?']);

            ?>

            <?php } ?>
        </div>
    </div>
</div>
</div>
