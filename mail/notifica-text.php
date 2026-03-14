<?php

use app\models\Configurazione;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $notifica app\models\Notifica */

$fondazione = Configurazione::get('nome_fondazione') ?: 'Albatros Manager';
$linkUrl = null;
if (!empty($notifica->link)) {
    $linkUrl = Url::to($notifica->link, true);
}
?>
<?= $fondazione ?> - Notifica
========================================

<?= $notifica->titolo ?>

<?= strip_tags($notifica->contenuto) ?>

<?php if ($linkUrl): ?>
Link: <?= $linkUrl ?>

<?php endif; ?>
---
Cordiali saluti,
<?= $fondazione ?>

Questa email e' stata generata automaticamente. Non rispondere a questa email.
