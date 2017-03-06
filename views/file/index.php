<?php

use demi\cropper\Cropper;
use thyseus\files\models\File;
use thyseus\files\models\FileSearch;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
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
            ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'created_at', 'filter' => false],
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
            'filename_user',
            [
                'filter' => FileSearch::mimeTypesGrouped(),
                'attribute' => 'mimetype',
            ],
            [
                'filter' => [0 => Yii::t('files', 'No'), 1 => Yii::t('files', 'Yes')],
                'attribute' => 'public',
                'format' => 'html',
                'value' => function($model) {
                    if($model->public) {
                        return Yii::t('files', 'File is public.'). '.<br />' . Html::a(Yii::t('files', 'Make protected.'), ['//files/file/protect', 'id' => $model->id], ['data-pjax' => '0']);
                    } else {
                        return Yii::t('files', 'File is protected.') . '.<br />'. Html::a(Yii::t('files', 'Make public.'), ['//files/file/publish', 'id' => $model->id], ['data-pjax' => '0']);
                    }
                }
            ],
            [
                'format' => 'raw',
                'header' => Yii::t('files', 'Actions'),
                'value' => function ($data) {
                    $actions = '';

                    if($data->isImage())
                        $actions .= Html::a(Yii::t('files', 'Crop Image'), ['//files/file/crop', 'id' => $data->id]) . '<br>';

                    $actions .= $data->downloadLink();

                    return $actions;
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {delete}',
                'urlCreator' => function ($action, $model, $key, $index) {
                    return Url::to(['file/' . $action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
