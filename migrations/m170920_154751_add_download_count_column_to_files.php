<?php

use yii\db\Migration;

class m170920_154751_add_download_count_column_to_files extends Migration
{
    public function safeUp()
    {
      $this->addColumn('{{%files}}', 'download_count', $this->integer()->notNull()->defaultValue(0));

    }

    public function safeDown()
    {
        echo "m170920_154751_add_download_count_column_to_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170920_154751_add_download_count_column_to_files cannot be reverted.\n";

        return false;
    }
    */
}
