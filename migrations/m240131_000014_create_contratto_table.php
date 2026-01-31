<?php

use yii\db\Migration;

class m240131_000014_create_contratto_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%contratto}}', [
            'id' => $this->primaryKey(),
            'assegnazione_id' => $this->integer()->notNull(),
            'tipo' => "ENUM('cococo', 'partita_iva', 'occasionale') NOT NULL",
            'numero_contratto' => $this->string(50)->unique(),
            'data_stipula' => $this->date()->notNull(),
            'data_inizio' => $this->date()->notNull(),
            'data_fine' => $this->date()->notNull(),
            'compenso_orario' => $this->decimal(8, 2)->notNull(),
            'ore_contrattualizzate' => $this->integer()->notNull(),
            'compenso_totale' => $this->decimal(10, 2)->notNull(),
            'ritenute' => $this->decimal(10, 2),
            'netto_docente' => $this->decimal(10, 2),
            'documento_path' => $this->string(500),
            'status' => "ENUM('bozza', 'firmato', 'attivo', 'sospeso', 'concluso', 'annullato') NOT NULL DEFAULT 'bozza'",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_contratto_assegnazione',
            '{{%contratto}}',
            'assegnazione_id',
            '{{%assegnazione}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_contratto_assegnazione', '{{%contratto}}');
        $this->dropTable('{{%contratto}}');
    }
}
