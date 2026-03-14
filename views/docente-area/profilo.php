<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $docente app\models\Docente */
/* @var $auth app\models\DocenteAuth */

$this->title = 'Il Mio Profilo';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-user me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Dati Anagrafici</h3>
                    </div>

                    <?php $form = AppActiveForm::begin(['id' => 'profilo-form']); ?>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Codice Fiscale</label>
                                    <input type="text" class="form-control" value="<?= Html::encode($docente->codice_fiscale) ?>" disabled>
                                    <small class="form-hint">Il codice fiscale non può essere modificato.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($docente, 'cognome')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($docente, 'nome')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($docente, 'email')->textInput(['type' => 'email']) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($docente, 'telefono')->textInput() ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($docente, 'data_nascita')->textInput(['type' => 'date']) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($docente, 'luogo_nascita')->textInput() ?>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h4 class="mb-3">Residenza</h4>

                        <div class="row">
                            <div class="col-12">
                                <?= $form->field($docente, 'indirizzo')->textInput(['placeholder' => 'Via/Piazza e numero civico']) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= $form->field($docente, 'cap')->textInput(['maxlength' => 5]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($docente, 'citta')->textInput() ?>
                            </div>
                            <div class="col-md-3">
                                <?= $form->field($docente, 'provincia')->textInput(['maxlength' => 2, 'style' => 'text-transform: uppercase;']) ?>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h4 class="mb-3">Informazioni Professionali</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($docente, 'titolo_studio')->textInput(['placeholder' => 'Es: Laurea in Informatica']) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($docente, 'partita_iva')->textInput(['maxlength' => 11]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <?= $form->field($docente, 'specializzazioni')->textarea([
                                    'rows' => 3,
                                    'placeholder' => 'Descrivi le tue specializzazioni e competenze...',
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva Modifiche', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php AppActiveForm::end(); ?>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Info Account -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Info Account</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-6">Stato:</dt>
                            <dd class="col-6">
                                <span class="badge <?= $auth->getStatusBadgeClass() ?>"><?= $auth->getStatusLabel() ?></span>
                            </dd>

                            <dt class="col-6">Email verificata:</dt>
                            <dd class="col-6">
                                <?php if ($auth->email_verified_at): ?>
                                    <span class="text-success"><i class="ti ti-check"></i> Sì</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="ti ti-x"></i> No</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-6">Registrato il:</dt>
                            <dd class="col-6"><?= Yii::$app->formatter->asDate($auth->created_at) ?></dd>

                            <?php if ($auth->last_login_at): ?>
                                <dt class="col-6">Ultimo accesso:</dt>
                                <dd class="col-6"><?= Yii::$app->formatter->asDatetime($auth->last_login_at) ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>

                <!-- Sicurezza -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-shield-lock me-2"></i>Sicurezza</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <span class="me-auto text-secondary">Autenticazione 2FA</span>
                            <?php if ($auth->isTotpEnabled()): ?>
                                <span class="badge bg-green text-green-fg"><i class="ti ti-shield-check me-1"></i>Attiva</span>
                            <?php else: ?>
                                <span class="badge bg-default"><i class="ti ti-shield-off me-1"></i>Non attiva</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($auth->isTotpEnabled() && $auth->totp_enabled_at): ?>
                            <div class="d-flex mb-3">
                                <span class="me-auto text-secondary">Attivata il</span>
                                <span><?= Yii::$app->formatter->asDate($auth->totp_enabled_at) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <?php if ($auth->isTotpEnabled()): ?>
                            <?= Html::a('<i class="ti ti-shield-off me-1"></i> Disattiva 2FA', ['disattiva-2fa'], ['class' => 'btn btn-outline-danger w-100 mb-2']) ?>
                        <?php else: ?>
                            <?= Html::a('<i class="ti ti-shield-check me-1"></i> Attiva 2FA', ['attiva-2fa'], ['class' => 'btn btn-outline-primary w-100 mb-2']) ?>
                        <?php endif; ?>
                        <?= Html::a('<i class="ti ti-key me-1"></i> Cambia Password', ['cambia-password'], ['class' => 'btn btn-outline-secondary w-100']) ?>
                    </div>
                </div>

                <!-- Tag Curriculum -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">I Miei Tag</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $docenteTags = $docente->docenteTags;
                        if (empty($docenteTags)):
                        ?>
                            <p class="text-muted mb-0">Nessun tag nel profilo. I tag vengono aggiunti quando un amministratore promuove i tag dalle tue iscrizioni.</p>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($docenteTags as $dt): ?>
                                    <span class="badge bg-azure-lt">
                                        <?= Html::encode($dt->tag->nome ?? 'Tag #' . $dt->tag_id) ?>
                                        <?php if ($dt->livello): ?>
                                            <small>(Liv. <?= $dt->livello ?>)</small>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
