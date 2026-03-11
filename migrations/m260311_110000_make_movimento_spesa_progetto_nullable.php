<?php

use yii\db\Migration;

class m260311_110000_make_movimento_spesa_progetto_nullable extends Migration
{
    public function safeUp()
    {
        // Rimuovi FK esistente
        $this->dropForeignKey('fk_movimento_spesa_progetto', '{{%movimento_spesa}}');

        // Rendi colonna nullable
        $this->alterColumn('{{%movimento_spesa}}', 'progetto_id', $this->integer()->null());

        // Ri-aggiungi FK con SET NULL
        $this->addForeignKey(
            'fk_movimento_spesa_progetto',
            '{{%movimento_spesa}}',
            'progetto_id',
            '{{%progetto}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_movimento_spesa_progetto', '{{%movimento_spesa}}');
        $this->alterColumn('{{%movimento_spesa}}', 'progetto_id', $this->integer()->notNull());
        $this->addForeignKey(
            'fk_movimento_spesa_progetto',
            '{{%movimento_spesa}}',
            'progetto_id',
            '{{%progetto}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
