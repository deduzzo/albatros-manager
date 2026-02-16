<?php
use yii\helpers\Html;

$this->title = 'Documento: ' . $model->titolo;
$this->params['breadcrumbs'][] = ['label' => 'Documentazione', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documentazione-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-file-text me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questo documento?', 'method' => 'post'],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Stato</div>
                            </div>
                            <div class="mt-2"><span class="badge <?= $model->getStatusBadgeClass() ?> fs-5"><?= $model->getStatusLabel() ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Tipo</div>
                            <div class="mt-2"><span class="badge bg-blue-lt fs-5"><?= Html::encode($model->tipoDocumento->descrizione ?? '-') ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Scadenza</div>
                            <div class="h1 mt-2"><?= $model->data_scadenza ? Yii::$app->formatter->asDate($model->data_scadenza) : '-' ?></div>
                            <?php if ($model->isScaduto()): ?>
                                <span class="badge bg-danger">Scaduto</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Intestatario</div>
                            <div class="h3 mt-2">
                                <?php if ($model->docente): ?>
                                    <i class="ti ti-user me-1"></i><?= Html::a(Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale]) ?>
                                <?php elseif ($model->allievo): ?>
                                    <i class="ti ti-school me-1"></i><?= Html::a(Html::encode($model->allievo->cognome . ' ' . $model->allievo->nome), ['/allievo/view', 'id' => $model->allievo_codice_fiscale]) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dettagli -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli Documento</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID</div>
                            <div class="datagrid-content"><?= $model->id ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Titolo</div>
                            <div class="datagrid-content"><?= Html::encode($model->titolo) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tipo Documento</div>
                            <div class="datagrid-content"><span class="badge bg-blue-lt"><?= Html::encode($model->tipoDocumento->descrizione ?? '-') ?></span></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Docente</div>
                            <div class="datagrid-content">
                                <?= $model->docente
                                    ? Html::a(Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale])
                                    : '<span class="text-muted">-</span>' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Allievo</div>
                            <div class="datagrid-content">
                                <?= $model->allievo
                                    ? Html::a(Html::encode($model->allievo->cognome . ' ' . $model->allievo->nome), ['/allievo/view', 'id' => $model->allievo_codice_fiscale])
                                    : '<span class="text-muted">-</span>' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Descrizione</div>
                            <div class="datagrid-content"><?= Html::encode($model->descrizione) ?: '<span class="text-muted">-</span>' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Data Documento</div>
                            <div class="datagrid-content"><?= $model->data_documento ? Yii::$app->formatter->asDate($model->data_documento) : '-' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Data Scadenza</div>
                            <div class="datagrid-content"><?= $model->data_scadenza ? Yii::$app->formatter->asDate($model->data_scadenza) : '-' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Info -->
            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-paperclip me-2"></i>Informazioni File</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Percorso File</div>
                            <div class="datagrid-content"><code><?= Html::encode($model->file_path) ?></code></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nome Originale</div>
                            <div class="datagrid-content"><?= Html::encode($model->file_nome_originale) ?: '-' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Dimensione</div>
                            <div class="datagrid-content"><?= $model->file_size ? Yii::$app->formatter->asShortSize($model->file_size) : '-' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tipo MIME</div>
                            <div class="datagrid-content"><?= Html::encode($model->file_mime_type) ?: '-' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->note): ?>
            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-notes me-2"></i>Note</h3></div>
                <div class="card-body"><?= nl2br(Html::encode($model->note)) ?></div>
            </div>
            <?php endif; ?>

            <!-- Audit -->
            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-clock me-2"></i>Audit</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Creato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Aggiornato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3"><div class="card-footer"><?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?></div></div>
        </div>
    </div>
</div>
