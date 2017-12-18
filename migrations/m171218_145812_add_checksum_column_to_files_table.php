<?php

use yii\db\Migration;

/**
 * Handles adding checksum to table `files`.
 */
class m171218_145812_add_checksum_column_to_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%files}}', 'checksum', $this->string(32)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
