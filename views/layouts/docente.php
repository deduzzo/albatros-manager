<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\assets\TablerAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\components\widgets\Alert;

TablerAsset::register($this);

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

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
