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
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst(Html::encode($tipoDocumento)) ?> disponibile</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #206bc4; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #e0e0e0; }
        .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #999; border-radius: 0 0 8px 8px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table td { padding: 8px 12px; border: 1px solid #ddd; }
        .info-table td:first-child { background: #f0f0f0; font-weight: bold; width: 40%; }
        .btn { display: inline-block; padding: 12px 24px; background: #206bc4; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin: 0;"><?= ucfirst(Html::encode($tipoDocumento)) ?> disponibile</h2>
        <p style="margin: 5px 0 0; opacity: 0.9;"><?= Html::encode($fondazione) ?></p>
    </div>

    <div class="content">
        <p>Gentile <?= Html::encode($docente->getNomeCompleto()) ?>,</p>

        <p>le comunichiamo che il suo <strong><?= Html::encode($tipoDocumento) ?></strong><?php if ($modulo): ?> per il modulo <strong><?= Html::encode($modulo->titolo) ?></strong><?php endif; ?> e' disponibile nell'area riservata.</p>

        <table class="info-table">
            <tr>
                <td>Tipo documento</td>
                <td><?= ucfirst(Html::encode($tipoDocumento)) ?></td>
            </tr>
            <?php if ($modulo): ?>
            <tr>
                <td>Modulo</td>
                <td><?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Importo</td>
                <td>&euro; <?= number_format($model->importo, 2, ',', '.') ?></td>
            </tr>
            <?php if ($model->periodo_riferimento): ?>
            <tr>
                <td>Periodo di riferimento</td>
                <td><?= Html::encode($model->periodo_riferimento) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Data ricezione</td>
                <td><?= Yii::$app->formatter->asDate($model->data_ricezione, 'long') ?></td>
            </tr>
        </table>

        <p>Puo' accedere alla sua area riservata per consultare il documento:</p>

        <p style="text-align: center;">
            <a href="<?= $urlAreaDocente ?>" class="btn">Accedi all'Area Docente</a>
        </p>

        <p>Cordiali saluti,<br><?= Html::encode($fondazione) ?></p>
    </div>

    <div class="footer">
        Questa email e' stata generata automaticamente dal sistema <?= Html::encode($fondazione) ?>.<br>
        Non rispondere a questa email.
    </div>
</div>
</body>
</html>
