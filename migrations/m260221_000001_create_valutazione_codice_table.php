<?php

use yii\db\Migration;

class m260221_000001_create_valutazione_codice_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%valutazione_codice}}', [
            'id' => $this->primaryKey(),
            'modulo_id' => $this->integer()->notNull(),
            'codice' => $this->string(8)->notNull()->unique(),
            'usato_at' => $this->timestamp()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk_valutazione_codice_modulo',
            '{{%valutazione_codice}}',
            'modulo_id',
            '{{%modulo}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_valutazione_codice_created_by',
            '{{%valutazione_codice}}',
            'created_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Aggiungere campo valutazione_codice_id a valutazione_docente
        $this->addColumn('{{%valutazione_docente}}', 'valutazione_codice_id', $this->integer()->null()->after('id'));

        $this->addForeignKey(
            'fk_valutazione_docente_codice',
            '{{%valutazione_docente}}',
            'valutazione_codice_id',
            '{{%valutazione_codice}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_valutazione_docente_codice', '{{%valutazione_docente}}');
        $this->dropColumn('{{%valutazione_docente}}', 'valutazione_codice_id');
        $this->dropForeignKey('fk_valutazione_codice_created_by', '{{%valutazione_codice}}');
        $this->dropForeignKey('fk_valutazione_codice_modulo', '{{%valutazione_codice}}');
        $this->dropTable('{{%valutazione_codice}}');
    }
}
