<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Modulo */

$this->title = 'Modulo: ' . $model->codice;
$this->params['breadcrumbs'][] = ['label' => 'Moduli/Insegnamenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->codice;
?>

<div class="modulo-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <?php if ($model->isApertoIscrizioni()): ?>
                        <span class="badge bg-green text-green-fg">Aperto per iscrizioni</span>
                    <?php endif; ?>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo modulo?',
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
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dettagli Modulo</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered'],
                                'attributes' => [
                                    [
                                        'attribute' => 'codice',
                                        'format' => 'raw',
                                        'value' => '<span class="badge bg-blue-lt">' . Html::encode($model->codice) . '</span>',
                                    ],
                                    'titolo',
                                    [
                                        'attribute' => 'avviso_id',
                                        'format' => 'raw',
                                        'value' => $model->avviso
                                            ? Html::a($model->avviso->codice_avviso . ' - ' . $model->avviso->titolo, ['/avviso/view', 'id' => $model->avviso_id])
                                            : '-',
                                    ],
                                    [
                                        'attribute' => 'sede_id',
                                        'value' => $model->sede ? $model->sede->nome . ' (' . $model->sede->citta . ')' : '-',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'format' => 'raw',
                                        'value' => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>',
                                    ],
                                    'ore_totali',
                                    'compenso_orario:currency',
                                    [
                                        'label' => 'Compenso Totale',
                                        'value' => $model->getCompensoTotale(),
                                        'format' => 'currency',
                                    ],
                                    'max_allievi',
                                    'data_inizio:date',
                                    'data_fine:date',
                                    'descrizione:ntext',
                                    'requisiti_docente:ntext',
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
                </div>

                <div class="col-lg-4">
                    <!-- Docente Assegnato -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-user-check me-2"></i>Docente Assegnato</h3>
                        </div>
                        <?php
                            // Assegnazione attiva (non revocata)
                            $assegnazione = $model->getAssegnazioni()
                                ->andWhere(['!=', 'status', \app\models\Assegnazione::STATUS_REVOCATO])
                                ->one();
                            $docenteAssegnato = $assegnazione ? $assegnazione->docente : null;

                            // Storico completo (tutte le assegnazioni, incluse revocate)
                            $tutteAssegnazioni = $model->getAssegnazioni()
                                ->with(['docente'])
                                ->orderBy(['data_assegnazione' => SORT_DESC])
                                ->all();
                            $assegnazioniRevocate = array_filter($tutteAssegnazioni, fn($a) => $a->status === \app\models\Assegnazione::STATUS_REVOCATO);
                        ?>
                        <?php if ($docenteAssegnato): ?>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <span class="avatar bg-blue-lt me-3"><i class="ti ti-user"></i></span>
                                <div>
                                    <strong><?= Html::a(Html::encode($docenteAssegnato->cognome . ' ' . $docenteAssegnato->nome), ['/docente/view', 'id' => $docenteAssegnato->codice_fiscale]) ?></strong>
                                    <br><small class="text-muted"><?= Html::encode($docenteAssegnato->codice_fiscale) ?></small>
                                </div>
                            </div>
                            <div class="d-flex mb-1">
                                <span class="me-auto">Provenienza</span>
                                <span class="badge bg-cyan-lt"><?= $docenteAssegnato->provenienza === 'RU' ? 'Ricercatore Univ.' : 'Professionista' ?></span>
                            </div>
                            <div class="d-flex mb-1">
                                <span class="me-auto">Data assegnazione</span>
                                <span><?= Yii::$app->formatter->asDate($assegnazione->data_assegnazione) ?></span>
                            </div>
                            <div class="d-flex mb-1">
                                <span class="me-auto">Stato</span>
                                <span class="badge <?= $assegnazione->getStatusBadgeClass() ?>"><?= $assegnazione->getStatusLabel() ?></span>
                            </div>
                            <?php if ($assegnazione->contratto): ?>
                            <div class="d-flex mb-1">
                                <span class="me-auto">Contratto</span>
                                <?= Html::a('<span class="badge bg-green-lt">' . Html::encode($assegnazione->contratto->numero_contratto ?: 'Visualizza') . '</span>', ['/contratto/view', 'id' => $assegnazione->contratto->id]) ?>
                            </div>
                            <?php endif; ?>
                            <div class="mt-3 d-flex gap-2">
                                <?= Html::a('<i class="ti ti-eye me-1"></i> Dettaglio', ['/assegnazione/view', 'id' => $assegnazione->id], ['class' => 'btn btn-sm btn-outline-blue']) ?>
                                <?= Html::a('<i class="ti ti-user-minus me-1"></i> Revoca / Sostituisci', ['/assegnazione/view', 'id' => $assegnazione->id, '#' => 'revoca'], ['class' => 'btn btn-sm btn-outline-red']) ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card-body text-center">
                            <i class="ti ti-user-off mb-3 text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-3">Nessun docente assegnato</p>
                            <div class="d-flex flex-column gap-2">
                                <?= Html::a(
                                    '<i class="ti ti-search me-1"></i> Cerca nell\'Albo e Assegna',
                                    ['/docente/albo', 'modulo_id' => $model->id],
                                    ['class' => 'btn btn-primary']
                                ) ?>
                                <?= Html::a(
                                    '<i class="ti ti-plus me-1"></i> Assegna direttamente',
                                    ['/assegnazione/create', 'modulo_id' => $model->id],
                                    ['class' => 'btn btn-outline-blue']
                                ) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Storico Assegnazioni -->
                    <?php if (!empty($assegnazioniRevocate)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-history me-2"></i>Storico Docenti Precedenti</h3>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($assegnazioniRevocate as $assPrec): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm bg-default me-2"><i class="ti ti-user"></i></span>
                                    <div class="flex-fill">
                                        <?php if ($assPrec->docente): ?>
                                            <strong><?= Html::a(Html::encode($assPrec->docente->cognome . ' ' . $assPrec->docente->nome), ['/docente/view', 'id' => $assPrec->docente_codice_fiscale]) ?></strong>
                                        <?php else: ?>
                                            <strong><?= Html::encode($assPrec->docente_codice_fiscale) ?></strong>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= Yii::$app->formatter->asDate($assPrec->data_assegnazione) ?>
                                            — <span class="badge bg-red-lt">Revocato</span>
                                        </small>
                                        <?php if ($assPrec->note): ?>
                                            <br><small class="text-muted"><i class="ti ti-note me-1"></i><?= Html::encode($assPrec->note) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?= Html::a('<i class="ti ti-eye"></i>', ['/assegnazione/view', 'id' => $assPrec->id], ['class' => 'btn btn-sm btn-outline-default', 'title' => 'Dettaglio']) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Statistiche -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-chart-bar me-2"></i>Statistiche</h3>
                        </div>
                        <div class="card-body">
                            <a href="<?= \yii\helpers\Url::to(['/istanza/index', 'IstanzaSearch[modulo_id]' => $model->id]) ?>" class="d-flex mb-2 text-decoration-none">
                                <span class="me-auto">Candidature ricevute</span>
                                <span class="badge bg-blue text-blue-fg"><?= $model->getIstanzeCount() ?></span>
                            </a>
                            <a href="<?= \yii\helpers\Url::to(['/assegnazione/index', 'AssegnazioneSearch[modulo_id]' => $model->id]) ?>" class="d-flex mb-2 text-decoration-none">
                                <span class="me-auto">Assegnazioni</span>
                                <span class="badge bg-green text-green-fg"><?= count($model->assegnazioni) ?></span>
                            </a>
                        </div>
                    </div>

                    <!-- Info Bando -->
                    <?php if ($model->avviso): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-speakerphone me-2"></i>Corso di Riferimento</h3>
                        </div>
                        <div class="card-body">
                            <h4><?= Html::a($model->avviso->codice_avviso, ['/avviso/view', 'id' => $model->avviso_id]) ?></h4>
                            <p class="text-muted"><?= Html::encode($model->avviso->titolo) ?></p>
                            <div>
                                <span class="badge <?= $model->avviso->getStatusBadgeClass() ?>"><?= $model->avviso->getStatusLabel() ?></span>
                                <?php if ($model->avviso->data_scadenza): ?>
                                    <br><small class="text-muted">Scadenza: <?= Yii::$app->formatter->asDate($model->avviso->data_scadenza) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabella Candidature/Istanze -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-file-description me-2"></i>Candidature Docenti</h3>
                </div>
                <?php $istanze = $model->istanze; ?>
                <?php if ($istanze): ?>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>Provenienza</th>
                                <th>Punteggio</th>
                                <th>Storico Esiti</th>
                                <th>Stato Attuale</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($istanze as $istanza): ?>
                            <tr>
                                <td>
                                    <?php if ($istanza->docente): ?>
                                        <?= Html::a('<i class="ti ti-user me-1"></i>' . Html::encode($istanza->docente->cognome . ' ' . $istanza->docente->nome), ['/docente/view', 'id' => $istanza->docente_codice_fiscale]) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($istanza->docente): ?>
                                        <span class="badge bg-cyan-lt"><?= $istanza->docente->provenienza === 'RU' ? 'RU' : 'TP' ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $istanza->punteggio ?? '-' ?></td>
                                <td>
                                    <?php foreach ($istanza->storicoEsiti as $esito): ?>
                                        <span class="badge <?= $esito->getEsitoBadgeClass() ?> me-1" title="<?= $esito->data_esito ? Yii::$app->formatter->asDate($esito->data_esito) : '' ?>">
                                            <i class="<?= $esito->getEsitoIconClass() ?> me-1"></i><?= $esito->getEsitoLabel() ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (empty($istanza->storicoEsiti)): ?>
                                        <?= $istanza->getEsitoCtsBadgeHtml() ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $istanza->getStatusBadgeClass() ?>"><?= $istanza->getStatusLabel() ?></span></td>
                                <td class="text-end">
                                    <?= Html::a('<i class="ti ti-eye"></i>', ['/istanza/view', 'id' => $istanza->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Dettaglio']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="card-body text-muted text-center">
                    <i class="ti ti-file-off mb-2" style="font-size: 2rem;"></i>
                    <p>Nessuna candidatura ricevuta per questo modulo</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sezione Pagamenti -->
            <?php
                $assegnazioneContratto = $model->getAssegnazioni()
                    ->andWhere(['!=', 'status', \app\models\Assegnazione::STATUS_REVOCATO])
                    ->one();
                $contrattoAttivo = $assegnazioneContratto ? $assegnazioneContratto->contratto : null;
            ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-cash me-2"></i>Pagamenti</h3>
                    <?php if ($contrattoAttivo): ?>
                    <div class="card-actions">
                        <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Pagamento', ['/pagamento/create', 'contratto_id' => $contrattoAttivo->id], [
                            'class' => 'btn btn-sm btn-success',
                        ]) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($contrattoAttivo): ?>
                <?php
                    $compensoContrattualizzato = $contrattoAttivo->compenso_totale;
                    $totalePagatoModulo = $contrattoAttivo->getTotalePagato();
                    $rimanente = $contrattoAttivo->getSaldoRimanente();
                    $percPagato = ($compensoContrattualizzato > 0) ? round(($totalePagatoModulo / $compensoContrattualizzato) * 100) : 0;

                    // Calcolo ore per alert
                    $assOre = $model->getAssegnazioni()->one();
                    $oreContrattoVal = $contrattoAttivo->ore_contrattualizzate;
                    $oreApprVal = $assOre ? \app\models\RendicontazioneOre::getTotaleOreApprovate($assOre->id) : 0;
                    $percOreVal = ($oreContrattoVal > 0) ? round(($oreApprVal / $oreContrattoVal) * 100) : 0;
                    $importoOreRendicontate = $oreApprVal * $contrattoAttivo->compenso_orario;
                ?>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-blue-lt me-3"><i class="ti ti-file-invoice"></i></span>
                                        <div>
                                            <div class="text-muted small">Contrattualizzato</div>
                                            <div class="fw-bold"><?= Yii::$app->formatter->asCurrency($compensoContrattualizzato) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-green-lt me-3"><i class="ti ti-cash"></i></span>
                                        <div>
                                            <div class="text-muted small">Pagato</div>
                                            <div class="fw-bold text-success"><?= Yii::$app->formatter->asCurrency($totalePagatoModulo) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar bg-orange-lt me-3"><i class="ti ti-clock-dollar"></i></span>
                                        <div>
                                            <div class="text-muted small">Rimanente</div>
                                            <div class="fw-bold <?= $rimanente > 0 ? 'text-warning' : 'text-success' ?>"><?= Yii::$app->formatter->asCurrency($rimanente) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Avanzamento pagamenti</small>
                            <small class="fw-bold"><?= $percPagato ?>%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar <?= $percPagato >= 100 ? 'bg-green' : ($percPagato > 0 ? 'bg-orange' : 'bg-default') ?>"
                                 style="width: <?= min($percPagato, 100) ?>%"></div>
                        </div>
                    </div>

                    <!-- Alert Ore Rendicontate -->
                    <?php if ($oreContrattoVal > 0 && $oreApprVal < $oreContrattoVal): ?>
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-alert-triangle me-2" style="font-size: 1.5rem;"></i>
                            <div class="flex-fill">
                                <strong>Ore non completamente rendicontate:</strong>
                                <?= number_format($oreApprVal, 1, ',', '.') ?>/<?= $oreContrattoVal ?> ore (<?= $percOreVal ?>%)
                            </div>
                        </div>
                        <div class="mt-2">
                            <?= Html::a(
                                '<i class="ti ti-clock-dollar me-1"></i> Paga solo ore rendicontate come acconto',
                                ['/pagamento/create',
                                    'contratto_id' => $contrattoAttivo->id,
                                    'tipo' => 'acconto',
                                    'importo' => $importoOreRendicontate,
                                ],
                                ['class' => 'btn btn-sm btn-green']
                            ) ?>
                        </div>
                    </div>
                    <?php elseif ($oreContrattoVal > 0): ?>
                    <div class="alert alert-success mb-3">
                        <i class="ti ti-circle-check me-2"></i>
                        <strong>Ore completamente rendicontate:</strong>
                        <?= number_format($oreApprVal, 1, ',', '.') ?>/<?= $oreContrattoVal ?> ore
                    </div>
                    <?php endif; ?>

                    <?php $pagamenti = $contrattoAttivo->pagamenti; ?>
                    <?php if ($pagamenti): ?>
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Importo Lordo</th>
                                    <th class="text-end">Ritenute</th>
                                    <th class="text-end">Netto</th>
                                    <th>Stato</th>
                                    <th>Note</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagamenti as $pagamento): ?>
                                <tr>
                                    <td><?= $pagamento->data_pagamento ? Yii::$app->formatter->asDate($pagamento->data_pagamento) : '-' ?></td>
                                    <td><span class="badge bg-cyan-lt"><?= $pagamento->getTipoLabel() ?></span></td>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($pagamento->importo_lordo) ?></td>
                                    <td class="text-end"><?= $pagamento->ritenute ? Yii::$app->formatter->asCurrency($pagamento->ritenute) : '-' ?></td>
                                    <td class="text-end"><strong><?= Yii::$app->formatter->asCurrency($pagamento->importo_netto) ?></strong></td>
                                    <td><span class="badge <?= $pagamento->getStatusBadgeClass() ?>"><?= $pagamento->getStatusLabel() ?></span></td>
                                    <td><small class="text-muted"><?= Html::encode($pagamento->note) ?></small></td>
                                    <td class="text-end">
                                        <?= Html::a('<i class="ti ti-eye"></i>', ['/pagamento/view', 'id' => $pagamento->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Dettaglio']) ?>
                                        <?= Html::a('<i class="ti ti-edit"></i>', ['/pagamento/update', 'id' => $pagamento->id], ['class' => 'btn btn-sm btn-outline-blue', 'title' => 'Modifica']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-muted text-center py-3">
                        <i class="ti ti-receipt-off mb-2" style="font-size: 1.5rem;"></i>
                        <p class="mb-0">Nessun pagamento registrato</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="card-body text-muted text-center">
                    <i class="ti ti-file-off mb-2" style="font-size: 2rem;"></i>
                    <p>Nessun contratto attivo per questo modulo</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sezione Rendicontazione Ore -->
            <?php if ($assegnazioneContratto): ?>
            <?php
                $oreContrattualizzate = $contrattoAttivo ? $contrattoAttivo->ore_contrattualizzate : 0;
                $oreApprovate = \app\models\RendicontazioneOre::getTotaleOreApprovate($assegnazioneContratto->id);
                $oreInserite = \app\models\RendicontazioneOre::getTotaleOreInserite($assegnazioneContratto->id);
                $percOre = ($oreContrattualizzate > 0) ? round(($oreApprovate / $oreContrattualizzate) * 100) : 0;
            ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-clock-hour-4 me-2"></i>Rendicontazione Ore</h3>
                    <div class="card-actions">
                        <?= Html::a('<i class="ti ti-list me-1"></i> Dettaglio Ore', ['/rendicontazione-ore/index', 'assegnazione_id' => $assegnazioneContratto->id], [
                            'class' => 'btn btn-sm btn-primary',
                        ]) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm bg-green-lt me-2"><i class="ti ti-circle-check"></i></span>
                                <div>
                                    <div class="text-muted small">Approvate</div>
                                    <div class="fw-bold"><?= number_format($oreApprovate, 1, ',', '.') ?> / <?= $oreContrattualizzate ?> ore</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm bg-yellow-lt me-2"><i class="ti ti-clock"></i></span>
                                <div>
                                    <div class="text-muted small">In Attesa</div>
                                    <div class="fw-bold text-warning"><?= number_format($oreInserite, 1, ',', '.') ?> ore</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm bg-blue-lt me-2"><i class="ti ti-percentage"></i></span>
                                <div>
                                    <div class="text-muted small">Completamento</div>
                                    <div class="fw-bold"><?= $percOre ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $percOre > 100 ? 'bg-red' : ($percOre >= 100 ? 'bg-green' : 'bg-blue') ?>"
                             style="width: <?= min($percOre, 100) ?>%"></div>
                    </div>
                    <?php if ($oreApprovate > $oreContrattualizzate && $oreContrattualizzate > 0): ?>
                    <div class="mt-2">
                        <span class="badge bg-red text-red-fg"><i class="ti ti-alert-triangle me-1"></i>Ore Superiori: +<?= number_format($oreApprovate - $oreContrattualizzate, 1, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
