<?php

namespace thyseus\files\models;

use thyseus\files\models\File;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * FilsSearch represents the model behind the search form about `app\models\File`.
 */
class FileSearch extends File
{
    /**
     * @var display_shared_files when set to true, only files that are shared with me are found, not my own files
     */
    public $display_shared_files;

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
     * add 'display_shared_files' as safe
     * @return array the added rules
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['display_shared_files', 'safe'];

        return $rules;
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
        ]);

        if ($this->display_shared_files) {
            $query->andFilterWhere([
                'like', 'shared_with', ', ' . Yii::$app->user->identity->username
            ]);
        } else {
            $query->andFilterWhere([
                'created_by' => Yii::$app->user->can('admin') ? null : Yii::$app->user->id,
            ]);
        }

        $query->andFilterWhere(['like', 'filename_user', $this->filename_user]);
        $query->andFilterWhere(['like', 'filename_path', $this->filename_path]);
        $query->andFilterWhere(['like', 'mimetype', $this->mimetype]);

        return $dataProvider;
    }
}
