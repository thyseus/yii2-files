<?php

use thyseus\files\models\File;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use kartik\select2\Select2;

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
                        'value' => function ($model) {
                            if ($model->target) {
                                $caption = '';

                                if (method_exists($model->target, 'identifierAttribute')) {
                                    $identifierAttribute = $model->target->identifierAttribute();
                                    $caption = $model->target->$identifierAttribute;
                                }

                                if (method_exists($model, '__toString')) {
                                    $caption = $model->target->__toString();
                                }

                                return Html::a($caption, $model->target_url);
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
                    ],
                    [
                        'attribute' => 'tags',
                        'value' => $model->getTagsFormatted(),
                        'visible' => Yii::$app->getModule('files')->possibleTags,
                    ],
                    [
                        'attribute' => 'checksum',
                    ]
                ]
            ]);
            ?>
        </div>
        <div class="col-md-6">
            <?php if ($model->isImage()): ?>
                <img src="<?= $model->downloadUrl(); ?>" alt="image"/>
                <br>
                <?php if ($owner) { ?>
                    <?= Html::a(Yii::t('files', 'Crop Image'), ['crop', 'id' => $model->id]); ?>
                <?php } ?>
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

                    <?= $this->render('_shared_with', ['model' => $model, 'users' => $users]); ?>

                    <br>

                    <?= Html::a(Yii::t('files', 'Make public'), ['//files/file/publish', 'id' => $model->id], ['class' => 'btn btn-primary']); ?>

                <?php } ?>

                <hr>

                <?php if (Yii::$app->getModule('files')->possibleTags) { ?>

                    <?php $form = ActiveForm::begin(['id' => 'file-tags-form']); ?>

                    <?= $form->field($model, 'tags')->widget(Select2::classname(), [
                        'data' => File::possibleTagsTranslated(),
                        'options' => [
                            'placeholder' => Yii::t('files', 'Select tags'),
                        ],
                        'pluginOptions' => [
                            'multiple' => true,
                            'disabled' => !$owner,
                            'allowClear' => true,
                        ],
                    ]);
                    ?>

                    <?= Html::submitButton(Yii::t('files', 'Save tags'), ['class' => 'btn btn-primary']); ?>

                    <?php ActiveForm::end(); ?>

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
