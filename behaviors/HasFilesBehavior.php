<?php
namespace thyseus\files\behaviors;

use thyseus\files\models\File;
use yii\base\Behavior;

class HasFilesBehavior extends Behavior
{
    /**
     * Attaches an relation 'files' to the owner model that retrieves all files.
     *
     * @return yii\db\ActiveQuery
     */
    public function getFiles()
    {
        $identifierAttribute = 'id';

        if (method_exists($this->owner, 'identifierAttribute'))
            $identifierAttribute = $this->owner->identifierAttribute();

        return $this->owner->hasMany(File::class, ['target_id' => $identifierAttribute])->orderBy('position ASC');
    }

    /**
     * Attaches an relation 'filesPublic' to the owner model that retrieves all public files.
     *
     * @return yii\db\ActiveQuery
     */
    public function getFilesPublic()
    {
        $identifierAttribute = 'id';

        if (method_exists($this->owner, 'identifierAttribute'))
            $identifierAttribute = $this->owner->identifierAttribute();

        return $this->owner->hasMany(File::class,
            ['target_id' => $identifierAttribute])
            ->andWhere(['files.public' => 1])
            ->orderBy('position ASC');
    }

    /**
     * Attaches an relation 'filesProtected' to the owner model that retrieves all protected files.
     *
     * @return yii\db\ActiveQuery
     */
    public function getFilesProtected()
    {
        $identifierAttribute = 'id';

        if (method_exists($this->owner, 'identifierAttribute'))
            $identifierAttribute = $this->owner->identifierAttribute();

        return $this->owner->hasMany(File::class,
            ['target_id' => $identifierAttribute])
            ->andWhere(['files.public' => 0])
            ->orderBy('position ASC');
    }

    /**
     * Attaches an method 'filesFromUser(<id>)' to the owner model that retrieves all files that are owned by this user.
     *
     * @return array list of found files
     */
    public function filesFromUser($id)
    {
        $identifierAttribute = 'id';

        if (method_exists($this->owner, 'identifierAttribute'))
            $identifierAttribute = $this->owner->identifierAttribute();

        return $this->owner
            ->hasMany(File::class, ['target_id' => $identifierAttribute])
            ->andWhere(['files.created_by' => $id])
            ->orderBy('position ASC')
            ->all();
    }

    /**
     * Attaches an method 'filesWithTag(<tag>)' to the owner model that retrieves all files that are tagged with the given tag.
     *
     * @return array list of found files
     */
    public function filesWithTag($tag)
    {
        $identifierAttribute = 'id';

        if (method_exists($this->owner, 'identifierAttribute'))
            $identifierAttribute = $this->owner->identifierAttribute();

        return $this->owner
            ->hasMany(File::class, ['target_id' => $identifierAttribute])
            ->andWhere(['like', 'files.tags' , $tag])
            ->andWhere(['status' => File::STATUS_NORMAL])
            ->orderBy('position ASC')
            ->all();
    }
}