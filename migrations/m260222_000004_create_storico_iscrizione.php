<?php

use yii\db\Migration;

/**
 * Crea la tabella storico_iscrizione per tracciare i cambi di stato
 * delle iscrizioni studenti (analogo a storico_esito per i docenti).
 * NO soft delete, NO audit _by (solo created_at e created_by).
 */
class m260222_000004_create_storico_iscrizione extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%storico_iscrizione}}', [
            'id' => $this->primaryKey(),
            'iscrizione_id' => $this->integer()->notNull(),
            'status_precedente' => $this->string(30)->null(),
            'status_nuovo' => $this->string(30)->notNull(),
            'note' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->null(),
        ]);

        // FK verso iscrizione
        $this->addForeignKey(
            'fk_storico_iscrizione_iscrizione',
            '{{%storico_iscrizione}}',
            'iscrizione_id',
            '{{%iscrizione}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // FK created_by verso utente
        $this->addForeignKey(
            'fk_storico_iscrizione_created_by',
            '{{%storico_iscrizione}}',
            'created_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Indice per ricerca per iscrizione
        $this->createIndex('idx_storico_iscrizione_iscrizione', '{{%storico_iscrizione}}', 'iscrizione_id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_storico_iscrizione_created_by', '{{%storico_iscrizione}}');
        $this->dropForeignKey('fk_storico_iscrizione_iscrizione', '{{%storico_iscrizione}}');
        $this->dropTable('{{%storico_iscrizione}}');
    }
}
