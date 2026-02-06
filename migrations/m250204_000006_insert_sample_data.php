<?php

use yii\db\Migration;

/**
 * Inserisce dati di esempio per la demo:
 * - Sedi (se non esistono)
 * - Progetti
 * - Avvisi/Bandi (2 chiusi + 1 aperto)
 * - Moduli per ogni bando
 * - Documenti richiesti per il bando aperto
 * - Docenti di esempio con tag
 * - Credenziali demo per docenti
 */
class m250204_000006_insert_sample_data extends Migration
{
    public function safeUp()
    {
        // ============================================
        // SEDI - Usiamo le sedi esistenti o le prime disponibili
        // ============================================
        $sedi = (new \yii\db\Query())->select(['id', 'nome'])->from('sede')->limit(3)->all();

        // Se non ci sono abbastanza sedi, usiamo la prima per tutto
        $sedeCentraleId = $sedi[0]['id'] ?? 1;
        $labNordId = $sedi[1]['id'] ?? $sedeCentraleId;

        // ============================================
        // FONTI FINANZIAMENTO (verifica se già esistono)
        // ============================================
        $fonteCount = (new \yii\db\Query())->from('fonte_finanziamento')->count();
        if ($fonteCount == 0) {
            $this->batchInsert('fonte_finanziamento', ['codice', 'nome', 'descrizione'], [
                ['FSE', 'Fondo Sociale Europeo', 'Finanziamenti FSE per la formazione'],
                ['PNRR', 'Piano Nazionale Ripresa e Resilienza', 'Fondi PNRR per innovazione e formazione'],
                ['MIM', 'Ministero Istruzione e Merito', 'Fondi ministeriali per aggiornamento docenti'],
            ]);
        }

        // Ottieni IDs fonti
        $fseId = (new \yii\db\Query())->select('id')->from('fonte_finanziamento')->where(['codice' => 'FSE'])->scalar();
        $pnrrId = (new \yii\db\Query())->select('id')->from('fonte_finanziamento')->where(['codice' => 'PNRR'])->scalar();
        $mimId = (new \yii\db\Query())->select('id')->from('fonte_finanziamento')->where(['codice' => 'MIM'])->scalar();

        // ============================================
        // PROGETTI
        // ============================================
        $this->batchInsert('progetto',
            ['fonte_finanziamento_id', 'codice_progetto', 'titolo', 'descrizione', 'budget_totale', 'data_inizio', 'data_fine', 'status'],
            [
                [$fseId, 'FSE-2024-001', 'Competenze Digitali per il Lavoro', 'Progetto per lo sviluppo delle competenze digitali dei lavoratori', 500000.00, '2024-01-01', '2025-12-31', 'attivo'],
                [$pnrrId, 'PNRR-2024-001', 'STEM e Innovazione Didattica', 'Formazione STEM per docenti delle scuole secondarie', 750000.00, '2024-03-01', '2026-02-28', 'attivo'],
                [$mimId, 'MIM-2024-001', 'Aggiornamento Docenti Scuola', 'Programma di aggiornamento professionale docenti', 200000.00, '2024-01-01', '2024-12-31', 'concluso'],
            ]
        );

        $progettoFseId = (new \yii\db\Query())->select('id')->from('progetto')->where(['codice_progetto' => 'FSE-2024-001'])->scalar();
        $progettoPnrrId = (new \yii\db\Query())->select('id')->from('progetto')->where(['codice_progetto' => 'PNRR-2024-001'])->scalar();

        // ============================================
        // AVVISI/BANDI
        // ============================================
        $this->batchInsert('avviso',
            ['progetto_id', 'codice_avviso', 'titolo', 'descrizione', 'data_pubblicazione', 'data_scadenza', 'status'],
            [
                // Bandi chiusi
                [$progettoFseId, 'AVV-001', 'Selezione Esperti ICT', 'Selezione esperti per formazione ICT aziendale', '2024-03-01', '2024-06-30', 'chiuso'],
                [$progettoFseId, 'AVV-002', 'Selezione Docenti Web Development', 'Selezione docenti per corsi di sviluppo web', '2024-07-01', '2024-12-31', 'chiuso'],
                // Bando aperto (scadenza futura)
                [$progettoPnrrId, 'AVV-003', 'Esperti Formazione STEM 2025', 'Selezione esperti per percorsi formativi STEM destinati a docenti delle scuole secondarie. Competenze richieste: programmazione, data science, intelligenza artificiale, cloud computing.', '2025-01-15', '2025-06-30', 'pubblicato'],
            ]
        );

        $avvisoBandoApertoId = (new \yii\db\Query())->select('id')->from('avviso')->where(['codice_avviso' => 'AVV-003'])->scalar();
        $avvisoBandoChiuso1Id = (new \yii\db\Query())->select('id')->from('avviso')->where(['codice_avviso' => 'AVV-001'])->scalar();
        $avvisoBandoChiuso2Id = (new \yii\db\Query())->select('id')->from('avviso')->where(['codice_avviso' => 'AVV-002'])->scalar();

        // ============================================
        // MODULI per tutti i bandi
        // ============================================
        $this->batchInsert('modulo',
            ['avviso_id', 'sede_id', 'codice', 'titolo', 'descrizione', 'ore_totali', 'compenso_orario', 'data_inizio', 'data_fine', 'requisiti_docente', 'max_allievi', 'status'],
            [
                // Moduli bando chiuso 1
                [$avvisoBandoChiuso1Id, $sedeCentraleId, 'MOD-ICT-01', 'Fondamenti ICT', 'Corso base sulle tecnologie ICT', 30, 65.00, '2024-09-01', '2024-10-31', 'Laurea in Informatica o equipollente', 25, 'concluso'],
                [$avvisoBandoChiuso1Id, $labNordId, 'MOD-ICT-02', 'Sicurezza Informatica', 'Corso sulla cybersecurity aziendale', 40, 70.00, '2024-09-15', '2024-11-15', 'Esperienza in sicurezza informatica', 20, 'concluso'],

                // Moduli bando chiuso 2
                [$avvisoBandoChiuso2Id, $sedeCentraleId, 'MOD-WEB-01', 'HTML/CSS Avanzato', 'Tecniche avanzate di web design', 30, 65.00, '2025-01-15', '2025-02-28', 'Esperienza web development', 25, 'concluso'],
                [$avvisoBandoChiuso2Id, $labNordId, 'MOD-WEB-02', 'JavaScript Moderno', 'ES6+, React, Vue.js', 50, 75.00, '2025-01-20', '2025-03-20', 'Esperienza JavaScript', 20, 'concluso'],

                // Moduli bando APERTO (AVV-003)
                [$avvisoBandoApertoId, $sedeCentraleId, 'MOD-PYTHON', 'Python per Data Science', 'Introduzione a Python per analisi dati e machine learning. Librerie: NumPy, Pandas, Scikit-learn.', 40, 70.00, '2025-09-01', '2025-10-31', 'Laurea in discipline STEM. Esperienza programmazione.', 25, 'aperto'],
                [$avvisoBandoApertoId, $labNordId, 'MOD-WEB', 'Sviluppo Web Full Stack', 'Corso completo su sviluppo web: HTML5, CSS3, JavaScript, Node.js, React, database.', 60, 70.00, '2025-09-01', '2025-11-30', 'Esperienza sviluppo web almeno 3 anni.', 20, 'aperto'],
                [$avvisoBandoApertoId, $sedeCentraleId, 'MOD-AI', 'Introduzione all\'Intelligenza Artificiale', 'Fondamenti di AI, machine learning, deep learning e applicazioni pratiche.', 30, 80.00, '2025-10-01', '2025-11-15', 'Laurea in Informatica/Matematica. Conoscenza Python.', 25, 'aperto'],
                [$avvisoBandoApertoId, $labNordId, 'MOD-CLOUD', 'Cloud Computing Base', 'Introduzione a AWS, Azure e Google Cloud. Architetture cloud e best practices.', 40, 75.00, '2025-10-15', '2025-12-15', 'Esperienza sistemistica. Certificazioni cloud gradite.', 20, 'aperto'],
            ]
        );

        // ============================================
        // DOCUMENTI RICHIESTI per bando aperto
        // ============================================
        $this->batchInsert('avviso_documento_tipo',
            ['avviso_id', 'nome', 'descrizione', 'formati_ammessi', 'dimensione_max_mb', 'obbligatorio', 'ordine'],
            [
                [$avvisoBandoApertoId, 'Curriculum Vitae', 'Curriculum vitae in formato europeo o libero', 'pdf', 5, 1, 1],
                [$avvisoBandoApertoId, 'Documento di Identità', 'Copia fronte/retro di un documento di identità valido', 'pdf,jpg,png', 3, 1, 2],
                [$avvisoBandoApertoId, 'Certificazioni Professionali', 'Eventuali certificazioni attinenti (AWS, Azure, Google, etc.)', 'pdf', 10, 0, 3],
            ]
        );

        // ============================================
        // DOCENTI DI ESEMPIO (solo se non esistono già)
        // ============================================
        $docentiDaInserire = [
            ['RSSMRA80A01F205X', 'Rossi', 'Mario', '1980-01-01', 'Milano', 'Via Roma 10', '20100', 'Milano', 'MI', '333-1234567', 'mario.rossi@email.it', 'Laurea in Informatica', 'Backend development, Database design', '12345678901'],
            ['VRDLGI85B15H501Y', 'Verdi', 'Luigi', '1985-02-15', 'Roma', 'Via Nazionale 25', '00184', 'Roma', 'RM', '333-2345678', 'luigi.verdi@email.it', 'Laurea in Matematica', 'Data Science, Machine Learning', '23456789012'],
            ['BNCLRA78C20F839Z', 'Bianchi', 'Laura', '1978-03-20', 'Napoli', 'Via Toledo 100', '80134', 'Napoli', 'NA', '333-3456789', 'laura.bianchi@email.it', 'Laurea in Ingegneria Informatica', 'Frontend, UX/UI Design', '34567890123'],
            ['NRIGPP82D10A944W', 'Neri', 'Giuseppe', '1982-04-10', 'Torino', 'Corso Francia 50', '10138', 'Torino', 'TO', '333-4567890', 'giuseppe.neri@email.it', 'Laurea in Informatica', 'Java Enterprise, Microservices', '45678901234'],
            ['GLLMRC75E25L219V', 'Gialli', 'Marco', '1975-05-25', 'Firenze', 'Via dei Calzaiuoli 15', '50122', 'Firenze', 'FI', '333-5678901', 'marco.gialli@email.it', 'Laurea in Ingegneria', 'Cloud Architecture, DevOps', '56789012345'],
            ['RSSSFN88F30G273U', 'Russo', 'Sofia', '1988-06-30', 'Bologna', 'Via Indipendenza 20', '40121', 'Bologna', 'BO', '333-6789012', 'sofia.russo@email.it', 'Laurea in Scienze della Formazione', 'Didattica digitale, E-learning', '67890123456'],
        ];

        foreach ($docentiDaInserire as $docente) {
            $exists = (new \yii\db\Query())->from('docente')->where(['codice_fiscale' => $docente[0]])->exists();
            if (!$exists) {
                $this->insert('docente', [
                    'codice_fiscale' => $docente[0],
                    'cognome' => $docente[1],
                    'nome' => $docente[2],
                    'data_nascita' => $docente[3],
                    'luogo_nascita' => $docente[4],
                    'indirizzo' => $docente[5],
                    'cap' => $docente[6],
                    'citta' => $docente[7],
                    'provincia' => $docente[8],
                    'telefono' => $docente[9],
                    'email' => $docente[10],
                    'titolo_studio' => $docente[11],
                    'specializzazioni' => $docente[12],
                    'partita_iva' => $docente[13],
                ]);
            }
        }

        // ============================================
        // ASSEGNAZIONE TAG AI DOCENTI
        // ============================================
        // Ottieni IDs tag
        $tagPhp = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'php'])->scalar();
        $tagLaravel = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'laravel'])->scalar();
        $tagMysql = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'mysql'])->scalar();
        $tagPython = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'python'])->scalar();
        $tagAi = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'ai'])->scalar();
        $tagJavascript = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'javascript'])->scalar();
        $tagReact = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'react'])->scalar();
        $tagJava = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'java'])->scalar();
        $tagSpring = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'spring'])->scalar();
        $tagAws = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'aws'])->scalar();
        $tagDocker = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'docker'])->scalar();
        $tagTeaching = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'teaching'])->scalar();
        $tagUx = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'ux'])->scalar();
        $tagNodejs = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => 'nodejs'])->scalar();

        // Inserisci tag solo se non esistono già
        $tagsDocenti = [
            ['RSSMRA80A01F205X', $tagPhp, 5],
            ['RSSMRA80A01F205X', $tagLaravel, 4],
            ['RSSMRA80A01F205X', $tagMysql, 4],
            ['VRDLGI85B15H501Y', $tagPython, 5],
            ['VRDLGI85B15H501Y', $tagAi, 4],
            ['VRDLGI85B15H501Y', $tagMysql, 3],
            ['BNCLRA78C20F839Z', $tagJavascript, 5],
            ['BNCLRA78C20F839Z', $tagReact, 4],
            ['BNCLRA78C20F839Z', $tagUx, 4],
            ['NRIGPP82D10A944W', $tagJava, 5],
            ['NRIGPP82D10A944W', $tagSpring, 4],
            ['NRIGPP82D10A944W', $tagAws, 3],
            ['GLLMRC75E25L219V', $tagAws, 5],
            ['GLLMRC75E25L219V', $tagDocker, 4],
            ['GLLMRC75E25L219V', $tagPython, 3],
            ['RSSSFN88F30G273U', $tagTeaching, 5],
            ['RSSSFN88F30G273U', $tagUx, 3],
            ['RSSSFN88F30G273U', $tagJavascript, 2],
        ];

        foreach ($tagsDocenti as $tag) {
            if ($tag[1]) { // Solo se il tag esiste
                $exists = (new \yii\db\Query())->from('docente_tag')
                    ->where(['docente_codice_fiscale' => $tag[0], 'tag_id' => $tag[1]])
                    ->exists();
                if (!$exists) {
                    $this->insert('docente_tag', [
                        'docente_codice_fiscale' => $tag[0],
                        'tag_id' => $tag[1],
                        'livello' => $tag[2],
                    ]);
                }
            }
        }

        // ============================================
        // CREDENZIALI DOCENTI (password: docente123)
        // ============================================
        $passwordHash = Yii::$app->security->generatePasswordHash('docente123');
        $docentiCF = ['RSSMRA80A01F205X', 'VRDLGI85B15H501Y', 'BNCLRA78C20F839Z', 'NRIGPP82D10A944W', 'GLLMRC75E25L219V', 'RSSSFN88F30G273U'];

        foreach ($docentiCF as $cf) {
            $exists = (new \yii\db\Query())->from('docente_auth')->where(['docente_codice_fiscale' => $cf])->exists();
            if (!$exists) {
                $this->insert('docente_auth', [
                    'docente_codice_fiscale' => $cf,
                    'password_hash' => $passwordHash,
                    'auth_key' => Yii::$app->security->generateRandomString(32),
                    'status' => 'active',
                    'email_verified_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return true;
    }

    public function safeDown()
    {
        // Rimuovi dati in ordine inverso
        $this->delete('docente_auth');
        $this->delete('docente_tag');
        $this->delete('avviso_documento_tipo');
        $this->delete('modulo');
        $this->delete('avviso');
        $this->delete('progetto');
        // Non rimuoviamo sedi e fonti finanziamento perché potrebbero essere usate altrove

        return true;
    }
}
