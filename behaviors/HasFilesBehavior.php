<?php
namespace thyseus\files\behaviors;

use thyseus\files\models\File;
use yii\base\Behavior;

class HasFilesBehavior extends Behavior
{
    public function getFiles()
    {
        if (\Yii::$app->user->isGuest)
            return $this;
        else {
            $identifierAttribute = 'id';

            if(method_exists($this->owner, 'identifierAttribute'))
                $identifierAttribute = $this->owner->identifierAttribute();

            return $this->owner
                ->hasMany(File::className(), ['target_id' => $identifierAttribute])
                ->onCondition(['created_by' => \Yii::$app->user->id]);
        }
    }
}