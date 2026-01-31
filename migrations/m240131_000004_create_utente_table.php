<?php

use yii\db\Migration;

class m240131_000004_create_utente_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%utente}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(50)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'email' => $this->string(100)->notNull()->unique(),
            'ruolo' => "ENUM('admin', 'segreteria', 'coordinatore', 'operatore') NOT NULL DEFAULT 'operatore'",
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'auth_key' => $this->string(32),
            'password_reset_token' => $this->string(255)->unique(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%utente}}');
    }
}
