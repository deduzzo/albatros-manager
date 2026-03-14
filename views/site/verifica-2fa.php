<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $error string|null */
/* @var $locked bool */

$this->title = 'Verifica in Due Passaggi';
?>

<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <div class="mb-3">
                <span class="avatar avatar-xl bg-blue-lt">
                    <i class="ti ti-shield-lock" style="font-size: 2.5rem;"></i>
                </span>
            </div>
            <h2><?= Html::encode($this->title) ?></h2>
            <p class="text-secondary">
                Inserisci il codice generato dalla tua app di autenticazione.
            </p>
        </div>

        <div class="card card-md">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-circle me-2"></i><?= Html::encode($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($locked): ?>
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="ti ti-lock text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="text-danger">Account temporaneamente bloccato</h3>
                        <p class="text-secondary">
                            Hai superato il numero massimo di tentativi.
                            Riprova tra 5 minuti.
                        </p>
                    </div>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                        <div class="mb-3">
                            <label class="form-label">Codice di verifica</label>
                            <input type="text" name="totp_code" class="form-control form-control-lg text-center font-monospace"
                                   maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code"
                                   placeholder="000000" autofocus required>
                            <div class="form-hint">Inserisci il codice a 6 cifre dalla tua app</div>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-shield-check me-1"></i> Verifica
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center text-secondary mt-3">
            <?= Html::a('<i class="ti ti-logout me-1"></i> Annulla e torna al login', ['logout'], [
                'data-method' => 'post',
                'class' => 'text-secondary',
            ]) ?>
        </div>
    </div>
</div>
