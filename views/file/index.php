<?php

use thyseus\files\models\FileSearch;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SitecontentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('files', 'Files');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="files-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= Html::a(Yii::t('files', 'Upload new File'), ['upload'], ['class' => 'btn btn-primary']); ?>

    <hr>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'tags',
                'visible' => Yii::$app->getModule('files')->possibleTags,
                'filter' => Yii::$app->getModule('files')->possibleTags,
                'value' => function($model) {
                    return $model->getTagsFormatted();
                },
            ],
            [
                'attribute' => 'created_at',
                'filter' => false,
                'format' => 'date'
            ],
            [
                'attribute' => 'created_by',
                'filter' => Yii::$app->user->can('admin') ? FileSearch::uploadedByFilter(-1) : FileSearch::uploadedByFilter(),
                'value' => function ($model, $key, $index, $column) {
                    return $model->owner->username;
                },
            ],
            'filename_user',
            [
                'filter' => Yii::$app->user->can('admin') ? FileSearch::targetsGrouped(-1) : FileSearch::targetsGrouped(),
                'format' => 'html',
                'attribute' => 'target_id',
                'value' => function ($data) {
                    if ($data->target) {
                        $caption = null;

                        if (method_exists($data->target, '__toString')) {
                            $caption = $data->target->__toString();
                        } else if (method_exists($data->target, 'identifierAttribute')) {
                            $identifierAttribute = 'id';
                            $identifierAttribute = $data->target->identifierAttribute();
                            $caption = $data->target->$identifierAttribute;
                        }

                        return Html::a($caption, $data->target_url);
                    }
                },
            ],
            [
                'filter' => FileSearch::mimeTypesGrouped(Yii::$app->user->can('admin') ? -1 : 0),
                'attribute' => 'mimetype',
            ],
            [
                'filter' => [
                    0 => Yii::t('files', 'No'),
                    1 => Yii::t('files', 'Yes'),
                ],
                'attribute' => 'public',
                'format' => 'html',
                'value' => function ($model) {
                    $owner = $model->created_by == Yii::$app->user->id;
                    if ($owner) {
                        if ($model->public) {
                            return '<span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span> '
                                . Yii::t('files', 'File is public')
                                . '.<br />'
                                . Html::a(
                                    Yii::t('files', 'Make protected'),
                                    ['//files/file/protect', 'id' => $model->id],
                                    ['data-pjax' => '0']);
                        } else {
                            return '<span class="glyphicon glyphicon-folder-close" aria-hidden="true"></span> '
                                . Yii::t('files', 'File is protected')
                                . '.<br />'
                                . Html::a(
                                    Yii::t('files', 'Configure shares'),
                                    ['//files/file/view', 'id' => $model->id],
                                    ['data-pjax' => '0'])
                                . '<br />'
                                . Html::a(
                                    Yii::t('files', 'Make public'),
                                    ['//files/file/publish', 'id' => $model->id],
                                    ['data-pjax' => '0']);
                        }
                    }
                }
            ],
            [
                'attribute' => 'position',
                'format' => 'html',
                'value' => function ($model) {
                    $owner = $model->created_by == Yii::$app->user->id;
                    $str = $model->position . '<br>';
                    if ($owner) {
                        $str .= Html::a('<span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span>',
                                ['move', 'id' => $model->id, 'dir' => 'up']) . '&nbsp;';
                        $str .= Html::a('<span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span>',
                                ['move', 'id' => $model->id, 'dir' => 'down']) . '&nbsp;';
                    }
                    return $str;
                },
            ],
            [
                'attribute' => 'download_count',
                'filter' => false,
                'headerOptions' => ['style' => 'width:50px;'],
            ],
            [
                'format' => 'raw',
                'header' => Yii::t('files', 'Actions'),
                'value' => function ($model) {
                    $owner = $model->created_by == Yii::$app->user->id;
                    $actions = '';

                    // The current implementation of the cropper does only work on Google Chrome (Webkit) based browsers
                    if ($model->isImage() && stripos($_SERVER['HTTP_USER_AGENT'], 'chrome') !== FALSE) {
                        $actions .= '<nobr>' . Html::a(
                                '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span> ' . Yii::t('files', 'Crop Image'),
                                ['//files/file/crop', 'id' => $model->slug]) . '</nobr><br>';
                    }

                    $actions .= $model->downloadLink() . '<br>';
                    $actions .= '<nobr>' . Html::a(
                            '<span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> ' . Yii::t('files', 'Properties'),
                            ['//files/file/view', 'id' => $model->slug], [
                            'data-pjax' => '0',
                        ]) . '</nobr><br>';
                    if ($owner) {
                        $actions .= '<nobr>' . Html::a(
                                '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> ' . Yii::t('files', 'Delete File'),
                                ['//files/file/delete', 'id' => $model->slug], [
                                'data-pjax' => '0',
                                'data-method' => 'POST',
                                'data-confirm' => Yii::t('files', 'Are you Sure?'),
                            ]) . '</nobr>';
                    }

                    return $actions;
                }
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
