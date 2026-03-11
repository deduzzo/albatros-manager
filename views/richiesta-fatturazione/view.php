<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\RichiestaFatturazione */

$contratto = $model->contratto;
$assegnazione = $contratto ? $contratto->assegnazione : null;
$docente = $assegnazione ? $assegnazione->docente : null;
$docenteNome = $docente ? $docente->cognome . ' ' . $docente->nome : ($model->destinatario_nome ?: 'N/D');

$this->title = 'Richiesta Fatturazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Richieste Fatturazione', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="richiesta-fatturazione-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-file-invoice me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($docenteNome) ?>
                        &mdash; <?= $model->getTipoBadge() ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?php if ($model->isModificabile()): ?>
                        <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-1']) ?>
                        <?= Html::a('<i class="ti ti-send me-1"></i> Invia', ['invia', 'id' => $model->id], [
                            'class' => 'btn btn-azure',
                            'data' => [
                                'confirm' => 'Confermi l\'invio della richiesta a ' . $model->destinatario_email . '?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php endif; ?>
                    <?php if (in_array($model->status, ['inviata', 'ricevuta'])): ?>
                        <?= Html::a('<i class="ti ti-upload me-1"></i> Ricevi Documento', ['ricevi-documento', 'id' => $model->id], ['class' => 'btn btn-green me-1']) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questa richiesta?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <!-- Stat Cards -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Stato</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->getStatusBadge() ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Tipo</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->getTipoBadge() ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Importo</div>
                            <div class="h1 mb-0 mt-2">
                                <?= Yii::$app->formatter->asCurrency($model->importo, 'EUR') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Documento</div>
                            <div class="h1 mb-0 mt-2">
                                <?php if ($model->hasDocumentoRicevuto()): ?>
                                    <span class="badge bg-green text-green-fg"><i class="ti ti-check me-1"></i>Ricevuto</span>
                                <?php else: ?>
                                    <span class="badge bg-orange text-orange-fg"><i class="ti ti-clock me-1"></i>In attesa</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Status -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-timeline me-2"></i>
                                Avanzamento
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul class="steps steps-vertical">
                                <li class="step-item <?= $model->status !== '' ? 'active' : '' ?>">
                                    <div class="h4 m-0">Bozza creata</div>
                                    <div class="text-muted small"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                                </li>
                                <li class="step-item <?= in_array($model->status, ['inviata', 'ricevuta', 'verificata']) ? 'active' : '' ?>">
                                    <div class="h4 m-0">Inviata al destinatario</div>
                                    <div class="text-muted small"><?= $model->status !== 'bozza' ? $model->destinatario_email : 'In attesa di invio' ?></div>
                                </li>
                                <li class="step-item <?= in_array($model->status, ['ricevuta', 'verificata']) ? 'active' : '' ?>">
                                    <div class="h4 m-0">Documento ricevuto</div>
                                    <div class="text-muted small"><?= $model->data_ricezione ? Yii::$app->formatter->asDate($model->data_ricezione) : 'In attesa' ?></div>
                                </li>
                                <li class="step-item <?= $model->status === 'verificata' ? 'active' : '' ?>">
                                    <div class="h4 m-0">Verificata</div>
                                    <div class="text-muted small"><?= $model->status === 'verificata' ? 'Completata' : 'In attesa' ?></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Dettagli -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-info-circle me-2"></i>
                                Dettagli
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">ID</div>
                                    <div class="datagrid-content"><?= $model->id ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Contratto</div>
                                    <div class="datagrid-content">
                                        <?php if ($contratto): ?>
                                            <?= Html::a(
                                                'Contratto #' . $contratto->id . ($contratto->numero_contratto ? ' (' . $contratto->numero_contratto . ')' : ''),
                                                ['/contratto/view', 'id' => $contratto->id]
                                            ) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Destinatario</div>
                                    <div class="datagrid-content">
                                        <?= Html::encode($model->destinatario_nome) ?><br>
                                        <small class="text-muted"><?= Html::encode($model->destinatario_email) ?></small>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Periodo</div>
                                    <div class="datagrid-content"><?= Html::encode($model->periodo_riferimento) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Richiesta</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asDate($model->data_richiesta, 'long') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Scadenza</div>
                                    <div class="datagrid-content"><?= $model->data_scadenza ? Yii::$app->formatter->asDate($model->data_scadenza, 'long') : '-' ?></div>
                                </div>
                            </div>

                            <?php if ($model->hasDocumentoRicevuto()): ?>
                                <hr>
                                <div class="mb-2"><strong>Documento ricevuto:</strong></div>
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-file-check me-2 text-green" style="font-size: 1.5rem;"></i>
                                    <?= Html::a(
                                        basename($model->documento_ricevuto_path),
                                        Yii::getAlias('@web/' . $model->documento_ricevuto_path),
                                        ['target' => '_blank', 'class' => 'btn btn-sm btn-outline-green']
                                    ) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($model->note): ?>
                                <hr>
                                <div class="mb-2"><strong>Note:</strong></div>
                                <p class="text-muted"><?= nl2br(Html::encode($model->note)) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Preview email -->
                <?php if ($model->corpo_email): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-mail me-2"></i>
                                Contenuto Email
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if ($model->oggetto_email): ?>
                                <div class="mb-2">
                                    <strong>Oggetto:</strong> <?= Html::encode($model->oggetto_email) ?>
                                </div>
                                <hr>
                            <?php endif; ?>
                            <pre class="p-3 bg-light rounded" style="white-space: pre-wrap;"><?= Html::encode($model->corpo_email) ?></pre>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                            <?php if ($contratto): ?>
                                <?= Html::a('<i class="ti ti-file-text me-1"></i> Vai al Contratto', ['/contratto/view', 'id' => $contratto->id], ['class' => 'btn btn-info ms-2']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
