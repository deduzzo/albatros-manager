<?php

use yii\db\Migration;

class m260311_160000_make_valutazione_docente_modulo_nullable extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%valutazione_docente}}', 'modulo_id', $this->integer()->null());
        $this->alterColumn('{{%valutazione_docente}}', 'docente_codice_fiscale', $this->char(16)->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%valutazione_docente}}', 'modulo_id', $this->integer()->notNull());
        $this->alterColumn('{{%valutazione_docente}}', 'docente_codice_fiscale', $this->char(16)->notNull());
    }
}
