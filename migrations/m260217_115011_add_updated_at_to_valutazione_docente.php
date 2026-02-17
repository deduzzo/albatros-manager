<?php

use yii\db\Migration;

class m260217_115011_add_updated_at_to_valutazione_docente extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%valutazione_docente}}', 'updated_at', $this->timestamp()->null()->after('created_at'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%valutazione_docente}}', 'updated_at');
    }
}
