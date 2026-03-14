<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Configurazione;

/* @var $this yii\web\View */
/* @var $notifica app\models\Notifica */

$fondazione = Configurazione::get('nome_fondazione') ?: 'Albatros Manager';
$linkUrl = null;
if (!empty($notifica->link)) {
    $linkUrl = Url::to($notifica->link, true);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($notifica->titolo) ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #206bc4; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #e0e0e0; }
        .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #999; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #206bc4; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin: 0;"><?= Html::encode($fondazione) ?></h2>
        <p style="margin: 5px 0 0; opacity: 0.9;">Notifica</p>
    </div>

    <div class="content">
        <h3 style="margin-top: 0;"><?= Html::encode($notifica->titolo) ?></h3>

        <div style="margin: 15px 0;">
            <?= $notifica->contenuto ?>
        </div>

        <?php if ($linkUrl): ?>
            <p style="text-align: center;">
                <a href="<?= Html::encode($linkUrl) ?>" class="btn">Vai al dettaglio</a>
            </p>
        <?php endif; ?>

        <p style="margin-bottom: 0; color: #666;">
            Cordiali saluti,<br>
            <?= Html::encode($fondazione) ?>
        </p>
    </div>

    <div class="footer">
        Questa email e' stata generata automaticamente dal sistema <?= Html::encode($fondazione) ?>.<br>
        Non rispondere a questa email.
    </div>
</div>
</body>
</html>
