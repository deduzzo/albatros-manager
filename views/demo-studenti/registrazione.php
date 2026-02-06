<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Registrazione';
?>

<div class="page-body">
    <div class="container py-4" style="max-width: 500px;">
        <div class="card card-md">
            <div class="card-body">
                <h2 class="h2 text-center mb-4">Crea il tuo account</h2>

                <form method="post">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                    <div class="mb-3">
                        <label class="form-label required">Codice Fiscale</label>
                        <input type="text" class="form-control" name="codice_fiscale" placeholder="Es: RSSMRA80A01F205X" maxlength="16" style="text-transform: uppercase;" required>
                        <small class="form-hint">Sarà il tuo username per accedere</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Cognome</label>
                            <input type="text" class="form-control" name="cognome" placeholder="Rossi" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Nome</label>
                            <input type="text" class="form-control" name="nome" placeholder="Mario" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="mario.rossi@email.it" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Minimo 6 caratteri" required minlength="6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Conferma Password</label>
                        <input type="password" class="form-control" name="password_confirm" placeholder="Ripeti la password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="privacy" required>
                            <span class="form-check-label">
                                Ho letto e accetto la <a href="#" target="_blank">Privacy Policy</a>
                            </span>
                        </label>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-user-plus me-2"></i> Registrati
                        </button>
                    </div>
                </form>
            </div>

            <div class="hr-text">oppure</div>

            <div class="card-body">
                <div class="text-center">
                    Hai già un account?
                    <a href="<?= Url::to(['login']) ?>">Accedi</a>
                </div>
            </div>
        </div>
    </div>
</div>
