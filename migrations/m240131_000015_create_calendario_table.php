<?php

use yii\db\Migration;

class m240131_000015_create_calendario_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%calendario}}', [
            'id' => $this->primaryKey(),
            'modulo_id' => $this->integer()->notNull(),
            'docente_codice_fiscale' => $this->char(16)->notNull(),
            'data' => $this->date()->notNull(),
            'ora_inizio' => $this->time()->notNull(),
            'ora_fine' => $this->time()->notNull(),
            'ore' => $this->decimal(4, 2)->notNull(),
            'aula' => $this->string(50),
            'argomento' => $this->string(255),
            'descrizione_attivita' => $this->text(),
            'status' => "ENUM('programmata', 'completata', 'annullata', 'recupero') NOT NULL DEFAULT 'programmata'",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_calendario_data', '{{%calendario}}', ['modulo_id', 'data']);

        $this->addForeignKey(
            'fk_calendario_modulo',
            '{{%calendario}}',
            'modulo_id',
            '{{%modulo}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_calendario_docente',
            '{{%calendario}}',
            'docente_codice_fiscale',
            '{{%docente}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_calendario_docente', '{{%calendario}}');
        $this->dropForeignKey('fk_calendario_modulo', '{{%calendario}}');
        $this->dropTable('{{%calendario}}');
    }
}
