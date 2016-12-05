<?php

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
                'attribute' => 'owner.username',
            ],
            [
                'attribute' => 'mimetype',
            ],
            ['format' => 'html',
            'header' => Yii::t('files', 'Download'),
            'value' => function ($data) {
                return $data->downloadLink();
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
