<?php

use yii\db\Migration;

/**
 * Crea la tabella istanza_documento per i documenti caricati dai docenti nelle iscrizioni.
 */
class m250204_000003_create_istanza_documento_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%istanza_documento}}', [
            'id' => $this->primaryKey(),
            'istanza_id' => $this->integer()->notNull(),
            'avviso_documento_tipo_id' => $this->integer()->notNull(),
            'nome_file_originale' => $this->string(255)->notNull(),
            'nome_file_salvato' => $this->string(100)->notNull(),
            'path' => $this->string(255)->notNull(),
            'mime_type' => $this->string(100)->notNull(),
            'dimensione_bytes' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->null(),
        ]);

        // Foreign keys
        $this->addForeignKey(
            'fk_istanza_doc_istanza',
            '{{%istanza_documento}}',
            'istanza_id',
            '{{%istanza}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_istanza_doc_tipo',
            '{{%istanza_documento}}',
            'avviso_documento_tipo_id',
            '{{%avviso_documento_tipo}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey('fk_istanza_doc_created_by', '{{%istanza_documento}}', 'created_by', '{{%utente}}', 'id', 'SET NULL');

        // Indexes
        $this->createIndex('idx_istanza_doc_istanza', '{{%istanza_documento}}', 'istanza_id');
        $this->createIndex('idx_istanza_doc_tipo', '{{%istanza_documento}}', 'avviso_documento_tipo_id');

        return true;
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_istanza_doc_created_by', '{{%istanza_documento}}');
        $this->dropForeignKey('fk_istanza_doc_tipo', '{{%istanza_documento}}');
        $this->dropForeignKey('fk_istanza_doc_istanza', '{{%istanza_documento}}');
        $this->dropTable('{{%istanza_documento}}');

        return true;
    }
}
