<?php

use yii\db\Migration;

class m260221_100004_insert_demo_bandi_studenti extends Migration
{
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        // Sedi demo
        $this->insert('{{%sede}}', [
            'nome' => 'Milano - Via Torino 42',
            'indirizzo' => 'Via Torino 42',
            'cap' => '20123',
            'citta' => 'Milano',
            'provincia' => 'MI',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $sedeMilanoId = $this->db->getLastInsertID();

        $this->insert('{{%sede}}', [
            'nome' => 'Roma - Via Nazionale 50',
            'indirizzo' => 'Via Nazionale 50',
            'cap' => '00184',
            'citta' => 'Roma',
            'provincia' => 'RM',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $sedeRomaId = $this->db->getLastInsertID();

        // Conto corrente (richiesto da progetto)
        $contoId = (new \yii\db\Query())
            ->select('id')
            ->from('{{%conto_corrente}}')
            ->where(['deleted_at' => null])
            ->scalar($this->db);

        if (!$contoId) {
            $this->insert('{{%conto_corrente}}', [
                'intestatario' => 'Fondazione ITS Albatros Academy',
                'iban' => 'IT60X0542811101000000123456',
                'banca' => 'Banca Etica',
                'note' => 'Conto demo',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $contoId = $this->db->getLastInsertID();
        }

        // Progetto 1 - FSE Competenze Digitali
        $this->insert('{{%progetto}}', [
            'codice_progetto' => 'FSE-2024-DIGITAL',
            'titolo' => 'Competenze Digitali per il Lavoro 4.0',
            'descrizione' => 'Programma formativo per colmare il gap di competenze digitali nel mercato del lavoro.',
            'fonte_finanziamento_id' => 1, // FSE
            'conto_corrente_id' => $contoId,
            'numero_studenti' => 25,
            'status' => 'attivo',
            'data_inizio' => '2025-03-01',
            'data_fine' => '2025-12-31',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $progetto1Id = $this->db->getLastInsertID();

        // Progetto 2 - PNRR Transizione Ecologica
        $this->insert('{{%progetto}}', [
            'codice_progetto' => 'PNRR-2024-GREEN',
            'titolo' => 'Professioni per la Transizione Ecologica',
            'descrizione' => 'Formazione per figure professionali specializzate nella sostenibilita ambientale.',
            'fonte_finanziamento_id' => 2, // PNRR
            'conto_corrente_id' => $contoId,
            'numero_studenti' => 20,
            'status' => 'attivo',
            'data_inizio' => '2025-04-01',
            'data_fine' => '2025-12-31',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $progetto2Id = $this->db->getLastInsertID();

        // Avviso 1 - FSE Competenze Digitali
        $this->insert('{{%avviso}}', [
            'progetto_id' => $progetto1Id,
            'codice_avviso' => 'FSE-2024-DIGITAL',
            'titolo' => 'Competenze Digitali per il Lavoro 4.0',
            'sottotitolo' => 'Programma formativo finanziato dal Fondo Sociale Europeo',
            'descrizione' => 'Il programma "Competenze Digitali per il Lavoro 4.0" nasce dall\'esigenza di colmare il gap di competenze digitali nel mercato del lavoro italiano. Attraverso percorsi formativi mirati, i partecipanti acquisiranno le competenze necessarie per affrontare le sfide della trasformazione digitale.',
            'obiettivi' => json_encode([
                'Sviluppare competenze digitali avanzate richieste dal mercato del lavoro',
                'Fornire strumenti pratici per l\'utilizzo delle tecnologie emergenti',
                'Preparare i partecipanti alle certificazioni professionali riconosciute',
                'Facilitare l\'inserimento o il reinserimento nel mondo del lavoro',
            ]),
            'destinatari' => 'Disoccupati, inoccupati e lavoratori che necessitano di riqualificazione professionale. Eta compresa tra 18 e 65 anni. Residenti in Lombardia.',
            'prospettive' => json_encode([
                'Inserimento presso aziende partner del programma',
                'Avvio di attivita freelance nel settore digitale',
                'Progressione di carriera per chi e gia occupato',
                'Accesso a ulteriori percorsi formativi avanzati',
            ]),
            'requisiti_ammissione' => json_encode([
                'Diploma di scuola secondaria superiore',
                'Conoscenza base dell\'uso del computer',
                'Disponibilita a frequentare almeno l\'80% delle lezioni',
                'Motivazione e interesse per il settore digitale',
            ]),
            'certificazioni' => json_encode([
                'Attestato di frequenza',
                'Certificazione competenze digitali',
                'Open Badge verificabile',
            ]),
            'colore_hex' => '#206bc4',
            'data_pubblicazione' => '2025-01-15',
            'data_scadenza' => '2026-04-30',
            'status' => 'pubblicato',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $avviso1Id = $this->db->getLastInsertID();

        // Avviso 2 - PNRR Transizione Ecologica
        $this->insert('{{%avviso}}', [
            'progetto_id' => $progetto2Id,
            'codice_avviso' => 'PNRR-2024-GREEN',
            'titolo' => 'Professioni per la Transizione Ecologica',
            'sottotitolo' => 'Formazione finanziata dal Piano Nazionale di Ripresa e Resilienza',
            'descrizione' => 'Il programma forma figure professionali specializzate nella sostenibilita ambientale e nella transizione ecologica. I partecipanti acquisiranno competenze tecniche e gestionali per operare nei settori delle energie rinnovabili, dell\'efficienza energetica e dell\'economia circolare.',
            'obiettivi' => json_encode([
                'Formare esperti in energie rinnovabili e efficienza energetica',
                'Sviluppare competenze per la gestione sostenibile delle risorse',
                'Preparare professionisti per la certificazione ambientale',
                'Promuovere l\'innovazione green nelle imprese',
            ]),
            'destinatari' => 'Giovani NEET (18-34 anni), disoccupati di lunga durata, lavoratori in settori in transizione. Priorita a residenti in aree svantaggiate.',
            'prospettive' => json_encode([
                'Energy Manager presso aziende e PA',
                'Consulente per la sostenibilita ambientale',
                'Tecnico installatore impianti rinnovabili',
                'Responsabile economia circolare',
            ]),
            'requisiti_ammissione' => json_encode([
                'Eta compresa tra 18 e 45 anni',
                'Diploma tecnico o laurea (preferibile in materie scientifiche)',
                'Interesse per le tematiche ambientali',
                'Residenza in Italia',
            ]),
            'certificazioni' => json_encode([
                'Qualifica professionale regionale',
                'Certificazione Energy Manager',
                'Attestato sicurezza sul lavoro',
            ]),
            'colore_hex' => '#2fb344',
            'data_pubblicazione' => '2025-02-01',
            'data_scadenza' => '2026-05-15',
            'status' => 'pubblicato',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $avviso2Id = $this->db->getLastInsertID();

        // Moduli Avviso 1 - Competenze Digitali
        $this->insert('{{%modulo}}', [
            'avviso_id' => $avviso1Id,
            'sede_id' => $sedeMilanoId,
            'codice' => 'DIG-PYTHON',
            'titolo' => 'Programmazione Python',
            'descrizione' => 'Corso completo di programmazione Python, dalle basi alle applicazioni avanzate.',
            'ore_totali' => 60,
            'compenso_orario' => 60,
            'data_inizio' => '2025-03-15',
            'data_fine' => '2025-06-15',
            'max_allievi' => 25,
            'status' => 'aperto',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->insert('{{%modulo}}', [
            'avviso_id' => $avviso1Id,
            'sede_id' => $sedeMilanoId,
            'codice' => 'DIG-DATA',
            'titolo' => 'Data Analysis & Visualization',
            'descrizione' => 'Analisi dati e visualizzazione con strumenti professionali.',
            'ore_totali' => 40,
            'compenso_orario' => 60,
            'data_inizio' => '2025-04-01',
            'data_fine' => '2025-06-01',
            'max_allievi' => 20,
            'status' => 'aperto',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->insert('{{%modulo}}', [
            'avviso_id' => $avviso1Id,
            'sede_id' => $sedeMilanoId,
            'codice' => 'DIG-CLOUD',
            'titolo' => 'Cloud Computing Fundamentals',
            'descrizione' => 'Fondamenti di cloud computing: AWS, Azure e Google Cloud.',
            'ore_totali' => 50,
            'compenso_orario' => 60,
            'data_inizio' => '2025-03-20',
            'data_fine' => '2025-06-20',
            'max_allievi' => 20,
            'status' => 'aperto',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Moduli Avviso 2 - Transizione Ecologica
        $this->insert('{{%modulo}}', [
            'avviso_id' => $avviso2Id,
            'sede_id' => $sedeRomaId,
            'codice' => 'GREEN-ENERGY',
            'titolo' => 'Energie Rinnovabili e Fotovoltaico',
            'descrizione' => 'Progettazione e installazione di impianti fotovoltaici e sistemi di energia rinnovabile.',
            'ore_totali' => 80,
            'compenso_orario' => 70,
            'data_inizio' => '2025-04-10',
            'data_fine' => '2025-08-10',
            'max_allievi' => 20,
            'status' => 'aperto',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->insert('{{%modulo}}', [
            'avviso_id' => $avviso2Id,
            'sede_id' => $sedeRomaId,
            'codice' => 'GREEN-AUDIT',
            'titolo' => 'Audit Energetico e Certificazioni',
            'descrizione' => 'Metodologie di audit energetico e certificazioni ambientali per edifici e aziende.',
            'ore_totali' => 60,
            'compenso_orario' => 70,
            'data_inizio' => '2025-04-20',
            'data_fine' => '2025-07-20',
            'max_allievi' => 15,
            'status' => 'aperto',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->insert('{{%modulo}}', [
            'avviso_id' => $avviso2Id,
            'sede_id' => $sedeRomaId,
            'codice' => 'GREEN-CIRCULAR',
            'titolo' => 'Economia Circolare e Sostenibilita',
            'descrizione' => 'Principi e pratiche dell\'economia circolare applicata alle imprese.',
            'ore_totali' => 40,
            'compenso_orario' => 70,
            'data_inizio' => '2025-05-01',
            'data_fine' => '2025-07-01',
            'max_allievi' => 25,
            'status' => 'aperto',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function safeDown()
    {
        // Rimuovi moduli demo
        $this->delete('{{%modulo}}', ['codice' => ['DIG-PYTHON', 'DIG-DATA', 'DIG-CLOUD', 'GREEN-ENERGY', 'GREEN-AUDIT', 'GREEN-CIRCULAR']]);

        // Rimuovi avvisi demo
        $this->delete('{{%avviso}}', ['codice_avviso' => ['FSE-2024-DIGITAL', 'PNRR-2024-GREEN']]);

        // Rimuovi progetti demo
        $this->delete('{{%progetto}}', ['codice_progetto' => ['FSE-2024-DIGITAL', 'PNRR-2024-GREEN']]);

        // Rimuovi sedi demo
        $this->delete('{{%sede}}', ['nome' => ['Milano - Via Torino 42', 'Roma - Via Nazionale 50']]);
    }
}
