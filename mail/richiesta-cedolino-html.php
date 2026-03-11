<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */

$contratto = $model->contratto;
$assegnazione = $contratto ? $contratto->assegnazione : null;
$docente = $assegnazione ? $assegnazione->docente : null;
$modulo = $assegnazione ? $assegnazione->modulo : null;
$fondazione = \app\models\Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
$cfEnte = \app\models\Configurazione::get('codice_fiscale_ente') ?: '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Richiesta Elaborazione Cedolino</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #8e44ad; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #e0e0e0; }
        .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #999; border-radius: 0 0 8px 8px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table td { padding: 8px 12px; border: 1px solid #ddd; }
        .info-table td:first-child { background: #f0f0f0; font-weight: bold; width: 40%; }
        .amount { font-size: 20px; font-weight: bold; color: #8e44ad; }
        .warning { color: #e53e3e; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin: 0;">Richiesta Elaborazione Cedolino</h2>
        <p style="margin: 5px 0 0; opacity: 0.9;"><?= Html::encode($fondazione) ?></p>
    </div>

    <div class="content">
        <p>Spett.le Consulente,</p>

        <p>si prega di elaborare il cedolino per il seguente collaboratore Co.Co.Co:</p>

        <table class="info-table">
            <?php if ($docente): ?>
            <tr>
                <td>Collaboratore</td>
                <td><?= Html::encode($docente->getNomeCompleto()) ?></td>
            </tr>
            <tr>
                <td>Codice Fiscale</td>
                <td><?= Html::encode($docente->codice_fiscale) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Ente committente</td>
                <td><?= Html::encode($fondazione) ?></td>
            </tr>
            <?php if ($cfEnte): ?>
            <tr>
                <td>C.F. Ente</td>
                <td><?= Html::encode($cfEnte) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($modulo): ?>
            <tr>
                <td>Modulo</td>
                <td><?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($contratto): ?>
            <tr>
                <td>N. Contratto</td>
                <td><?= Html::encode($contratto->numero_contratto ?: '#' . $contratto->id) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Importo Lordo</td>
                <td class="amount">&euro; <?= number_format($model->importo, 2, ',', '.') ?></td>
            </tr>
            <?php if ($model->periodo_riferimento): ?>
            <tr>
                <td>Periodo di riferimento</td>
                <td><?= Html::encode($model->periodo_riferimento) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <?php if ($model->data_scadenza): ?>
        <p class="warning">Scadenza elaborazione: <?= Yii::$app->formatter->asDate($model->data_scadenza, 'long') ?></p>
        <?php endif; ?>

        <?php if ($model->corpo_email): ?>
        <hr>
        <p><?= nl2br(Html::encode($model->corpo_email)) ?></p>
        <?php endif; ?>

        <p>Cordiali saluti,<br><?= Html::encode($fondazione) ?></p>
    </div>

    <div class="footer">
        Questa email e' stata generata automaticamente dal sistema <?= Html::encode($fondazione) ?>.
    </div>
</div>
</body>
</html>
