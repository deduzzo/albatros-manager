<?php

use yii\db\Migration;

/**
 * Crea la tabella configurazione per le impostazioni generali del sistema.
 * NO soft delete (le configurazioni non si cancellano logicamente).
 * Con audit (created_at, updated_at, created_by, updated_by).
 */
class m260222_000006_create_configurazione extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%configurazione}}', [
            'id' => $this->primaryKey(),
            'chiave' => $this->string(100)->notNull()->unique(),
            'valore' => $this->text()->null(),
            'tipo' => "ENUM('text','email','url','phone','image','json') NOT NULL DEFAULT 'text'",
            'gruppo' => $this->string(50)->null(),
            'etichetta' => $this->string(255)->null(),
            'descrizione' => $this->text()->null(),
            'ordine' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        // FK audit _by verso utente
        $this->addForeignKey(
            'fk_configurazione_created_by',
            '{{%configurazione}}',
            'created_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_configurazione_updated_by',
            '{{%configurazione}}',
            'updated_by',
            '{{%utente}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Indice per gruppo (per query filtrate)
        $this->createIndex('idx_configurazione_gruppo', '{{%configurazione}}', 'gruppo');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_configurazione_updated_by', '{{%configurazione}}');
        $this->dropForeignKey('fk_configurazione_created_by', '{{%configurazione}}');
        $this->dropTable('{{%configurazione}}');
    }
}
