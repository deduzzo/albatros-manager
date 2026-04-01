<?php

use yii\db\Migration;

class m260401_100300_add_erogazione_bloccata_status_borsa extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%borsa_studio}}', 'status', "ENUM('richiesta','approvata','in_erogazione','erogazione_bloccata','completata','revocata','sospesa') NOT NULL DEFAULT 'richiesta'");
    }

    public function safeDown()
    {
        $this->alterColumn('{{%borsa_studio}}', 'status', "ENUM('richiesta','approvata','in_erogazione','completata','revocata','sospesa') NOT NULL DEFAULT 'richiesta'");
    }
}
