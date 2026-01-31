<?php

use yii\db\Migration;

/**
 * Tabella per audit logging completo
 */
class m240131_100002_create_audit_log_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%audit_log}}', [
            'id' => $this->bigPrimaryKey(),

            // Riferimento al record modificato
            'table_name' => $this->string(100)->notNull(),
            'record_id' => $this->string(50)->notNull(), // string per supportare PK composite o CF

            // Tipo di azione
            'action' => "ENUM('INSERT', 'UPDATE', 'DELETE', 'SOFT_DELETE', 'RESTORE') NOT NULL",

            // Valori prima e dopo (JSON)
            'old_values' => $this->json(),
            'new_values' => $this->json(),

            // Campi modificati (per UPDATE)
            'changed_fields' => $this->json(),

            // Chi ha fatto la modifica
            'user_id' => $this->integer(),
            'user_type' => $this->string(50)->defaultValue('utente'), // utente, sistema, api
            'username' => $this->string(100), // cached per storico

            // Contesto della richiesta
            'ip_address' => $this->string(45), // IPv6 support
            'user_agent' => $this->string(500),
            'request_url' => $this->string(500),
            'request_method' => $this->string(10),

            // Metadata aggiuntivi
            'note' => $this->text(),
            'session_id' => $this->string(100),

            // Timestamp
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Indici per ricerche frequenti
        $this->createIndex('idx_audit_table_record', '{{%audit_log}}', ['table_name', 'record_id']);
        $this->createIndex('idx_audit_user', '{{%audit_log}}', 'user_id');
        $this->createIndex('idx_audit_action', '{{%audit_log}}', 'action');
        $this->createIndex('idx_audit_created', '{{%audit_log}}', 'created_at');
        $this->createIndex('idx_audit_table_created', '{{%audit_log}}', ['table_name', 'created_at']);

        // FK opzionale verso utente (può essere null per azioni di sistema)
        $this->addForeignKey(
            'fk_audit_user',
            '{{%audit_log}}',
            'user_id',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_audit_user', '{{%audit_log}}');
        $this->dropTable('{{%audit_log}}');
    }
}
