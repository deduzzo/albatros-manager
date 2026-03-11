<?php

use yii\db\Migration;

/**
 * Crea la tabella rimborso_spesa per le richieste di rimborso dei docenti.
 */
class m260311_100200_create_rimborso_spesa_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%rimborso_spesa}}', [
            'id' => $this->primaryKey(),
            'assegnazione_id' => $this->integer()->notNull(),
            'tipo' => "ENUM('materiale','chilometrico','vitto_alloggio','viaggio','altro') NOT NULL",
            'descrizione' => $this->string(500)->notNull(),
            'importo' => $this->decimal(10, 2)->notNull(),
            'data_spesa' => $this->date()->notNull(),
            'allegato_path' => $this->string(500)->null(),
            'status' => "ENUM('richiesto','approvato','rifiutato') NOT NULL DEFAULT 'richiesto'",
            'motivo_rifiuto' => $this->text()->null(),
            'approvato_da' => $this->integer()->null(),
            'approvato_at' => $this->timestamp()->null(),
            'note' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk_rimborso_spesa_assegnazione',
            '{{%rimborso_spesa}}',
            'assegnazione_id',
            '{{%assegnazione}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_rimborso_spesa_approvato_da',
            '{{%rimborso_spesa}}',
            'approvato_da',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->createIndex('idx_rimborso_spesa_deleted', '{{%rimborso_spesa}}', 'deleted_at');
        $this->createIndex('idx_rimborso_spesa_assegnazione', '{{%rimborso_spesa}}', 'assegnazione_id');
        $this->createIndex('idx_rimborso_spesa_status', '{{%rimborso_spesa}}', 'status');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_rimborso_spesa_approvato_da', '{{%rimborso_spesa}}');
        $this->dropForeignKey('fk_rimborso_spesa_assegnazione', '{{%rimborso_spesa}}');
        $this->dropTable('{{%rimborso_spesa}}');
    }
}
