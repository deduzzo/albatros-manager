<?php

use yii\db\Migration;

class m240131_000006_create_progetto_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%progetto}}', [
            'id' => $this->primaryKey(),
            'fonte_finanziamento_id' => $this->integer()->notNull(),
            'conto_corrente_id' => $this->integer(),
            'codice_progetto' => $this->string(100)->notNull()->unique(),
            'titolo' => $this->string(255)->notNull(),
            'descrizione' => $this->text(),
            'budget_totale' => $this->decimal(12, 2),
            'data_inizio' => $this->date(),
            'data_fine' => $this->date(),
            'status' => "ENUM('bozza', 'attivo', 'sospeso', 'concluso') NOT NULL DEFAULT 'bozza'",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_progetto_fonte_finanziamento',
            '{{%progetto}}',
            'fonte_finanziamento_id',
            '{{%fonte_finanziamento}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_progetto_conto_corrente',
            '{{%progetto}}',
            'conto_corrente_id',
            '{{%conto_corrente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_progetto_conto_corrente', '{{%progetto}}');
        $this->dropForeignKey('fk_progetto_fonte_finanziamento', '{{%progetto}}');
        $this->dropTable('{{%progetto}}');
    }
}
