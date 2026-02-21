<?php

use yii\db\Migration;

/**
 * Inserisce i valori di default nella tabella configurazione
 * per i dati della fondazione.
 */
class m260222_000007_insert_configurazione_defaults extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%configurazione}}', [
            'chiave', 'valore', 'tipo', 'gruppo', 'etichetta', 'ordine',
        ], [
            ['nome_fondazione', 'Fondazione ITS Albatros Academy', 'text', 'fondazione', 'Nome Fondazione', 1],
            ['indirizzo', '', 'text', 'fondazione', 'Indirizzo', 2],
            ['telefono', '', 'phone', 'fondazione', 'Telefono', 3],
            ['email', 'info@albatros.it', 'email', 'fondazione', 'Email', 4],
            ['pec', '', 'email', 'fondazione', 'PEC', 5],
            ['partita_iva', '', 'text', 'fondazione', 'Partita IVA', 6],
            ['codice_fiscale_ente', '', 'text', 'fondazione', 'Codice Fiscale Ente', 7],
            ['sito_web', '', 'url', 'fondazione', 'Sito Web', 8],
            ['logo_url', '', 'image', 'fondazione', 'Logo', 9],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%configurazione}}', ['gruppo' => 'fondazione']);
    }
}
