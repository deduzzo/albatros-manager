<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Avviso */
/* @var $rendiconto array */

$this->title = 'Corso: ' . $model->codice_avviso;
$this->params['breadcrumbs'][] = ['label' => 'Corsi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->codice_avviso;

$formatter = Yii::$app->formatter;
$percentualePagatoTotale = $rendiconto['contrattualizzato'] > 0
    ? round(($rendiconto['pagato'] / $rendiconto['contrattualizzato']) * 100)
    : 0;
$percentualeInAttesa = $rendiconto['contrattualizzato'] > 0
    ? round(($rendiconto['inAttesa'] / $rendiconto['contrattualizzato']) * 100)
    : 0;
?>

<div class="avviso-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <?php if ($model->isAperto()): ?>
                        <span class="badge bg-green text-green-fg">Aperto per iscrizioni</span>
                    <?php endif; ?>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-file-plus me-1"></i> Documenti Richiesti', ['documenti', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('<i class="ti ti-users-plus me-1"></i> Candidatura Multi-modulo',
                        ['/gestione/nuova-candidatura', 'corso_id' => $model->id],
                        ['class' => 'btn btn-outline-indigo']) ?>
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo corso?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Dettagli Corso</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered'],
                                'attributes' => [
                                    [
                                        'attribute' => 'codice_avviso',
                                        'format' => 'raw',
                                        'value' => '<span class="badge bg-blue-lt">' . Html::encode($model->codice_avviso) . '</span>',
                                    ],
                                    'titolo',
                                    [
                                        'attribute' => 'progetto_id',
                                        'format' => 'raw',
                                        'value' => $model->progetto
                                            ? Html::a($model->progetto->codice_progetto . ' - ' . $model->progetto->titolo, ['/progetto/view', 'id' => $model->progetto_id])
                                            : '-',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'format' => 'raw',
                                        'value' => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>',
                                    ],
                                    'data_pubblicazione:date',
                                    'data_scadenza:date',
                                    'descrizione:ntext',
                                    'note:ntext',
                                    'created_at:datetime',
                                    'updated_at:datetime',
                                ],
                            ]) ?>
                        </div>
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
                    </div>

                    <!-- Documenti Richiesti -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Documenti Richiesti</h3>
                            <div class="card-actions">
                                <?= Html::a('<i class="ti ti-plus"></i> Gestisci', ['documenti', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Documento</th>
                                        <th>Formati</th>
                                        <th>Max</th>
                                        <th>Obbl.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($model->documentiRichiesti): ?>
                                        <?php foreach ($model->documentiRichiesti as $doc): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= Html::encode($doc->nome) ?></strong>
                                                    <?php if ($doc->descrizione): ?>
                                                        <br><small class="text-muted"><?= Html::encode($doc->descrizione) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><code><?= Html::encode($doc->formati_ammessi) ?></code></td>
                                                <td><?= $doc->dimensione_max_mb ?> MB</td>
                                                <td>
                                                    <?php if ($doc->obbligatorio): ?>
                                                        <span class="badge bg-red text-red-fg">Si</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-default text-default-fg">No</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">Nessun documento richiesto configurato</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Riepilogo -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Riepilogo</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-2">
                                <span class="me-auto">Moduli</span>
                                <span class="badge bg-blue text-blue-fg"><?= count($model->moduli) ?></span>
                            </div>
                            <div class="d-flex mb-2">
                                <span class="me-auto">Candidature</span>
                                <span class="badge bg-azure text-azure-fg"><?= $model->getIstanzeCount() ?></span>
                            </div>
                            <?php if ($rendiconto['contrattualizzato'] > 0): ?>
                                <hr class="my-2">
                                <div class="d-flex mb-2">
                                    <span class="me-auto">Contrattualizzato</span>
                                    <strong><?= $formatter->asCurrency($rendiconto['contrattualizzato']) ?></strong>
                                </div>
                                <div class="d-flex mb-2">
                                    <span class="me-auto">Pagato</span>
                                    <strong class="text-success"><?= $formatter->asCurrency($rendiconto['pagato']) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella Moduli -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-books me-2"></i>Moduli del Corso</h3>
                    <div class="card-actions">
                        <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Modulo', ['/modulo/create', 'avviso_id' => $model->id], [
                            'class' => 'btn btn-sm btn-success',
                        ]) ?>
                    </div>
                </div>
                <?php if ($model->moduli): ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Codice</th>
                                <th>Titolo</th>
                                <th>Ore</th>
                                <th>Compenso/h</th>
                                <th>Docente Assegnato</th>
                                <th>Stato</th>
                                <th class="text-end">Importo</th>
                                <th>Pagato</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($model->moduli as $modulo): ?>
                            <?php
                                $assegnazione = $modulo->getAssegnazioni()->one();
                                $docente = $assegnazione ? $assegnazione->docente : null;
                            ?>
                            <tr>
                                <td>
                                    <?= Html::a('<span class="badge bg-blue-lt">' . Html::encode($modulo->codice) . '</span>', ['/modulo/view', 'id' => $modulo->id]) ?>
                                </td>
                                <td><?= Html::a(Html::encode($modulo->titolo), ['/modulo/view', 'id' => $modulo->id]) ?></td>
                                <td><?= $modulo->ore_totali ?></td>
                                <td><?= $formatter->asCurrency($modulo->compenso_orario) ?></td>
                                <td>
                                    <?php if ($docente): ?>
                                        <?= Html::a('<i class="ti ti-user me-1"></i>' . Html::encode($docente->cognome . ' ' . $docente->nome), ['/docente/view', 'id' => $docente->codice_fiscale]) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Non assegnato</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $modulo->getStatusBadgeClass() ?>"><?= $modulo->getStatusLabel() ?></span></td>
                                <?php
                                    $contratto = $assegnazione ? $assegnazione->contratto : null;
                                    $importoContrattualizzato = $contratto ? $contratto->compenso_totale : null;
                                    $totalePagato = $contratto ? $contratto->getTotalePagato() : 0;
                                    $percentualePagato = ($importoContrattualizzato && $importoContrattualizzato > 0) ? round(($totalePagato / $importoContrattualizzato) * 100) : 0;
                                ?>
                                <td class="text-end">
                                    <?php if ($contratto): ?>
                                        <strong><?= $formatter->asCurrency($importoContrattualizzato) ?></strong>
                                    <?php elseif ($modulo->getCompensoTotale()): ?>
                                        <span class="text-muted"><?= $formatter->asCurrency($modulo->getCompensoTotale()) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width: 140px;">
                                    <?php if ($contratto): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar <?= $percentualePagato >= 100 ? 'bg-green' : ($percentualePagato > 0 ? 'bg-orange' : 'bg-default') ?>"
                                                     style="width: <?= min($percentualePagato, 100) ?>%"></div>
                                            </div>
                                            <small class="text-nowrap <?= $percentualePagato >= 100 ? 'text-success' : ($percentualePagato > 0 ? 'text-warning' : 'text-muted') ?>">
                                                <?= $percentualePagato ?>%
                                            </small>
                                        </div>
                                        <small class="text-muted"><?= $formatter->asCurrency($totalePagato) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('<i class="ti ti-eye"></i>', ['/modulo/view', 'id' => $modulo->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Dettaglio']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="card-body text-muted text-center">
                    <i class="ti ti-books mb-2" style="font-size: 2rem;"></i>
                    <p>Nessun modulo collegato a questo corso</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Rendicontazione Finanziaria -->
            <?php if ($rendiconto['contrattualizzato'] > 0 || $rendiconto['compensoPrevisto'] > 0): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-report-money me-2"></i>Rendicontazione Finanziaria</h3>
                </div>
                <div class="card-body">
                    <!-- Stat Cards -->
                    <div class="row mb-3">
                        <div class="col-sm-6 col-lg-3 mb-2">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="bg-purple text-purple-fg avatar me-3">
                                            <i class="ti ti-file-invoice"></i>
                                        </span>
                                        <div>
                                            <div class="text-muted small">Contrattualizzato</div>
                                            <div class="fw-bold"><?= $formatter->asCurrency($rendiconto['contrattualizzato']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3 mb-2">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="bg-green text-green-fg avatar me-3">
                                            <i class="ti ti-circle-check"></i>
                                        </span>
                                        <div>
                                            <div class="text-muted small">Pagato</div>
                                            <div class="fw-bold"><?= $formatter->asCurrency($rendiconto['pagato']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3 mb-2">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="bg-orange text-orange-fg avatar me-3">
                                            <i class="ti ti-clock"></i>
                                        </span>
                                        <div>
                                            <div class="text-muted small">In Attesa</div>
                                            <div class="fw-bold"><?= $formatter->asCurrency($rendiconto['inAttesa']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3 mb-2">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="bg-orange text-orange-fg avatar me-3">
                                            <i class="ti ti-wallet"></i>
                                        </span>
                                        <div>
                                            <div class="text-muted small">Da Spendere</div>
                                            <div class="fw-bold"><?= $formatter->asCurrency(max(0, $rendiconto['daSpendere'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar Multi-segmento -->
                    <?php if ($rendiconto['contrattualizzato'] > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Avanzamento pagamenti</small>
                            <small class="fw-bold"><?= $percentualePagatoTotale ?>%</small>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-green" style="width: <?= min($percentualePagatoTotale, 100) ?>%" title="Pagato: <?= $formatter->asCurrency($rendiconto['pagato']) ?>"></div>
                            <div class="progress-bar bg-orange" style="width: <?= min($percentualeInAttesa, 100 - min($percentualePagatoTotale, 100)) ?>%" title="In Attesa: <?= $formatter->asCurrency($rendiconto['inAttesa']) ?>"></div>
                        </div>
                        <div class="d-flex gap-3 mt-1">
                            <small><span class="badge bg-green text-green-fg">&nbsp;</span> Pagato</small>
                            <small><span class="badge bg-orange text-orange-fg">&nbsp;</span> In Attesa</small>
                            <small><span class="badge bg-light border">&nbsp;</span> Da Spendere</small>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tabella Dettaglio Moduli -->
                    <?php if (!empty($rendiconto['moduli'])): ?>

                    <!-- Toolbar Liquidazione Massiva -->
                    <div id="bulk-toolbar" class="alert alert-info mb-3" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between">
                            <span>
                                <strong id="bulk-count">0</strong> moduli selezionati -
                                Totale: <strong id="bulk-totale">&euro; 0,00</strong>
                            </span>
                            <button type="button" class="btn btn-green btn-sm" data-bs-toggle="modal" data-bs-target="#modal-liquidazione">
                                <i class="ti ti-cash me-1"></i> Liquida selezionati
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-moduli" title="Seleziona tutti"></th>
                                    <th>Codice</th>
                                    <th>Docente</th>
                                    <th class="text-end">Contrattualizzato</th>
                                    <th class="text-end">Pagato</th>
                                    <th>Ore Rend.</th>
                                    <th style="width: 120px;">%</th>
                                    <th>Stato</th>
                                    <th class="text-end">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rendiconto['moduli'] as $rm): ?>
                                <?php $m = $rm['modulo']; ?>
                                <tr>
                                    <td>
                                        <?php if ($rm['contratto'] && ($rm['saldo_rimanente'] ?? 0) > 0.01): ?>
                                            <input type="checkbox" class="modulo-checkbox" value="<?= $rm['contratto']->id ?>"
                                                   data-saldo="<?= $rm['saldo_rimanente'] ?>"
                                                   data-docente="<?= Html::encode($rm['docente'] ? $rm['docente']->cognome . ' ' . $rm['docente']->nome : '-') ?>"
                                                   data-modulo="<?= Html::encode($m->codice) ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= Html::a('<span class="badge bg-blue-lt">' . Html::encode($m->codice) . '</span>', ['/modulo/view', 'id' => $m->id]) ?></td>
                                    <td>
                                        <?php if ($rm['docente']): ?>
                                            <?= Html::a(Html::encode($rm['docente']->cognome . ' ' . $rm['docente']->nome), ['/docente/view', 'id' => $rm['docente']->codice_fiscale]) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($rm['contrattualizzato']): ?>
                                            <?= $formatter->asCurrency($rm['contrattualizzato']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= $formatter->asCurrency($rm['pagato']) ?></td>
                                    <td>
                                        <?php if (($rm['ore_contrattualizzate'] ?? 0) > 0): ?>
                                            <?php $percOreM = round(($rm['ore_approvate'] / $rm['ore_contrattualizzate']) * 100); ?>
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="progress flex-grow-1" style="height: 4px; width: 50px;">
                                                    <div class="progress-bar <?= $percOreM >= 100 ? 'bg-green' : ($percOreM > 0 ? 'bg-orange' : '') ?>"
                                                         style="width: <?= min($percOreM, 100) ?>%"></div>
                                                </div>
                                                <small class="text-nowrap"><?= $rm['ore_approvate'] ?>/<?= $rm['ore_contrattualizzate'] ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rm['percentuale'] !== null): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar <?= $rm['percentuale'] >= 100 ? 'bg-green' : ($rm['percentuale'] > 0 ? 'bg-orange' : 'bg-default') ?>"
                                                         style="width: <?= min($rm['percentuale'], 100) ?>%"></div>
                                                </div>
                                                <small class="text-nowrap"><?= $rm['percentuale'] ?>%</small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rm['contratto']): ?>
                                            <?php if ($rm['percentuale'] >= 100): ?>
                                                <span class="badge bg-green text-green-fg">Saldato</span>
                                            <?php elseif ($rm['pagato'] > 0): ?>
                                                <span class="badge bg-orange text-orange-fg">Parziale</span>
                                            <?php else: ?>
                                                <span class="badge bg-default text-default-fg">Da pagare</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-default text-default-fg">No contratto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($rm['contratto'] && ($rm['saldo_rimanente'] ?? 0) > 0.01): ?>
                                            <?= Html::a('<i class="ti ti-cash"></i>', ['/pagamento/create', 'contratto_id' => $rm['contratto']->id], ['class' => 'btn btn-sm btn-outline-green', 'title' => 'Nuovo pagamento']) ?>
                                        <?php endif; ?>
                                        <?= Html::a('<i class="ti ti-eye"></i>', ['/modulo/view', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Dettaglio']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td></td>
                                    <td colspan="2">Totale</td>
                                    <td class="text-end"><?= $formatter->asCurrency($rendiconto['contrattualizzato']) ?></td>
                                    <td class="text-end"><?= $formatter->asCurrency($rendiconto['pagato']) ?></td>
                                    <td></td>
                                    <td>
                                        <?php if ($rendiconto['contrattualizzato'] > 0): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-green" style="width: <?= min($percentualePagatoTotale, 100) ?>%"></div>
                                                </div>
                                                <small class="text-nowrap"><?= $percentualePagatoTotale ?>%</small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Modale Conferma Liquidazione Massiva -->
            <div class="modal modal-blur fade" id="modal-liquidazione" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="ti ti-cash me-2"></i>Conferma Liquidazione Massiva</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="modal-lista-docenti"></div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label required">Tipo Pagamento</label>
                                    <select id="bulk-tipo" class="form-select" required>
                                        <option value="">-- Seleziona --</option>
                                        <option value="acconto">Acconto</option>
                                        <option value="saldo">Saldo</option>
                                        <option value="rata">Rata</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stato</label>
                                    <select id="bulk-status" class="form-select">
                                        <option value="programmato">Programmato</option>
                                        <option value="in_attesa">In Attesa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                            <button type="button" id="btn-conferma-liquidazione" class="btn btn-green">
                                <i class="ti ti-check me-1"></i> Conferma Liquidazione
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$liquidaMassivaUrl = Url::to(['/pagamento/liquida-massiva']);
$this->registerJs(<<<JS
    // Checkbox select all
    $('#select-all-moduli').on('change', function() {
        $('.modulo-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkToolbar();
    });

    $('.modulo-checkbox').on('change', updateBulkToolbar);

    function updateBulkToolbar() {
        var checked = $('.modulo-checkbox:checked');
        if (checked.length > 0) {
            var totale = 0;
            checked.each(function() { totale += parseFloat($(this).data('saldo')); });
            $('#bulk-count').text(checked.length);
            $('#bulk-totale').text(new Intl.NumberFormat('it-IT', {style:'currency',currency:'EUR'}).format(totale));
            $('#bulk-toolbar').slideDown(200);
        } else {
            $('#bulk-toolbar').slideUp(200);
        }
    }

    // Popola modale
    $('#modal-liquidazione').on('show.bs.modal', function() {
        var html = '<table class="table table-sm"><thead><tr><th>Docente</th><th>Modulo</th><th class="text-end">Importo</th></tr></thead><tbody>';
        var totale = 0;
        $('.modulo-checkbox:checked').each(function() {
            var saldo = parseFloat($(this).data('saldo'));
            totale += saldo;
            html += '<tr><td>' + $(this).data('docente') + '</td><td>' + $(this).data('modulo') + '</td><td class="text-end">' +
                new Intl.NumberFormat('it-IT',{style:'currency',currency:'EUR'}).format(saldo) + '</td></tr>';
        });
        html += '</tbody><tfoot><tr class="fw-bold"><td colspan="2">Totale</td><td class="text-end">' +
            new Intl.NumberFormat('it-IT',{style:'currency',currency:'EUR'}).format(totale) + '</td></tr></tfoot></table>';
        $('#modal-lista-docenti').html(html);
    });

    // Conferma liquidazione
    $('#btn-conferma-liquidazione').on('click', function() {
        var tipo = $('#bulk-tipo').val();
        if (!tipo) { alert('Seleziona il tipo di pagamento.'); return; }

        var ids = [];
        $('.modulo-checkbox:checked').each(function() { ids.push(parseInt($(this).val())); });

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Elaborazione...');

        $.post('{$liquidaMassivaUrl}', {
            contratti_ids: ids,
            tipo: tipo,
            status: $('#bulk-status').val(),
            _csrf: yii.getCsrfToken()
        }, function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert('Errore: ' + (data.message || 'Errore sconosciuto'));
                btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i> Conferma Liquidazione');
            }
        }, 'json');
    });
JS
);
?>
