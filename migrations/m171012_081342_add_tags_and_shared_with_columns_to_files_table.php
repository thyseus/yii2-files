<?php

use yii\db\Migration;

/**
 * Handles adding tags_and_shared_with to table `files`.
 */
class m171012_081342_add_tags_and_shared_with_columns_to_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%files}}', 'tags', $this->text()->defaultValue(''));
        $this->addColumn('{{%files}}', 'shared_with', $this->text()->defaultValue(''));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
