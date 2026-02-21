<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Assegnazione */

$docenteNome = $model->docente ? $model->docente->cognome . ' ' . $model->docente->nome : 'N/D';
$moduloTitolo = $model->modulo ? $model->modulo->titolo : 'N/D';

$this->title = 'Assegnazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Assegnazioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="assegnazione-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-circle-check me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($docenteNome) ?> &rarr; <?= Html::encode($moduloTitolo) ?>
                    </div>
                </div>
                <div class="col-auto ms-auto d-flex gap-2">
                    <?php if ($model->isRevocabile()): ?>
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-revoca">
                            <i class="ti ti-user-minus me-1"></i> Revoca / Rinuncia
                        </button>
                    <?php endif; ?>
                    <?php if ($model->status === \app\models\Assegnazione::STATUS_REVOCATO && $model->modulo && $model->modulo->getIdoneiDisponibiliCount() > 0): ?>
                        <?= Html::a('<i class="ti ti-replace me-1"></i> Subentro (' . $model->modulo->getIdoneiDisponibiliCount() . ' idonei)', ['subentro', 'id' => $model->id], ['class' => 'btn btn-green']) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questa assegnazione?',
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
                <!-- Dati Assegnazione -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-info-circle me-2"></i>
                                Dettagli Assegnazione
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">ID</div>
                                    <div class="datagrid-content"><?= $model->id ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Stato</div>
                                    <div class="datagrid-content">
                                        <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Assegnazione</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asDate($model->data_assegnazione, 'long') ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Delibera CTS</div>
                                    <div class="datagrid-content"><?= Html::encode($model->delibera_cts) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data Delibera</div>
                                    <div class="datagrid-content">
                                        <?= $model->data_delibera ? Yii::$app->formatter->asDate($model->data_delibera, 'long') : '-' ?>
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

                <!-- Riferimenti -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-link me-2"></i>
                                Riferimenti
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Docente</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->docente): ?>
                                            <?= Html::a(
                                                Html::encode($model->docente->cognome . ' ' . $model->docente->nome),
                                                ['/docente/view', 'id' => $model->docente_codice_fiscale]
                                            ) ?>
                                            <br><small class="text-muted"><code><?= Html::encode($model->docente_codice_fiscale) ?></code></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Modulo</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->modulo): ?>
                                            <?= Html::a(
                                                Html::encode($model->modulo->codice . ' - ' . $model->modulo->titolo),
                                                ['/modulo/view', 'id' => $model->modulo_id]
                                            ) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Istanza</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->istanza): ?>
                                            <?= Html::a(
                                                'Istanza #' . $model->istanza_id,
                                                ['/istanza/view', 'id' => $model->istanza_id]
                                            ) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Contratto</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->contratto): ?>
                                            <?= Html::a(
                                                'Contratto #' . $model->contratto->id . ($model->contratto->numero_contratto ? ' (' . $model->contratto->numero_contratto . ')' : ''),
                                                ['/contratto/view', 'id' => $model->contratto->id]
                                            ) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Nessun contratto associato</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer con azioni -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer d-flex gap-2">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                            <?php if ($model->modulo): ?>
                                <?= Html::a('<i class="ti ti-list-numbers me-1"></i> Graduatoria modulo', ['/istanza/graduatoria', 'modulo_id' => $model->modulo_id], ['class' => 'btn btn-azure']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Revoca -->
<?php if ($model->isRevocabile()): ?>
<div class="modal modal-blur fade" id="modal-revoca" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?= Html::beginForm(['revoca', 'id' => $model->id], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-user-minus me-2 text-warning"></i>
                    Revoca Assegnazione
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <div class="d-flex">
                        <i class="ti ti-alert-triangle me-2 mt-1"></i>
                        <div>
                            Stai per revocare l'assegnazione di <strong><?= Html::encode($docenteNome) ?></strong>
                            al modulo <strong><?= Html::encode($moduloTitolo) ?></strong>.
                            <?php $idoneiCount = $model->modulo ? $model->modulo->getIdoneiDisponibiliCount() : 0; ?>
                            <?php if ($idoneiCount > 0): ?>
                                <br><span class="text-azure">Ci sono <strong><?= $idoneiCount ?></strong> docenti idonei disponibili per il subentro.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo_revoca" class="form-select">
                        <option value="rinuncia">Rinuncia del docente</option>
                        <option value="subentro">Revoca amministrativa</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivazione</label>
                    <textarea name="note_revoca" class="form-control" rows="3" placeholder="Inserisci la motivazione della revoca..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="submit" class="btn btn-warning">
                    <i class="ti ti-user-minus me-1"></i> Conferma Revoca
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php endif; ?>
