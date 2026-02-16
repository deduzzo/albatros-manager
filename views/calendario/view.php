<?php
use yii\helpers\Html;

$this->title = 'Lezione del ' . Yii::$app->formatter->asDate($model->data);
$this->params['breadcrumbs'][] = ['label' => 'Calendario Lezioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="calendario-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-calendar me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Registra Presenza', ['/presenza-allievo/create', 'calendario_id' => $model->id], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questa lezione?', 'method' => 'post'],
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
                            <div class="subheader">Stato</div>
                            <div class="mt-2"><span class="badge <?= $model->getStatusBadgeClass() ?> fs-5"><?= $model->getStatusLabel() ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Data</div>
                            <div class="h1 mt-2"><?= Yii::$app->formatter->asDate($model->data) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Orario</div>
                            <div class="h1 mt-2"><?= substr($model->ora_inizio, 0, 5) ?> - <?= substr($model->ora_fine, 0, 5) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Ore</div>
                            <div class="h1 mt-2"><?= $model->ore ?>h</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dettagli -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli Lezione</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID</div>
                            <div class="datagrid-content"><?= $model->id ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Modulo</div>
                            <div class="datagrid-content">
                                <?= $model->modulo
                                    ? Html::a(Html::encode($model->modulo->titolo), ['/modulo/view', 'id' => $model->modulo_id])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Docente</div>
                            <div class="datagrid-content">
                                <?= $model->docente
                                    ? Html::a(Html::encode($model->docente->cognome . ' ' . $model->docente->nome), ['/docente/view', 'id' => $model->docente_codice_fiscale])
                                    : '-' ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Aula</div>
                            <div class="datagrid-content"><?= Html::encode($model->aula) ?: '<span class="text-muted">-</span>' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Argomento</div>
                            <div class="datagrid-content"><?= Html::encode($model->argomento) ?: '<span class="text-muted">-</span>' ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Descrizione Attività</div>
                            <div class="datagrid-content"><?= Html::encode($model->descrizione_attivita) ?: '<span class="text-muted">-</span>' ?></div>
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

            <!-- Presenze -->
            <?php $presenze = $model->presenze; ?>
            <?php if (!empty($presenze)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-checkbox me-2"></i>Presenze (<?= count($presenze) ?>)</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Allievo</th>
                                <th>Presenza</th>
                                <th>Ingresso</th>
                                <th>Uscita</th>
                                <th>Ore</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presenze as $presenza): ?>
                            <tr>
                                <td><?= $presenza->allievo ? Html::a(Html::encode($presenza->allievo->cognome . ' ' . $presenza->allievo->nome), ['/allievo/view', 'id' => $presenza->allievo_codice_fiscale]) : '-' ?></td>
                                <td><span class="badge <?= $presenza->getPresenzaBadgeClass() ?>"><?= $presenza->getPresenzaLabel() ?></span></td>
                                <td><?= $presenza->ora_ingresso ? substr($presenza->ora_ingresso, 0, 5) : '-' ?></td>
                                <td><?= $presenza->ora_uscita ? substr($presenza->ora_uscita, 0, 5) : '-' ?></td>
                                <td><?= $presenza->ore_presenza ? $presenza->ore_presenza . 'h' : '-' ?></td>
                                <td class="text-end"><?= Html::a('<i class="ti ti-eye"></i>', ['/presenza-allievo/view', 'id' => $presenza->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div class="card mt-3"><div class="card-footer"><?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?></div></div>
        </div>
    </div>
</div>
