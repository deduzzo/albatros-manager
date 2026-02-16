<?php

use yii\db\Migration;

/**
 * Aggiunge esito valutazione CTS alla tabella istanza
 *
 * Dalla seconda riunione: il workflow prevede che la Commissione Tecnico Scientifica (CTS)
 * valuti le candidature e produca tre esiti:
 * - assegnato: docente selezionato per l'incarico
 * - idoneo: valutato positivamente ma non assegnato (entra in long list per future chiamate)
 * - non_idoneo: valutazione negativa (importante tracciare storicità delle non idoneità)
 *
 * Il campo status rimane per il workflow generale della domanda.
 */
class m250216_000003_add_esito_cts_to_istanza extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%istanza}}', 'esito_cts',
            "ENUM('assegnato', 'idoneo', 'non_idoneo') NULL COMMENT 'Esito valutazione Commissione Tecnico Scientifica' AFTER status"
        );

        $this->createIndex('idx_istanza_esito_cts', '{{%istanza}}', 'esito_cts');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_istanza_esito_cts', '{{%istanza}}');
        $this->dropColumn('{{%istanza}}', 'esito_cts');
    }
}
