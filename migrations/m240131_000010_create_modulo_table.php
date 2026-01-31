<?php

use yii\db\Migration;

class m240131_000010_create_modulo_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%modulo}}', [
            'id' => $this->primaryKey(),
            'avviso_id' => $this->integer()->notNull(),
            'sede_id' => $this->integer()->notNull(),
            'codice' => $this->string(50)->notNull(),
            'titolo' => $this->string(255)->notNull(),
            'descrizione' => $this->text(),
            'ore_totali' => $this->integer()->notNull(),
            'compenso_orario' => $this->decimal(8, 2),
            'data_inizio' => $this->date(),
            'data_fine' => $this->date(),
            'requisiti_docente' => $this->text(),
            'max_allievi' => $this->integer(),
            'status' => "ENUM('bozza', 'aperto', 'in_corso', 'concluso', 'annullato') NOT NULL DEFAULT 'bozza'",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_modulo_codice', '{{%modulo}}', ['avviso_id', 'codice'], true);

        $this->addForeignKey(
            'fk_modulo_avviso',
            '{{%modulo}}',
            'avviso_id',
            '{{%avviso}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_modulo_sede',
            '{{%modulo}}',
            'sede_id',
            '{{%sede}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_modulo_sede', '{{%modulo}}');
        $this->dropForeignKey('fk_modulo_avviso', '{{%modulo}}');
        $this->dropTable('{{%modulo}}');
    }
}
