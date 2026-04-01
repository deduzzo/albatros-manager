<?php

use yii\db\Migration;

class m260401_100000_add_docente_presenza_to_calendario extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%calendario}}', 'docente_presente', $this->tinyInteger()->null()->defaultValue(null)->after('status')->comment('null=non acquisito, 1=presente, 0=assente'));
        $this->addColumn('{{%calendario}}', 'docente_ore_effettive', $this->decimal(5, 2)->null()->defaultValue(null)->after('docente_presente'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%calendario}}', 'docente_ore_effettive');
        $this->dropColumn('{{%calendario}}', 'docente_presente');
    }
}
