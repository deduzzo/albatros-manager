<?php

use yii\db\Migration;

/**
 * Crea la tabella richiesta_fatturazione per gestire richieste fatture e cedolini.
 */
class m260311_100300_create_richiesta_fatturazione_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%richiesta_fatturazione}}', [
            'id' => $this->primaryKey(),
            'contratto_id' => $this->integer()->notNull(),
            'tipo' => "ENUM('fattura','cedolino') NOT NULL",
            'importo' => $this->decimal(12, 2)->notNull(),
            'periodo_riferimento' => $this->string(100)->null(),
            'data_richiesta' => $this->date()->notNull(),
            'data_scadenza' => $this->date()->null(),
            'destinatario_email' => $this->string(255)->notNull(),
            'destinatario_nome' => $this->string(255)->null(),
            'oggetto_email' => $this->string(500)->null(),
            'corpo_email' => $this->text()->null(),
            'documento_ricevuto_path' => $this->string(500)->null(),
            'data_ricezione' => $this->date()->null(),
            'status' => "ENUM('bozza','inviata','ricevuta','verificata') NOT NULL DEFAULT 'bozza'",
            'note' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk_richiesta_fatturazione_contratto',
            '{{%richiesta_fatturazione}}',
            'contratto_id',
            '{{%contratto}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createIndex('idx_richiesta_fatturazione_deleted', '{{%richiesta_fatturazione}}', 'deleted_at');
        $this->createIndex('idx_richiesta_fatturazione_contratto', '{{%richiesta_fatturazione}}', 'contratto_id');
        $this->createIndex('idx_richiesta_fatturazione_status', '{{%richiesta_fatturazione}}', 'status');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_richiesta_fatturazione_contratto', '{{%richiesta_fatturazione}}');
        $this->dropTable('{{%richiesta_fatturazione}}');
    }
}
