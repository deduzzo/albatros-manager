<?php

use yii\db\Migration;

class m240131_000012_create_istanza_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%istanza}}', [
            'id' => $this->primaryKey(),
            'avviso_id' => $this->integer()->notNull(),
            'docente_codice_fiscale' => $this->char(16)->notNull(),
            'modulo_id' => $this->integer()->notNull(),
            'data_presentazione' => $this->date()->notNull(),
            'punteggio_titoli' => $this->decimal(5, 2),
            'punteggio_esperienza' => $this->decimal(5, 2),
            'punteggio_totale' => $this->decimal(5, 2),
            'posizione_graduatoria' => $this->integer(),
            'status' => "ENUM('presentata', 'in_valutazione', 'valutata', 'accettata', 'rifiutata', 'rinuncia') NOT NULL DEFAULT 'presentata'",
            'note_valutazione' => $this->text(),
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_istanza_unica', '{{%istanza}}', ['avviso_id', 'docente_codice_fiscale', 'modulo_id'], true);

        $this->addForeignKey(
            'fk_istanza_avviso',
            '{{%istanza}}',
            'avviso_id',
            '{{%avviso}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_istanza_docente',
            '{{%istanza}}',
            'docente_codice_fiscale',
            '{{%docente}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_istanza_modulo',
            '{{%istanza}}',
            'modulo_id',
            '{{%modulo}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_istanza_modulo', '{{%istanza}}');
        $this->dropForeignKey('fk_istanza_docente', '{{%istanza}}');
        $this->dropForeignKey('fk_istanza_avviso', '{{%istanza}}');
        $this->dropTable('{{%istanza}}');
    }
}
