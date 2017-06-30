<?php

use yii\db\Migration;
use yii\db\Schema;

class m170630_131050_add_position_column extends Migration
{
    public function up()
    {
        $this->addColumn('{{%files}}', 'position', $this->integer()->notNull());
    }

    public function down()
    {
        $this->dropColumn('{{%files}}', 'position');
    }
}
