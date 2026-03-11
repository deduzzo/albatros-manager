<?php

use yii\db\Migration;

/**
 * Crea la tabella borsa_studio per la gestione borse di studio studenti.
 */
class m260311_100400_create_borsa_studio_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%borsa_studio}}', [
            'id' => $this->primaryKey(),
            'allievo_codice_fiscale' => $this->char(16)->notNull(),
            'progetto_id' => $this->integer()->notNull(),
            'anno_formativo' => $this->string(9)->notNull()->comment('es. 2024/2025'),

            // Classificazione studente
            'tipologia_sede' => "ENUM('in_sede','pendolare','fuori_sede') NOT NULL",

            // Dati ISEE/ISPE
            'isee_importo' => $this->decimal(12, 2)->null(),
            'ispe_importo' => $this->decimal(12, 2)->null(),
            'isee_protocollo' => $this->string(100)->null(),

            // Importo calcolato
            'importo_base' => $this->decimal(10, 2)->notNull(),
            'maggiorazione_tipo' => $this->string(50)->null()->comment('isee_basso, studentessa, disabilita, tirocinio_estero'),
            'maggiorazione_percentuale' => $this->decimal(5, 2)->defaultValue(0),
            'importo_maggiorato' => $this->decimal(10, 2)->null(),
            'importo_finale' => $this->decimal(10, 2)->notNull(),

            // Erogazione trimestrale
            'importo_q1' => $this->decimal(10, 2)->defaultValue(0),
            'importo_q2' => $this->decimal(10, 2)->defaultValue(0),
            'importo_q3' => $this->decimal(10, 2)->defaultValue(0),
            'importo_q4' => $this->decimal(10, 2)->defaultValue(0),
            'totale_erogato' => $this->decimal(10, 2)->defaultValue(0),

            // Requisiti di merito
            'percentuale_frequenza' => $this->decimal(5, 2)->defaultValue(0),
            'verifiche_superate' => $this->tinyInteger(1)->defaultValue(0),

            // IBAN per pagamento
            'iban_studente' => $this->string(34)->null(),

            'status' => "ENUM('richiesta','approvata','in_erogazione','completata','revocata','sospesa') NOT NULL DEFAULT 'richiesta'",
            'motivo_revoca' => $this->text()->null(),
            'data_approvazione' => $this->date()->null(),
            'note' => $this->text()->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        // Allinea charset/collation della colonna FK con la tabella allievo
        $this->execute("ALTER TABLE {{%borsa_studio}} MODIFY allievo_codice_fiscale CHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");

        $this->addForeignKey(
            'fk_borsa_studio_allievo',
            '{{%borsa_studio}}',
            'allievo_codice_fiscale',
            '{{%allievo}}',
            'codice_fiscale',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_borsa_studio_progetto',
            '{{%borsa_studio}}',
            'progetto_id',
            '{{%progetto}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createIndex('idx_borsa_studio_deleted', '{{%borsa_studio}}', 'deleted_at');
        $this->createIndex('idx_borsa_studio_allievo', '{{%borsa_studio}}', 'allievo_codice_fiscale');
        $this->createIndex('idx_borsa_studio_progetto', '{{%borsa_studio}}', 'progetto_id');
        $this->createIndex('idx_borsa_studio_status', '{{%borsa_studio}}', 'status');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_borsa_studio_progetto', '{{%borsa_studio}}');
        $this->dropForeignKey('fk_borsa_studio_allievo', '{{%borsa_studio}}');
        $this->dropTable('{{%borsa_studio}}');
    }
}
