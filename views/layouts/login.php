<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\assets\TablerAsset;
use yii\helpers\Html;

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
<body class="bg-white">
<?php $this->beginBody() ?>

<div class="page">
    <?= $content ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
