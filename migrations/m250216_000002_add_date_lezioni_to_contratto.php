<?php

use yii\db\Migration;

/**
 * Aggiunge date effettive lezioni alla tabella contratto
 *
 * Dalla seconda riunione: i campi data_inizio e data_fine rappresentano il periodo contrattuale,
 * mentre servono anche le date effettive delle lezioni per:
 * - Alert automatici di scadenza
 * - Sapere quando un docente termina per poterlo riassegnare
 * - Rendicontazione precisa delle ore
 */
class m250216_000002_add_date_lezioni_to_contratto extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%contratto}}', 'data_inizio_lezioni',
            "DATE NULL COMMENT 'Data effettiva inizio lezioni (diversa dalla data_inizio contrattuale)' AFTER data_fine"
        );

        $this->addColumn('{{%contratto}}', 'data_fine_lezioni',
            "DATE NULL COMMENT 'Data effettiva/prevista fine lezioni (per alert e pianificazione)' AFTER data_inizio_lezioni"
        );

        $this->createIndex('idx_contratto_date_lezioni', '{{%contratto}}', ['data_inizio_lezioni', 'data_fine_lezioni']);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_contratto_date_lezioni', '{{%contratto}}');
        $this->dropColumn('{{%contratto}}', 'data_fine_lezioni');
        $this->dropColumn('{{%contratto}}', 'data_inizio_lezioni');
    }
}
