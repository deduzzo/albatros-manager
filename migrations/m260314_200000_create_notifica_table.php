<?php

use yii\db\Migration;

class m260314_200000_create_notifica_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('notifica', [
            'id' => $this->primaryKey(),
            'titolo' => $this->string(255)->notNull(),
            'contenuto' => $this->text()->notNull(),
            'link' => $this->string(500)->null(),
            'destinatario_tipo' => "ENUM('tutti','admin','docente','studente','singolo_admin','singolo_docente','singolo_studente') NOT NULL",
            'destinatario_id' => $this->string(50)->null(),
            'invia_email' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'email_inviata' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx_notifica_tipo', 'notifica', 'destinatario_tipo');
        $this->createIndex('idx_notifica_deleted', 'notifica', 'deleted_at');
    }

    public function safeDown()
    {
        $this->dropTable('notifica');
    }
}
