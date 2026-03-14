<?php

use yii\db\Migration;

class m260314_200001_create_notifica_lettura_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('notifica_lettura', [
            'id' => $this->primaryKey(),
            'notifica_id' => $this->integer()->notNull(),
            'user_type' => "ENUM('admin','docente','studente') NOT NULL",
            'user_id' => $this->string(50)->notNull(),
            'letta_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('uk_notifica_lettura', 'notifica_lettura', ['notifica_id', 'user_type', 'user_id'], true);
        $this->addForeignKey('fk_notifica_lettura_notifica', 'notifica_lettura', 'notifica_id', 'notifica', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('notifica_lettura');
    }
}
