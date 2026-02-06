<?php

use yii\db\Migration;

/**
 * Crea la tabella istanza_tag per i tag selezionati per ogni iscrizione.
 * I tag sono specifici dell'iscrizione e possono essere "promossi" al profilo docente.
 */
class m250204_000004_create_istanza_tag_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%istanza_tag}}', [
            'id' => $this->primaryKey(),
            'istanza_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'livello' => $this->smallInteger()->null(),
            'note' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'promosso_at' => $this->timestamp()->null(),
            'promosso_by' => $this->integer()->null(),
        ]);

        // Foreign keys
        $this->addForeignKey(
            'fk_istanza_tag_istanza',
            '{{%istanza_tag}}',
            'istanza_id',
            '{{%istanza}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_istanza_tag_tag',
            '{{%istanza_tag}}',
            'tag_id',
            '{{%tag_curriculum}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey('fk_istanza_tag_promosso_by', '{{%istanza_tag}}', 'promosso_by', '{{%utente}}', 'id', 'SET NULL');

        // Indexes
        $this->createIndex('idx_istanza_tag_istanza', '{{%istanza_tag}}', 'istanza_id');
        $this->createIndex('idx_istanza_tag_tag', '{{%istanza_tag}}', 'tag_id');
        $this->createIndex('idx_istanza_tag_unique', '{{%istanza_tag}}', ['istanza_id', 'tag_id'], true);

        return true;
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_istanza_tag_promosso_by', '{{%istanza_tag}}');
        $this->dropForeignKey('fk_istanza_tag_tag', '{{%istanza_tag}}');
        $this->dropForeignKey('fk_istanza_tag_istanza', '{{%istanza_tag}}');
        $this->dropTable('{{%istanza_tag}}');

        return true;
    }
}
