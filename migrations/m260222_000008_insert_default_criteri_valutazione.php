<?php

use yii\db\Migration;

/**
 * Inserisce 5 criteri di valutazione globali (avviso_id=NULL)
 * per la valutazione delle candidature studenti.
 */
class m260222_000008_insert_default_criteri_valutazione extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%criterio_valutazione_studente}}', [
            'avviso_id', 'nome', 'peso', 'punteggio_min', 'punteggio_max', 'ordine', 'attivo',
        ], [
            [null, 'Titolo di Studio', 2.0, 0, 10, 1, 1],
            [null, 'Esperienza Lavorativa', 1.5, 0, 10, 2, 1],
            [null, 'Motivazione', 1.0, 0, 10, 3, 1],
            [null, 'Requisiti Specifici', 1.5, 0, 10, 4, 1],
            [null, 'Situazione Occupazionale', 1.0, 0, 10, 5, 1],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%criterio_valutazione_studente}}', ['avviso_id' => null]);
    }
}
