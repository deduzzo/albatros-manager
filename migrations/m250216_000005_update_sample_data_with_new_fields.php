<?php

use yii\db\Migration;

/**
 * Aggiorna i dati di esempio con i nuovi campi aggiunti:
 * - Provenienza docenti (TP/RU)
 * - Numero studenti nei progetti
 * - Esito CTS nelle istanze
 * - Crea contratti di esempio con date lezioni
 * - Crea assegnazioni e istanze di esempio
 */
class m250216_000005_update_sample_data_with_new_fields extends Migration
{
    public function safeUp()
    {
        // ============================================
        // 1. AGGIORNA PROVENIENZA DOCENTI
        // ============================================
        // Mario Rossi - Tipo Professionista (backend developer freelance)
        $this->update('docente', ['provenienza' => 'TP'], ['codice_fiscale' => 'RSSMRA80A01F205X']);

        // Luigi Verdi - Ricercatore Universitario (matematica)
        $this->update('docente', ['provenienza' => 'RU'], ['codice_fiscale' => 'VRDLGI85B15H501Y']);

        // Laura Bianchi - Tipo Professionista (frontend developer)
        $this->update('docente', ['provenienza' => 'TP'], ['codice_fiscale' => 'BNCLRA78C20F839Z']);

        // Giuseppe Neri - Tipo Professionista (Java enterprise)
        $this->update('docente', ['provenienza' => 'TP'], ['codice_fiscale' => 'NRIGPP82D10A944W']);

        // Marco Gialli - Ricercatore Universitario (ingegneria)
        $this->update('docente', ['provenienza' => 'RU'], ['codice_fiscale' => 'GLLMRC75E25L219V']);

        // Sofia Russo - Ricercatore Universitario (scienze della formazione)
        $this->update('docente', ['provenienza' => 'RU'], ['codice_fiscale' => 'RSSSFN88F30G273U']);

        // ============================================
        // 2. AGGIORNA NUMERO STUDENTI PROGETTI
        // ============================================
        $this->update('progetto', ['numero_studenti' => 20], ['codice_progetto' => 'FSE-2024-001']);
        $this->update('progetto', ['numero_studenti' => 20], ['codice_progetto' => 'PNRR-2024-001']);
        $this->update('progetto', ['numero_studenti' => 15], ['codice_progetto' => 'MIM-2024-001']);

        // ============================================
        // 3. CREA ISTANZE DI ESEMPIO CON ESITO CTS
        // ============================================
        // Ottieni IDs necessari
        $avvisoApertoId = (new \yii\db\Query())->select('id')->from('avviso')->where(['codice_avviso' => 'AVV-003'])->scalar();
        $moduloPythonId = (new \yii\db\Query())->select('id')->from('modulo')->where(['codice' => 'MOD-PYTHON'])->scalar();
        $moduloWebId = (new \yii\db\Query())->select('id')->from('modulo')->where(['codice' => 'MOD-WEB'])->scalar();
        $moduloAiId = (new \yii\db\Query())->select('id')->from('modulo')->where(['codice' => 'MOD-AI'])->scalar();
        $moduloCloudId = (new \yii\db\Query())->select('id')->from('modulo')->where(['codice' => 'MOD-CLOUD'])->scalar();

        if ($avvisoApertoId && $moduloPythonId) {
            // Istanze per modulo Python
            $this->batchInsert('istanza',
                ['avviso_id', 'docente_codice_fiscale', 'modulo_id', 'data_presentazione', 'punteggio_titoli', 'punteggio_esperienza', 'punteggio_totale', 'posizione_graduatoria', 'status', 'esito_cts'],
                [
                    // Luigi Verdi - ASSEGNATO (RU, quindi €70/ora in PNRR)
                    [$avvisoApertoId, 'VRDLGI85B15H501Y', $moduloPythonId, '2025-02-01', 45.0, 40.0, 85.0, 1, 'valutata', 'assegnato'],
                    // Mario Rossi - IDONEO (in long list)
                    [$avvisoApertoId, 'RSSMRA80A01F205X', $moduloPythonId, '2025-02-02', 38.0, 35.0, 73.0, 2, 'valutata', 'idoneo'],
                    // Giuseppe Neri - IDONEO
                    [$avvisoApertoId, 'NRIGPP82D10A944W', $moduloPythonId, '2025-02-03', 32.0, 30.0, 62.0, 3, 'valutata', 'idoneo'],
                ]
            );

            // Istanze per modulo Web
            $this->batchInsert('istanza',
                ['avviso_id', 'docente_codice_fiscale', 'modulo_id', 'data_presentazione', 'punteggio_titoli', 'punteggio_esperienza', 'punteggio_totale', 'posizione_graduatoria', 'status', 'esito_cts'],
                [
                    // Laura Bianchi - ASSEGNATO (TP, quindi €60/ora in PNRR)
                    [$avvisoApertoId, 'BNCLRA78C20F839Z', $moduloWebId, '2025-02-01', 42.0, 45.0, 87.0, 1, 'valutata', 'assegnato'],
                    // Mario Rossi - IDONEO
                    [$avvisoApertoId, 'RSSMRA80A01F205X', $moduloWebId, '2025-02-02', 40.0, 38.0, 78.0, 2, 'valutata', 'idoneo'],
                ]
            );

            // Istanze per modulo AI
            $this->batchInsert('istanza',
                ['avviso_id', 'docente_codice_fiscale', 'modulo_id', 'data_presentazione', 'punteggio_titoli', 'punteggio_esperienza', 'punteggio_totale', 'posizione_graduatoria', 'status', 'esito_cts'],
                [
                    // Luigi Verdi - ASSEGNATO (RU, €70/ora + modulo premium €80/ora)
                    [$avvisoApertoId, 'VRDLGI85B15H501Y', $moduloAiId, '2025-02-01', 48.0, 42.0, 90.0, 1, 'valutata', 'assegnato'],
                    // Sofia Russo - NON IDONEO (manca esperienza pratica AI)
                    [$avvisoApertoId, 'RSSSFN88F30G273U', $moduloAiId, '2025-02-05', 25.0, 15.0, 40.0, 4, 'valutata', 'non_idoneo'],
                ]
            );

            // Istanze per modulo Cloud
            $this->batchInsert('istanza',
                ['avviso_id', 'docente_codice_fiscale', 'modulo_id', 'data_presentazione', 'punteggio_titoli', 'punteggio_esperienza', 'punteggio_totale', 'posizione_graduatoria', 'status', 'esito_cts'],
                [
                    // Marco Gialli - ASSEGNATO (RU, esperto cloud)
                    [$avvisoApertoId, 'GLLMRC75E25L219V', $moduloCloudId, '2025-02-01', 50.0, 48.0, 98.0, 1, 'valutata', 'assegnato'],
                    // Giuseppe Neri - IDONEO
                    [$avvisoApertoId, 'NRIGPP82D10A944W', $moduloCloudId, '2025-02-02', 35.0, 32.0, 67.0, 2, 'valutata', 'idoneo'],
                ]
            );
        }

        // ============================================
        // 4. CREA ASSEGNAZIONI PER GLI ASSEGNATI
        // ============================================
        // Ottieni IDs istanze assegnate
        $istanzaPythonAssegnataId = (new \yii\db\Query())->select('id')->from('istanza')
            ->where(['modulo_id' => $moduloPythonId, 'esito_cts' => 'assegnato'])->scalar();

        $istanzaWebAssegnataId = (new \yii\db\Query())->select('id')->from('istanza')
            ->where(['modulo_id' => $moduloWebId, 'esito_cts' => 'assegnato'])->scalar();

        $istanzaAiAssegnataId = (new \yii\db\Query())->select('id')->from('istanza')
            ->where(['modulo_id' => $moduloAiId, 'esito_cts' => 'assegnato'])->scalar();

        $istanzaCloudAssegnataId = (new \yii\db\Query())->select('id')->from('istanza')
            ->where(['modulo_id' => $moduloCloudId, 'esito_cts' => 'assegnato'])->scalar();

        if ($istanzaPythonAssegnataId) {
            $this->insert('assegnazione', [
                'modulo_id' => $moduloPythonId,
                'docente_codice_fiscale' => 'VRDLGI85B15H501Y',
                'istanza_id' => $istanzaPythonAssegnataId,
                'data_assegnazione' => '2025-02-10',
                'note' => 'Assegnazione CTS del 10/02/2025 - Esperto Python e Data Science',
            ]);
        }

        if ($istanzaWebAssegnataId) {
            $this->insert('assegnazione', [
                'modulo_id' => $moduloWebId,
                'docente_codice_fiscale' => 'BNCLRA78C20F839Z',
                'istanza_id' => $istanzaWebAssegnataId,
                'data_assegnazione' => '2025-02-10',
                'note' => 'Assegnazione CTS del 10/02/2025 - Esperta Frontend e UX',
            ]);
        }

        if ($istanzaAiAssegnataId) {
            $this->insert('assegnazione', [
                'modulo_id' => $moduloAiId,
                'docente_codice_fiscale' => 'VRDLGI85B15H501Y',
                'istanza_id' => $istanzaAiAssegnataId,
                'data_assegnazione' => '2025-02-10',
                'note' => 'Assegnazione CTS del 10/02/2025 - Ricercatore in AI e Machine Learning',
            ]);
        }

        if ($istanzaCloudAssegnataId) {
            $this->insert('assegnazione', [
                'modulo_id' => $moduloCloudId,
                'docente_codice_fiscale' => 'GLLMRC75E25L219V',
                'istanza_id' => $istanzaCloudAssegnataId,
                'data_assegnazione' => '2025-02-10',
                'note' => 'Assegnazione CTS del 10/02/2025 - Esperto Cloud Architecture e DevOps',
            ]);
        }

        // ============================================
        // 5. CREA CONTRATTI CON DATE LEZIONI
        // ============================================
        // Ottieni IDs assegnazioni appena create
        $assegnazionePythonId = (new \yii\db\Query())->select('id')->from('assegnazione')
            ->where(['modulo_id' => $moduloPythonId])->scalar();

        $assegnazioneWebId = (new \yii\db\Query())->select('id')->from('assegnazione')
            ->where(['modulo_id' => $moduloWebId])->scalar();

        $assegnazioneAiId = (new \yii\db\Query())->select('id')->from('assegnazione')
            ->where(['modulo_id' => $moduloAiId])->scalar();

        $assegnazioneCloudId = (new \yii\db\Query())->select('id')->from('assegnazione')
            ->where(['modulo_id' => $moduloCloudId])->scalar();

        if ($assegnazionePythonId) {
            // Contratto Python - RU quindi €70/ora (PNRR)
            $this->insert('contratto', [
                'assegnazione_id' => $assegnazionePythonId,
                'tipo' => 'cococo',
                'numero_contratto' => 'CONTR-PNRR-2025-001',
                'data_stipula' => '2025-02-15',
                'data_inizio' => '2025-09-01',
                'data_fine' => '2025-11-30',
                'data_inizio_lezioni' => '2025-09-15', // Lezioni iniziano 2 settimane dopo
                'data_fine_lezioni' => '2025-10-31',   // Terminano 1 mese prima della fine contratto
                'compenso_orario' => 70.00,             // RU in PNRR
                'ore_contrattualizzate' => 40,
                'compenso_totale' => 2800.00,           // 40 ore × €70
                'status' => 'attivo',
                'note' => 'Contratto CoCoCo - Modulo Python per Data Science',
            ]);
        }

        if ($assegnazioneWebId) {
            // Contratto Web - TP quindi €60/ora (PNRR)
            $this->insert('contratto', [
                'assegnazione_id' => $assegnazioneWebId,
                'tipo' => 'partita_iva',
                'numero_contratto' => 'CONTR-PNRR-2025-002',
                'data_stipula' => '2025-02-15',
                'data_inizio' => '2025-09-01',
                'data_fine' => '2025-12-31',
                'data_inizio_lezioni' => '2025-09-20',
                'data_fine_lezioni' => '2025-11-30',
                'compenso_orario' => 60.00,             // TP in PNRR
                'ore_contrattualizzate' => 60,
                'compenso_totale' => 3600.00,           // 60 ore × €60
                'status' => 'attivo',
                'note' => 'Contratto Partita IVA - Modulo Sviluppo Web Full Stack',
            ]);
        }

        if ($assegnazioneAiId) {
            // Contratto AI - RU + modulo premium €80/ora
            $this->insert('contratto', [
                'assegnazione_id' => $assegnazioneAiId,
                'tipo' => 'cococo',
                'numero_contratto' => 'CONTR-PNRR-2025-003',
                'data_stipula' => '2025-02-15',
                'data_inizio' => '2025-10-01',
                'data_fine' => '2025-12-31',
                'data_inizio_lezioni' => '2025-10-15',
                'data_fine_lezioni' => '2025-11-15',   // Scadenza vicina!
                'compenso_orario' => 80.00,             // Modulo premium AI
                'ore_contrattualizzate' => 30,
                'compenso_totale' => 2400.00,           // 30 ore × €80
                'status' => 'attivo',
                'note' => 'Contratto CoCoCo - Modulo Intelligenza Artificiale (premium)',
            ]);
        }

        if ($assegnazioneCloudId) {
            // Contratto Cloud - RU quindi €70/ora (ma modulo cloud €75/ora)
            $this->insert('contratto', [
                'assegnazione_id' => $assegnazioneCloudId,
                'tipo' => 'cococo',
                'numero_contratto' => 'CONTR-PNRR-2025-004',
                'data_stipula' => '2025-02-15',
                'data_inizio' => '2025-10-15',
                'data_fine' => '2026-01-15',
                'data_inizio_lezioni' => '2025-10-20',
                'data_fine_lezioni' => '2025-12-15',
                'compenso_orario' => 75.00,             // Cloud computing
                'ore_contrattualizzate' => 40,
                'compenso_totale' => 3000.00,           // 40 ore × €75
                'status' => 'attivo',
                'note' => 'Contratto CoCoCo - Modulo Cloud Computing Base',
            ]);
        }

        return true;
    }

    public function safeDown()
    {
        // Rimuovi in ordine inverso
        $this->delete('contratto', ['numero_contratto' => ['CONTR-PNRR-2025-001', 'CONTR-PNRR-2025-002', 'CONTR-PNRR-2025-003', 'CONTR-PNRR-2025-004']]);
        $this->delete('assegnazione', ['modulo_id' => (new \yii\db\Query())->select('id')->from('modulo')->where(['codice' => ['MOD-PYTHON', 'MOD-WEB', 'MOD-AI', 'MOD-CLOUD']])->column()]);
        $this->delete('istanza', ['avviso_id' => (new \yii\db\Query())->select('id')->from('avviso')->where(['codice_avviso' => 'AVV-003'])->scalar()]);

        // Rimuovi provenienza docenti
        $this->update('docente', ['provenienza' => null]);

        // Rimuovi numero studenti
        $this->update('progetto', ['numero_studenti' => null]);

        return true;
    }
}
