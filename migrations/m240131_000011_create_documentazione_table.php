<?php

use yii\db\Migration;

class m240131_000011_create_documentazione_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%documentazione}}', [
            'id' => $this->primaryKey(),
            'tipo_documento_id' => $this->integer()->notNull(),
            'docente_codice_fiscale' => $this->char(16),
            'allievo_codice_fiscale' => $this->char(16),
            'titolo' => $this->string(255)->notNull(),
            'descrizione' => $this->text(),
            'file_path' => $this->string(500)->notNull(),
            'file_nome_originale' => $this->string(255),
            'file_size' => $this->integer(),
            'file_mime_type' => $this->string(100),
            'data_documento' => $this->date(),
            'data_scadenza' => $this->date(),
            'status' => "ENUM('attivo', 'archiviato', 'scaduto') NOT NULL DEFAULT 'attivo'",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_documentazione_tipo',
            '{{%documentazione}}',
            'tipo_documento_id',
            '{{%tipo_documento}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_documentazione_docente',
            '{{%documentazione}}',
            'docente_codice_fiscale',
            '{{%docente}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_documentazione_allievo',
            '{{%documentazione}}',
            'allievo_codice_fiscale',
            '{{%allievo}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx_documentazione_docente', '{{%documentazione}}', 'docente_codice_fiscale');
        $this->createIndex('idx_documentazione_allievo', '{{%documentazione}}', 'allievo_codice_fiscale');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_documentazione_allievo', '{{%documentazione}}');
        $this->dropForeignKey('fk_documentazione_docente', '{{%documentazione}}');
        $this->dropForeignKey('fk_documentazione_tipo', '{{%documentazione}}');
        $this->dropTable('{{%documentazione}}');
    }
}
