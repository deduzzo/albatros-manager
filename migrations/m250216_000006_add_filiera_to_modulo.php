<?php

use yii\db\Migration;

/**
 * Aggiunge campo filiera alla tabella modulo
 *
 * Alcuni moduli appartengono a filiere specifiche (es: olivicola, brassicola, ortofrutticola)
 * Questo campo opzionale permette di categorizzare i moduli per filiera produttiva
 */
class m250216_000006_add_filiera_to_modulo extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%modulo}}', 'filiera',
            "VARCHAR(100) NULL COMMENT 'Filiera produttiva (es: olivicola, brassicola, ortofrutticola)' AFTER descrizione"
        );

        $this->createIndex('idx_modulo_filiera', '{{%modulo}}', 'filiera');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_modulo_filiera', '{{%modulo}}');
        $this->dropColumn('{{%modulo}}', 'filiera');
    }
}
