<?php

use yii\db\Migration;

/**
 * Seeder: inserisce le voci di spesa dal Bilancio Preventivo 2025.
 * Categorie B1-B4 (costi diretti), C1 (costi indiretti), D1 (attivita accessorie).
 */
class m260311_100600_seed_voci_spesa_bilancio extends Migration
{
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        // ---- CATEGORIE ----

        // B1 - Preparazione
        $this->insert('{{%voce_spesa_categoria}}', [
            'codice' => 'B1',
            'nome' => 'Preparazione',
            'sezione' => 'costi_diretti',
            'ordinamento' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $catB1 = $this->db->getLastInsertID();

        // B2 - Realizzazione
        $this->insert('{{%voce_spesa_categoria}}', [
            'codice' => 'B2',
            'nome' => 'Realizzazione',
            'sezione' => 'costi_diretti',
            'ordinamento' => 20,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $catB2 = $this->db->getLastInsertID();

        // B3 - Diffusione
        $this->insert('{{%voce_spesa_categoria}}', [
            'codice' => 'B3',
            'nome' => 'Diffusione',
            'sezione' => 'costi_diretti',
            'ordinamento' => 30,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $catB3 = $this->db->getLastInsertID();

        // B4 - Direzione e controllo
        $this->insert('{{%voce_spesa_categoria}}', [
            'codice' => 'B4',
            'nome' => 'Direzione e controllo',
            'sezione' => 'costi_diretti',
            'ordinamento' => 40,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $catB4 = $this->db->getLastInsertID();

        // C1 - Costi indiretti
        $this->insert('{{%voce_spesa_categoria}}', [
            'codice' => 'C1',
            'nome' => 'Costi indiretti / Spese generali',
            'sezione' => 'costi_indiretti',
            'ordinamento' => 50,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $catC1 = $this->db->getLastInsertID();

        // D1 - Attivita accessorie
        $this->insert('{{%voce_spesa_categoria}}', [
            'codice' => 'D1',
            'nome' => 'Attività accessorie / Costi destinatari',
            'sezione' => 'attivita_accessorie',
            'ordinamento' => 60,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $catD1 = $this->db->getLastInsertID();

        // ---- VOCI DI SPESA ----

        $voci = [
            // B1 - Preparazione
            [$catB1, 'B1.1', 'Indagine preliminare domanda-offerta', 10],
            [$catB1, 'B1.2', 'Ideazione e progettazione', 20],
            [$catB1, 'B1.3', 'Pubblicizzazione e promozione del progetto', 30],
            [$catB1, 'B1.4', 'Selezione e orientamento partecipanti', 40],
            [$catB1, 'B1.5', 'Elaborazione materiale didattico', 50],

            // B2 - Realizzazione
            [$catB2, 'B2.1', 'Docenza', 10],
            [$catB2, 'B2.2', 'Tutoraggio', 20],
            [$catB2, 'B2.3', 'Coordinamento', 30],
            [$catB2, 'B2.4', 'Erogazione del servizio', 40],
            [$catB2, 'B2.5', 'Attività di sostegno all\'utenza', 50],
            [$catB2, 'B2.6', 'Esami finali', 60],
            [$catB2, 'B2.7', 'Utilizzo locali e attrezzature per l\'attività programmata', 70],
            [$catB2, 'B2.8', 'Utilizzo materiali di consumo per l\'attività programmata', 80],
            [$catB2, 'B2.9', 'Costi per viaggi / trasferte / rimborsi', 90],
            [$catB2, 'B2.10', 'Costi di natura assicurativa', 100],
            [$catB2, 'B2.11', 'Stage e tirocini aziendali', 110],
            [$catB2, 'B2.12', 'Visite guidate', 120],
            [$catB2, 'B2.13', 'Certificazione delle competenze', 130],
            [$catB2, 'B2.14', 'Altre attività di realizzazione', 140],

            // B3 - Diffusione
            [$catB3, 'B3.1', 'Diffusione e disseminazione risultati', 10],
            [$catB3, 'B3.2', 'Seminari e convegni finali', 20],

            // B4 - Direzione e controllo
            [$catB4, 'B4.1', 'Direzione del progetto', 10],
            [$catB4, 'B4.2', 'Coordinamento e segreteria tecnica', 20],
            [$catB4, 'B4.3', 'Valutazione in itinere e finale del progetto', 30],
            [$catB4, 'B4.4', 'Monitoraggio fisico-finanziario', 40],
            [$catB4, 'B4.5', 'Comitato tecnico-scientifico', 50],
            [$catB4, 'B4.6', 'Rendicontazione e certificazione', 60],
            [$catB4, 'B4.7', 'Azioni di accompagnamento', 70],
            [$catB4, 'B4.8', 'Altre attività di direzione e controllo', 80],

            // C1 - Costi indiretti
            [$catC1, 'C1.1', 'Spese generali di funzionamento', 10],
            [$catC1, 'C1.2', 'Consulenza fiscale e amministrativa', 20],
            [$catC1, 'C1.3', 'Consulenza del lavoro', 30],
            [$catC1, 'C1.4', 'Consulenza legale', 40],
            [$catC1, 'C1.5', 'Assistenza informatica e tecnologica', 50],
            [$catC1, 'C1.6', 'Altre spese generali', 60],

            // D1 - Attivita accessorie
            [$catD1, 'D1.1', 'Borse di studio', 10],
            [$catD1, 'D1.2', 'Indennità di frequenza e/o trasporto', 20],
            [$catD1, 'D1.3', 'Vitto e alloggio allievi', 30],
            [$catD1, 'D1.4', 'Materiale didattico individuale per gli allievi', 40],
            [$catD1, 'D1.5', 'Copertura assicurativa allievi', 50],
            [$catD1, 'D1.6', 'Altre attività accessorie', 60],
        ];

        foreach ($voci as $voce) {
            $this->insert('{{%voce_spesa}}', [
                'categoria_id' => $voce[0],
                'codice' => $voce[1],
                'nome' => $voce[2],
                'ordinamento' => $voce[3],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function safeDown()
    {
        $this->delete('{{%voce_spesa}}');
        $this->delete('{{%voce_spesa_categoria}}');
    }
}
