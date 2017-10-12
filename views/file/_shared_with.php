<?php

use app\models\User;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php if ($model->shared_with && $model->shared_with != ['']) { ?>
    <p> <?= Yii::t('files', 'This file is visible for this users'); ?>: </p>

    <table class="table">
        <?php foreach ($model->shared_with as $username) { ?>
            <?php if ($username) { ?>
                <tr>
                    <td> <?= $username; ?>
                    <td> <?= Html::a(Yii::t('files', 'Remove share'),
                            ['share-with-user', 'file_id' => $model->id, 'username' => $username, 'add' => 0]
                        ); ?> </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </table>

<?php } else { ?>

    <p> <?= Yii::t('files', 'This file is not visible for any user except yourself'); ?>. </p>

<?php } ?>


<?php ActiveForm::begin(['action' =>
    ['share-with-user', 'file_id' => $model->id, 'add' => 1]
]); ?>

<?= Yii::t('files', 'Share file with user'); ?>:

<div class="row">
    <div class="col-md-6">
        <?= Html::dropDownList(
            'username',
            null,
            ArrayHelper::map(User::find()->all(), 'username', 'username'),
            ['class' => 'form-control']); ?>
    </div>

    <div class="col-md-6">
        <?= Html::submitButton(Yii::t('files', 'Share'), ['class' => 'form-control']); ?>

    </div>
</div>


<?php ActiveForm::end(); ?>

