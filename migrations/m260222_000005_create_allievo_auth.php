<?php

use yii\db\Migration;

/**
 * Crea la tabella allievo_auth per l'autenticazione degli studenti
 * nell'area riservata. Struttura clonata da docente_auth.
 */
class m260222_000005_create_allievo_auth extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%allievo_auth}}', [
            'id' => $this->primaryKey(),
            'allievo_codice_fiscale' => $this->char(16)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'email_verify_token' => $this->string(64)->null(),
            'password_reset_token' => $this->string(64)->null(),
            'status' => "ENUM('pending_verification','active','suspended') NOT NULL DEFAULT 'active'",
            'email_verified_at' => $this->dateTime()->null(),
            'last_login_at' => $this->dateTime()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        // FK verso allievo (ON DELETE CASCADE)
        $this->addForeignKey(
            'fk_allievo_auth_allievo',
            '{{%allievo_auth}}',
            'allievo_codice_fiscale',
            '{{%allievo}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );

        // FK audit _by verso utente
        $this->addForeignKey(
            'fk_allievo_auth_created_by',
            '{{%allievo_auth}}',
            'created_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_allievo_auth_updated_by',
            '{{%allievo_auth}}',
            'updated_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_allievo_auth_deleted_by',
            '{{%allievo_auth}}',
            'deleted_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Indici
        $this->createIndex('idx_allievo_auth_deleted', '{{%allievo_auth}}', 'deleted_at');
        $this->createIndex('idx_allievo_auth_status', '{{%allievo_auth}}', 'status');
        $this->createIndex('idx_allievo_auth_email_verify', '{{%allievo_auth}}', 'email_verify_token');
        $this->createIndex('idx_allievo_auth_pwd_reset', '{{%allievo_auth}}', 'password_reset_token');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_allievo_auth_deleted_by', '{{%allievo_auth}}');
        $this->dropForeignKey('fk_allievo_auth_updated_by', '{{%allievo_auth}}');
        $this->dropForeignKey('fk_allievo_auth_created_by', '{{%allievo_auth}}');
        $this->dropForeignKey('fk_allievo_auth_allievo', '{{%allievo_auth}}');
        $this->dropTable('{{%allievo_auth}}');
    }
}
