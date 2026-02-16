<?php

use yii\db\Migration;

class m260216_152125_add_codice_fiscale_validato_to_docente extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Aggiungi campo codice_fiscale_validato (boolean, default false)
        $this->addColumn('docente', 'codice_fiscale_validato', $this->boolean()->notNull()->defaultValue(0)->after('codice_fiscale'));

        // Azzera tutte le email fittizie
        $this->execute("UPDATE docente SET email = NULL WHERE email LIKE '%@albatros-temp.it'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('docente', 'codice_fiscale_validato');
    }
}
