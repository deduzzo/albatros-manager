<?php

use yii\db\Migration;

/**
 * Migration per il sistema di valutazione docenti con criteri personalizzabili.
 *
 * Crea:
 * - criterio_valutazione_docente: criteri di valutazione configurabili
 * - valutazione_docente_punteggio: punteggi per singolo criterio
 * - Modifica valutazione_docente: nuovi campi per import CSV
 * - Seed: 15 criteri di default dai questionari
 */
class m260311_150000_create_criterio_valutazione_docente_tables extends Migration
{
    public function safeUp()
    {
        // 1. Tabella criterio_valutazione_docente
        $this->createTable('{{%criterio_valutazione_docente}}', [
            'id' => $this->primaryKey(),
            'codice' => $this->string(50)->notNull()->unique(),
            'nome' => $this->string(255)->notNull(),
            'descrizione' => $this->text()->null(),
            'scala_min' => $this->integer()->notNull()->defaultValue(1),
            'scala_max' => $this->integer()->notNull()->defaultValue(10),
            'ordinamento' => $this->integer()->notNull()->defaultValue(0),
            'attivo' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx-criterio-val-doc-codice', '{{%criterio_valutazione_docente}}', 'codice');
        $this->createIndex('idx-criterio-val-doc-attivo', '{{%criterio_valutazione_docente}}', 'attivo');
        $this->createIndex('idx-criterio-val-doc-ordinamento', '{{%criterio_valutazione_docente}}', 'ordinamento');

        // 2. Tabella valutazione_docente_punteggio
        $this->createTable('{{%valutazione_docente_punteggio}}', [
            'id' => $this->primaryKey(),
            'valutazione_docente_id' => $this->integer()->notNull(),
            'criterio_id' => $this->integer()->notNull(),
            'punteggio' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk-val-doc-punt-valutazione',
            '{{%valutazione_docente_punteggio}}',
            'valutazione_docente_id',
            '{{%valutazione_docente}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-val-doc-punt-criterio',
            '{{%valutazione_docente_punteggio}}',
            'criterio_id',
            '{{%criterio_valutazione_docente}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Indice unique composito (con deleted_at per soft delete)
        $this->createIndex(
            'idx-val-doc-punt-unique',
            '{{%valutazione_docente_punteggio}}',
            ['valutazione_docente_id', 'criterio_id', 'deleted_at'],
            true
        );

        // 3. Modifica tabella valutazione_docente: nuovi campi
        $this->addColumn('{{%valutazione_docente}}', 'studente_email', $this->string(255)->null()->after('data_valutazione'));
        $this->addColumn('{{%valutazione_docente}}', 'studente_nome', $this->string(255)->null()->after('studente_email'));
        $this->addColumn('{{%valutazione_docente}}', 'docente_nome', $this->string(255)->null()->after('studente_nome'));
        $this->addColumn('{{%valutazione_docente}}', 'sorgente_url', $this->string(500)->null()->after('docente_nome'));
        $this->addColumn('{{%valutazione_docente}}', 'sorgente_tipo', "ENUM('manuale','codice_monouso','import_csv') NOT NULL DEFAULT 'manuale' AFTER `sorgente_url`");
        $this->addColumn('{{%valutazione_docente}}', 'note', $this->text()->null()->after('sorgente_tipo'));

        // 4. Seed dei 15 criteri di default
        $criteri = [
            ['competenza_materia', 'Competenza nella materia', 'Il docente ha dimostrato una profonda competenza e padronanza della materia insegnata.', 1],
            ['contenuti_aggiornati', 'Contenuti aggiornati', 'I contenuti del modulo sono stati ritenuti aggiornati e coerenti con le esigenze formative ITS.', 2],
            ['organizzazione', 'Organizzazione', 'La materia è stata presentata in modo organizzato e sequenziale (si è capito il filo logico).', 3],
            ['rilevanza_pratica', 'Rilevanza pratica', 'Il docente ha saputo evidenziare la rilevanza pratica del modulo per il percorso di studi e il mondo del lavoro.', 4],
            ['linguaggio_chiaro', 'Linguaggio chiaro', 'Il docente ha utilizzato un linguaggio chiaro e comprensibile per tutti gli studenti.', 5],
            ['velocita_esposizione', 'Velocità di esposizione', 'La velocità di esposizione e il ritmo della lezione sono stati adeguati.', 6],
            ['metodologie_didattiche', 'Metodologie didattiche', 'Il docente ha variato le metodologie didattiche (non solo lezione frontale, ma anche esercitazioni/attività pratiche).', 7],
            ['materiale_didattico', 'Materiale didattico', 'Il materiale didattico fornito (slide, dispense, riferimenti) è stato utile e ben strutturato.', 8],
            ['interesse_motivazione', 'Interesse e motivazione', 'Il docente ha saputo mantenere l\'interesse e la motivazione degli studenti.', 9],
            ['partecipazione_interazione', 'Partecipazione e interazione', 'Il docente ha incoraggiato la partecipazione, l\'interazione e il dibattito costruttivo.', 10],
            ['disponibilita_supporto', 'Disponibilità e supporto', 'Il docente è stato disponibile a rispondere a domande, chiarire dubbi e fornire supporto individuale.', 11],
            ['comportamento_professionale', 'Comportamento professionale', 'Il docente ha dimostrato un comportamento professionale e rispettoso nei confronti degli studenti.', 12],
            ['obiettivi_chiari', 'Obiettivi chiari', 'Gli obiettivi di apprendimento del modulo sono stati chiaramente esposti all\'inizio del corso.', 13],
            ['modalita_verifica', 'Modalità di verifica', 'Le modalità di verifica (esami, project work, test) sono state coerenti con i contenuti erogati.', 14],
            ['criteri_valutazione', 'Criteri di valutazione', 'I criteri di valutazione sono stati chiari, trasparenti ed equi.', 15],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($criteri as $c) {
            $this->insert('{{%criterio_valutazione_docente}}', [
                'codice' => $c[0],
                'nome' => $c[1],
                'descrizione' => $c[2],
                'scala_min' => 1,
                'scala_max' => 10,
                'ordinamento' => $c[3],
                'attivo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return true;
    }

    public function safeDown()
    {
        $this->dropColumn('{{%valutazione_docente}}', 'note');
        $this->dropColumn('{{%valutazione_docente}}', 'sorgente_tipo');
        $this->dropColumn('{{%valutazione_docente}}', 'sorgente_url');
        $this->dropColumn('{{%valutazione_docente}}', 'docente_nome');
        $this->dropColumn('{{%valutazione_docente}}', 'studente_nome');
        $this->dropColumn('{{%valutazione_docente}}', 'studente_email');

        $this->dropTable('{{%valutazione_docente_punteggio}}');
        $this->dropTable('{{%criterio_valutazione_docente}}');

        return true;
    }
}
