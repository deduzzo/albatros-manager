<?php

use yii\db\Migration;

class m260311_120000_add_nominativo_to_utente extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%utente}}', 'nominativo', $this->string(255)->null()->after('username'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%utente}}', 'nominativo');
    }
}
