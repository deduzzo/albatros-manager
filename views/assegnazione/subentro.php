<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $assegnazioneRevocata app\models\Assegnazione */
/* @var $modulo app\models\Modulo */
/* @var $idoneiDisponibili app\models\Istanza[] */
/* @var $graduatoria app\models\Istanza[] */

$docenteRevocato = $assegnazioneRevocata->docente;
$docenteRevocatoNome = $docenteRevocato ? $docenteRevocato->getNomeCompleto() : 'N/D';

$this->title = 'Subentro - ' . $modulo->codice;
$this->params['breadcrumbs'][] = ['label' => 'Assegnazioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Assegnazione #' . $assegnazioneRevocata->id, 'url' => ['view', 'id' => $assegnazioneRevocata->id]];
$this->params['breadcrumbs'][] = 'Subentro';
?>

<div class="assegnazione-subentro">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-replace me-2"></i>
                        Subentro Docente
                    </h2>
                    <div class="text-muted mt-1">
                        Modulo: <strong><?= Html::encode($modulo->codice . ' - ' . $modulo->titolo) ?></strong>
                    </div>
                </div>
                <div class="col-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna all\'assegnazione', ['view', 'id' => $assegnazioneRevocata->id], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Riepilogo situazione -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card border-red">
                        <div class="card-status-top bg-red"></div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="avatar avatar-lg bg-red-lt text-red me-3">
                                    <i class="ti ti-user-minus"></i>
                                </span>
                                <div>
                                    <h3 class="mb-0">Docente Revocato</h3>
                                    <div class="text-muted">Assegnazione #<?= $assegnazioneRevocata->id ?></div>
                                </div>
                            </div>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Docente</div>
                                    <div class="datagrid-content">
                                        <strong><?= Html::encode($docenteRevocatoNome) ?></strong>
                                        <br><small class="text-muted"><code><?= Html::encode($assegnazioneRevocata->docente_codice_fiscale) ?></code></small>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Stato</div>
                                    <div class="datagrid-content">
                                        <span class="badge <?= $assegnazioneRevocata->getStatusBadgeClass() ?>"><?= $assegnazioneRevocata->getStatusLabel() ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Assegnazione</div>
                                    <div class="datagrid-content"><?= Yii::$app->formatter->asDate($assegnazioneRevocata->data_assegnazione) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-blue">
                        <div class="card-status-top bg-blue"></div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="avatar avatar-lg bg-blue-lt text-blue me-3">
                                    <i class="ti ti-book"></i>
                                </span>
                                <div>
                                    <h3 class="mb-0"><?= Html::encode($modulo->codice) ?></h3>
                                    <div class="text-muted"><?= Html::encode($modulo->titolo) ?></div>
                                </div>
                            </div>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ore Totali</div>
                                    <div class="datagrid-content"><?= $modulo->ore_totali ?> h</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Compenso Orario</div>
                                    <div class="datagrid-content"><?= $modulo->compenso_orario ? Yii::$app->formatter->asCurrency($modulo->compenso_orario) : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Corso</div>
                                    <div class="datagrid-content"><?= $modulo->avviso ? Html::encode($modulo->avviso->titolo) : '-' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista docenti idonei disponibili -->
            <?php if (!empty($idoneiDisponibili)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-users me-2"></i>
                        Docenti Idonei Disponibili
                        <span class="badge bg-azure-lt ms-2"><?= count($idoneiDisponibili) ?></span>
                    </h3>
                    <div class="card-actions">
                        <span class="text-muted">Ordinati per punteggio decrescente</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th class="w-1">#</th>
                                <th>Docente</th>
                                <th>Provenienza</th>
                                <th class="text-center">Punteggio Titoli</th>
                                <th class="text-center">Punteggio Esperienza</th>
                                <th class="text-center">Punteggio Totale</th>
                                <th>Data Candidatura</th>
                                <th class="text-end">Azione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($idoneiDisponibili as $i => $istanza): ?>
                            <?php $docente = $istanza->docente; ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-azure-lt text-azure me-2">
                                            <?= strtoupper(substr($docente->cognome ?? '?', 0, 1) . substr($docente->nome ?? '?', 0, 1)) ?>
                                        </span>
                                        <div>
                                            <div class="font-weight-medium">
                                                <?= Html::a(Html::encode($docente ? $docente->getNomeCompleto() : 'N/D'), ['/docente/view', 'id' => $istanza->docente_codice_fiscale]) ?>
                                            </div>
                                            <div class="text-muted small"><code><?= Html::encode($istanza->docente_codice_fiscale) ?></code></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($docente && $docente->provenienza): ?>
                                        <span class="badge <?= $docente->provenienza === 'RU' ? 'bg-purple-lt' : 'bg-cyan-lt' ?>">
                                            <?= Html::encode($docente->provenienza) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $istanza->punteggio_titoli !== null ? number_format($istanza->punteggio_titoli, 1) : '-' ?></td>
                                <td class="text-center"><?= $istanza->punteggio_esperienza !== null ? number_format($istanza->punteggio_esperienza, 1) : '-' ?></td>
                                <td class="text-center">
                                    <strong class="text-azure"><?= $istanza->punteggio_totale !== null ? number_format($istanza->punteggio_totale, 1) : '-' ?></strong>
                                </td>
                                <td><?= Yii::$app->formatter->asDate($istanza->data_presentazione) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-green"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-conferma-subentro"
                                            data-istanza-id="<?= $istanza->id ?>"
                                            data-docente-nome="<?= Html::encode($docente ? $docente->getNomeCompleto() : 'N/D') ?>"
                                            data-punteggio="<?= $istanza->punteggio_totale ?>">
                                        <i class="ti ti-user-plus me-1"></i> Assegna
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <span class="avatar avatar-xl bg-orange-lt text-orange mb-3">
                        <i class="ti ti-users-minus"></i>
                    </span>
                    <h3 class="text-muted">Nessun docente idoneo disponibile</h3>
                    <p class="text-secondary mb-3">Non ci sono docenti con esito "Idoneo" per questo modulo che possano subentrare.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna all\'assegnazione', ['view', 'id' => $assegnazioneRevocata->id], ['class' => 'btn btn-secondary']) ?>
                        <?= Html::a('<i class="ti ti-list me-1"></i> Vedi graduatoria completa', ['/istanza/graduatoria', 'modulo_id' => $modulo->id], ['class' => 'btn btn-azure']) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Graduatoria completa di contesto -->
            <?php if (!empty($graduatoria) && count($graduatoria) > count($idoneiDisponibili)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-list-numbers me-2"></i>
                        Graduatoria Completa del Modulo
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th class="w-1">Pos.</th>
                                <th>Docente</th>
                                <th class="text-center">Punteggio</th>
                                <th>Esito CTS</th>
                                <th>Stato</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($graduatoria as $i => $ist): ?>
                            <tr class="<?= $ist->isIdoneo() && $ist->status !== 'rinuncia' ? 'table-azure' : ($ist->isAssegnato() ? 'table-success' : '') ?>">
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <?= Html::encode($ist->docente ? $ist->docente->getNomeCompleto() : $ist->docente_codice_fiscale) ?>
                                </td>
                                <td class="text-center">
                                    <strong><?= $ist->punteggio_totale !== null ? number_format($ist->punteggio_totale, 1) : '-' ?></strong>
                                </td>
                                <td><?= $ist->getEsitoCtsBadgeHtml() ?></td>
                                <td>
                                    <span class="badge <?= $ist->getStatusBadgeClass() ?>"><?= $ist->getStatusLabel() ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Conferma Subentro -->
<div class="modal modal-blur fade" id="modal-conferma-subentro" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?= Html::beginForm(['conferma-subentro', 'id' => $assegnazioneRevocata->id], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-replace me-2"></i>
                    Conferma Subentro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <div class="d-flex">
                        <i class="ti ti-info-circle me-2 mt-1"></i>
                        <div>
                            Stai per assegnare il modulo <strong><?= Html::encode($modulo->codice) ?></strong> a:
                            <div class="mt-1 fs-3" id="subentro-docente-nome"></div>
                            <div class="text-muted" id="subentro-punteggio-info"></div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="istanza_id" id="subentro-istanza-id">
                <div class="mb-3">
                    <label class="form-label">Note (opzionale)</label>
                    <textarea name="note_subentro" class="form-control" rows="3" placeholder="Motivazione del subentro..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="submit" class="btn btn-green">
                    <i class="ti ti-check me-1"></i> Conferma Subentro
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?php
$js = <<<JS
document.getElementById('modal-conferma-subentro').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var istanzaId = button.getAttribute('data-istanza-id');
    var docenteNome = button.getAttribute('data-docente-nome');
    var punteggio = button.getAttribute('data-punteggio');

    document.getElementById('subentro-istanza-id').value = istanzaId;
    document.getElementById('subentro-docente-nome').textContent = docenteNome;
    document.getElementById('subentro-punteggio-info').textContent = punteggio ? 'Punteggio: ' + punteggio : '';
});
JS;
$this->registerJs($js);
?>
