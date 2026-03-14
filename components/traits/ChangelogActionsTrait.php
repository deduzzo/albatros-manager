<?php

namespace app\components\traits;

use Yii;
use app\components\ChangelogParser;

/**
 * Trait condiviso per le actions changelog nei controller.
 *
 * Il controller che usa questo trait deve implementare:
 * - getChangelogUserType(): string — 'admin', 'docente', 'studente'
 * - getChangelogIdentity(): model auth corrente (con campo last_changelog_seen)
 */
trait ChangelogActionsTrait
{
    public function actionChangelog()
    {
        $parser = new ChangelogParser();
        $type = $this->getChangelogUserType();
        $versions = $parser->getForUserType($type);

        // Segna come letto
        $this->markChangelogSeen($parser);

        return $this->render('@app/views/site/changelog', [
            'versions' => $versions,
        ]);
    }

    public function actionChangelogModal()
    {
        $parser = new ChangelogParser();
        $type = $this->getChangelogUserType();
        $identity = $this->getChangelogIdentity();
        $lastSeen = $identity ? $identity->last_changelog_seen : null;

        // Filtra solo versioni non lette
        $versions = $parser->getForUserType($type);
        $unreadVersions = [];
        foreach ($versions as $version) {
            if ($lastSeen !== null && version_compare($version['version'], $lastSeen, '<=')) {
                break;
            }
            $unreadVersions[] = $version;
        }

        // Segna come letto
        $this->markChangelogSeen($parser);

        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        return $this->renderPartial('@app/views/site/_changelog-modal-content', [
            'versions' => !empty($unreadVersions) ? $unreadVersions : $versions,
            'isUnread' => !empty($unreadVersions),
        ]);
    }

    /**
     * POST action per segnare il changelog come letto (usato via AJAX dal badge).
     */
    public function actionSegnaChangelogLetto()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $parser = new ChangelogParser();
        $this->markChangelogSeen($parser);
        return ['success' => true];
    }

    private function markChangelogSeen(ChangelogParser $parser): void
    {
        $identity = $this->getChangelogIdentity();
        if ($identity === null) {
            return;
        }
        $latestVersion = $parser->getLatestVersion();
        if ($latestVersion !== null && $identity->last_changelog_seen !== $latestVersion) {
            $identity->last_changelog_seen = $latestVersion;
            $identity->save(false, ['last_changelog_seen']);
        }
    }
}
