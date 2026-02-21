<?php

use yii\db\Migration;

/**
 * Crea la tabella criterio_valutazione_studente per definire i criteri
 * di valutazione delle candidature studenti (globali o specifici per bando).
 */
class m260222_000002_create_criterio_valutazione_studente extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%criterio_valutazione_studente}}', [
            'id' => $this->primaryKey(),
            'avviso_id' => $this->integer()->null()->comment('NULL=globale, valorizzato=specifico per bando'),
            'nome' => $this->string(255)->notNull(),
            'descrizione' => $this->text()->null(),
            'peso' => $this->decimal(5, 2)->defaultValue(1.0),
            'punteggio_min' => $this->decimal(5, 2)->defaultValue(0),
            'punteggio_max' => $this->decimal(5, 2)->defaultValue(10),
            'ordine' => $this->integer()->defaultValue(0),
            'attivo' => $this->boolean()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        // FK verso avviso (ON DELETE SET NULL)
        $this->addForeignKey(
            'fk_criterio_val_studente_avviso',
            '{{%criterio_valutazione_studente}}',
            'avviso_id',
            '{{%avviso}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // FK audit _by verso utente
        $this->addForeignKey(
            'fk_criterio_val_studente_created_by',
            '{{%criterio_valutazione_studente}}',
            'created_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_criterio_val_studente_updated_by',
            '{{%criterio_valutazione_studente}}',
            'updated_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_criterio_val_studente_deleted_by',
            '{{%criterio_valutazione_studente}}',
            'deleted_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Indice per soft delete
        $this->createIndex('idx_criterio_val_studente_deleted', '{{%criterio_valutazione_studente}}', 'deleted_at');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_criterio_val_studente_deleted_by', '{{%criterio_valutazione_studente}}');
        $this->dropForeignKey('fk_criterio_val_studente_updated_by', '{{%criterio_valutazione_studente}}');
        $this->dropForeignKey('fk_criterio_val_studente_created_by', '{{%criterio_valutazione_studente}}');
        $this->dropForeignKey('fk_criterio_val_studente_avviso', '{{%criterio_valutazione_studente}}');
        $this->dropTable('{{%criterio_valutazione_studente}}');
    }
}
