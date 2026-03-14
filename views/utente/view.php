<?php

use yii\helpers\Html;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->nominativo ?: $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Gestione Utenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$isSelf = $model->id === Yii::$app->user->id;
$iniziali = mb_strtoupper(mb_substr($model->nominativo ?: $model->username, 0, 2));
?>

<div class="utente-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg bg-blue-lt">
                        <?= Html::encode($iniziali) ?>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                        <?php
                        $ruoloColors = [
                            User::RUOLO_ADMIN => 'bg-red text-red-fg',
                            User::RUOLO_SEGRETERIA => 'bg-blue text-blue-fg',
                            User::RUOLO_COORDINATORE => 'bg-purple text-purple-fg',
                            User::RUOLO_OPERATORE => 'bg-default text-default-fg',
                        ];
                        $ruoloClass = $ruoloColors[$model->ruolo] ?? 'bg-default text-default-fg';
                        ?>
                        <span class="badge <?= $ruoloClass ?> ms-1"><?= Html::encode($model->getRuoloLabel()) ?></span>
                        <?php if ($isSelf): ?>
                            <span class="badge bg-azure-lt ms-1"><i class="ti ti-user me-1"></i>Tu</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Indietro', ['index'], ['class' => 'btn btn-outline-secondary me-2']) ?>
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <!-- Card Profilo -->
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-user me-2"></i>Profilo</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Username</div>
                                    <div class="datagrid-content"><?= Html::encode($model->username) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nominativo</div>
                                    <div class="datagrid-content"><?= Html::encode($model->nominativo ?: '-') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Email</div>
                                    <div class="datagrid-content"><?= $model->email ? Html::mailto(Html::encode($model->email)) : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ruolo</div>
                                    <div class="datagrid-content">
                                        <span class="badge <?= $ruoloClass ?>"><?= Html::encode($model->getRuoloLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Creato il</div>
                                    <div class="datagrid-content"><?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Modificato il</div>
                                    <div class="datagrid-content"><?= $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at) : '-' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Sicurezza -->
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-shield me-2"></i>Sicurezza</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Stato</div>
                                    <div class="datagrid-content">
                                        <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Autenticazione a Due Fattori</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->totp_enabled): ?>
                                            <span class="badge bg-green text-green-fg"><i class="ti ti-shield-check me-1"></i>Attivo</span>
                                            <?php if ($model->totp_enabled_at): ?>
                                                <small class="text-muted ms-1">dal <?= Yii::$app->formatter->asDatetime($model->totp_enabled_at) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-default text-default-fg"><i class="ti ti-shield-off me-1"></i>Non attivo</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Forza Cambio Password</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->force_password_change): ?>
                                            <span class="badge bg-orange text-orange-fg"><i class="ti ti-alert-triangle me-1"></i>Richiesto</span>
                                        <?php else: ?>
                                            <span class="text-muted">No</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Azioni -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-settings me-2"></i>Azioni</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <?= Html::a(
                                    '<i class="ti ti-key me-1"></i> Reset Password',
                                    ['reset-password', 'id' => $model->id],
                                    ['class' => 'btn btn-outline-blue']
                                ) ?>

                                <?php if (!$isSelf): ?>
                                    <?= Html::a(
                                        $model->status === User::STATUS_ACTIVE
                                            ? '<i class="ti ti-user-off me-1"></i> Disabilita'
                                            : '<i class="ti ti-user-check me-1"></i> Attiva',
                                        ['toggle-status', 'id' => $model->id],
                                        [
                                            'class' => $model->status === User::STATUS_ACTIVE ? 'btn btn-outline-orange' : 'btn btn-outline-green',
                                            'data-method' => 'post',
                                            'data-confirm' => $model->status === User::STATUS_ACTIVE
                                                ? 'Sei sicuro di voler disabilitare questo utente?'
                                                : 'Sei sicuro di voler riattivare questo utente?',
                                        ]
                                    ) ?>
                                <?php endif; ?>

                                <?php if ($model->totp_enabled): ?>
                                    <?= Html::a(
                                        '<i class="ti ti-shield-off me-1"></i> Reset 2FA',
                                        ['reset-2fa', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-outline-red',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Sei sicuro di voler disabilitare il 2FA per questo utente?',
                                        ]
                                    ) ?>
                                <?php endif; ?>

                                <?php if (!$isSelf): ?>
                                    <?= Html::a(
                                        '<i class="ti ti-trash me-1"></i> Elimina',
                                        ['delete', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-outline-red',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Sei sicuro di voler eliminare questo utente? L\'operazione non e\' reversibile.',
                                        ]
                                    ) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
