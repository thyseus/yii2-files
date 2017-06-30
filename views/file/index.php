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

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'created_at',
                'filter' => false,
                'format' => 'datetime'
            ],
            'filename_user',
            [
                'filter' => false,
                'format' => 'html',
                'attribute' => 'target_id',
                'value' => function ($data) {
                    if ($data->target) {
                        $identifierAttribute = 'id';

                        if (method_exists($data->target, 'identifierAttribute'))
                            $identifierAttribute = $data->target->identifierAttribute();

                        return Html::a($data->target->$identifierAttribute, $data->target_url);
                    }
                },
            ],
            [
                'filter' => FileSearch::mimeTypesGrouped(),
                'attribute' => 'mimetype',
            ],
            [
                'filter' => [0 => Yii::t('files', 'No'), 1 => Yii::t('files', 'Yes')],
                'attribute' => 'public',
                'format' => 'html',
                'value' => function ($model) {
                    if ($model->public) {
                        return '<span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span> ' . Yii::t('files', 'File is public.') . '.<br />' . Html::a(Yii::t('files', 'Make protected.'), ['//files/file/protect', 'id' => $model->id], ['data-pjax' => '0']);
                    } else {
                        return '<span class="glyphicon glyphicon-folder-close" aria-hidden="true"></span> ' . Yii::t('files', 'File is protected.') . '.<br />' . Html::a(Yii::t('files', 'Make public.'), ['//files/file/publish', 'id' => $model->id], ['data-pjax' => '0']);
                    }
                }
            ],
            [
                'attribute' => 'position',
                'format' => 'html',
                'value' => function($data) {
                    $str = $data->position . '&nbsp;';
                    $str .= Html::a('<span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span>',
                        ['move', 'id' => $data->id, 'dir' => 'up']) . '&nbsp;';
                    $str .= Html::a('<span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span>',
                            ['move', 'id' => $data->id, 'dir' => 'down']) . '&nbsp;';
                    return $str;
                },
            ],
            [
                'format' => 'raw',
                'header' => Yii::t('files', 'Actions'),
                'value' => function ($data) {
                    $actions = '';

                    if ($data->isImage())
                        $actions .= Html::a(
                                '<span class="glyphicon glyphicon-scissors" aria-hidden="true"></span> ' . Yii::t('files', 'Crop Image'),
                                ['//files/file/crop', 'id' => $data->id]) . '<br>';

                    $actions .= $data->downloadLink() . '<br>';
                    $actions .= Html::a(
                        '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> ' . Yii::t('files', 'Delete File'),
                        ['//files/file/delete', 'id' => $data->id], [
                        'data-method' => 'POST',
                        'data-confirm' => Yii::t('files', 'Are you Sure?'),
                    ]);

                    return $actions;
                }
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
