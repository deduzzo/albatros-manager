<?php

use yii\db\Migration;

class m260221_100001_add_public_fields_to_avviso extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%avviso}}', 'sottotitolo', $this->string(255)->null()->after('titolo'));
        $this->addColumn('{{%avviso}}', 'obiettivi', $this->json()->null()->after('descrizione'));
        $this->addColumn('{{%avviso}}', 'destinatari', $this->text()->null()->after('obiettivi'));
        $this->addColumn('{{%avviso}}', 'prospettive', $this->json()->null()->after('destinatari'));
        $this->addColumn('{{%avviso}}', 'requisiti_ammissione', $this->json()->null()->after('prospettive'));
        $this->addColumn('{{%avviso}}', 'certificazioni', $this->json()->null()->after('requisiti_ammissione'));
        $this->addColumn('{{%avviso}}', 'colore_hex', $this->string(7)->null()->defaultValue('#206bc4')->after('certificazioni'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%avviso}}', 'sottotitolo');
        $this->dropColumn('{{%avviso}}', 'obiettivi');
        $this->dropColumn('{{%avviso}}', 'destinatari');
        $this->dropColumn('{{%avviso}}', 'prospettive');
        $this->dropColumn('{{%avviso}}', 'requisiti_ammissione');
        $this->dropColumn('{{%avviso}}', 'certificazioni');
        $this->dropColumn('{{%avviso}}', 'colore_hex');
    }
}
