<?php

use yii\db\Migration;

/**
 * Crea le tabelle per voci di spesa e bilancio:
 * - voce_spesa_categoria
 * - voce_spesa
 * - movimento_spesa
 * - tranche_finanziamento
 */
class m260311_100100_create_voce_spesa_tables extends Migration
{
    public function safeUp()
    {
        // ---- voce_spesa_categoria ----
        $this->createTable('{{%voce_spesa_categoria}}', [
            'id' => $this->primaryKey(),
            'codice' => $this->string(20)->notNull(),
            'nome' => $this->string(255)->notNull(),
            'sezione' => "ENUM('costi_diretti','costi_indiretti','attivita_accessorie') NOT NULL",
            'ordinamento' => $this->integer()->defaultValue(0),
            'fonte_finanziamento_id' => $this->integer()->null()->comment('NULL = tutte le fonti'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk_voce_spesa_cat_fonte',
            '{{%voce_spesa_categoria}}',
            'fonte_finanziamento_id',
            '{{%fonte_finanziamento}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->createIndex('idx_voce_spesa_cat_deleted', '{{%voce_spesa_categoria}}', 'deleted_at');
        $this->createIndex('idx_voce_spesa_cat_sezione', '{{%voce_spesa_categoria}}', 'sezione');

        // ---- voce_spesa ----
        $this->createTable('{{%voce_spesa}}', [
            'id' => $this->primaryKey(),
            'categoria_id' => $this->integer()->notNull(),
            'codice' => $this->string(20)->null(),
            'nome' => $this->string(255)->notNull(),
            'descrizione' => $this->text()->null(),
            'ordinamento' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk_voce_spesa_categoria',
            '{{%voce_spesa}}',
            'categoria_id',
            '{{%voce_spesa_categoria}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createIndex('idx_voce_spesa_deleted', '{{%voce_spesa}}', 'deleted_at');

        // ---- movimento_spesa ----
        $this->createTable('{{%movimento_spesa}}', [
            'id' => $this->primaryKey(),
            'progetto_id' => $this->integer()->notNull(),
            'voce_spesa_id' => $this->integer()->notNull(),
            'descrizione' => $this->string(500)->notNull(),
            'importo' => $this->decimal(12, 2)->notNull(),
            'data_movimento' => $this->date()->notNull(),
            'data_documento' => $this->date()->null(),
            'numero_documento' => $this->string(100)->null(),
            'fornitore' => $this->string(255)->null(),
            'tipo' => "ENUM('spesa','entrata') NOT NULL DEFAULT 'spesa'",
            'riferimento_tipo' => $this->string(50)->null()->comment('pagamento, rimborso, null=manuale'),
            'riferimento_id' => $this->integer()->null(),
            'allegato_path' => $this->string(500)->null(),
            'note' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk_movimento_spesa_progetto',
            '{{%movimento_spesa}}',
            'progetto_id',
            '{{%progetto}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_movimento_spesa_voce',
            '{{%movimento_spesa}}',
            'voce_spesa_id',
            '{{%voce_spesa}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createIndex('idx_movimento_spesa_deleted', '{{%movimento_spesa}}', 'deleted_at');
        $this->createIndex('idx_movimento_spesa_progetto', '{{%movimento_spesa}}', 'progetto_id');
        $this->createIndex('idx_movimento_spesa_tipo', '{{%movimento_spesa}}', 'tipo');
        $this->createIndex('idx_movimento_spesa_data', '{{%movimento_spesa}}', 'data_movimento');

        // ---- tranche_finanziamento ----
        $this->createTable('{{%tranche_finanziamento}}', [
            'id' => $this->primaryKey(),
            'progetto_id' => $this->integer()->notNull(),
            'importo' => $this->decimal(12, 2)->notNull(),
            'data_accredito' => $this->date()->null(),
            'data_prevista' => $this->date()->null(),
            'numero_tranche' => $this->integer()->null(),
            'riferimento' => $this->string(255)->null()->comment('es. Anticipo, SAL 1, Saldo'),
            'status' => "ENUM('prevista','accreditata','parziale') NOT NULL DEFAULT 'prevista'",
            'note' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk_tranche_finanziamento_progetto',
            '{{%tranche_finanziamento}}',
            'progetto_id',
            '{{%progetto}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createIndex('idx_tranche_finanziamento_deleted', '{{%tranche_finanziamento}}', 'deleted_at');
        $this->createIndex('idx_tranche_finanziamento_progetto', '{{%tranche_finanziamento}}', 'progetto_id');
        $this->createIndex('idx_tranche_finanziamento_status', '{{%tranche_finanziamento}}', 'status');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_tranche_finanziamento_progetto', '{{%tranche_finanziamento}}');
        $this->dropTable('{{%tranche_finanziamento}}');

        $this->dropForeignKey('fk_movimento_spesa_voce', '{{%movimento_spesa}}');
        $this->dropForeignKey('fk_movimento_spesa_progetto', '{{%movimento_spesa}}');
        $this->dropTable('{{%movimento_spesa}}');

        $this->dropForeignKey('fk_voce_spesa_categoria', '{{%voce_spesa}}');
        $this->dropTable('{{%voce_spesa}}');

        $this->dropForeignKey('fk_voce_spesa_cat_fonte', '{{%voce_spesa_categoria}}');
        $this->dropTable('{{%voce_spesa_categoria}}');
    }
}
