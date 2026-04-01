<?php

use yii\db\Migration;

class m260401_100400_add_banca_config extends Migration
{
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');
        $rows = [
            ['chiave' => 'banca_iban_fondazione', 'valore' => '', 'gruppo' => 'banca', 'descrizione' => 'IBAN della Fondazione per pagamenti SEPA'],
            ['chiave' => 'banca_bic',             'valore' => '', 'gruppo' => 'banca', 'descrizione' => 'BIC/SWIFT della banca'],
            ['chiave' => 'banca_creditor_id',     'valore' => '', 'gruppo' => 'banca', 'descrizione' => 'Creditor Identifier per SEPA'],
            ['chiave' => 'banca_nome_istituto',   'valore' => '', 'gruppo' => 'banca', 'descrizione' => 'Nome istituto bancario'],
        ];

        foreach ($rows as $row) {
            $exists = (new \yii\db\Query())
                ->from('{{%configurazione}}')
                ->where(['chiave' => $row['chiave']])
                ->exists();

            if (!$exists) {
                $this->insert('{{%configurazione}}', array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function safeDown()
    {
        $this->delete('{{%configurazione}}', ['chiave' => [
            'banca_iban_fondazione',
            'banca_bic',
            'banca_creditor_id',
            'banca_nome_istituto',
        ]]);
    }
}
