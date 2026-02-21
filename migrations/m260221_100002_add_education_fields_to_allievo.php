<?php

use yii\db\Migration;

class m260221_100002_add_education_fields_to_allievo extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%allievo}}', 'titolo_studio', "ENUM('licenza_media','diploma','laurea_triennale','laurea_magistrale','master','dottorato') NULL AFTER `note`");
        $this->addColumn('{{%allievo}}', 'anno_conseguimento', $this->smallInteger()->unsigned()->null()->after('titolo_studio'));
        $this->addColumn('{{%allievo}}', 'istituto', $this->string(255)->null()->after('anno_conseguimento'));
        $this->addColumn('{{%allievo}}', 'stato_occupazionale', "ENUM('occupato','disoccupato','inoccupato','studente','pensionato') NULL AFTER `istituto`");
        $this->addColumn('{{%allievo}}', 'settore_attivita', $this->string(100)->null()->after('stato_occupazionale'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%allievo}}', 'titolo_studio');
        $this->dropColumn('{{%allievo}}', 'anno_conseguimento');
        $this->dropColumn('{{%allievo}}', 'istituto');
        $this->dropColumn('{{%allievo}}', 'stato_occupazionale');
        $this->dropColumn('{{%allievo}}', 'settore_attivita');
    }
}
