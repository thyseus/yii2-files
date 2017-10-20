<?php

use yii\db\Migration;

/**
 * Handles adding slug to table `files`.
 */
class m171019_091513_add_slug_column_to_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%files}}', 'slug', $this->string()->notNull());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
