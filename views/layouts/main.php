<?php

/* @var $this \yii\web\View */
/* @var $content string */

use synamen\yii2tabler\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,500,500i,600,600i,700,700i&amp;subset=latin-ext">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - Albatros Manager</title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="page">
    <div class="page-main">
        <?= $this->render('header.php'); ?>
        <?= $this->render('menu.php'); ?>
        <?= $content ?>
        <?= $this->render('footer.php'); ?>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
