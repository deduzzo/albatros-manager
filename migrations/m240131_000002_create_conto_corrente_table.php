<?php

use yii\db\Migration;

class m240131_000002_create_conto_corrente_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%conto_corrente}}', [
            'id' => $this->primaryKey(),
            'banca' => $this->string(100)->notNull(),
            'iban' => $this->string(34)->notNull()->unique(),
            'intestatario' => $this->string(255)->notNull(),
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%conto_corrente}}');
    }
}
