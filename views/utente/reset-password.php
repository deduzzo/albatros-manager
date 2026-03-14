<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Reset Password: ' . ($model->nominativo ?: $model->username);
$this->params['breadcrumbs'][] = ['label' => 'Gestione Utenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nominativo ?: $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Reset Password';
?>

<div class="utente-reset-password">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-key me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-lock me-2"></i>
                                Reimposta password per <?= Html::encode($model->nominativo ?: $model->username) ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?= Html::beginForm(['reset-password', 'id' => $model->id], 'POST') ?>

                            <div class="mb-3">
                                <label class="form-label" for="new_password">
                                    Nuova Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="new_password" id="new_password"
                                       class="form-control" required
                                       placeholder="Inserisci la nuova password"
                                       minlength="6">
                            </div>

                            <div class="mb-3">
                                <label class="form-check">
                                    <input type="checkbox" name="force_change" value="1" class="form-check-input" checked>
                                    <span class="form-check-label">Forza cambio password al prossimo login</span>
                                </label>
                            </div>

                            <div class="d-flex justify-content-between">
                                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Annulla', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i> Reimposta Password
                                </button>
                            </div>

                            <?= Html::endForm() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
