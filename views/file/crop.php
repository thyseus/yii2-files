<?php

use demi\cropper\Cropper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Sitecontent */

$this->title = $model->filename_user;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="file-view">

    <div class="row">
        <div class="row-lg-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <p>
                <a href="" onclick="history.go(-1);"> <?= Yii::t('files', 'Back'); ?> </a>

                <?php if ($model->isImage()): ?>
                    <?php
                    $cropperOptions = array_merge(Yii::$app->getModule('files')->cropperOptions, [
                        'cropUrl' => ['//files/file/crop', 'id' => $model->id],
                        'image' => $model->downloadUrl(),
                    ]);

                    echo Cropper::widget($cropperOptions);

                    $json = Json::encode($cropperOptions);

                    echo '<a class="btn btn-primary pull-right crop-submit">' . Yii::t('files', 'Crop Image') . '</a>';
                    // unfortunately demi/cropper is not working properly when 'modal' is set to false.
                    // we need to register our JS manually here.
                    // FIXME TODO remove when original demi/cropper plugin has fixed this
                    $this->registerJs("\$('.cropper-image').cropper($json);");

                    $this->registerJs("
                    $('.crop-submit').click(function(e) {
                    
                    dimensions = $('.cropper-image').cropper('getData');
                    
                    window.location = '" . Url::to(['crop', 'id' => $model->id]) . "&x=' + dimensions.x + '&y=' + dimensions.y + '&width=' + dimensions.width + '&height=' + dimensions.height;
                 });
              ");
                    ?>
                <?php else: ?>
                    <?= Yii::t('files', 'Choosen file is not an image. File can not be cropped.'); ?>
                <?php endif ?>

        </div>
    </div>
</div>
