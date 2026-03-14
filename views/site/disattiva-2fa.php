<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Disattiva Autenticazione a Due Fattori';
$this->params['breadcrumbs'][] = ['label' => 'Profilo', 'url' => ['profilo']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-shield-off me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-alert-triangle me-2 text-orange"></i>Disattiva 2FA</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <div class="d-flex">
                                <div>
                                    <i class="ti ti-alert-triangle icon me-2"></i>
                                </div>
                                <div>
                                    <h4 class="alert-title">Attenzione</h4>
                                    <p class="mb-0">
                                        Stai per disattivare l'autenticazione a due fattori.
                                        Il tuo account sara meno sicuro senza questa protezione aggiuntiva.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p class="text-secondary">
                            Per confermare la disattivazione, inserisci il codice a 6 cifre
                            dalla tua app di autenticazione.
                        </p>

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
                                <button type="submit" class="btn btn-danger">
                                    <i class="ti ti-shield-off me-1"></i> Disattiva 2FA
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
