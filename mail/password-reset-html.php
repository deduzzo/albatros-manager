<?php

use yii\helpers\Html;
use app\models\Configurazione;

/* @var $this yii\web\View */
/* @var $nome string Nome completo dell'utente */
/* @var $resetLink string Link completo per il reset */
/* @var $expiry string Tempo di scadenza (default: "24 ore") */

$fondazione = Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
$expiry = $expiry ?? '24 ore';

$this->title = 'Reimposta la tua password';
?>

<h2 style="margin: 0 0 15px; font-size: 20px; color: #1a3a5c;">Reimposta la tua password</h2>

<p>Gentile <strong><?= Html::encode($nome) ?></strong>,</p>

<p>abbiamo ricevuto una richiesta per reimpostare la password del tuo account sulla piattaforma <?= Html::encode($fondazione) ?>.</p>

<p>Per procedere con il reset della password, clicca sul pulsante qui sotto:</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?= Html::encode($resetLink) ?>" style="display: inline-block; padding: 14px 32px; background-color: #206bc4; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">Reimposta Password</a>
</p>

<p style="font-size: 13px; color: #666666;">
    Se il pulsante non funziona, copia e incolla il seguente link nel tuo browser:<br>
    <a href="<?= Html::encode($resetLink) ?>" style="color: #206bc4; word-break: break-all;"><?= Html::encode($resetLink) ?></a>
</p>

<div style="margin: 25px 0; padding: 15px; background-color: #fff8e1; border-left: 4px solid #f0a000; border-radius: 4px;">
    <p style="margin: 0 0 5px; font-weight: bold; color: #856404; font-size: 13px;">Informazioni importanti:</p>
    <ul style="margin: 0; padding-left: 18px; color: #856404; font-size: 13px;">
        <li>Questo link e' valido per <strong><?= Html::encode($expiry) ?></strong>.</li>
        <li>Se non hai richiesto tu il reset della password, ignora questa email.</li>
        <li>Non condividere questo link con nessuno.</li>
    </ul>
</div>

<p style="margin: 20px 0 0; color: #666666;">
    Cordiali saluti,<br>
    <?= Html::encode($fondazione) ?>
</p>
