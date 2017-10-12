<?php

use demi\cropper\Cropper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Sitecontent */

$this->title = $model->filename_user;
$this->params['breadcrumbs'][] = ['label' => Yii::t('files', 'Files'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$cropperOptions = ArrayHelper::merge(Yii::$app->getModule('files')->cropperOptions, [
    'cropUrl' => ['//files/file/crop', 'id' => $model->id],
    'image' => $model->downloadUrl(),
]);

$crop_target_width = Yii::$app->getModule('files')->crop_target_width;
$crop_target_height = Yii::$app->getModule('files')->crop_target_height;
?>
    <div class="file-view">

        <?php if ($model->isImage()): ?>
            <div class="row">
                <div class="row-lg-12">
                    <h1><?= Html::encode($this->title) ?></h1>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <?= Cropper::widget($cropperOptions);

                    $cropperOptions['preview'] = '.img-preview';
                    $json = Json::encode($cropperOptions);

                    // unfortunately demi/cropper is not working properly when 'modal' is set to false.
                    // we need to register our JS manually here.
                    // FIXME TODO remove when original demi/cropper plugin has fixed this
                    $this->registerJs("\$('.cropper-image').cropper($json);");

                    $img_receive_url = Url::to(['//files/file/upload-raw', 'id' => $model->id]);

                    ?>
                </div>

                <div class="col-lg-3">
                    <p> <?= Yii::t('files', 'Preview'); ?>: </p>
                    <div class="img-preview"
                         style="border: 1px solid; overflow: hidden;width:<?= $crop_target_width; ?>px;height:<?= $crop_target_width; ?>px;"></div>
                    <hr>

                    <a class="btn btn-primary btn-rotate-left" href="#" title="Rotate Left">
                        <span class="glyphicon glyphicon-chevron-left"></span>
                    </a>

                    <a class="btn btn-primary btn-rotate-right" href="#" title="Rotate Right">
                        <span class="glyphicon glyphicon-chevron-right"></span>
                    </a>

                    <a class="btn btn-primary btn-zoom-out" href="#" title="Zoom Out">
                        <span class="glyphicon glyphicon-minus"></span>
                    </a>

                    <a class="btn btn-primary btn-zoom-in" href="#" title="Zoom In">
                        <span class="glyphicon glyphicon-plus"></span>
                    </a>

                    <a class="btn btn-primary btn-zoom-reset" href="#" title="Reset">
                        <span class="glyphicon glyphicon-off"></span>
                    </a>

                    <a class="btn btn-primary btn-flip-horizontal" href="#" title="Flip horizontal">
                        <span class="glyphicon glyphicon-resize-horizontal"></span>
                    </a>

                    <a class="btn btn-primary btn-flip-vertical" href="#" title="Flip Vertical">
                        <span class="glyphicon glyphicon-resize-vertical"></span>
                    </a>

                    <hr>

                    <a class="btn btn-primary" href="" onclick="history.go(-1);"> <?= Yii::t('files', 'Back'); ?> </a>
                    <a class="btn btn-primary pull-right crop-submit"><?= Yii::t('files', 'Crop Image'); ?></a>
                </div>


            </div>
        <?php else: ?>
            <?= Yii::t('files', 'Choosen file is not an image. File can not be cropped.'); ?>
        <?php endif ?>

    </div>

<?php $this->registerJs("
   $('a.btn-rotate-left').click(function() { $('.cropper-image').cropper('rotate', -45); } );
   $('a.btn-rotate-right').click(function() { $('.cropper-image').cropper('rotate', 45); } );
   $('a.btn-zoom-out').click(function() { $('.cropper-image').cropper('zoom', -0.1); } );
   $('a.btn-zoom-in').click(function() { $('.cropper-image').cropper('zoom', 0.1); } );
   $('a.btn-zoom-reset').click(function() { $('.cropper-image').cropper('reset'); } );
   $('a.btn-flip-horizontal').click(function() { $('.cropper-image').cropper('scaleX', -1); } );
   $('a.btn-flip-vertical').click(function() { $('.cropper-image').cropper('scaleY', -1); } );

    $('a.crop-submit').click(function(e) {
        result = $('.cropper-image').cropper('getCroppedCanvas', {width: $crop_target_width, height: $crop_target_height} );
        $('.img-preview').html(result);
        $('.img-preview canvas').first().width(240);
        $.post('$img_receive_url', {'raw-data': ($('.img-preview canvas')[0]).toDataURL()});
        window.location = '" . Yii::$app->request->referrer . "';
     });
   ");
