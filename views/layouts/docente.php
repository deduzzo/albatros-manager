<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\assets\TablerAsset;
use app\components\assets\TomSelectAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\components\widgets\Alert;

TablerAsset::register($this);
TomSelectAsset::register($this);

$docenteUser = Yii::$app->docenteUser;
$docente = ($docenteUser->isGuest || !$docenteUser->identity) ? null : $docenteUser->identity->docente;
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
    <title><?= Html::encode($this->title) ?> - Area Docenti | Albatros</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php $this->head() ?>
    <style>
        :root {
            --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
        /* Validazione form Yii2 con tema Tabler (Bootstrap 5) */
        .is-invalid ~ .invalid-feedback,
        .is-invalid ~ .invalid-tooltip { display: block; }
        .form-select.is-invalid ~ .invalid-feedback { display: block; }
        .has-error .form-control,
        .has-error .form-select { border-color: #d63939; }
        .has-error .help-block { color: #d63939; display: block; font-size: 0.875rem; margin-top: 0.25rem; }
        .field-has-error .form-control,
        .field-has-error .form-select { border-color: #d63939; }
        .field-has-error .invalid-feedback,
        .field-has-error .help-block { color: #d63939; display: block; }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="page">
    <!-- Header Area Docenti -->
    <header class="navbar navbar-expand-md d-print-none" data-bs-theme="dark">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-docente">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="<?= Url::to(['/docente-area/index']) ?>" class="text-decoration-none">
                    <i class="ti ti-school me-2"></i>
                    <span>Area Docenti</span>
                </a>
            </h1>
            <div class="navbar-nav flex-row order-md-last">
                <?php if (!$docenteUser->isGuest): ?>
                    <!-- Novità -->
                    <?php
                    $changelogParser = new \app\components\ChangelogParser();
                    $unreadCount = $changelogParser->countUnreadItems('docente', $docenteUser->identity->last_changelog_seen ?? null);
                    ?>
                    <div class="nav-item d-none d-md-flex me-3">
                        <a href="#" class="nav-link px-0" data-bs-toggle="modal" data-bs-target="#changelog-modal-docente" id="changelog-trigger-docente" title="Novità">
                            <i class="ti ti-sparkles"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-red text-red-fg badge-notification badge-blink changelog-badge"><?= $unreadCount ?></span>
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
                            <a href="<?= Url::to(['/docente-area/guida']) ?>" class="dropdown-item">
                                <i class="ti ti-compass me-2"></i> Guida Interattiva
                            </a>
                            <a href="<?= Url::to(['/site/manuale-docente']) ?>" class="dropdown-item" target="_blank">
                                <i class="ti ti-book me-2"></i> Manuale Utente
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= Url::to(['/site/supporto']) ?>" class="dropdown-item" target="_blank">
                                <i class="ti ti-bug me-2"></i> Segnala un Problema
                            </a>
                        </div>
                    </div>
                    <!-- Notifiche dropdown -->
                    <?php
                        $notificheCount = \app\models\Notifica::countNonLette('docente', $docenteUser->identity->docente_codice_fiscale);
                        $ultimeNotifiche = \app\models\Notifica::getUltimeNonLette('docente', $docenteUser->identity->docente_codice_fiscale, 5);
                    ?>
                    <div class="nav-item dropdown d-none d-md-flex me-3">
                        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Notifiche">
                            <i class="ti ti-bell"></i>
                            <?php if ($notificheCount > 0): ?>
                                <span class="badge bg-red text-red-fg badge-notification badge-blink"><?= $notificheCount > 99 ? '99+' : $notificheCount ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card" style="width: 380px;">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h3 class="card-title">Notifiche</h3>
                                    <?php if ($notificheCount > 0): ?>
                                        <span class="badge bg-blue-lt ms-auto"><?= $notificheCount ?> nuove</span>
                                    <?php endif; ?>
                                </div>
                                <div class="list-group list-group-flush list-group-hoverable" style="max-height: 300px; overflow-y: auto;">
                                    <?php if (empty($ultimeNotifiche)): ?>
                                        <div class="list-group-item text-center text-secondary py-3">
                                            Nessuna nuova notifica
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($ultimeNotifiche as $n): ?>
                                            <a href="<?= Url::to(['/docente-area/notifica-view', 'id' => $n->id]) ?>" class="list-group-item list-group-item-action">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <span class="status-dot status-dot-animated bg-blue d-block"></span>
                                                    </div>
                                                    <div class="col text-truncate">
                                                        <div class="text-body d-block fw-bold"><?= Html::encode(mb_substr($n->titolo, 0, 50)) ?></div>
                                                        <div class="d-block text-secondary text-truncate mt-n1">
                                                            <?= Yii::$app->formatter->asRelativeTime($n->created_at) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="<?= Url::to(['/docente-area/notifiche']) ?>" class="text-decoration-none">
                                        Vedi tutte le notifiche
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm bg-blue-lt">
                                <i class="ti ti-user"></i>
                            </span>
                            <div class="d-none d-xl-block ps-2">
                                <div><?= Html::encode($docente ? $docente->cognome . ' ' . $docente->nome : 'Docente') ?></div>
                                <div class="mt-1 small text-secondary"><?= Html::encode($docente ? $docente->email : '') ?></div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="<?= Url::to(['/docente-area/profilo']) ?>" class="dropdown-item">
                                <i class="ti ti-user me-2"></i> Il mio profilo
                            </a>
                            <a href="<?= Url::to(['/docente-area/le-mie-iscrizioni']) ?>" class="dropdown-item">
                                <i class="ti ti-list me-2"></i> Le mie iscrizioni
                            </a>
                            <a href="<?= Url::to(['/docente-area/le-mie-assegnazioni']) ?>" class="dropdown-item">
                                <i class="ti ti-clock me-2"></i> Le mie ore
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= Url::to(['/docente-area/logout']) ?>" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-2"></i> Esci
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="collapse navbar-collapse" id="navbar-docente">
                <?php if (!$docenteUser->isGuest): ?>
                    <ul class="navbar-nav">
                        <li class="nav-item <?= $currentAction == 'index' ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/docente-area/index']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-home"></i></span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item <?= in_array($currentAction, ['bandi', 'bando', 'iscrizione']) ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/docente-area/bandi']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-file-text"></i></span>
                                <span class="nav-link-title">Bandi Aperti</span>
                            </a>
                        </li>
                        <li class="nav-item <?= $currentAction == 'le-mie-iscrizioni' ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/docente-area/le-mie-iscrizioni']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-list-check"></i></span>
                                <span class="nav-link-title">Le Mie Iscrizioni</span>
                            </a>
                        </li>
                        <li class="nav-item <?= in_array($currentAction, ['le-mie-assegnazioni', 'inserisci-ore', 'le-mie-ore', 'modifica-ore']) ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/docente-area/le-mie-assegnazioni']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-clock"></i></span>
                                <span class="nav-link-title">Le Mie Ore</span>
                            </a>
                        </li>
                        <li class="nav-item <?= in_array($currentAction, ['contratti', 'contratto-download']) ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/docente-area/contratti']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-file-certificate"></i></span>
                                <span class="nav-link-title">I Miei Contratti</span>
                            </a>
                        </li>
                        <li class="nav-item <?= $currentAction == 'documenti' ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/docente-area/documenti']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-file-invoice"></i></span>
                                <span class="nav-link-title">Documenti</span>
                            </a>
                        </li>
                        <li class="nav-item <?= in_array($currentAction, ['rimborsi', 'nuovo-rimborso', 'modifica-rimborso']) ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/docente-area/rimborsi']) ?>">
                                <span class="nav-link-icon"><i class="ti ti-receipt-refund"></i></span>
                                <span class="nav-link-title">Rimborsi Spese</span>
                            </a>
                        </li>
                        <li class="nav-item <?= $currentAction == 'profilo' ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= Url::to(['/docente-area/profilo']) ?>">
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
                                    'label' => 'Area Docenti',
                                    'url' => ['/docente-area/index'],
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

<?php if (!$docenteUser->isGuest): ?>
<!-- Modal Changelog (docente) -->
<div class="modal modal-blur fade" id="changelog-modal-docente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" id="changelog-modal-docente-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-blue" role="status"></div>
                <p class="mt-2 text-secondary">Caricamento novità...</p>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var changelogModal = document.getElementById('changelog-modal-docente');
    if (changelogModal) {
        changelogModal.addEventListener('show.bs.modal', function() {
            fetch('<?= Url::to(['/docente-area/changelog-modal']) ?>')
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    document.getElementById('changelog-modal-docente-content').innerHTML = html;
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
