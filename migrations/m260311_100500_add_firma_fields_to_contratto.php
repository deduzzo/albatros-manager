<?php

use yii\db\Migration;

/**
 * Aggiunge campi per gestione firma digitale e documento generato alla tabella contratto.
 */
class m260311_100500_add_firma_fields_to_contratto extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%contratto}}', 'documento_generato_path', $this->string(500)->null()->after('documento_path'));
        $this->addColumn('{{%contratto}}', 'firma_tipo', "ENUM('nessuna','p7m','pdf_firmato','otp') DEFAULT 'nessuna' AFTER documento_generato_path");
        $this->addColumn('{{%contratto}}', 'firma_verificata', $this->tinyInteger(1)->defaultValue(0)->after('firma_tipo'));
        $this->addColumn('{{%contratto}}', 'firma_data', $this->timestamp()->null()->after('firma_verificata'));
        $this->addColumn('{{%contratto}}', 'firma_dettagli', $this->text()->null()->after('firma_data'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%contratto}}', 'firma_dettagli');
        $this->dropColumn('{{%contratto}}', 'firma_data');
        $this->dropColumn('{{%contratto}}', 'firma_verificata');
        $this->dropColumn('{{%contratto}}', 'firma_tipo');
        $this->dropColumn('{{%contratto}}', 'documento_generato_path');
    }
}
