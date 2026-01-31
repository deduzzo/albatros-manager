<?php

use yii\db\Migration;

class m240131_000003_create_sede_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%sede}}', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(100)->notNull(),
            'indirizzo' => $this->string(255),
            'cap' => $this->string(5),
            'citta' => $this->string(100),
            'provincia' => $this->string(2),
            'telefono' => $this->string(20),
            'email' => $this->string(100),
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Sedi iniziali
        $this->batchInsert('{{%sede}}', ['nome', 'citta', 'provincia'], [
            ['Sede Messina', 'Messina', 'ME'],
            ['Sede Catania', 'Catania', 'CT'],
            ['Sede Barcellona P.G.', 'Barcellona Pozzo di Gotto', 'ME'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%sede}}');
    }
}
