<?php

use yii\db\Migration;

class m260221_100003_add_consent_fields_to_iscrizione extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%iscrizione}}', 'accetta_privacy', $this->boolean()->defaultValue(0)->after('note'));
        $this->addColumn('{{%iscrizione}}', 'accetta_marketing', $this->boolean()->defaultValue(0)->after('accetta_privacy'));
        $this->addColumn('{{%iscrizione}}', 'dichiarazione_veridicita', $this->boolean()->defaultValue(0)->after('accetta_marketing'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%iscrizione}}', 'accetta_privacy');
        $this->dropColumn('{{%iscrizione}}', 'accetta_marketing');
        $this->dropColumn('{{%iscrizione}}', 'dichiarazione_veridicita');
    }
}
