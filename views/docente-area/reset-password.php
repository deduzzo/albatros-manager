<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $token string */

$this->title = 'Reimposta Password';
?>

<div class="container container-tight py-4">
    <div class="text-center mb-4">
        <a href="<?= Url::to(['/site/index']) ?>" class="navbar-brand navbar-brand-autodark">
            <h1 class="h2"><i class="ti ti-school me-2"></i>Area Docenti</h1>
        </a>
    </div>

    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Reimposta Password</h2>
            <p class="text-secondary text-center mb-4">
                Inserisci la tua nuova password.
            </p>

            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="mb-3">
                    <label class="form-label">Nuova Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimo 6 caratteri" required minlength="6">
                </div>

                <div class="mb-3">
                    <label class="form-label">Conferma Password</label>
                    <input type="password" name="password_confirm" class="form-control" placeholder="Ripeti la password" required>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-key me-2"></i> Reimposta Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-secondary mt-3">
        <a href="<?= Url::to(['login']) ?>" tabindex="-1">Torna al login</a>
    </div>
</div>
