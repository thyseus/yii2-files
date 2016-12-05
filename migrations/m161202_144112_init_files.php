<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * @author Herbert Maschke <thyseus@gmail.com
 */
class m161202_144112_init_files extends Migration
{
    public function up()
    {
        $tableOptions = '';

        if (Yii::$app->db->driverName == 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%files}}', [
            'id'                   => Schema::TYPE_PK,
            'target_id'            => Schema::TYPE_TEXT, // dont use integer - it could be a slug, not only an numeric id
            'target_url'           => Schema::TYPE_TEXT,
            'model'                => Schema::TYPE_TEXT,
            'title'                => Schema::TYPE_TEXT,
            'description'          => Schema::TYPE_TEXT,
            'created_by'           => Schema::TYPE_INTEGER,
            'updated_by'           => Schema::TYPE_INTEGER,
            'created_at'           => Schema::TYPE_DATETIME,
            'updated_at'           => Schema::TYPE_DATETIME,
            'filename_user'        => Schema::TYPE_TEXT,
            'filename_path'        => Schema::TYPE_TEXT,
            'status'               => Schema::TYPE_INTEGER,
            'mimetype'             => Schema::TYPE_TEXT,
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%files}}');
    }
}
