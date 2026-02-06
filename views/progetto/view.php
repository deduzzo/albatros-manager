<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Progetto */

$this->title = 'Progetto: ' . $model->codice_progetto;
$this->params['breadcrumbs'][] = ['label' => 'Progetti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->codice_progetto;
?>

<div class="progetto-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo progetto?',
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
                            <h3 class="card-title">Dettagli Progetto</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered'],
                                'attributes' => [
                                    [
                                        'attribute' => 'codice_progetto',
                                        'format' => 'raw',
                                        'value' => '<span class="badge bg-blue-lt">' . Html::encode($model->codice_progetto) . '</span>',
                                    ],
                                    'titolo',
                                    [
                                        'attribute' => 'fonte_finanziamento_id',
                                        'value' => $model->fonteFinanziamento ? $model->fonteFinanziamento->getNomeCompleto() : '-',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'format' => 'raw',
                                        'value' => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>',
                                    ],
                                    'budget_totale:currency',
                                    'data_inizio:date',
                                    'data_fine:date',
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
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Avvisi/Bandi</h3>
                            <div class="card-actions">
                                <?= Html::a('<i class="ti ti-plus"></i>', ['/avviso/create', 'progetto_id' => $model->id], [
                                    'class' => 'btn btn-sm btn-success',
                                    'title' => 'Nuovo Avviso',
                                ]) ?>
                            </div>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if ($model->avvisi): ?>
                                <?php foreach ($model->avvisi as $avviso): ?>
                                    <a href="<?= \yii\helpers\Url::to(['/avviso/view', 'id' => $avviso->id]) ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?= Html::encode($avviso->codice_avviso) ?></h5>
                                            <span class="badge <?= $avviso->getStatusBadgeClass() ?>"><?= $avviso->getStatusLabel() ?></span>
                                        </div>
                                        <p class="mb-1 text-muted small"><?= Html::encode($avviso->titolo) ?></p>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted">
                                    Nessun avviso collegato
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
