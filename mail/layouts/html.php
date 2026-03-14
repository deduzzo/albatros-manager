<?php

use yii\helpers\Html;
use app\models\Configurazione;

/** @var \yii\web\View $this view component instance */
/** @var \yii\mail\MessageInterface $message the message being composed */
/** @var string $content main view render result */

$fondazione = Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
$anno = date('Y');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body style="margin: 0; padding: 0; background-color: #eef2f7; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased;">
    <?php $this->beginBody() ?>

    <!-- Wrapper table -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eef2f7;">
        <tr>
            <td align="center" style="padding: 30px 15px;">

                <!-- Main container -->
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1a3a5c; background: linear-gradient(135deg, #1a3a5c 0%, #206bc4 100%); padding: 28px 30px; text-align: center;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <span style="display: inline-block; font-size: 28px; font-weight: 700; color: #ffffff; letter-spacing: 1px; font-family: Arial, Helvetica, sans-serif;">&#9670; <?= Html::encode($fondazione) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 6px;">
                                        <span style="font-size: 13px; color: rgba(255,255,255,0.75); font-family: Arial, Helvetica, sans-serif; letter-spacing: 0.5px;">Gestione Docenti FSE / PNRR</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px; font-size: 14px; color: #333333; line-height: 1.6;">
                            <?= $content ?>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f4f6f8; border-top: 1px solid #e0e4e8; padding: 20px 30px; text-align: center;">
                            <p style="margin: 0 0 5px; font-size: 12px; color: #8899aa; font-family: Arial, Helvetica, sans-serif;">
                                <?= Html::encode($fondazione) ?> &mdash; Gestione Docenti FSE/PNRR
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #aab5c2; font-family: Arial, Helvetica, sans-serif;">
                                &copy; <?= $anno ?> &mdash; Questa email e' stata generata automaticamente. Non rispondere a questo messaggio.
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- /Main container -->

            </td>
        </tr>
    </table>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
