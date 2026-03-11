<?php

use yii\db\Migration;

/**
 * Importa utenti reali dal template XLSX e rimuove l'utente admin di prova.
 */
class m260311_170000_import_utenti_reali extends Migration
{
    public function safeUp()
    {
        $utenti = [
            ['nominativo' => 'UTENTE UNO', 'email' => 'utente1@example.com', 'password' => 'CAMBIAMI', 'ruolo' => 'admin'],
            ['nominativo' => 'UTENTE DUE', 'email' => 'utente2@example.com', 'password' => 'CAMBIAMI', 'ruolo' => 'operatore'],
            ['nominativo' => 'UTENTE TRE', 'email' => 'utente3@example.com', 'password' => 'CAMBIAMI', 'ruolo' => 'operatore'],
            ['nominativo' => 'UTENTE QUATTRO', 'email' => 'utente4@example.com', 'password' => 'CAMBIAMI', 'ruolo' => 'segreteria'],
            ['nominativo' => 'UTENTE CINQUE', 'email' => 'utente5@example.com', 'password' => 'CAMBIAMI', 'ruolo' => 'operatore'],
            ['nominativo' => 'UTENTE SEI', 'email' => 'utente6@example.com', 'password' => 'CAMBIAMI', 'ruolo' => 'operatore'],
            ['nominativo' => 'UTENTE SETTE', 'email' => 'utente7@example.com', 'password' => 'CAMBIAMI', 'ruolo' => 'segreteria'],
            // Sviluppatore
            ['nominativo' => 'Roberto De Domenico', 'email' => 'robertodedomenico@gmail.com', 'password' => 'CAMBIAMI', 'ruolo' => 'admin'],
        ];

        foreach ($utenti as $u) {
            // Verifica se esiste già per email
            $exists = (new \yii\db\Query())
                ->from('{{%utente}}')
                ->where(['email' => $u['email']])
                ->exists();

            if (!$exists) {
                // Genera username dalla email (parte prima della @)
                $username = explode('@', $u['email'])[0];
                // Rimuovi caratteri non alfanumerici
                $username = preg_replace('/[^a-zA-Z0-9._-]/', '', $username);

                $this->insert('{{%utente}}', [
                    'username' => $username,
                    'nominativo' => $u['nominativo'],
                    'email' => $u['email'],
                    'password_hash' => Yii::$app->security->generatePasswordHash($u['password']),
                    'ruolo' => $u['ruolo'],
                    'status' => 1,
                    'auth_key' => Yii::$app->security->generateRandomString(),
                    'created_at' => new \yii\db\Expression('NOW()'),
                    'updated_at' => new \yii\db\Expression('NOW()'),
                ]);
                echo "  Creato: {$u['nominativo']} ({$u['email']}) - {$u['ruolo']}\n";
            } else {
                echo "  Esiste già: {$u['email']}\n";
            }
        }

        // Rimuovi utente admin di prova (soft delete)
        $this->update('{{%utente}}', [
            'deleted_at' => new \yii\db\Expression('NOW()'),
            'status' => 0,
        ], ['username' => 'admin', 'email' => 'admin@albatros.it']);

        echo "  Disattivato utente admin di prova.\n";
    }

    public function safeDown()
    {
        // Ripristina admin di prova
        $this->update('{{%utente}}', [
            'deleted_at' => null,
            'status' => 1,
        ], ['username' => 'admin']);

        // Rimuovi utenti importati
        $emails = [
            'utente1@example.com',
            'utente2@example.com',
            'utente3@example.com',
            'utente4@example.com',
            'utente5@example.com',
            'utente6@example.com',
            'utente7@example.com',
            'robertodedomenico@gmail.com',
        ];
        $this->delete('{{%utente}}', ['email' => $emails]);
    }
}
