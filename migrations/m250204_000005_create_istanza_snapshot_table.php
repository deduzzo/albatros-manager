<?php

use yii\db\Migration;

/**
 * Crea la tabella istanza_snapshot per salvare i dati storici del docente
 * al momento dell'iscrizione.
 */
class m250204_000005_create_istanza_snapshot_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%istanza_snapshot}}', [
            'id' => $this->primaryKey(),
            'istanza_id' => $this->integer()->notNull()->unique(),
            'dati_docente' => $this->json()->notNull(),
            'tags_docente' => $this->json()->null(),
            'tags_iscrizione' => $this->json()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Foreign keys
        $this->addForeignKey(
            'fk_istanza_snapshot_istanza',
            '{{%istanza_snapshot}}',
            'istanza_id',
            '{{%istanza}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_istanza_snapshot_istanza', '{{%istanza_snapshot}}');
        $this->dropTable('{{%istanza_snapshot}}');

        return true;
    }
}
