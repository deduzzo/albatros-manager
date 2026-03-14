<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */

$contratto = $model->contratto;
$assegnazione = $contratto ? $contratto->assegnazione : null;
$modulo = $assegnazione ? $assegnazione->modulo : null;
$fondazione = \app\models\Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
$cfEnte = \app\models\Configurazione::get('codice_fiscale_ente') ?: '';

$this->title = 'Richiesta Emissione Fattura';
?>

<h2 style="margin: 0 0 15px; font-size: 20px; color: #1a3a5c;">Richiesta Emissione Fattura</h2>

<p>Gentile <?= Html::encode($model->destinatario_nome) ?>,</p>

<p>si prega di emettere fattura per la seguente prestazione professionale:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; margin: 15px 0;">
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold; width: 40%;">Ente committente</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= Html::encode($fondazione) ?></td>
    </tr>
    <?php if ($cfEnte): ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">C.F. Ente</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= Html::encode($cfEnte) ?></td>
    </tr>
    <?php endif; ?>
    <?php if ($modulo): ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">Modulo</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?></td>
    </tr>
    <?php endif; ?>
    <?php if ($contratto): ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">N. Contratto</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= Html::encode($contratto->numero_contratto ?: '#' . $contratto->id) ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">Importo</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd; font-size: 20px; font-weight: bold; color: #206bc4;">&euro; <?= number_format($model->importo, 2, ',', '.') ?></td>
    </tr>
    <?php if ($model->periodo_riferimento): ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">Periodo di riferimento</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= Html::encode($model->periodo_riferimento) ?></td>
    </tr>
    <?php endif; ?>
</table>

<?php if ($model->data_scadenza): ?>
<p style="color: #e53e3e; font-weight: bold;">Scadenza presentazione fattura: <?= Yii::$app->formatter->asDate($model->data_scadenza, 'long') ?></p>
<?php endif; ?>

<?php if ($model->corpo_email): ?>
<hr style="border: none; border-top: 1px solid #e0e0e0; margin: 15px 0;">
<p><?= nl2br(Html::encode($model->corpo_email)) ?></p>
<?php endif; ?>

<p style="margin: 20px 0 0; color: #666666;">
    Cordiali saluti,<br>
    <?= Html::encode($fondazione) ?>
</p>
