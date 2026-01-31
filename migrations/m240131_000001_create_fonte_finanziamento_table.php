<?php

use yii\db\Migration;

class m240131_000001_create_fonte_finanziamento_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%fonte_finanziamento}}', [
            'id' => $this->primaryKey(),
            'codice' => $this->string(50)->notNull()->unique(),
            'descrizione' => $this->string(255)->notNull(),
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Dati iniziali
        $this->batchInsert('{{%fonte_finanziamento}}', ['codice', 'descrizione'], [
            ['FSE', 'Fondo Sociale Europeo'],
            ['PNRR', 'Piano Nazionale di Ripresa e Resilienza'],
            ['MIM', 'Ministero dell\'Istruzione e del Merito'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%fonte_finanziamento}}');
    }
}
