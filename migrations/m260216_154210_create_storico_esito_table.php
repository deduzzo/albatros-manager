<?php

use yii\db\Migration;

/**
 * Crea la tabella storico_esito per tracciare tutti i cambiamenti di stato
 * delle istanze docente (idoneo, assegnato, rinuncia, subentro, non_idoneo).
 *
 * Ogni riga rappresenta un evento nel ciclo di vita dell'istanza.
 */
class m260216_154210_create_storico_esito_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('storico_esito', [
            'id' => $this->primaryKey(),
            'istanza_id' => $this->integer()->notNull(),
            'esito' => "ENUM('assegnato', 'idoneo', 'non_idoneo', 'rinuncia', 'subentro') NOT NULL",
            'data_esito' => $this->date(),
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-storico_esito-istanza_id',
            'storico_esito',
            'istanza_id',
            'istanza',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-storico_esito-created_by',
            'storico_esito',
            'created_by',
            'utente',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->createIndex('idx-storico_esito-istanza_id', 'storico_esito', 'istanza_id');

        // Popola storico da dati esistenti
        // Per le rinunce: prima il record "assegnato" originale, poi "rinuncia"
        $this->execute("
            INSERT INTO storico_esito (istanza_id, esito, data_esito, created_at)
            SELECT id, 'assegnato', DATE(created_at), created_at
            FROM istanza
            WHERE status = 'rinuncia' AND esito_cts = 'assegnato' AND deleted_at IS NULL
        ");
        $this->execute("
            INSERT INTO storico_esito (istanza_id, esito, data_esito, created_at)
            SELECT id, 'rinuncia', DATE(updated_at), updated_at
            FROM istanza
            WHERE status = 'rinuncia' AND deleted_at IS NULL
        ");

        // Per tutte le altre istanze con esito_cts: un singolo record
        $this->execute("
            INSERT INTO storico_esito (istanza_id, esito, data_esito, created_at)
            SELECT id, esito_cts, DATE(created_at), created_at
            FROM istanza
            WHERE esito_cts IS NOT NULL AND status != 'rinuncia' AND deleted_at IS NULL
        ");
    }

    public function safeDown()
    {
        $this->dropTable('storico_esito');
    }
}
