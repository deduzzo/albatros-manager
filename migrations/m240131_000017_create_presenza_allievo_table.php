<?php

use yii\db\Migration;

class m240131_000017_create_presenza_allievo_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%presenza_allievo}}', [
            'id' => $this->primaryKey(),
            'calendario_id' => $this->integer()->notNull(),
            'allievo_codice_fiscale' => $this->char(16)->notNull(),
            'presente' => $this->boolean()->notNull()->defaultValue(false),
            'ora_ingresso' => $this->time(),
            'ora_uscita' => $this->time(),
            'ore_presenza' => $this->decimal(4, 2),
            'giustificato' => $this->boolean()->defaultValue(false),
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_presenza_unica', '{{%presenza_allievo}}', ['calendario_id', 'allievo_codice_fiscale'], true);

        $this->addForeignKey(
            'fk_presenza_calendario',
            '{{%presenza_allievo}}',
            'calendario_id',
            '{{%calendario}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_presenza_allievo',
            '{{%presenza_allievo}}',
            'allievo_codice_fiscale',
            '{{%allievo}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_presenza_allievo', '{{%presenza_allievo}}');
        $this->dropForeignKey('fk_presenza_calendario', '{{%presenza_allievo}}');
        $this->dropTable('{{%presenza_allievo}}');
    }
}
