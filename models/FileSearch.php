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

    public static function mimeTypesGrouped($user_id = null)
    {
        if(!$user_id) {
            $user_id = Yii::$app->user->id;
        }

        $mimetypes = [];

        foreach(File::find()->where(['created_by' => $user_id])->groupBy('mimetype')->all() as $file)
            $mimetypes[$file->mimetype] = $file->mimetype;

        return $mimetypes;
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
            'created_by' => Yii::$app->user->can('admin') ? null : Yii::$app->user->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'public' => $this->public,
        ]);

        $query->andFilterWhere(['like', 'filename_user', $this->filename_user]);
        $query->andFilterWhere(['like', 'filename_path', $this->filename_path]);
        $query->andFilterWhere(['like', 'mimetype', $this->mimetype]);

        return $dataProvider;
    }
}
