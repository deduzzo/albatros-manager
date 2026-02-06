<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\Istanza;

/* @var $this yii\web\View */
/* @var $model app\models\Istanza */

$this->title = 'Modifica Istanza #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Istanze', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Istanza #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="istanza-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Valutazione e Stato</h3>
                </div>

                <?php $form = AppActiveForm::begin(); ?>

                <div class="card-body">
                    <!-- Info istanza (sola lettura) -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Docente:</strong> <?= $model->docente ? $model->docente->cognome . ' ' . $model->docente->nome : $model->docente_codice_fiscale ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Bando:</strong> <?= $model->avviso ? $model->avviso->codice_avviso : '-' ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Modulo:</strong> <?= $model->modulo ? $model->modulo->codice : '-' ?>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <strong>Data Presentazione:</strong> <?= Yii::$app->formatter->asDate($model->data_presentazione) ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($model, 'status')->dropDownList(Istanza::getStatusLabels()) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'punteggio_titoli')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => 0,
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'punteggio_esperienza')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => 0,
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'posizione_graduatoria')->textInput([
                                'type' => 'number',
                                'min' => 1,
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <?= $form->field($model, 'note_valutazione')->textarea([
                                'rows' => 3,
                                'placeholder' => 'Note sulla valutazione della domanda...',
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <?= $form->field($model, 'note')->textarea([
                                'rows' => 2,
                                'placeholder' => 'Note generali (opzionale)',
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <?= Html::a('<i class="ti ti-x me-1"></i> Annulla', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                    <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva', ['class' => 'btn btn-success ms-2']) ?>
                </div>

                <?php AppActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
