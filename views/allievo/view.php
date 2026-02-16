<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Allievo */

$this->title = $model->getNomeCompleto();
$this->params['breadcrumbs'][] = ['label' => 'Allievi', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$iniziali = strtoupper(mb_substr($model->cognome, 0, 1) . mb_substr($model->nome, 0, 1));
?>

<div class="allievo-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg bg-green-lt" title="<?= Html::encode($model->getNomeCompleto()) ?>">
                        <?= Html::encode($iniziali) ?>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <code><?= Html::encode($model->codice_fiscale) ?></code>
                        <?php if ($model->email): ?>
                            <span class="mx-2">|</span>
                            <i class="ti ti-mail me-1"></i>
                            <?= Html::a($model->email, 'mailto:' . $model->email) ?>
                        <?php endif; ?>
                        <?php if ($model->telefono): ?>
                            <span class="mx-2">|</span>
                            <i class="ti ti-phone me-1"></i>
                            <?= Html::encode($model->telefono) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->codice_fiscale], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->codice_fiscale], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo allievo?',
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
                <!-- Anagrafica -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-user me-2"></i>
                                Dati Anagrafici
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Codice Fiscale</div>
                                    <div class="datagrid-content"><code><?= Html::encode($model->codice_fiscale) ?></code></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nome Completo</div>
                                    <div class="datagrid-content"><?= Html::encode($model->getNomeCompleto()) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data di Nascita</div>
                                    <div class="datagrid-content">
                                        <?= $model->data_nascita ? Yii::$app->formatter->asDate($model->data_nascita, 'long') : '-' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Luogo di Nascita</div>
                                    <div class="datagrid-content"><?= Html::encode($model->luogo_nascita) ?: '-' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contatti e Indirizzo -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-address-book me-2"></i>
                                Contatti e Indirizzo
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Indirizzo</div>
                                    <div class="datagrid-content">
                                        <?php
                                        $indirizzo = array_filter([
                                            $model->indirizzo,
                                            $model->cap,
                                            $model->citta,
                                            $model->provincia ? '(' . $model->provincia . ')' : null,
                                        ]);
                                        echo $indirizzo ? Html::encode(implode(', ', $indirizzo)) : '-';
                                        ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Telefono</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->telefono): ?>
                                            <i class="ti ti-phone me-1"></i>
                                            <?= Html::a($model->telefono, 'tel:' . $model->telefono) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Email</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->email): ?>
                                            <i class="ti ti-mail me-1"></i>
                                            <?= Html::a($model->email, 'mailto:' . $model->email) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Creato il</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ultimo aggiornamento</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asDatetime($model->updated_at) ?>
                                    </div>
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

                <!-- Footer con azioni -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
