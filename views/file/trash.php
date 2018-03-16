<?php

use thyseus\files\models\FileSearch;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SitecontentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('files', 'Trash');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="files-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= Html::a(Yii::t('files', 'Files'), ['index'], ['class' => 'btn btn-primary']); ?>

    <?= Html::a(Yii::t('files', 'Empty trash'), ['delete', 'id' => 'remove-all-files-from-trash-bin'], [
            'data-confirm' => Yii::t('files', 'Are you sure you want to remove all files in your trash bin permanently?'),
            'class' => 'btn btn-primary']); ?>

    <hr>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'created_at',
                'filter' => false,
                'format' => 'date'
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
                'format' => 'raw',
                'header' => Yii::t('files', 'Actions'),
                'value' => function ($model) {
                    $actions = '';

                    $actions .= Html::a(Yii::t('files', 'Restore'),
                            ['//files/file/restore', 'id' => $model->slug]);

                    $actions .= '<br>';

                    $actions .= Html::a(Yii::t('files', 'Remove permanent'),
                        ['//files/file/delete', 'id' => $model->slug],
                        ['data-confirm' => Yii::t('files', 'Are you sure to permanently remove this file? It can never be restored anymore.')]);

                    return $actions;
                }
            ],
        ],
    ]); ?>
</div>
