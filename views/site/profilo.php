<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\ProfiloForm */
/* @var $user app\models\User */

$this->title = 'Il Mio Profilo';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-profilo">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-user me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <!-- Card profilo -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <span class="avatar avatar-xl bg-blue-lt" style="font-size: 2rem;">
                                    <?= strtoupper(substr($user->username, 0, 2)) ?>
                                </span>
                            </div>
                            <h3 class="mb-1"><?= Html::encode($user->nominativo ?: ucfirst($user->username)) ?></h3>
                            <p class="text-secondary mb-3"><?= Html::encode($user->getRuoloLabel()) ?></p>
                            <div class="text-start">
                                <div class="d-flex mb-2">
                                    <span class="me-auto text-secondary"><i class="ti ti-mail me-1"></i> Email</span>
                                    <span><?= Html::encode($user->email) ?></span>
                                </div>
                                <div class="d-flex mb-2">
                                    <span class="me-auto text-secondary"><i class="ti ti-calendar me-1"></i> Registrato</span>
                                    <span><?= Yii::$app->formatter->asDate($user->created_at) ?></span>
                                </div>
                                <div class="d-flex mb-2">
                                    <span class="me-auto text-secondary"><i class="ti ti-clock me-1"></i> Ultimo accesso</span>
                                    <span><?= $user->updated_at ? Yii::$app->formatter->asRelativeTime($user->updated_at) : '-' ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-settings me-1"></i> Impostazioni', ['impostazioni'], ['class' => 'btn btn-outline-primary w-100']) ?>
                        </div>
                    </div>
                </div>

                <!-- Card Sicurezza -->
                <div class="col-lg-4 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-shield-lock me-2"></i>Sicurezza</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <span class="me-auto text-secondary">Autenticazione 2FA</span>
                                <?php if ($user->isTotpEnabled()): ?>
                                    <span class="badge bg-green text-green-fg"><i class="ti ti-shield-check me-1"></i>Attiva</span>
                                <?php else: ?>
                                    <span class="badge bg-default"><i class="ti ti-shield-off me-1"></i>Non attiva</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($user->isTotpEnabled() && $user->totp_enabled_at): ?>
                                <div class="d-flex mb-3">
                                    <span class="me-auto text-secondary">Attivata il</span>
                                    <span><?= Yii::$app->formatter->asDate($user->totp_enabled_at) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <?php if ($user->isTotpEnabled()): ?>
                                <?= Html::a('<i class="ti ti-shield-off me-1"></i> Disattiva 2FA', ['disattiva-2fa'], ['class' => 'btn btn-outline-danger w-100 mb-2']) ?>
                            <?php else: ?>
                                <?= Html::a('<i class="ti ti-shield-check me-1"></i> Attiva 2FA', ['attiva-2fa'], ['class' => 'btn btn-outline-primary w-100 mb-2']) ?>
                            <?php endif; ?>
                            <?= Html::a('<i class="ti ti-key me-1"></i> Cambia Password', ['cambia-password'], ['class' => 'btn btn-outline-secondary w-100']) ?>
                        </div>
                    </div>
                </div>

                <!-- Form modifica -->
                <div class="col-lg-8">
                    <?php $form = AppActiveForm::begin([
                        'id' => 'profilo-form',
                        'options' => ['class' => 'card'],
                    ]); ?>

                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-edit me-2"></i>Modifica Profilo</h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'nominativo')->textInput([
                                    'maxlength' => true,
                                    'autofocus' => true,
                                    'placeholder' => 'Es: Mario Rossi',
                                ])->label('Nome e Cognome') ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'username')->textInput([
                                    'maxlength' => true,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'email')->textInput([
                                    'maxlength' => true,
                                    'type' => 'email',
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ruolo</label>
                                    <input type="text" class="form-control" value="<?= Html::encode($user->getRuoloLabel()) ?>" disabled>
                                    <small class="form-hint">Il ruolo può essere modificato solo da un amministratore.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ID Utente</label>
                                    <input type="text" class="form-control" value="<?= $user->id ?>" disabled>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva Modifiche', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php AppActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
