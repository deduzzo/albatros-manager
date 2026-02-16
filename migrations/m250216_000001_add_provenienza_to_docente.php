<?php

use yii\db\Migration;

/**
 * Aggiunge campo provenienza alla tabella docente
 *
 * Dalla seconda riunione: necessario distinguere tra docenti provenienti dal mondo del lavoro (TP)
 * e ricercatori universitari (RU) per differenziare i compensi orari.
 * Esempio PNRR: RU = €70/ora, TP = €60/ora
 */
class m250216_000001_add_provenienza_to_docente extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%docente}}', 'provenienza',
            "ENUM('TP', 'RU') NULL COMMENT 'TP=Tipo Professionista (mondo lavoro), RU=Ricercatore Universitario' AFTER specializzazioni"
        );

        $this->createIndex('idx_docente_provenienza', '{{%docente}}', 'provenienza');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_docente_provenienza', '{{%docente}}');
        $this->dropColumn('{{%docente}}', 'provenienza');
    }
}
