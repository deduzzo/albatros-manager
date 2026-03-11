<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */

$contratto = $model->contratto;
$assegnazione = $contratto ? $contratto->assegnazione : null;
$modulo = $assegnazione ? $assegnazione->modulo : null;
$fondazione = \app\models\Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
$cfEnte = \app\models\Configurazione::get('codice_fiscale_ente') ?: '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Richiesta Emissione Fattura</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #206bc4; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #e0e0e0; }
        .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #999; border-radius: 0 0 8px 8px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table td { padding: 8px 12px; border: 1px solid #ddd; }
        .info-table td:first-child { background: #f0f0f0; font-weight: bold; width: 40%; }
        .amount { font-size: 20px; font-weight: bold; color: #206bc4; }
        .warning { color: #e53e3e; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin: 0;">Richiesta Emissione Fattura</h2>
        <p style="margin: 5px 0 0; opacity: 0.9;"><?= Html::encode($fondazione) ?></p>
    </div>

    <div class="content">
        <p>Gentile <?= Html::encode($model->destinatario_nome) ?>,</p>

        <p>si prega di emettere fattura per la seguente prestazione professionale:</p>

        <table class="info-table">
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
                <td>Importo</td>
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
        <p class="warning">Scadenza presentazione fattura: <?= Yii::$app->formatter->asDate($model->data_scadenza, 'long') ?></p>
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
