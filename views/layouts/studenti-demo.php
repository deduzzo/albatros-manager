<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\assets\TablerAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\Alert;

TablerAsset::register($this);

$isStudenteLogged = !Yii::$app->studenteUser->isGuest;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="light">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
    <!-- Header Area Studenti Pubblica -->
    <header class="navbar navbar-expand-md d-print-none bg-purple text-purple-fg" data-bs-theme="dark">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-studenti">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="<?= Url::to(['/studenti/index']) ?>" class="text-decoration-none text-white">
                    <i class="ti ti-school me-2"></i>
                    <span>Area Studenti</span>
                </a>
            </h1>
            <div class="navbar-nav flex-row order-md-last">
                <?php if ($isStudenteLogged): ?>
                    <a href="<?= Url::to(['/studente-area/index']) ?>" class="btn btn-light">
                        <i class="ti ti-layout-dashboard me-1"></i> La Mia Area
                    </a>
                <?php else: ?>
                    <a href="<?= Url::to(['/area-studenti/login']) ?>" class="btn btn-light">
                        <i class="ti ti-login me-1"></i> Accedi
                    </a>
                <?php endif; ?>
            </div>
            <div class="collapse navbar-collapse" id="navbar-studenti">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/studenti/index']) ?>">
                            <span class="nav-link-icon"><i class="ti ti-home"></i></span>
                            <span class="nav-link-title">Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/studenti/index']) ?>#bandi">
                            <span class="nav-link-icon"><i class="ti ti-speakerphone"></i></span>
                            <span class="nav-link-title">Bandi Aperti</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <!-- Flash Messages -->
        <div class="container-xl mt-3">
            <?= Alert::widget() ?>
        </div>

        <!-- Page Content -->
        <?= $content ?>

        <!-- Footer -->
        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center">
                    <div class="col-12">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item text-muted">
                                &copy; <?= date('Y') ?> Fondazione ITS Albatros Academy
                            </li>
                            <li class="list-inline-item">
                                <a href="<?= Url::to(['/site/login']) ?>" class="link-secondary">
                                    Accedi
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
