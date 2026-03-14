<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="<?= Url::home() ?>">
                <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-badge-small.png" alt="Albatros" height="32">
                <span class="ms-2">Albatros Manager</span>
            </a>
        </h1>
        <div class="navbar-nav flex-row order-md-last">
            <div class="d-none d-md-flex">
                <!-- Theme Toggle -->
                <a href="#" class="nav-link px-0 me-3" id="theme-toggle" title="Cambia tema">
                    <i class="ti ti-moon" id="theme-icon"></i>
                </a>
                <!-- Aiuto dropdown -->
                <div class="nav-item dropdown me-3">
                    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" title="Aiuto">
                        <i class="ti ti-help"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <h6 class="dropdown-header">Aiuto</h6>
                        <a href="<?= Url::to(['/site/guida-admin']) ?>" class="dropdown-item" target="_blank">
                            <i class="ti ti-compass me-2"></i> Guida Interattiva
                        </a>
                        <a href="<?= Url::to(['/site/manuale-admin']) ?>" class="dropdown-item" target="_blank">
                            <i class="ti ti-book me-2"></i> Manuale Amministratore
                        </a>
                        <a href="<?= Url::to(['/site/manuale-docente']) ?>" class="dropdown-item" target="_blank">
                            <i class="ti ti-school me-2"></i> Manuale Docente
                        </a>
                        <a href="<?= Url::to(['/site/manuale-studente']) ?>" class="dropdown-item" target="_blank">
                            <i class="ti ti-users me-2"></i> Manuale Studente
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= Url::to(['/site/supporto']) ?>" class="dropdown-item" target="_blank">
                            <i class="ti ti-bug me-2"></i> Segnala un Problema
                        </a>
                    </div>
                </div>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?php
                    $changelogParser = new \app\components\ChangelogParser();
                    $unreadCount = $changelogParser->countUnreadItems('admin', Yii::$app->user->identity->last_changelog_seen ?? null);
                    ?>
                    <div class="nav-item d-none d-md-flex me-3">
                        <a href="#" class="nav-link px-0" data-bs-toggle="modal" data-bs-target="#changelog-modal" id="changelog-trigger" title="Novità">
                            <i class="ti ti-sparkles"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-red text-red-fg badge-notification badge-blink changelog-badge"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="nav-item dropdown d-none d-md-flex me-3">
                    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Notifiche">
                        <i class="ti ti-bell"></i>
                        <span class="badge bg-red badge-notification badge-blink"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Notifiche</h3>
                            </div>
                            <div class="list-group list-group-flush list-group-hoverable">
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="status-dot status-dot-animated bg-green d-block"></span>
                                        </div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Sistema</a>
                                            <div class="d-block text-secondary text-truncate mt-n1">
                                                Benvenuto in Albatros Manager
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!Yii::$app->user->isGuest): ?>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Account">
                    <span class="avatar avatar-sm bg-blue-lt"><?= strtoupper(substr(Yii::$app->user->identity->username, 0, 1)) ?></span>
                    <div class="d-none d-xl-block ps-2">
                        <div><?= Html::encode(Yii::$app->user->identity->nominativo ?: ucfirst(Yii::$app->user->identity->username)) ?></div>
                        <div class="mt-1 small text-secondary"><?= Html::encode(Yii::$app->user->identity->getRuoloLabel()) ?></div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="<?= \yii\helpers\Url::to(['/site/profilo']) ?>" class="dropdown-item">
                        <i class="ti ti-user me-2"></i> Profilo
                    </a>
                    <a href="<?= \yii\helpers\Url::to(['/site/impostazioni']) ?>" class="dropdown-item">
                        <i class="ti ti-settings me-2"></i> Impostazioni
                    </a>
                    <div class="dropdown-divider"></div>
                    <?= Html::a('<i class="ti ti-logout me-2"></i> Esci', ['/site/logout'], [
                        'class' => 'dropdown-item',
                        'data-method' => 'post',
                    ]) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

