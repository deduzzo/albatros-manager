<?php

use yii\db\Migration;

/**
 * Crea la tabella docente_auth per l'autenticazione docenti.
 * Separata dalla tabella utente per una gestione indipendente.
 */
class m250204_000001_create_docente_auth_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%docente_auth}}', [
            'id' => $this->primaryKey(),
            'docente_codice_fiscale' => $this->char(16)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'email_verify_token' => $this->string(64)->null(),
            'password_reset_token' => $this->string(64)->null(),
            'status' => "ENUM('pending_verification', 'active', 'suspended') NOT NULL DEFAULT 'pending_verification'",
            'email_verified_at' => $this->timestamp()->null(),
            'last_login_at' => $this->timestamp()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        // Foreign keys
        $this->addForeignKey(
            'fk_docente_auth_docente',
            '{{%docente_auth}}',
            'docente_codice_fiscale',
            '{{%docente}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey('fk_docente_auth_created_by', '{{%docente_auth}}', 'created_by', '{{%utente}}', 'id', 'SET NULL');
        $this->addForeignKey('fk_docente_auth_updated_by', '{{%docente_auth}}', 'updated_by', '{{%utente}}', 'id', 'SET NULL');
        $this->addForeignKey('fk_docente_auth_deleted_by', '{{%docente_auth}}', 'deleted_by', '{{%utente}}', 'id', 'SET NULL');

        // Indexes
        $this->createIndex('idx_docente_auth_status', '{{%docente_auth}}', 'status');
        $this->createIndex('idx_docente_auth_email_verify', '{{%docente_auth}}', 'email_verify_token');
        $this->createIndex('idx_docente_auth_password_reset', '{{%docente_auth}}', 'password_reset_token');
        $this->createIndex('idx_docente_auth_deleted', '{{%docente_auth}}', 'deleted_at');

        return true;
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_docente_auth_deleted_by', '{{%docente_auth}}');
        $this->dropForeignKey('fk_docente_auth_updated_by', '{{%docente_auth}}');
        $this->dropForeignKey('fk_docente_auth_created_by', '{{%docente_auth}}');
        $this->dropForeignKey('fk_docente_auth_docente', '{{%docente_auth}}');
        $this->dropTable('{{%docente_auth}}');

        return true;
    }
}
