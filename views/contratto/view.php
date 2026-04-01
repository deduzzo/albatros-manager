<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contratto */

$assegnazione = $model->assegnazione;
$docente = $assegnazione ? $assegnazione->docente : null;
$modulo = $assegnazione ? $assegnazione->modulo : null;
$docenteNome = $docente ? $docente->cognome . ' ' . $docente->nome : 'N/D';

$this->title = 'Contratto #' . $model->id . ($model->numero_contratto ? ' (' . $model->numero_contratto . ')' : '');
$this->params['breadcrumbs'][] = ['label' => 'Contratti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Dettagli firma
$firmaDettagli = null;
if ($model->firma_dettagli) {
    $firmaDettagli = json_decode($model->firma_dettagli, true);
}
?>

<div class="contratto-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-file-text me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($docenteNome) ?>
                        <?php if ($modulo): ?>
                            &mdash; <?= Html::encode($modulo->titolo) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo contratto?',
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
                <!-- Riepilogo rapido -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Stato</div>
                            <div class="h1 mb-0 mt-2">
                                <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Compenso Totale</div>
                            <div class="h1 mb-0 mt-2">
                                <?= Yii::$app->formatter->asCurrency($model->compenso_totale, 'EUR') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Ore</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->ore_contrattualizzate ?>
                            </div>
                            <div class="text-muted small mt-1">
                                a <?= Yii::$app->formatter->asCurrency($model->compenso_orario, 'EUR') ?>/ora
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Lezioni</div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->getGiorniRimanentiBadge() ?>
                            </div>
                            <div class="text-muted small mt-1">
                                giorni rimanenti
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dettagli contratto -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-info-circle me-2"></i>
                                Dettagli Contratto
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Numero Contratto</div>
                                    <div class="datagrid-content">
                                        <?= $model->numero_contratto ? '<code>' . Html::encode($model->numero_contratto) . '</code>' : '-' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Tipo</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-blue-lt"><?= Html::encode($model->getTipoLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Stipula</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asDate($model->data_stipula, 'long') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Periodo Contratto</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asDate($model->data_inizio, 'medium') ?>
                                        &rarr;
                                        <?= Yii::$app->formatter->asDate($model->data_fine, 'medium') ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Periodo Lezioni</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->data_inizio_lezioni): ?>
                                            <?= Yii::$app->formatter->asDate($model->data_inizio_lezioni, 'medium') ?>
                                            &rarr;
                                            <?= $model->data_fine_lezioni ? Yii::$app->formatter->asDate($model->data_fine_lezioni, 'medium') : 'N/D' ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Creato il</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                                </div>
                            </div>
                            <?php if ($model->note): ?>
                                <hr>
                                <div class="mb-2"><strong>Note:</strong></div>
                                <p class="text-muted"><?= nl2br(Html::encode($model->note)) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Dati economici e riferimenti -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-currency-euro me-2"></i>
                                Dati Economici
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Compenso Orario</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asCurrency($model->compenso_orario, 'EUR') ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ore Contrattualizzate</div>
                                    <div class="datagrid-content"><?= $model->ore_contrattualizzate ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Compenso Totale</div>
                                    <div class="datagrid-content"><strong><?= Yii::$app->formatter->asCurrency($model->compenso_totale, 'EUR') ?></strong></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ritenute</div>
                                    <div class="datagrid-content"><?= $model->ritenute !== null ? Yii::$app->formatter->asCurrency($model->ritenute, 'EUR') : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Netto Docente</div>
                                    <div class="datagrid-content"><?= $model->netto_docente !== null ? Yii::$app->formatter->asCurrency($model->netto_docente, 'EUR') : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Totale Pagato</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asCurrency($model->getTotalePagato(), 'EUR') ?>
                                        <?php if ($model->isSaldato()): ?>
                                            <span class="badge bg-green text-green-fg ms-1">Saldato</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Saldo Rimanente</div>
                                    <div class="datagrid-content">
                                        <?php $saldo = $model->getSaldoRimanente(); ?>
                                        <span class="<?= $saldo > 0 ? 'text-danger' : 'text-success' ?>">
                                            <?= Yii::$app->formatter->asCurrency($saldo, 'EUR') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riferimenti -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-link me-2"></i>
                                Riferimenti
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Assegnazione</div>
                                    <div class="datagrid-content">
                                        <?php if ($assegnazione): ?>
                                            <?= Html::a('Assegnazione #' . $assegnazione->id, ['/assegnazione/view', 'id' => $assegnazione->id]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Docente</div>
                                    <div class="datagrid-content">
                                        <?php if ($docente): ?>
                                            <?= Html::a(Html::encode($docente->cognome . ' ' . $docente->nome), ['/docente/view', 'id' => $docente->codice_fiscale]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Modulo</div>
                                    <div class="datagrid-content">
                                        <?php if ($modulo): ?>
                                            <?= Html::a(Html::encode($modulo->codice . ' - ' . $modulo->titolo), ['/modulo/view', 'id' => $modulo->id]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documento Contratto -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-file-certificate me-2"></i>
                                Documento Contratto
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Generazione PDF -->
                                <div class="col-lg-6">
                                    <h4 class="mb-3">Contratto PDF</h4>
                                    <?php if ($model->hasDocumentoGenerato()): ?>
                                        <div class="alert alert-success">
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-check me-2" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <strong>Documento generato</strong>
                                                    <div class="text-muted small"><?= Html::encode($model->documento_generato_path) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btn-list">
                                            <?= Html::a('<i class="ti ti-download me-1"></i> Scarica PDF', ['download', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                                            <?= Html::a('<i class="ti ti-eye me-1"></i> Anteprima', ['anteprima', 'id' => $model->id], ['class' => 'btn btn-info', 'target' => '_blank']) ?>
                                            <?= Html::a('<i class="ti ti-refresh me-1"></i> Rigenera', ['genera', 'id' => $model->id], [
                                                'class' => 'btn btn-outline-orange',
                                                'data' => [
                                                    'confirm' => 'Sei sicuro di voler rigenerare il documento? Il precedente verra\' sovrascritto.',
                                                    'method' => 'post',
                                                ],
                                            ]) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4 text-muted">
                                            <i class="ti ti-file-off" style="font-size: 3rem;"></i>
                                            <p class="mt-2">Il documento PDF non e' stato ancora generato.</p>
                                        </div>
                                        <?= Html::a('<i class="ti ti-file-plus me-1"></i> Genera Contratto PDF', ['genera', 'id' => $model->id], [
                                            'class' => 'btn btn-success',
                                            'data' => [
                                                'confirm' => 'Generare il documento PDF del contratto?',
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Upload Firmato -->
                                <div class="col-lg-6">
                                    <h4 class="mb-3">Contratto Firmato</h4>
                                    <?php if ($model->documento_path): ?>
                                        <div class="alert <?= $model->firma_verificata ? 'alert-success' : 'alert-warning' ?>">
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-<?= $model->firma_verificata ? 'certificate' : 'alert-triangle' ?> me-2" style="font-size: 1.5rem;"></i>
                                                <div>
                                                    <strong>
                                                        <?php if ($model->firma_verificata): ?>
                                                            Firma digitale VALIDA
                                                        <?php else: ?>
                                                            Firma non verificata
                                                        <?php endif; ?>
                                                    </strong>
                                                    <div class="text-muted small">
                                                        Tipo: <?= Html::encode($model->getFirmaTipoLabel()) ?>
                                                        <?php if ($model->firma_data): ?>
                                                            &mdash; Data: <?= Html::encode($model->firma_data) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($firmaDettagli && !empty($firmaDettagli['firmatario']['cn'])): ?>
                                            <div class="datagrid mb-3">
                                                <div class="datagrid-item">
                                                    <div class="datagrid-title">Firmatario</div>
                                                    <div class="datagrid-content"><?= Html::encode($firmaDettagli['firmatario']['cn']) ?></div>
                                                </div>
                                                <?php if (!empty($firmaDettagli['firmatario']['cf'])): ?>
                                                <div class="datagrid-item">
                                                    <div class="datagrid-title">Codice Fiscale</div>
                                                    <div class="datagrid-content">
                                                        <code><?= Html::encode($firmaDettagli['firmatario']['cf']) ?></code>
                                                        <?php if ($docente && strtoupper($firmaDettagli['firmatario']['cf']) === strtoupper($docente->codice_fiscale)): ?>
                                                            <span class="badge bg-green text-green-fg ms-1">Match</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-red text-red-fg ms-1">Non corrisponde</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($firmaDettagli['firmatario']['o'])): ?>
                                                <div class="datagrid-item">
                                                    <div class="datagrid-title">Organizzazione</div>
                                                    <div class="datagrid-content"><?= Html::encode($firmaDettagli['firmatario']['o']) ?></div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($firmaDettagli['certificato']['emittente'])): ?>
                                                <div class="datagrid-item">
                                                    <div class="datagrid-title">Emittente certificato</div>
                                                    <div class="datagrid-content"><?= Html::encode($firmaDettagli['certificato']['emittente']) ?></div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <?= Html::beginForm(['upload-firmato', 'id' => $model->id], 'post', ['enctype' => 'multipart/form-data']) ?>
                                            <div class="mb-3">
                                                <label class="form-label">Carica contratto firmato (P7M o PDF)</label>
                                                <input type="file" name="file_firmato" class="form-control" accept=".p7m,.pdf" required>
                                                <div class="form-text">
                                                    Formati accettati: .p7m (CAdES), .pdf (PAdES). La firma verra' verificata automaticamente.
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="ti ti-upload me-1"></i>
                                                <?= $model->documento_path ? 'Sostituisci contratto firmato' : 'Carica contratto firmato' ?>
                                            </button>
                                        <?= Html::endForm() ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagamenti -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-currency-euro me-2"></i>
                                Pagamenti
                            </h3>
                            <div class="card-actions">
                                <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Pagamento', ['/pagamento/create', 'contratto_id' => $model->id], ['class' => 'btn btn-sm btn-success']) ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $pagamenti = $model->pagamenti; ?>
                            <?php if (empty($pagamenti)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="ti ti-currency-euro" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Nessun pagamento registrato per questo contratto.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tipo</th>
                                                <th>Importo Lordo</th>
                                                <th>Importo Netto</th>
                                                <th>Data Pagamento</th>
                                                <th>Stato</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pagamenti as $pagamento): ?>
                                                <tr>
                                                    <td><?= $pagamento->id ?></td>
                                                    <td><span class="badge bg-blue-lt"><?= Html::encode($pagamento->getTipoLabel()) ?></span></td>
                                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($pagamento->importo_lordo, 'EUR') ?></td>
                                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($pagamento->importo_netto, 'EUR') ?></td>
                                                    <td><?= $pagamento->data_pagamento ? Yii::$app->formatter->asDate($pagamento->data_pagamento) : '-' ?></td>
                                                    <td><span class="badge <?= $pagamento->getStatusBadgeClass() ?>"><?= Html::encode($pagamento->getStatusLabel()) ?></span></td>
                                                    <td>
                                                        <?= Html::a('<i class="ti ti-eye"></i>', ['/pagamento/view', 'id' => $pagamento->id], [
                                                            'class' => 'btn btn-info btn-sm',
                                                            'title' => 'Visualizza',
                                                        ]) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Fatturazione -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-file-invoice me-2"></i>
                                Fatturazione / Cedolini
                            </h3>
                            <div class="card-actions">
                                <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Richiesta', ['/richiesta-fatturazione/create', 'contratto_id' => $model->id], ['class' => 'btn btn-sm btn-success']) ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $richieste = $model->richiesteFatturazione; ?>
                            <?php if (empty($richieste)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="ti ti-file-invoice" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Nessuna richiesta di fatturazione per questo contratto.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tipo</th>
                                                <th>Importo</th>
                                                <th>Destinatario</th>
                                                <th>Data Richiesta</th>
                                                <th>Stato</th>
                                                <th>Documento</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($richieste as $richiesta): ?>
                                                <tr>
                                                    <td><?= $richiesta->id ?></td>
                                                    <td><?= $richiesta->getTipoBadge() ?></td>
                                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($richiesta->importo, 'EUR') ?></td>
                                                    <td><?= Html::encode($richiesta->destinatario_nome ?: $richiesta->destinatario_email) ?></td>
                                                    <td><?= Yii::$app->formatter->asDate($richiesta->data_richiesta) ?></td>
                                                    <td><?= $richiesta->getStatusBadge() ?></td>
                                                    <td>
                                                        <?php if ($richiesta->hasDocumentoRicevuto()): ?>
                                                            <span class="badge bg-green text-green-fg"><i class="ti ti-check me-1"></i>Si</span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= Html::a('<i class="ti ti-eye"></i>', ['/richiesta-fatturazione/view', 'id' => $richiesta->id], [
                                                            'class' => 'btn btn-info btn-sm',
                                                            'title' => 'Visualizza',
                                                        ]) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Allegati Scansione -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-paperclip me-2"></i>Allegati</h3>
                        </div>
                        <div class="card-body">
                            <?php $allegati = $model->getAllegati(); ?>
                            <?php if (!empty($allegati)): ?>
                            <div class="list-group list-group-flush mb-3">
                                <?php foreach ($allegati as $all): ?>
                                <div class="list-group-item d-flex align-items-center">
                                    <i class="ti ti-file me-2 text-muted"></i>
                                    <div>
                                        <a href="<?= Yii::$app->request->baseUrl . '/' . $all['path'] ?>" target="_blank">
                                            <?= Html::encode($all['nome']) ?>
                                        </a>
                                        <small class="text-muted d-block"><?= $all['data'] ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-muted mb-3">Nessun allegato caricato.</div>
                            <?php endif; ?>

                            <form method="post" action="<?= \yii\helpers\Url::to(['/contratto/upload-allegato', 'id' => $model->id]) ?>"
                                  enctype="multipart/form-data">
                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                <div class="input-group">
                                    <input type="file" name="allegato" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <button type="submit" class="btn btn-outline-indigo">
                                        <i class="ti ti-upload me-1"></i> Carica
                                    </button>
                                </div>
                                <small class="text-muted">Formati: PDF, JPG, PNG</small>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Footer con azioni -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
