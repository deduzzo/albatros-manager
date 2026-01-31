<?php

use yii\db\Migration;

class m240131_000013_create_assegnazione_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%assegnazione}}', [
            'id' => $this->primaryKey(),
            'modulo_id' => $this->integer()->notNull(),
            'docente_codice_fiscale' => $this->char(16)->notNull(),
            'istanza_id' => $this->integer(),
            'data_assegnazione' => $this->date()->notNull(),
            'delibera_cts' => $this->string(100),
            'data_delibera' => $this->date(),
            'status' => "ENUM('assegnato', 'accettato', 'rifiutato', 'revocato') NOT NULL DEFAULT 'assegnato'",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_assegnazione_unica', '{{%assegnazione}}', ['modulo_id', 'docente_codice_fiscale'], true);

        $this->addForeignKey(
            'fk_assegnazione_modulo',
            '{{%assegnazione}}',
            'modulo_id',
            '{{%modulo}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_assegnazione_docente',
            '{{%assegnazione}}',
            'docente_codice_fiscale',
            '{{%docente}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_assegnazione_istanza',
            '{{%assegnazione}}',
            'istanza_id',
            '{{%istanza}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_assegnazione_istanza', '{{%assegnazione}}');
        $this->dropForeignKey('fk_assegnazione_docente', '{{%assegnazione}}');
        $this->dropForeignKey('fk_assegnazione_modulo', '{{%assegnazione}}');
        $this->dropTable('{{%assegnazione}}');
    }
}
