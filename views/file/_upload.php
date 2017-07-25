<?php

use kartik\file\FileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

if (!isset($pluginOptions)) {
    $pluginOptions = [];
}

if (!isset($pluginOptions['uploadUrl'])) {
    $pluginOptions['uploadUrl'] = Url::to(Yii::$app->getModule('files')->uploadUrl);
}

$pluginOptions = ArrayHelper::merge($pluginOptions, [
    'uploadExtraData' => [
        'model' => $model::className(),
        'attribute' => isset($_POST['attribute']) ? $_POST['attribute'] : '',
        'target_id' => method_exists($model, 'identifierAttribute') ? $model->{$model->identifierAttribute()} : $model->id,
        'target_url' => isset($target_url) ? $target_url : '',
    ],
]);

echo FileInput::widget([
    'name' => 'files',
    'options' => isset($options) ? $options : [],
    'pluginOptions' => $pluginOptions,
    'pluginEvents' => isset($pluginEvents) ? $pluginEvents : [],
]);
