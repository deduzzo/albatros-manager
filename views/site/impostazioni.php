<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\CambioPasswordForm */
/* @var $user app\models\User */

$this->title = 'Impostazioni';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-impostazioni">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-settings me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <!-- Sidebar navigazione -->
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?= Html::a('<i class="ti ti-user me-2"></i> Profilo', ['profilo'], ['class' => 'list-group-item list-group-item-action']) ?>
                                <?= Html::a('<i class="ti ti-lock me-2"></i> Cambio Password', ['impostazioni'], ['class' => 'list-group-item list-group-item-action active']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Info account -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Info Account</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-2">
                                <span class="me-auto text-secondary">Utente</span>
                                <strong><?= Html::encode($user->username) ?></strong>
                            </div>
                            <div class="d-flex mb-2">
                                <span class="me-auto text-secondary">Ruolo</span>
                                <span class="badge bg-blue-lt"><?= Html::encode($user->getRuoloLabel()) ?></span>
                            </div>
                            <div class="d-flex mb-2">
                                <span class="me-auto text-secondary">Stato</span>
                                <span class="badge bg-green text-green-fg">Attivo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form cambio password -->
                <div class="col-lg-9">
                    <?php $form = AppActiveForm::begin([
                        'id' => 'cambio-password-form',
                        'options' => ['class' => 'card'],
                    ]); ?>

                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-lock me-2"></i>Cambio Password</h3>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <div class="d-flex">
                                <div><i class="ti ti-info-circle me-2"></i></div>
                                <div>La nuova password deve essere di almeno 6 caratteri.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'password_attuale')->passwordInput([
                                    'placeholder' => 'Inserisci la password attuale',
                                    'autofocus' => true,
                                ]) ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'nuova_password')->passwordInput([
                                    'placeholder' => 'Inserisci la nuova password',
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'conferma_password')->passwordInput([
                                    'placeholder' => 'Ripeti la nuova password',
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna al Profilo', ['profilo'], ['class' => 'btn btn-secondary me-2']) ?>
                        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Cambia Password', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php AppActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
