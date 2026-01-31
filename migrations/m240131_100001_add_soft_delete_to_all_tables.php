<?php

use yii\db\Migration;

/**
 * Aggiunge soft delete (deleted_at) a tutte le tabelle
 */
class m240131_100001_add_soft_delete_to_all_tables extends Migration
{
    private $tables = [
        'fonte_finanziamento',
        'conto_corrente',
        'sede',
        'utente',
        'tipo_documento',
        'progetto',
        'avviso',
        'docente',
        'allievo',
        'modulo',
        'documentazione',
        'istanza',
        'assegnazione',
        'contratto',
        'calendario',
        'iscrizione',
        'presenza_allievo',
        'pagamento',
        'valutazione_docente',
    ];

    public function safeUp()
    {
        foreach ($this->tables as $table) {
            $this->addColumn(
                "{{%$table}}",
                'deleted_at',
                $this->timestamp()->null()->defaultValue(null)
            );

            // Indice per performance sulle query che escludono i deleted
            $this->createIndex(
                "idx_{$table}_deleted_at",
                "{{%$table}}",
                'deleted_at'
            );
        }
    }

    public function safeDown()
    {
        foreach ($this->tables as $table) {
            $this->dropIndex("idx_{$table}_deleted_at", "{{%$table}}");
            $this->dropColumn("{{%$table}}", 'deleted_at');
        }
    }
}
