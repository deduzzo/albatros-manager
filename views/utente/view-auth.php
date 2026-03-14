<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DocenteAuth|app\models\AllievoAuth */
/* @var $type string 'docente' o 'studente' */

$isDocente = $type === 'docente';
$relazione = $isDocente ? $model->docente : $model->allievo;
$cf = $isDocente ? $model->docente_codice_fiscale : $model->allievo_codice_fiscale;
$nominativo = $relazione ? ($relazione->cognome . ' ' . $relazione->nome) : $cf;
$email = $relazione ? $relazione->email : null;
$tipoLabel = $isDocente ? 'Docente' : 'Studente';
$avatarColor = $isDocente ? 'bg-green-lt' : 'bg-purple-lt';

$iniziali = '??';
if ($relazione) {
    $iniziali = mb_strtoupper(mb_substr($relazione->cognome, 0, 1) . mb_substr($relazione->nome, 0, 1));
}

$this->title = "Credenziali {$tipoLabel}: {$nominativo}";
$this->params['breadcrumbs'][] = ['label' => 'Gestione Utenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $nominativo;
?>

<div class="utente-view-auth">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg <?= $avatarColor ?>">
                        <?= Html::encode($iniziali) ?>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        <?= Html::encode($nominativo) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <span class="badge <?= $isDocente ? 'bg-green-lt' : 'bg-purple-lt' ?>"><?= Html::encode($tipoLabel) ?></span>
                        <span class="badge <?= $model->getStatusBadgeClass() ?> ms-1"><?= Html::encode($model->getStatusLabel()) ?></span>
                        <code class="ms-2"><?= Html::encode($cf) ?></code>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Indietro', ['index'], ['class' => 'btn btn-outline-secondary me-2']) ?>
                    <?php
                    $entityUrl = $isDocente
                        ? ['docente/view', 'id' => $cf]
                        : ['allievo/view', 'id' => $cf];
                    ?>
                    <?= Html::a(
                        '<i class="ti ti-external-link me-1"></i> Scheda ' . $tipoLabel,
                        $entityUrl,
                        ['class' => 'btn btn-outline-primary']
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <!-- Card Dettaglio -->
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-id me-2"></i>Dettaglio Account</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Tipo</div>
                                    <div class="datagrid-content">
                                        <span class="badge <?= $isDocente ? 'bg-green-lt' : 'bg-purple-lt' ?>"><?= Html::encode($tipoLabel) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nominativo</div>
                                    <div class="datagrid-content"><?= Html::encode($nominativo) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Codice Fiscale</div>
                                    <div class="datagrid-content"><code><?= Html::encode($cf) ?></code></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Email</div>
                                    <div class="datagrid-content">
                                        <?= $email ? Html::mailto(Html::encode($email)) : '<span class="text-muted">-</span>' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Registrato il</div>
                                    <div class="datagrid-content"><?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ultimo Accesso</div>
                                    <div class="datagrid-content"><?= $model->last_login_at ? Yii::$app->formatter->asDatetime($model->last_login_at) : '<span class="text-muted">Mai</span>' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Sicurezza + Azioni -->
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
                                    ['reset-password-auth', 'type' => $type, 'id' => $model->id],
                                    ['class' => 'btn btn-outline-blue']
                                ) ?>

                                <?php
                                $isActive = ($isDocente && $model->status === \app\models\DocenteAuth::STATUS_ACTIVE) ||
                                            (!$isDocente && $model->status === \app\models\AllievoAuth::STATUS_ACTIVE);
                                ?>
                                <?= Html::a(
                                    $isActive
                                        ? '<i class="ti ti-user-off me-1"></i> Sospendi'
                                        : '<i class="ti ti-user-check me-1"></i> Attiva',
                                    ['toggle-status-auth', 'type' => $type, 'id' => $model->id],
                                    [
                                        'class' => $isActive ? 'btn btn-outline-orange' : 'btn btn-outline-green',
                                        'data-method' => 'post',
                                        'data-confirm' => $isActive
                                            ? 'Sei sicuro di voler sospendere questo account?'
                                            : 'Sei sicuro di voler riattivare questo account?',
                                    ]
                                ) ?>

                                <?php if (!$model->force_password_change): ?>
                                    <?= Html::a(
                                        '<i class="ti ti-lock-access me-1"></i> Forza Cambio Password',
                                        ['force-change-auth', 'type' => $type, 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-outline-orange',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Vuoi forzare il cambio password al prossimo login?',
                                        ]
                                    ) ?>
                                <?php endif; ?>

                                <?php if ($model->totp_enabled): ?>
                                    <?= Html::a(
                                        '<i class="ti ti-shield-off me-1"></i> Reset 2FA',
                                        ['reset-2fa-auth', 'type' => $type, 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-outline-red',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Sei sicuro di voler disabilitare il 2FA per questo account?',
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
