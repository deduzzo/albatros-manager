<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Sede */

$this->title = 'Sede: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Sedi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->nome;
?>

<div class="sede-view">
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
                    <?= Html::a('<i class="ti ti-trash-2 me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questa sede?',
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
                            <h3 class="card-title">Informazioni Sede</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table'],
                                'attributes' => [
                                    'id',
                                    'nome',
                                    [
                                        'label' => 'Indirizzo Completo',
                                        'value' => $model->indirizzoCompleto,
                                    ],
                                    [
                                        'attribute' => 'telefono',
                                        'format' => 'raw',
                                        'value' => $model->telefono ? Html::a($model->telefono, 'tel:' . $model->telefono) : '-',
                                    ],
                                    'email:email',
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
                            <h3 class="card-title">Statistiche</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="bg-blue text-white avatar me-3">
                                    <i class="ti ti-book-open"></i>
                                </span>
                                <div>
                                    <div class="text-muted">Moduli Attivi</div>
                                    <div class="h4 mb-0">0</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="bg-green text-white avatar me-3">
                                    <i class="ti ti-users"></i>
                                </span>
                                <div>
                                    <div class="text-muted">Docenti Assegnati</div>
                                    <div class="h4 mb-0">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
