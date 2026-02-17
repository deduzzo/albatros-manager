<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $avviso app\models\Avviso */
/* @var $documenti app\models\AvvisoDocumentoTipo[] */
/* @var $nuovoDocumento app\models\AvvisoDocumentoTipo */

$this->title = 'Documenti Richiesti: ' . $avviso->codice_avviso;
$this->params['breadcrumbs'][] = ['label' => 'Avvisi/Bandi', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $avviso->codice_avviso, 'url' => ['view', 'id' => $avviso->id]];
$this->params['breadcrumbs'][] = 'Documenti Richiesti';
?>

<div class="avviso-documenti">
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
            <div class="row">
                <div class="col-lg-8">
                    <!-- Lista documenti esistenti -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Documenti Configurati</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Formati</th>
                                        <th>Max</th>
                                        <th>Obbligatorio</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($documenti): ?>
                                        <?php foreach ($documenti as $doc): ?>
                                            <tr>
                                                <td><?= $doc->ordine ?></td>
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
                                                        <span class="badge bg-red text-red-fg">Sì</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-default text-default-fg">No</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= Html::a('<i class="ti ti-trash"></i>', ['delete-documento', 'id' => $doc->id], [
                                                        'class' => 'btn btn-sm btn-danger',
                                                        'title' => 'Elimina',
                                                        'data' => [
                                                            'confirm' => 'Sei sicuro di voler eliminare questo documento richiesto?',
                                                            'method' => 'post',
                                                        ],
                                                    ]) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-muted text-center">
                                                Nessun documento configurato. Aggiungi il primo usando il form a destra.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna all\'avviso', ['view', 'id' => $avviso->id], ['class' => 'btn btn-secondary']) ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Form nuovo documento -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Aggiungi Documento</h3>
                        </div>

                        <?php $form = AppActiveForm::begin(); ?>

                        <div class="card-body">
                            <?= $form->field($nuovoDocumento, 'nome')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Es: Curriculum Vitae',
                            ]) ?>

                            <?= $form->field($nuovoDocumento, 'descrizione')->textarea([
                                'rows' => 2,
                                'placeholder' => 'Descrizione opzionale',
                            ]) ?>

                            <?= $form->field($nuovoDocumento, 'formati_ammessi')->textInput([
                                'placeholder' => 'pdf,doc,docx',
                            ])->hint('Separati da virgola') ?>

                            <?= $form->field($nuovoDocumento, 'dimensione_max_mb')->textInput([
                                'type' => 'number',
                                'min' => 1,
                                'max' => 50,
                            ]) ?>

                            <?= $form->field($nuovoDocumento, 'ordine')->textInput([
                                'type' => 'number',
                                'min' => 0,
                            ]) ?>

                            <?= $form->field($nuovoDocumento, 'obbligatorio')->checkbox() ?>
                        </div>

                        <div class="card-footer">
                            <?= Html::submitButton('<i class="ti ti-plus me-1"></i> Aggiungi', ['class' => 'btn btn-success w-100']) ?>
                        </div>

                        <?php AppActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
