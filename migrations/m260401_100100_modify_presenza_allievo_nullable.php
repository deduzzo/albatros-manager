<?php

use yii\db\Migration;

class m260401_100100_modify_presenza_allievo_nullable extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%presenza_allievo}}', 'presente', $this->tinyInteger()->null()->defaultValue(null)->comment('null=non acquisito, 1=presente, 0=assente'));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%presenza_allievo}}', 'presente', $this->boolean()->notNull()->defaultValue(false));
    }
}
