<?php

use yii\db\Migration;

/**
 * Crea la tabella avviso_documento_tipo per definire i documenti richiesti per ogni bando.
 */
class m250204_000002_create_avviso_documento_tipo_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%avviso_documento_tipo}}', [
            'id' => $this->primaryKey(),
            'avviso_id' => $this->integer()->notNull(),
            'nome' => $this->string(100)->notNull(),
            'descrizione' => $this->text()->null(),
            'formati_ammessi' => $this->string(100)->notNull()->defaultValue('pdf'),
            'dimensione_max_mb' => $this->integer()->notNull()->defaultValue(5),
            'obbligatorio' => $this->boolean()->notNull()->defaultValue(true),
            'ordine' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        // Foreign keys
        $this->addForeignKey(
            'fk_avviso_doc_tipo_avviso',
            '{{%avviso_documento_tipo}}',
            'avviso_id',
            '{{%avviso}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey('fk_avviso_doc_tipo_created_by', '{{%avviso_documento_tipo}}', 'created_by', '{{%utente}}', 'id', 'SET NULL');
        $this->addForeignKey('fk_avviso_doc_tipo_updated_by', '{{%avviso_documento_tipo}}', 'updated_by', '{{%utente}}', 'id', 'SET NULL');
        $this->addForeignKey('fk_avviso_doc_tipo_deleted_by', '{{%avviso_documento_tipo}}', 'deleted_by', '{{%utente}}', 'id', 'SET NULL');

        // Indexes
        $this->createIndex('idx_avviso_doc_tipo_avviso', '{{%avviso_documento_tipo}}', 'avviso_id');
        $this->createIndex('idx_avviso_doc_tipo_deleted', '{{%avviso_documento_tipo}}', 'deleted_at');

        return true;
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_avviso_doc_tipo_deleted_by', '{{%avviso_documento_tipo}}');
        $this->dropForeignKey('fk_avviso_doc_tipo_updated_by', '{{%avviso_documento_tipo}}');
        $this->dropForeignKey('fk_avviso_doc_tipo_created_by', '{{%avviso_documento_tipo}}');
        $this->dropForeignKey('fk_avviso_doc_tipo_avviso', '{{%avviso_documento_tipo}}');
        $this->dropTable('{{%avviso_documento_tipo}}');

        return true;
    }
}
