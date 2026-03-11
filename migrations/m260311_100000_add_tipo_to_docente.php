<?php

use yii\db\Migration;

/**
 * Aggiunge il campo tipo alla tabella docente.
 * Permette di distinguere docenti, coordinatori e tutor.
 */
class m260311_100000_add_tipo_to_docente extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%docente}}', 'tipo', "ENUM('docente','coordinatore','tutor') NOT NULL DEFAULT 'docente' AFTER provenienza");
        $this->createIndex('idx_docente_tipo', '{{%docente}}', 'tipo');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_docente_tipo', '{{%docente}}');
        $this->dropColumn('{{%docente}}', 'tipo');
    }
}
