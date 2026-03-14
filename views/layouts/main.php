<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\assets\TablerAsset;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
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
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - Albatros Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php $this->head() ?>
    <style>
        :root {
            --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
        /* Sorting arrows for GridView */
        .grid-view th a {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }
        .grid-view th a::after {
            font-family: 'tabler-icons';
            margin-left: 0.25rem;
            opacity: 0.3;
            content: '\eb6a'; /* ti-arrows-sort */
        }
        .grid-view th a:hover::after {
            opacity: 0.6;
        }
        .grid-view th a.asc::after {
            content: '\ea62'; /* ti-chevron-up */
            opacity: 1;
            color: var(--tblr-primary);
        }
        .grid-view th a.desc::after {
            content: '\ea5f'; /* ti-chevron-down */
            opacity: 1;
            color: var(--tblr-primary);
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="page">
    <!-- Navbar -->
    <?= $this->render('header.php'); ?>

    <!-- Navbar menu -->
    <?= $this->render('menu.php'); ?>

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
                                    'label' => 'Home',
                                    'url' => Yii::$app->homeUrl,
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
        <?= $this->render('footer.php'); ?>
    </div>
</div>

<script>
// Theme Toggle
(function() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const htmlElement = document.documentElement;

    // Get saved theme or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    htmlElement.setAttribute('data-bs-theme', savedTheme);
    updateIcon(savedTheme);

    themeToggle.addEventListener('click', function(e) {
        e.preventDefault();
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        htmlElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateIcon(newTheme);
    });

    function updateIcon(theme) {
        if (theme === 'dark') {
            themeIcon.className = 'ti ti-sun';
        } else {
            themeIcon.className = 'ti ti-moon';
        }
    }
})();
</script>

<?php if (!Yii::$app->user->isGuest): ?>
<!-- Modal Changelog (admin) - deve essere figlio diretto di body per z-index -->
<div class="modal modal-blur fade" id="changelog-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" id="changelog-modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-blue" role="status"></div>
                <p class="mt-2 text-secondary">Caricamento novità...</p>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var changelogModal = document.getElementById('changelog-modal');
    if (changelogModal) {
        changelogModal.addEventListener('show.bs.modal', function() {
            fetch('<?= \yii\helpers\Url::to(['/site/changelog-modal']) ?>')
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    document.getElementById('changelog-modal-content').innerHTML = html;
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
