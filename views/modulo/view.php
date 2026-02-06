<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Modulo */

$this->title = 'Modulo: ' . $model->codice;
$this->params['breadcrumbs'][] = ['label' => 'Moduli/Corsi', 'url' => ['index']];
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
                        <span class="badge bg-success">Aperto per iscrizioni</span>
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
                    <!-- Statistiche -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Statistiche</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-2">
                                <span class="me-auto">Iscrizioni ricevute</span>
                                <span class="badge bg-primary"><?= $model->getIstanzeCount() ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Bando -->
                    <?php if ($model->avviso): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bando di Riferimento</h3>
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
        </div>
    </div>
</div>
