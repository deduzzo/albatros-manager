<?php

use yii\db\Migration;

class m260221_100005_add_avviso_id_to_iscrizione extends Migration
{
    public function safeUp()
    {
        // Aggiungere avviso_id per iscrizione a livello di bando
        $this->addColumn('{{%iscrizione}}', 'avviso_id', $this->integer()->null()->after('id'));
        $this->addForeignKey(
            'fk_iscrizione_avviso',
            '{{%iscrizione}}',
            'avviso_id',
            '{{%avviso}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Rendere modulo_id nullable (non piu obbligatorio per iscrizione a bando)
        $this->alterColumn('{{%iscrizione}}', 'modulo_id', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_iscrizione_avviso', '{{%iscrizione}}');
        $this->dropColumn('{{%iscrizione}}', 'avviso_id');
        $this->alterColumn('{{%iscrizione}}', 'modulo_id', $this->integer()->notNull());
    }
}
