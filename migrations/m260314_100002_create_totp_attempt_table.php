<?php

use yii\db\Migration;

/**
 * Crea la tabella totp_attempt per tracciare tentativi di verifica 2FA.
 */
class m260314_100002_create_totp_attempt_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('totp_attempt', [
            'id' => $this->primaryKey(),
            'user_type' => "ENUM('admin','docente','studente') NOT NULL",
            'user_id' => $this->string(50)->notNull(),
            'attempts' => $this->integer()->notNull()->defaultValue(0),
            'locked_until' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('uk_user', 'totp_attempt', ['user_type', 'user_id'], true);
    }

    public function safeDown()
    {
        $this->dropTable('totp_attempt');
    }
}
