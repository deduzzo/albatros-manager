<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\assets\TablerAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\Alert;

TablerAsset::register($this);

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
        .demo-badge {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 9999;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<!-- Badge DEMO -->
<div class="demo-badge">
    <span class="badge bg-orange text-orange-fg fs-6">
        <i class="ti ti-flask me-1"></i> DEMO - Nessun dato salvato
    </span>
</div>

<div class="page">
    <!-- Header Area Studenti -->
    <header class="navbar navbar-expand-md d-print-none" data-bs-theme="dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-studenti">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="<?= Url::to(['/demo-studenti/index']) ?>" class="text-decoration-none text-white">
                    <i class="ti ti-school me-2"></i>
                    <span>Area Studenti</span>
                </a>
            </h1>
            <div class="navbar-nav flex-row order-md-last">
                <a href="<?= Url::to(['/demo-studenti/login']) ?>" class="btn btn-light">
                    <i class="ti ti-login me-1"></i> Accedi
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-studenti">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/demo-studenti/index']) ?>">
                            <span class="nav-link-icon"><i class="ti ti-home"></i></span>
                            <span class="nav-link-title">Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/demo-studenti/corsi']) ?>">
                            <span class="nav-link-icon"><i class="ti ti-book"></i></span>
                            <span class="nav-link-title">Corsi Disponibili</span>
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
                                &copy; <?= date('Y') ?> Albatros Manager - Demo Area Studenti
                            </li>
                            <li class="list-inline-item">
                                <a href="<?= Url::to(['/site/index']) ?>" class="link-secondary">
                                    <i class="ti ti-arrow-left me-1"></i> Torna all'Amministrazione
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
