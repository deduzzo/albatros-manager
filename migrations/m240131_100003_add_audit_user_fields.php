<?php

use yii\db\Migration;

/**
 * Aggiunge i campi created_by, updated_by, deleted_by a tutte le tabelle
 * per tracciare quale utente ha effettuato le operazioni.
 */
class m240131_100003_add_audit_user_fields extends Migration
{
    /**
     * Tabelle da modificare (escludiamo migration e audit_log)
     */
    private $tables = [
        'allievo',
        'assegnazione',
        'avviso',
        'calendario',
        'conto_corrente',
        'contratto',
        'docente',
        'documentazione',
        'fonte_finanziamento',
        'iscrizione',
        'istanza',
        'modulo',
        'pagamento',
        'presenza_allievo',
        'progetto',
        'sede',
        'tipo_documento',
        'utente',
        'valutazione_docente',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->tables as $table) {
            // Aggiungi created_by
            $this->addColumn($table, 'created_by', $this->integer()->null()->after('deleted_at'));

            // Aggiungi updated_by
            $this->addColumn($table, 'updated_by', $this->integer()->null()->after('created_by'));

            // Aggiungi deleted_by
            $this->addColumn($table, 'deleted_by', $this->integer()->null()->after('updated_by'));

            // Aggiungi foreign keys verso la tabella utente
            $this->addForeignKey(
                "fk_{$table}_created_by",
                $table,
                'created_by',
                'utente',
                'id',
                'SET NULL',
                'CASCADE'
            );

            $this->addForeignKey(
                "fk_{$table}_updated_by",
                $table,
                'updated_by',
                'utente',
                'id',
                'SET NULL',
                'CASCADE'
            );

            $this->addForeignKey(
                "fk_{$table}_deleted_by",
                $table,
                'deleted_by',
                'utente',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->tables as $table) {
            // Rimuovi foreign keys
            $this->dropForeignKey("fk_{$table}_created_by", $table);
            $this->dropForeignKey("fk_{$table}_updated_by", $table);
            $this->dropForeignKey("fk_{$table}_deleted_by", $table);

            // Rimuovi colonne
            $this->dropColumn($table, 'created_by');
            $this->dropColumn($table, 'updated_by');
            $this->dropColumn($table, 'deleted_by');
        }

        return true;
    }
}
