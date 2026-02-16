<?php

use yii\db\Migration;

/**
 * Crea la tabella rendicontazione_ore per tracciare le ore lavorate dai docenti.
 * Aggiunge campi per ore extra alla tabella pagamento.
 */
class m260216_200000_create_rendicontazione_ore_table extends Migration
{
    public function safeUp()
    {
        // Tabella rendicontazione_ore
        $this->createTable('rendicontazione_ore', [
            'id' => $this->primaryKey(),
            'assegnazione_id' => $this->integer()->notNull(),
            'data' => $this->date()->notNull(),
            'ore' => $this->decimal(4, 2)->notNull(),
            'ora_inizio' => $this->time(),
            'ora_fine' => $this->time(),
            'firma_digitale' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'status' => "ENUM('inserito','approvato','rifiutato') NOT NULL DEFAULT 'inserito'",
            'note' => $this->text(),
            'motivo_rifiuto' => $this->text(),
            'approvato_da' => $this->integer(),
            'approvato_at' => $this->timestamp()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_by' => $this->integer(),
        ]);

        // Foreign keys
        $this->addForeignKey(
            'fk-rendicontazione_ore-assegnazione_id',
            'rendicontazione_ore',
            'assegnazione_id',
            'assegnazione',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-rendicontazione_ore-approvato_da',
            'rendicontazione_ore',
            'approvato_da',
            'utente',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-rendicontazione_ore-created_by',
            'rendicontazione_ore',
            'created_by',
            'utente',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-rendicontazione_ore-updated_by',
            'rendicontazione_ore',
            'updated_by',
            'utente',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-rendicontazione_ore-deleted_by',
            'rendicontazione_ore',
            'deleted_by',
            'utente',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Indici
        $this->createIndex(
            'idx-rendicontazione_ore-assegnazione_id',
            'rendicontazione_ore',
            'assegnazione_id'
        );
        $this->createIndex(
            'idx-rendicontazione_ore-data',
            'rendicontazione_ore',
            'data'
        );
        $this->createIndex(
            'idx-rendicontazione_ore-status',
            'rendicontazione_ore',
            'status'
        );

        // Unique key per evitare doppie rendicontazioni per stessa assegnazione e data
        // deleted_at incluso per permettere re-inserimento dopo soft delete
        $this->createIndex(
            'uk-rendicontazione_ore-assegnazione_data',
            'rendicontazione_ore',
            ['assegnazione_id', 'data', 'deleted_at'],
            true
        );

        // Aggiunta campi ore extra alla tabella pagamento
        $this->addColumn('pagamento', 'ore_extra', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('note'));
        $this->addColumn('pagamento', 'ore_extra_status', "ENUM('pendente','approvato','rifiutato') NULL AFTER ore_extra");
        $this->addColumn('pagamento', 'ore_extra_approvato_da', $this->integer()->null()->after('ore_extra_status'));
        $this->addColumn('pagamento', 'ore_extra_approvato_at', $this->timestamp()->null()->after('ore_extra_approvato_da'));
        $this->addColumn('pagamento', 'ore_extra_note', $this->text()->null()->after('ore_extra_approvato_at'));

        $this->addForeignKey(
            'fk-pagamento-ore_extra_approvato_da',
            'pagamento',
            'ore_extra_approvato_da',
            'utente',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        // Rimuovi FK e colonne da pagamento
        $this->dropForeignKey('fk-pagamento-ore_extra_approvato_da', 'pagamento');
        $this->dropColumn('pagamento', 'ore_extra_note');
        $this->dropColumn('pagamento', 'ore_extra_approvato_at');
        $this->dropColumn('pagamento', 'ore_extra_approvato_da');
        $this->dropColumn('pagamento', 'ore_extra_status');
        $this->dropColumn('pagamento', 'ore_extra');

        // Rimuovi tabella rendicontazione_ore
        $this->dropTable('rendicontazione_ore');
    }
}
