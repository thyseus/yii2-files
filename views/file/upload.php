<?php

use yii\helpers\Html;

$this->title = Yii::t('files', 'File upload');
$this->params['breadcrumbs'][] = ['label' => Yii::t('files', 'Files'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= $this->render('_upload', [
    'pluginEvents' => [
        'fileuploaded' => 'function() { window.location.reload(); }',
    ],
]); ?>

<?= Html::a(Yii::t('files', 'Your uploaded files'), ['index']); ?>
