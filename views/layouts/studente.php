<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\assets\TablerAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\components\widgets\Alert;

TablerAsset::register($this);

$studenteUser = Yii::$app->studenteUser;
$allievo = ($studenteUser->isGuest || !$studenteUser->identity) ? null : $studenteUser->identity->allievo;
$currentAction = Yii::$app->controller->action->id;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="light">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - Area Studenti | Albatros</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php $this->head() ?>
    <style>
        :root {
            --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="page">
    <!-- Header Area Studenti -->
    <header class="navbar navbar-expand-md d-print-none" data-bs-theme="dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-studente">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="<?= Url::to(['/studente-area/index']) ?>" class="text-decoration-none text-white">
                    <i class="ti ti-school me-2"></i>
                    <span>Area Studenti</span>
                </a>
            </h1>
            <div class="navbar-nav flex-row order-md-last">
                <?php if (!$studenteUser->isGuest): ?>
                    <!-- Novità -->
                    <?php
                    $changelogParser = new \app\components\ChangelogParser();
                    $unreadCount = $changelogParser->countUnreadItems('studente', $studenteUser->identity->last_changelog_seen ?? null);
                    ?>
                    <div class="nav-item me-3">
                        <a href="#" class="nav-link px-0" data-bs-toggle="modal" data-bs-target="#changelog-modal-studente" id="changelog-trigger-studente" title="Novità">
                            <i class="ti ti-sparkles"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-green badge-notification badge-blink changelog-badge"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <!-- Aiuto dropdown -->
                    <div class="nav-item dropdown me-3">
                        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" title="Aiuto">
                            <i class="ti ti-help"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <h6 class="dropdown-header">Aiuto</h6>
                            <a href="<?= Url::to(['/site/manuale-studente']) ?>" class="dropdown-item" target="_blank">
                                <i class="ti ti-book me-2"></i> Manuale Studente
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= Url::to(['/site/supporto']) ?>" class="dropdown-item" target="_blank">
                                <i class="ti ti-bug me-2"></i> Segnala un Problema
                            </a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm bg-purple-lt">
                                <i class="ti ti-user"></i>
                            </span>
                            <div class="d-none d-xl-block ps-2">
                                <div><?= Html::encode($allievo ? $allievo->cognome . ' ' . $allievo->nome : 'Studente') ?></div>
                                <div class="mt-1 small text-secondary"><?= Html::encode($allievo ? $allievo->email : '') ?></div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="<?= Url::to(['/studente-area/profilo']) ?>" class="dropdown-item">
                                <i class="ti ti-user me-2"></i> Il mio profilo
                            </a>
                            <a href="<?= Url::to(['/studente-area/le-mie-candidature']) ?>" class="dropdown-item">
                                <i class="ti ti-list-check me-2"></i> Le mie candidature
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= Url::to(['/studente-area/logout']) ?>" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-2"></i> Esci
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="collapse navbar-collapse" id="navbar-studente">
                <?php if (!$studenteUser->isGuest): ?>
                    <ul class="navbar-nav">
                        <li class="nav-item <?= $currentAction == 'index' ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/studente-area/index']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-home"></i></span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item <?= in_array($currentAction, ['bandi', 'bando', 'candidatura']) ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/studente-area/bandi']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-file-text"></i></span>
                                <span class="nav-link-title">Bandi Aperti</span>
                            </a>
                        </li>
                        <li class="nav-item <?= in_array($currentAction, ['le-mie-candidature', 'candidatura-view']) ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/studente-area/le-mie-candidature']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-list-check"></i></span>
                                <span class="nav-link-title">Le Mie Candidature</span>
                            </a>
                        </li>
                        <li class="nav-item <?= $currentAction == 'profilo' ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/studente-area/profilo']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-user"></i></span>
                                <span class="nav-link-title">Profilo</span>
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <!-- Breadcrumb -->
        <?php if (!empty($this->params['breadcrumbs'])): ?>
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <?= Breadcrumbs::widget([
                                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                                'options' => ['class' => 'breadcrumb'],
                                'itemTemplate' => "<li class=\"breadcrumb-item\">{link}</li>\n",
                                'activeItemTemplate' => "<li class=\"breadcrumb-item active\" aria-current=\"page\">{link}</li>\n",
                                'homeLink' => [
                                    'label' => 'Area Studenti',
                                    'url' => ['/studente-area/index'],
                                ],
                            ]) ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <div class="container-xl">
            <?= Alert::widget() ?>
        </div>

        <!-- Page Content -->
        <?= $content ?>

        <!-- Footer -->
        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center flex-row-reverse">
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                &copy; <?= date('Y') ?> Albatros Manager
                            </li>
                            <li class="list-inline-item">
                                <a href="<?= Url::to(['/site/index']) ?>" class="link-secondary">
                                    Area Amministrazione
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<?php if (!$studenteUser->isGuest): ?>
<!-- Modal Changelog (studente) -->
<div class="modal modal-blur fade" id="changelog-modal-studente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" id="changelog-modal-studente-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-blue" role="status"></div>
                <p class="mt-2 text-secondary">Caricamento novità...</p>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var changelogModal = document.getElementById('changelog-modal-studente');
    if (changelogModal) {
        changelogModal.addEventListener('show.bs.modal', function() {
            fetch('<?= Url::to(['/studente-area/changelog-modal']) ?>')
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    document.getElementById('changelog-modal-studente-content').innerHTML = html;
                    var badge = document.querySelector('.changelog-badge');
                    if (badge) badge.remove();
                });
        });
    }
});
</script>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
