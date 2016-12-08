<?php

use yii\db\Migration;
use yii\db\Schema;

class m161208_101931_add_public_column extends Migration
{
    public function up()
    {
        $this->addColumn('{{%files}}', 'public', $this->integer()->notNull());
    }

    public function down()
    {
        $this->dropolumn('{{%files}}', 'public');
    }
}
