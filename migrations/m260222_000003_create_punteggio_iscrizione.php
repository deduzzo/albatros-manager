<?php

use yii\db\Migration;

/**
 * Crea la tabella punteggio_iscrizione per salvare i punteggi
 * assegnati a ciascun criterio per ogni candidatura studente.
 */
class m260222_000003_create_punteggio_iscrizione extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%punteggio_iscrizione}}', [
            'id' => $this->primaryKey(),
            'iscrizione_id' => $this->integer()->notNull(),
            'criterio_id' => $this->integer()->notNull(),
            'punteggio' => $this->decimal(5, 2)->null(),
            'note' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        // UNIQUE KEY su (iscrizione_id, criterio_id)
        $this->createIndex(
            'uk_punteggio_iscrizione_criterio',
            '{{%punteggio_iscrizione}}',
            ['iscrizione_id', 'criterio_id'],
            true
        );

        // FK verso iscrizione
        $this->addForeignKey(
            'fk_punteggio_iscrizione_iscrizione',
            '{{%punteggio_iscrizione}}',
            'iscrizione_id',
            '{{%iscrizione}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // FK verso criterio_valutazione_studente
        $this->addForeignKey(
            'fk_punteggio_iscrizione_criterio',
            '{{%punteggio_iscrizione}}',
            'criterio_id',
            '{{%criterio_valutazione_studente}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // FK audit _by verso utente
        $this->addForeignKey(
            'fk_punteggio_iscrizione_created_by',
            '{{%punteggio_iscrizione}}',
            'created_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_punteggio_iscrizione_updated_by',
            '{{%punteggio_iscrizione}}',
            'updated_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_punteggio_iscrizione_deleted_by',
            '{{%punteggio_iscrizione}}',
            'deleted_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Indice per soft delete
        $this->createIndex('idx_punteggio_iscrizione_deleted', '{{%punteggio_iscrizione}}', 'deleted_at');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_punteggio_iscrizione_deleted_by', '{{%punteggio_iscrizione}}');
        $this->dropForeignKey('fk_punteggio_iscrizione_updated_by', '{{%punteggio_iscrizione}}');
        $this->dropForeignKey('fk_punteggio_iscrizione_created_by', '{{%punteggio_iscrizione}}');
        $this->dropForeignKey('fk_punteggio_iscrizione_criterio', '{{%punteggio_iscrizione}}');
        $this->dropForeignKey('fk_punteggio_iscrizione_iscrizione', '{{%punteggio_iscrizione}}');
        $this->dropTable('{{%punteggio_iscrizione}}');
    }
}
