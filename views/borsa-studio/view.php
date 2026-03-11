<?php

use yii\helpers\Html;
use app\models\BorsaStudio;
use app\models\AuditLog;

/** @var yii\web\View $this */
/** @var app\models\BorsaStudio $model */

$this->title = 'Borsa di Studio #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Borse di Studio', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$formatter = Yii::$app->formatter;
$importoTrimestre = $model->getImportoPerTrimestre();
$percentualeErogazione = $model->getPercentualeErogazione();
$requisitiOk = $model->verificaRequisitiMerito();
?>
<div class="borsa-studio-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-school me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <?php if ($model->status === BorsaStudio::STATUS_RICHIESTA): ?>
                        <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-1']) ?>
                    <?php endif; ?>
                    <?php if (in_array($model->status, [BorsaStudio::STATUS_APPROVATA, BorsaStudio::STATUS_IN_EROGAZIONE])): ?>
                        <?= Html::a('<i class="ti ti-cash me-1"></i> Eroga Trimestre', ['eroga', 'id' => $model->id], ['class' => 'btn btn-success me-1']) ?>
                    <?php endif; ?>
                    <?php if (!in_array($model->status, [BorsaStudio::STATUS_REVOCATA, BorsaStudio::STATUS_COMPLETATA])): ?>
                        <?= Html::a('<i class="ti ti-ban me-1"></i> Revoca', ['revoca', 'id' => $model->id], ['class' => 'btn btn-danger me-1']) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-outline-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questa borsa di studio?', 'method' => 'post'],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">

            <!-- Card Dati Studente -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user me-2"></i>Dati Studente</h3>
                    <div class="card-actions">
                        <?= $model->getStatusBadge() ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Studente</div>
                            <div class="datagrid-content">
                                <?= $model->allievo
                                    ? Html::a('<i class="ti ti-user me-1"></i>' . Html::encode($model->allievo->getNomeCompleto()), ['/allievo/view', 'id' => $model->allievo_codice_fiscale])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Codice Fiscale</div>
                            <div class="datagrid-content"><code><?= Html::encode($model->allievo_codice_fiscale) ?></code></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Progetto/Corso</div>
                            <div class="datagrid-content">
                                <?= $model->progetto
                                    ? Html::a('<i class="ti ti-book me-1"></i>' . Html::encode($model->progetto->titolo), ['/progetto/view', 'id' => $model->progetto_id])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Anno Formativo</div>
                            <div class="datagrid-content"><?= Html::encode($model->anno_formativo) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Data Approvazione</div>
                            <div class="datagrid-content">
                                <?= $model->data_approvazione ? $formatter->asDate($model->data_approvazione) : '<span class="text-muted">Non ancora approvata</span>' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">IBAN</div>
                            <div class="datagrid-content"><?= $model->iban_studente ? Html::encode($model->iban_studente) : '<span class="text-muted">Non inserito</span>' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Dati Economici -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-currency-euro me-2"></i>Dati Economici</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ISEE</div>
                            <div class="datagrid-content">
                                <?= $model->isee_importo !== null ? $formatter->asCurrency($model->isee_importo, 'EUR') : '<span class="text-muted">-</span>' ?>
                                <?php if ($model->isee_protocollo): ?>
                                    <small class="text-muted d-block">Prot. <?= Html::encode($model->isee_protocollo) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">ISPE</div>
                            <div class="datagrid-content"><?= $model->ispe_importo !== null ? $formatter->asCurrency($model->ispe_importo, 'EUR') : '<span class="text-muted">-</span>' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tipologia Sede</div>
                            <div class="datagrid-content"><?= $model->getTipologiaSedeBadge() ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Importo Base</div>
                            <div class="datagrid-content"><strong><?= $formatter->asCurrency($model->importo_base, 'EUR') ?></strong></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Maggiorazione</div>
                            <div class="datagrid-content">
                                <?php if ($model->maggiorazione_tipo): ?>
                                    <?php
                                    $tipoLabel = match ($model->maggiorazione_tipo) {
                                        'disabilita' => 'Disabilita',
                                        'isee_basso' => 'ISEE basso',
                                        'studentessa' => 'Studentessa',
                                        default => $model->maggiorazione_tipo,
                                    };
                                    ?>
                                    <span class="badge bg-purple-lt"><?= $tipoLabel ?> (+<?= $model->maggiorazione_percentuale ?>%)</span>
                                <?php else: ?>
                                    <span class="text-muted">Nessuna</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Importo Finale</div>
                            <div class="datagrid-content"><span class="h3 text-blue"><?= $formatter->asCurrency($model->importo_finale, 'EUR') ?></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Erogazioni Trimestrali -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-calendar-dollar me-2"></i>Erogazioni Trimestrali</h3>
                </div>
                <div class="card-body">
                    <!-- Progress bar totale -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Totale erogato: <strong><?= $formatter->asCurrency($model->totale_erogato, 'EUR') ?></strong></span>
                            <span>su <?= $formatter->asCurrency($model->importo_finale, 'EUR') ?> (<?= $percentualeErogazione ?>%)</span>
                        </div>
                        <?php
                        $colorBar = $percentualeErogazione >= 100 ? 'bg-green' : ($percentualeErogazione >= 50 ? 'bg-blue' : 'bg-azure');
                        ?>
                        <div class="progress">
                            <div class="progress-bar <?= $colorBar ?>" style="width: <?= $percentualeErogazione ?>%"></div>
                        </div>
                    </div>

                    <!-- 4 card affiancate Q1-Q4 -->
                    <div class="row row-deck row-cards">
                        <?php for ($q = 1; $q <= 4; $q++): ?>
                            <?php
                            $campo = 'importo_q' . $q;
                            $importoErogato = (float) $model->$campo;
                            $erogato = $importoErogato > 0;
                            ?>
                            <div class="col-sm-6 col-lg-3">
                                <div class="card <?= $erogato ? 'border-green' : '' ?>">
                                    <div class="card-body text-center">
                                        <div class="subheader mb-2">Trimestre Q<?= $q ?></div>
                                        <div class="mb-2">
                                            <small class="text-muted">Previsto: <?= $formatter->asCurrency($importoTrimestre, 'EUR') ?></small>
                                        </div>
                                        <?php if ($erogato): ?>
                                            <div class="h2 text-green mb-1"><?= $formatter->asCurrency($importoErogato, 'EUR') ?></div>
                                            <span class="badge bg-green text-green-fg"><i class="ti ti-check me-1"></i>Erogato</span>
                                        <?php else: ?>
                                            <div class="h2 text-muted mb-1">-</div>
                                            <span class="badge bg-default text-default-fg">Non erogato</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$erogato && in_array($model->status, [BorsaStudio::STATUS_APPROVATA, BorsaStudio::STATUS_IN_EROGAZIONE])): ?>
                                        <div class="card-footer text-center">
                                            <?= Html::a('<i class="ti ti-cash me-1"></i> Eroga Q' . $q, ['eroga', 'id' => $model->id, 'q' => $q], ['class' => 'btn btn-success btn-sm']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Card Requisiti Merito -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-certificate me-2"></i>Requisiti di Merito</h3>
                    <div class="card-actions">
                        <?php if ($requisitiOk): ?>
                            <span class="badge bg-green text-green-fg"><i class="ti ti-check me-1"></i>Requisiti soddisfatti</span>
                        <?php else: ?>
                            <span class="badge bg-red text-red-fg"><i class="ti ti-x me-1"></i>Requisiti non soddisfatti</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Frequenza (min. 80%)</label>
                                <div class="d-flex align-items-center">
                                    <div class="flex-fill">
                                        <?php
                                        $freqColor = $model->percentuale_frequenza >= 80 ? 'bg-green' : 'bg-red';
                                        ?>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar <?= $freqColor ?>" style="width: <?= min(100, $model->percentuale_frequenza) ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <strong><?= $model->percentuale_frequenza ?>%</strong>
                                        <?php if ($model->percentuale_frequenza >= 80): ?>
                                            <i class="ti ti-check text-green ms-1"></i>
                                        <?php else: ?>
                                            <i class="ti ti-x text-red ms-1"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Verifiche fine modulo</label>
                                <div>
                                    <?php if ($model->verifiche_superate): ?>
                                        <span class="badge bg-green text-green-fg fs-5"><i class="ti ti-check me-1"></i>Superate</span>
                                    <?php else: ?>
                                        <span class="badge bg-red text-red-fg fs-5"><i class="ti ti-x me-1"></i>Non superate</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note -->
            <?php if ($model->note): ?>
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-notes me-2"></i>Note</h3></div>
                    <div class="card-body"><?= nl2br(Html::encode($model->note)) ?></div>
                </div>
            <?php endif; ?>

            <!-- Motivo Revoca -->
            <?php if ($model->status === BorsaStudio::STATUS_REVOCATA && $model->motivo_revoca): ?>
                <div class="alert alert-danger mb-3">
                    <div class="d-flex">
                        <div><i class="ti ti-alert-circle icon me-2"></i></div>
                        <div>
                            <h4 class="alert-title">Borsa Revocata</h4>
                            <div class="text-secondary"><?= nl2br(Html::encode($model->motivo_revoca)) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Timeline Audit -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-history me-2"></i>Storico Modifiche</h3></div>
                <div class="card-body">
                    <?php
                    $auditLogs = AuditLog::getRecordHistory('borsa_studio', $model->id);
                    ?>
                    <?php if (!empty($auditLogs)): ?>
                        <ul class="list-timeline">
                            <?php foreach ($auditLogs as $log): ?>
                                <li class="list-timeline-item">
                                    <div class="list-timeline-time text-muted small"><?= $formatter->asDatetime($log->created_at) ?></div>
                                    <div class="list-timeline-content">
                                        <div class="list-timeline-title"><?= Html::encode($log->action) ?></div>
                                        <?php if ($log->note): ?>
                                            <p class="text-muted small mb-0"><?= Html::encode($log->note) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Nessuna modifica registrata.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
