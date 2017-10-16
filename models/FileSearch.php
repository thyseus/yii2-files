<?php

namespace thyseus\files\models;

use thyseus\files\models\File;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * FilsSearch represents the model behind the search form about `app\models\File`.
 */
class FileSearch extends File
{
    /**
     * @return array a grouped list of targets of all files that the user has been access to
     * Provide -1 to get ALL targets in the system. Useful for the administrator view.
     */
    public static function targetsGrouped($user_id = null)
    {
        if (!$user_id) {
            $user_id = Yii::$app->user->id;
        }

        $targets = [];

        foreach (File::find()
                     ->select(['target_id', 'model'])
                     ->where($user_id == -1 ? [] : ['created_by' => $user_id])
                     ->groupBy('target_id')
                     ->all() as $file) {
            if ($file->model && $target = $file->target) {
                $identifier = null;
                $caption = '';
                $identifierAttribute = 'id';
                $identifier = $target->id;

                if (method_exists($target, 'identifierAttribute')) {
                    $identifierAttribute = $target->identifierAttribute();
                    $caption = $target->$identifierAttribute;
                    $identifier = $target->$identifierAttribute;
                }

                if (method_exists($target, '__toString')) {
                    $caption = $target->__toString();
                }

                $targets[$identifier] = $caption;
            }
        }

        return $targets;
    }

    /**
     * @param null $user_id user id of the user.
     * Provide -1 to get ALL mime types in the system. Useful for the administrator view.
     *
     * @return array a list of mimetypes that the given user has in his file repository
     */
    public static function mimeTypesGrouped($user_id = null)
    {
        if (!$user_id) {
            $user_id = Yii::$app->user->id;
        }

        $mimetypes = [];

        foreach (File::find()
                     ->select('mimetype')
                     ->where($user_id == -1 ? [] : ['created_by' => $user_id])
                     ->groupBy('mimetype')
                     ->all() as $file) {
            $mimetypes[$file->mimetype] = $file->mimetype;
        }

        return $mimetypes;
    }


    /**
     * @return array a grouped list of uploaders of all files that the user has been access to
     * Provide -1 to get ALL targets in the system. Useful for the administrator view.
     *
     * -1 for my own files and -2 for files that have been shared with me are automatically appended
     * to the filter.
     * @see method search() in this File.
     */
    public static function uploadedByFilter($user_id = null): array
    {
        $uploadedBy = [
            -1 => Yii::t('files', 'Only my own files'),
            -2 => Yii::t('files', 'Files shared with me'),
        ];

        if (!$user_id) {
            $user_id = Yii::$app->user->id;
        }

        foreach (File::find()
                     ->select('created_by')
                     ->where($user_id == -1 ? [] : ['created_by' => $user_id])
                     ->groupBy('created_by')
                     ->all() as $file) {
            if ($user = User::findOne($file->created_by)) {
                $uploadedBy[$file->created_by] = $user->username;
            }

        }

        return $uploadedBy;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = File::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]]
        ]);

        $this->load($params);

        $query->andFilterWhere([
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'public' => $this->public,
            'position' => $this->position,
            'status' => $this->status,
            'target_id' => $this->target_id,
        ]);

        if ($this->created_by == -2) { # files shared with me
            $query->andFilterWhere([
                'like', 'shared_with', ', ' . Yii::$app->user->identity->username
            ]);
        } else {
            $query->andFilterWhere([
                'created_by' => Yii::$app->user->id,
            ]);
        }

        $query->andFilterWhere(['like', 'filename_user', $this->filename_user]);
        $query->andFilterWhere(['like', 'filename_path', $this->filename_path]);
        $query->andFilterWhere(['like', 'mimetype', $this->mimetype]);

        return $dataProvider;
    }
}
