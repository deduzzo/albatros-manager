<?php

use yii\db\Migration;

/**
 * Refactoring rimborso_spesa:
 * - Rimuove colonne tipo e importo (sostituiti da rimborso_spesa_dettaglio)
 * - Aggiunge numero_partecipanti (PAX)
 * - Crea tabella rimborso_spesa_dettaglio (voci multiple per rimborso)
 * - Aggiunge voce "Generale" in voce_spesa
 */
class m260318_100000_refactor_rimborso_spesa_dettaglio extends Migration
{
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Creare tabella rimborso_spesa_dettaglio PRIMA di eliminare le colonne
        $this->createTable('{{%rimborso_spesa_dettaglio}}', [
            'id' => $this->primaryKey(),
            'rimborso_spesa_id' => $this->integer()->notNull(),
            'voce_spesa_id' => $this->integer(),
            'descrizione' => $this->string(500)->notNull(),
            'importo' => $this->decimal(10, 2)->notNull(),
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_by' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_rimborso_dettaglio_rimborso',
            '{{%rimborso_spesa_dettaglio}}', 'rimborso_spesa_id',
            '{{%rimborso_spesa}}', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_rimborso_dettaglio_voce',
            '{{%rimborso_spesa_dettaglio}}', 'voce_spesa_id',
            '{{%voce_spesa}}', 'id',
            'SET NULL', 'CASCADE'
        );

        $this->createIndex('idx_rimborso_dettaglio_deleted', '{{%rimborso_spesa_dettaglio}}', 'deleted_at');
        $this->createIndex('idx_rimborso_dettaglio_rimborso', '{{%rimborso_spesa_dettaglio}}', 'rimborso_spesa_id');

        // 2. Migrare dati esistenti: creare dettagli dai rimborsi con importo/tipo
        $rimborsi = (new \yii\db\Query())
            ->select(['id', 'tipo', 'importo', 'descrizione'])
            ->from('{{%rimborso_spesa}}')
            ->all();

        // Mappa tipo -> label leggibile
        $tipoLabels = [
            'materiale' => 'Materiale didattico',
            'chilometrico' => 'Rimborso chilometrico',
            'vitto_alloggio' => 'Vitto e alloggio',
            'viaggio' => 'Viaggio/Trasporto',
            'altro' => 'Altro',
        ];

        foreach ($rimborsi as $rimborso) {
            $voceDescrizione = $tipoLabels[$rimborso['tipo']] ?? ($rimborso['tipo'] ?: 'Generale');
            $this->insert('{{%rimborso_spesa_dettaglio}}', [
                'rimborso_spesa_id' => $rimborso['id'],
                'descrizione' => $voceDescrizione,
                'importo' => $rimborso['importo'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 3. Rimuovere tipo e importo
        $this->dropColumn('{{%rimborso_spesa}}', 'tipo');
        $this->dropColumn('{{%rimborso_spesa}}', 'importo');

        // 4. Aggiungere voce "Generale" se non esiste
        // Cerchiamo la prima categoria (o creiamo una generica)
        $categoriaId = (new \yii\db\Query())
            ->select('id')
            ->from('{{%voce_spesa_categoria}}')
            ->where(['codice' => 'B2'])
            ->andWhere(['deleted_at' => null])
            ->scalar();

        if ($categoriaId) {
            // Verifica se esiste gia una voce "Generale"
            $exists = (new \yii\db\Query())
                ->from('{{%voce_spesa}}')
                ->where(['codice' => 'GEN', 'deleted_at' => null])
                ->exists();

            if (!$exists) {
                $this->insert('{{%voce_spesa}}', [
                    'categoria_id' => $categoriaId,
                    'codice' => 'GEN',
                    'nome' => 'Generale',
                    'descrizione' => 'Voce generica per spese non categorizzate',
                    'ordinamento' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function safeDown()
    {
        // Ripristina colonne
        $this->addColumn('{{%rimborso_spesa}}', 'tipo', "ENUM('materiale','chilometrico','vitto_alloggio','viaggio','altro') DEFAULT 'altro' AFTER descrizione");
        $this->addColumn('{{%rimborso_spesa}}', 'importo', $this->decimal(10, 2)->notNull()->defaultValue(0)->after('tipo'));

        // Riporta i dati dai dettagli ai rimborsi
        $dettagli = (new \yii\db\Query())
            ->select(['rimborso_spesa_id', 'SUM(importo) as totale'])
            ->from('{{%rimborso_spesa_dettaglio}}')
            ->where(['deleted_at' => null])
            ->groupBy('rimborso_spesa_id')
            ->all();

        foreach ($dettagli as $d) {
            $this->update('{{%rimborso_spesa}}', ['importo' => $d['totale']], ['id' => $d['rimborso_spesa_id']]);
        }

        $this->dropForeignKey('fk_rimborso_dettaglio_voce', '{{%rimborso_spesa_dettaglio}}');
        $this->dropForeignKey('fk_rimborso_dettaglio_rimborso', '{{%rimborso_spesa_dettaglio}}');
        $this->dropTable('{{%rimborso_spesa_dettaglio}}');

        // Rimuovi voce Generale
        $this->delete('{{%voce_spesa}}', ['codice' => 'GEN']);
    }
}
