<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */
/* @var $docente app\models\Docente */
/* @var $modulo app\models\Modulo|null */
/* @var $fondazione string */
/* @var $tipoDocumento string */

$urlAreaDocente = Url::to(['/area-docenti/login'], true);

$this->title = ucfirst(Html::encode($tipoDocumento)) . ' disponibile';
?>

<h2 style="margin: 0 0 15px; font-size: 20px; color: #1a3a5c;"><?= ucfirst(Html::encode($tipoDocumento)) ?> disponibile</h2>

<p>Gentile <?= Html::encode($docente->getNomeCompleto()) ?>,</p>

<p>le comunichiamo che il suo <strong><?= Html::encode($tipoDocumento) ?></strong><?php if ($modulo): ?> per il modulo <strong><?= Html::encode($modulo->titolo) ?></strong><?php endif; ?> e' disponibile nell'area riservata.</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; margin: 15px 0;">
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold; width: 40%;">Tipo documento</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= ucfirst(Html::encode($tipoDocumento)) ?></td>
    </tr>
    <?php if ($modulo): ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">Modulo</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">Importo</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">&euro; <?= number_format($model->importo, 2, ',', '.') ?></td>
    </tr>
    <?php if ($model->periodo_riferimento): ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">Periodo di riferimento</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= Html::encode($model->periodo_riferimento) ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td style="padding: 8px 12px; border: 1px solid #ddd; background: #f0f0f0; font-weight: bold;">Data ricezione</td>
        <td style="padding: 8px 12px; border: 1px solid #ddd;"><?= Yii::$app->formatter->asDate($model->data_ricezione, 'long') ?></td>
    </tr>
</table>

<p>Puo' accedere alla sua area riservata per consultare il documento:</p>

<p style="text-align: center; margin: 25px 0;">
    <a href="<?= $urlAreaDocente ?>" style="display: inline-block; padding: 12px 28px; background-color: #206bc4; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;">Accedi all'Area Docente</a>
</p>

<p style="margin: 20px 0 0; color: #666666;">
    Cordiali saluti,<br>
    <?= Html::encode($fondazione) ?>
</p>
