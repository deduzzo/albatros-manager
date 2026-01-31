<?php

use yii\db\Migration;

class m240131_000005_create_tipo_documento_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%tipo_documento}}', [
            'id' => $this->primaryKey(),
            'codice' => $this->string(50)->notNull()->unique(),
            'descrizione' => $this->string(255)->notNull(),
            'richiede_scadenza' => $this->boolean()->notNull()->defaultValue(false),
            'attivo' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Tipologie documento iniziali
        $this->batchInsert('{{%tipo_documento}}', ['codice', 'descrizione', 'richiede_scadenza'], [
            ['CV', 'Curriculum Vitae', 0],
            ['TITOLO_STUDIO', 'Titolo di Studio', 0],
            ['ATTESTATO', 'Attestato/Certificazione', 1],
            ['DOCUMENTO_IDENTITA', 'Documento di Identità', 1],
            ['MODULO_DOMANDA', 'Modulo Domanda', 0],
            ['DICHIARAZIONE', 'Dichiarazione', 0],
            ['CONTRATTO', 'Contratto Firmato', 0],
            ['ALTRO', 'Altro Documento', 0],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%tipo_documento}}');
    }
}
