<?php

use yii\db\Migration;

/**
 * Espande la tabella iscrizione per gestire il workflow completo di candidatura studenti.
 * Aggiunge campi per titolo studio, stato occupazionale, graduatoria e attestato.
 * Modifica l'ENUM status per includere i nuovi stati del workflow candidatura.
 */
class m260222_000001_expand_iscrizione_for_candidature extends Migration
{
    public function safeUp()
    {
        // Aggiungere nuove colonne dopo 'note'
        $this->addColumn('{{%iscrizione}}', 'titolo_studio', "ENUM('licenza_media','diploma','laurea_triennale','laurea_magistrale','master','dottorato') NULL AFTER `note`");
        $this->addColumn('{{%iscrizione}}', 'anno_conseguimento', $this->smallInteger()->null()->after('titolo_studio'));
        $this->addColumn('{{%iscrizione}}', 'istituto', $this->string(255)->null()->after('anno_conseguimento'));
        $this->addColumn('{{%iscrizione}}', 'stato_occupazionale', "ENUM('occupato','disoccupato','inoccupato','studente','pensionato') NULL AFTER `istituto`");
        $this->addColumn('{{%iscrizione}}', 'settore_attivita', $this->string(100)->null()->after('stato_occupazionale'));
        $this->addColumn('{{%iscrizione}}', 'punteggio_totale', $this->decimal(8, 2)->null()->after('settore_attivita'));
        $this->addColumn('{{%iscrizione}}', 'posizione_graduatoria', $this->integer()->null()->after('punteggio_totale'));
        $this->addColumn('{{%iscrizione}}', 'motivo_non_ammissione', $this->text()->null()->after('posizione_graduatoria'));
        $this->addColumn('{{%iscrizione}}', 'data_ammissione', $this->date()->null()->after('motivo_non_ammissione'));
        $this->addColumn('{{%iscrizione}}', 'data_completamento', $this->date()->null()->after('data_ammissione'));
        $this->addColumn('{{%iscrizione}}', 'punteggio_finale', $this->decimal(5, 2)->null()->after('data_completamento'));
        $this->addColumn('{{%iscrizione}}', 'numero_attestato', $this->string(50)->null()->after('punteggio_finale'));
        $this->addColumn('{{%iscrizione}}', 'data_attestato', $this->date()->null()->after('numero_attestato'));

        // Prima migrare i dati: 'iscritto' diventa 'candidato' nel nuovo workflow
        // Lo facciamo PRIMA di cambiare l'ENUM perche il nuovo ENUM non ha 'iscritto'
        // Step 1: Aggiungere i nuovi valori all'ENUM mantenendo 'iscritto' temporaneamente
        $this->alterColumn('{{%iscrizione}}', 'status', "ENUM('iscritto','candidato','in_valutazione','ammesso','ammesso_con_riserva','non_ammesso','frequentante','completato','ritirato') NOT NULL DEFAULT 'candidato'");

        // Step 2: Migrare i record esistenti
        $this->update('{{%iscrizione}}', ['status' => 'candidato'], ['status' => 'iscritto']);

        // Step 3: Rimuovere 'iscritto' dall'ENUM (non piu necessario)
        $this->alterColumn('{{%iscrizione}}', 'status', "ENUM('candidato','in_valutazione','ammesso','ammesso_con_riserva','non_ammesso','iscritto','frequentante','completato','ritirato') NOT NULL DEFAULT 'candidato'");
    }

    public function safeDown()
    {
        // Ripristinare status originale: prima migrare i dati
        // Mappare i nuovi valori ai vecchi
        $this->update('{{%iscrizione}}', ['status' => 'iscritto'], ['status' => 'candidato']);
        $this->update('{{%iscrizione}}', ['status' => 'iscritto'], ['status' => 'in_valutazione']);
        $this->update('{{%iscrizione}}', ['status' => 'iscritto'], ['status' => 'ammesso']);
        $this->update('{{%iscrizione}}', ['status' => 'iscritto'], ['status' => 'ammesso_con_riserva']);

        // Ripristinare ENUM originale
        $this->alterColumn('{{%iscrizione}}', 'status', "ENUM('iscritto','frequentante','ritirato','completato','non_ammesso') NOT NULL DEFAULT 'iscritto'");

        // Rimuovere colonne aggiunte (ordine inverso)
        $this->dropColumn('{{%iscrizione}}', 'data_attestato');
        $this->dropColumn('{{%iscrizione}}', 'numero_attestato');
        $this->dropColumn('{{%iscrizione}}', 'punteggio_finale');
        $this->dropColumn('{{%iscrizione}}', 'data_completamento');
        $this->dropColumn('{{%iscrizione}}', 'data_ammissione');
        $this->dropColumn('{{%iscrizione}}', 'motivo_non_ammissione');
        $this->dropColumn('{{%iscrizione}}', 'posizione_graduatoria');
        $this->dropColumn('{{%iscrizione}}', 'punteggio_totale');
        $this->dropColumn('{{%iscrizione}}', 'settore_attivita');
        $this->dropColumn('{{%iscrizione}}', 'stato_occupazionale');
        $this->dropColumn('{{%iscrizione}}', 'istituto');
        $this->dropColumn('{{%iscrizione}}', 'anno_conseguimento');
        $this->dropColumn('{{%iscrizione}}', 'titolo_studio');
    }
}
