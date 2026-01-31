<?php

use yii\db\Migration;

class m240131_000007_create_avviso_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%avviso}}', [
            'id' => $this->primaryKey(),
            'progetto_id' => $this->integer()->notNull(),
            'codice_avviso' => $this->string(100)->notNull(),
            'titolo' => $this->string(255)->notNull(),
            'descrizione' => $this->text(),
            'data_pubblicazione' => $this->date(),
            'data_scadenza' => $this->date(),
            'status' => "ENUM('bozza', 'pubblicato', 'chiuso', 'annullato') NOT NULL DEFAULT 'bozza'",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_avviso_codice', '{{%avviso}}', ['progetto_id', 'codice_avviso'], true);

        $this->addForeignKey(
            'fk_avviso_progetto',
            '{{%avviso}}',
            'progetto_id',
            '{{%progetto}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_avviso_progetto', '{{%avviso}}');
        $this->dropTable('{{%avviso}}');
    }
}
