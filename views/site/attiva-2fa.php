<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $secret string|null */
/* @var $qrCodeUrl string|null */
/* @var $error string|null */

$this->title = 'Attiva Autenticazione a Due Fattori';
$this->params['breadcrumbs'][] = ['label' => 'Profilo', 'url' => ['profilo']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-shield-lock me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-shield-check me-2"></i>Configura 2FA</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="ti ti-alert-circle me-2"></i><?= Html::encode($error) ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h4>1. Scansiona il QR Code</h4>
                            <p class="text-secondary">
                                Apri l'app <strong>Google Authenticator</strong> (o un'altra app TOTP compatibile)
                                e scansiona il codice QR qui sotto.
                            </p>
                        </div>

                        <?php if ($qrCodeUrl): ?>
                            <div class="text-center mb-4">
                                <img src="<?= $qrCodeUrl ?>" alt="QR Code 2FA" class="img-fluid" style="max-width: 250px;">
                            </div>
                        <?php endif; ?>

                        <?php if ($secret): ?>
                            <div class="mb-4">
                                <h4>2. Oppure inserisci il codice manualmente</h4>
                                <div class="input-group">
                                    <input type="text" class="form-control font-monospace" value="<?= Html::encode($secret) ?>" readonly id="totp-secret">
                                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('totp-secret').value); this.innerHTML='<i class=\'ti ti-check\'></i> Copiato';">
                                        <i class="ti ti-copy"></i> Copia
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <h4>3. Inserisci il codice di verifica</h4>
                            <p class="text-secondary">
                                Inserisci il codice a 6 cifre mostrato dall'app per confermare l'attivazione.
                            </p>
                        </div>

                        <form method="post">
                            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                            <div class="mb-3">
                                <label class="form-label">Codice di verifica</label>
                                <input type="text" name="totp_code" class="form-control form-control-lg text-center font-monospace"
                                       maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code"
                                       placeholder="000000" autofocus required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Annulla', ['profilo'], ['class' => 'btn btn-ghost-secondary']) ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-shield-check me-1"></i> Verifica e Attiva
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
