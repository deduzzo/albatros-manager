<?php

use yii\db\Migration;

class m240131_000018_create_pagamento_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%pagamento}}', [
            'id' => $this->primaryKey(),
            'contratto_id' => $this->integer()->notNull(),
            'importo_lordo' => $this->decimal(10, 2)->notNull(),
            'ritenute' => $this->decimal(10, 2),
            'importo_netto' => $this->decimal(10, 2)->notNull(),
            'data_pagamento' => $this->date(),
            'data_scadenza' => $this->date(),
            'tipo' => "ENUM('acconto', 'saldo', 'rata', 'rimborso') NOT NULL DEFAULT 'saldo'",
            'riferimento_fattura' => $this->string(100),
            'numero_mandato' => $this->string(50),
            'cro_bonifico' => $this->string(50),
            'status' => "ENUM('programmato', 'in_attesa', 'pagato', 'annullato') NOT NULL DEFAULT 'programmato'",
            'note' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_pagamento_contratto',
            '{{%pagamento}}',
            'contratto_id',
            '{{%contratto}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->createIndex('idx_pagamento_data', '{{%pagamento}}', 'data_pagamento');
        $this->createIndex('idx_pagamento_status', '{{%pagamento}}', 'status');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_pagamento_contratto', '{{%pagamento}}');
        $this->dropTable('{{%pagamento}}');
    }
}
