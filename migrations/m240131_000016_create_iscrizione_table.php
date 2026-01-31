<?php

use yii\db\Migration;

class m240131_000016_create_iscrizione_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%iscrizione}}', [
            'id' => $this->primaryKey(),
            'modulo_id' => $this->integer()->notNull(),
            'allievo_codice_fiscale' => $this->char(16)->notNull(),
            'data_iscrizione' => $this->date()->notNull(),
            'data_ritiro' => $this->date(),
            'motivo_ritiro' => $this->string(255),
            'status' => "ENUM('iscritto', 'frequentante', 'ritirato', 'completato', 'non_ammesso') NOT NULL DEFAULT 'iscritto'",
            'percentuale_frequenza' => $this->decimal(5, 2),
            'esito_finale' => "ENUM('idoneo', 'non_idoneo', 'ritirato') NULL",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_iscrizione_unica', '{{%iscrizione}}', ['modulo_id', 'allievo_codice_fiscale'], true);

        $this->addForeignKey(
            'fk_iscrizione_modulo',
            '{{%iscrizione}}',
            'modulo_id',
            '{{%modulo}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_iscrizione_allievo',
            '{{%iscrizione}}',
            'allievo_codice_fiscale',
            '{{%allievo}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_iscrizione_allievo', '{{%iscrizione}}');
        $this->dropForeignKey('fk_iscrizione_modulo', '{{%iscrizione}}');
        $this->dropTable('{{%iscrizione}}');
    }
}
