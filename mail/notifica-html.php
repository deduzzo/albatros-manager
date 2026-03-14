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

$this->title = Html::encode($notifica->titolo);
?>

<h2 style="margin: 0 0 15px; font-size: 20px; color: #1a3a5c;"><?= Html::encode($notifica->titolo) ?></h2>

<div style="margin: 15px 0;">
    <?= $notifica->contenuto ?>
</div>

<?php if ($linkUrl): ?>
    <p style="text-align: center; margin: 25px 0;">
        <a href="<?= Html::encode($linkUrl) ?>" style="display: inline-block; padding: 12px 28px; background-color: #206bc4; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;">Vai al dettaglio</a>
    </p>
<?php endif; ?>

<p style="margin: 20px 0 0; color: #666666;">
    Cordiali saluti,<br>
    <?= Html::encode($fondazione) ?>
</p>
