<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Password Dimenticata';
?>

<div class="container container-tight py-4">
    <div class="text-center mb-4">
        <a href="<?= Url::to(['/site/index']) ?>" class="navbar-brand navbar-brand-autodark">
            <h1 class="h2"><i class="ti ti-school me-2"></i>Area Docenti</h1>
        </a>
    </div>

    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Password dimenticata?</h2>
            <p class="text-secondary text-center mb-4">
                Inserisci il tuo codice fiscale e ti invieremo le istruzioni per reimpostare la password.
            </p>

            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="mb-3">
                    <label class="form-label">Codice Fiscale</label>
                    <input type="text" name="codice_fiscale" class="form-control" placeholder="Es: RSSMRA80A01F205X" maxlength="16" style="text-transform: uppercase;" required>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-mail me-2"></i> Invia istruzioni
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-secondary mt-3">
        Ricordi la password?
        <a href="<?= Url::to(['login']) ?>" tabindex="-1">Accedi</a>
    </div>
</div>
