<?php

use yii\db\Migration;

class m240131_000019_create_valutazione_docente_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%valutazione_docente}}', [
            'id' => $this->primaryKey(),
            'modulo_id' => $this->integer()->notNull(),
            'docente_codice_fiscale' => $this->char(16)->notNull(),
            // Valutazioni anonime - no riferimento allievo
            'punteggio_chiarezza' => $this->smallInteger(),
            'punteggio_competenza' => $this->smallInteger(),
            'punteggio_disponibilita' => $this->smallInteger(),
            'punteggio_materiali' => $this->smallInteger(),
            'punteggio_globale' => $this->smallInteger()->notNull(),
            'commento' => $this->text(),
            'data_valutazione' => $this->date()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_valutazione_modulo',
            '{{%valutazione_docente}}',
            'modulo_id',
            '{{%modulo}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_valutazione_docente',
            '{{%valutazione_docente}}',
            'docente_codice_fiscale',
            '{{%docente}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx_valutazione_modulo_docente', '{{%valutazione_docente}}', ['modulo_id', 'docente_codice_fiscale']);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_valutazione_docente', '{{%valutazione_docente}}');
        $this->dropForeignKey('fk_valutazione_modulo', '{{%valutazione_docente}}');
        $this->dropTable('{{%valutazione_docente}}');
    }
}
