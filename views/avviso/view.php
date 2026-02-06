<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Avviso */

$this->title = 'Avviso: ' . $model->codice_avviso;
$this->params['breadcrumbs'][] = ['label' => 'Avvisi/Bandi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->codice_avviso;
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
                        <span class="badge bg-success">Aperto per iscrizioni</span>
                    <?php endif; ?>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-file-plus me-1"></i> Documenti Richiesti', ['documenti', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo avviso?',
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
                            <h3 class="card-title">Dettagli Avviso</h3>
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
                                                        <span class="badge bg-danger">Sì</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">No</span>
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
                    <!-- Moduli -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Moduli/Corsi</h3>
                            <div class="card-actions">
                                <?= Html::a('<i class="ti ti-plus"></i>', ['/modulo/create', 'avviso_id' => $model->id], [
                                    'class' => 'btn btn-sm btn-success',
                                    'title' => 'Nuovo Modulo',
                                ]) ?>
                            </div>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if ($model->moduli): ?>
                                <?php foreach ($model->moduli as $modulo): ?>
                                    <a href="<?= \yii\helpers\Url::to(['/modulo/view', 'id' => $modulo->id]) ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?= Html::encode($modulo->codice) ?></h5>
                                            <span class="badge <?= $modulo->getStatusBadgeClass() ?>"><?= $modulo->getStatusLabel() ?></span>
                                        </div>
                                        <p class="mb-1 text-muted small"><?= Html::encode($modulo->titolo) ?></p>
                                        <small class="text-muted"><?= $modulo->ore_totali ?> ore - <?= Yii::$app->formatter->asCurrency($modulo->compenso_orario) ?>/h</small>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted">
                                    Nessun modulo collegato
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
