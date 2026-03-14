<?php

use yii\db\Migration;

/**
 * Aggiunge campi per gestione 2FA e changelog alle tabelle docente_auth e allievo_auth.
 */
class m260314_100001_add_auth_management_fields extends Migration
{
    public function safeUp()
    {
        // Campi per docente_auth
        $this->addColumn('docente_auth', 'force_password_change', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('status'));
        $this->addColumn('docente_auth', 'totp_secret', $this->string(255)->null()->after('force_password_change'));
        $this->addColumn('docente_auth', 'totp_enabled', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('totp_secret'));
        $this->addColumn('docente_auth', 'totp_enabled_at', $this->dateTime()->null()->after('totp_enabled'));
        $this->addColumn('docente_auth', 'last_changelog_seen', $this->string(20)->null()->after('totp_enabled_at'));

        // Campi per allievo_auth
        $this->addColumn('allievo_auth', 'force_password_change', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('status'));
        $this->addColumn('allievo_auth', 'totp_secret', $this->string(255)->null()->after('force_password_change'));
        $this->addColumn('allievo_auth', 'totp_enabled', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('totp_secret'));
        $this->addColumn('allievo_auth', 'totp_enabled_at', $this->dateTime()->null()->after('totp_enabled'));
        $this->addColumn('allievo_auth', 'last_changelog_seen', $this->string(20)->null()->after('totp_enabled_at'));
    }

    public function safeDown()
    {
        // Rimuovi da allievo_auth
        $this->dropColumn('allievo_auth', 'last_changelog_seen');
        $this->dropColumn('allievo_auth', 'totp_enabled_at');
        $this->dropColumn('allievo_auth', 'totp_enabled');
        $this->dropColumn('allievo_auth', 'totp_secret');
        $this->dropColumn('allievo_auth', 'force_password_change');

        // Rimuovi da docente_auth
        $this->dropColumn('docente_auth', 'last_changelog_seen');
        $this->dropColumn('docente_auth', 'totp_enabled_at');
        $this->dropColumn('docente_auth', 'totp_enabled');
        $this->dropColumn('docente_auth', 'totp_secret');
        $this->dropColumn('docente_auth', 'force_password_change');
    }
}
