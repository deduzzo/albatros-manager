<?php

use yii\db\Migration;

class m260401_100200_add_allegati_to_contratto extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%contratto}}', 'allegati_path', $this->json()->null()->after('documento_path')->comment('JSON array di percorsi allegati'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%contratto}}', 'allegati_path');
    }
}
