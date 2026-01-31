<?php

use yii\db\Migration;

class m240131_000008_create_docente_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%docente}}', [
            'codice_fiscale' => $this->char(16)->notNull(),
            'cognome' => $this->string(100)->notNull(),
            'nome' => $this->string(100)->notNull(),
            'data_nascita' => $this->date(),
            'luogo_nascita' => $this->string(100),
            'indirizzo' => $this->string(255),
            'cap' => $this->string(5),
            'citta' => $this->string(100),
            'provincia' => $this->string(2),
            'telefono' => $this->string(20),
            'email' => $this->string(100),
            'titolo_studio' => $this->string(255),
            'specializzazioni' => $this->text(),
            'partita_iva' => $this->string(11),
            'iban' => $this->string(34),
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'PRIMARY KEY ([[codice_fiscale]])',
        ]);

        $this->createIndex('idx_docente_cognome_nome', '{{%docente}}', ['cognome', 'nome']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%docente}}');
    }
}
