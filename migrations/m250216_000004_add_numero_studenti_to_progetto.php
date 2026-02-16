<?php

use yii\db\Migration;

/**
 * Aggiunge numero studenti alla tabella progetto
 *
 * Dalla seconda riunione: ogni progetto (chiamato "corso" dal punto di vista studente)
 * ha tipicamente 20 studenti iscritti. Utile per statistiche e pianificazione.
 */
class m250216_000004_add_numero_studenti_to_progetto extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%progetto}}', 'numero_studenti',
            "INT DEFAULT 20 COMMENT 'Numero studenti previsti/iscritti al progetto' AFTER budget_totale"
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%progetto}}', 'numero_studenti');
    }
}
