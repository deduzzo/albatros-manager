<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Accesso';
?>

<div class="page-body">
    <div class="container-tight py-4">
        <div class="card card-md">
            <div class="card-body">
                <h2 class="h2 text-center mb-4">Accedi all'Area Studenti</h2>

                <form method="post">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                    <div class="mb-3">
                        <label class="form-label">Codice Fiscale</label>
                        <input type="text" class="form-control" name="codice_fiscale" placeholder="Es: RSSMRA80A01F205X" maxlength="16" style="text-transform: uppercase;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Password
                            <span class="form-label-description">
                                <a href="#">Password dimenticata?</a>
                            </span>
                        </label>
                        <input type="password" class="form-control" name="password" placeholder="La tua password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="remember">
                            <span class="form-check-label">Ricordami su questo dispositivo</span>
                        </label>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-login me-2"></i> Accedi
                        </button>
                    </div>
                </form>
            </div>

            <div class="hr-text">oppure</div>

            <div class="card-body">
                <div class="text-center">
                    Non hai un account?
                    <a href="<?= Url::to(['registrazione']) ?>">Registrati</a>
                </div>
            </div>
        </div>
    </div>
</div>
