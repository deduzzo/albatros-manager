<?php

use yii\db\Migration;

/**
 * Aggiunge campi per gestione utenti, 2FA e changelog alla tabella utente.
 */
class m260314_100000_add_user_management_fields extends Migration
{
    public function safeUp()
    {
        $this->addColumn('utente', 'force_password_change', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('status'));
        $this->addColumn('utente', 'totp_secret', $this->string(255)->null()->after('force_password_change'));
        $this->addColumn('utente', 'totp_enabled', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('totp_secret'));
        $this->addColumn('utente', 'totp_enabled_at', $this->dateTime()->null()->after('totp_enabled'));
        $this->addColumn('utente', 'last_changelog_seen', $this->string(20)->null()->after('totp_enabled_at'));
    }

    public function safeDown()
    {
        $this->dropColumn('utente', 'last_changelog_seen');
        $this->dropColumn('utente', 'totp_enabled_at');
        $this->dropColumn('utente', 'totp_enabled');
        $this->dropColumn('utente', 'totp_secret');
        $this->dropColumn('utente', 'force_password_change');
    }
}
