<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $avviso app\models\Avviso */
/* @var $moduli app\models\Modulo[] */
/* @var $iscrizioniEffettuate array */

$this->title = $avviso->codice_avviso;
$this->params['breadcrumbs'][] = ['label' => 'Bandi Aperti', 'url' => ['bandi']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Header Bando -->
        <div class="card mb-4">
            <div class="card-status-top bg-success"></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-blue-lt me-2"><?= Html::encode($avviso->codice_avviso) ?></span>
                            <span class="badge bg-success">Aperto</span>
                        </div>
                        <h2><?= Html::encode($avviso->titolo) ?></h2>
                        <?php if ($avviso->descrizione): ?>
                            <p class="text-secondary mt-3"><?= nl2br(Html::encode($avviso->descrizione)) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <?php if ($avviso->data_scadenza): ?>
                                    <div class="mb-2">
                                        <span class="text-secondary"><i class="ti ti-calendar me-1"></i> Scadenza:</span>
                                        <strong class="text-danger"><?= Yii::$app->formatter->asDate($avviso->data_scadenza) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($avviso->progetto): ?>
                                    <div class="mb-2">
                                        <span class="text-secondary"><i class="ti ti-folder me-1"></i> Progetto:</span>
                                        <strong><?= Html::encode($avviso->progetto->titolo) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="text-secondary"><i class="ti ti-book me-1"></i> Moduli disponibili:</span>
                                    <strong><?= count($moduli) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documenti Richiesti -->
        <?php if (!empty($avviso->documentiRichiesti)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-file-upload me-2"></i> Documenti Richiesti per l'Iscrizione</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Formati ammessi</th>
                                    <th>Dimensione max</th>
                                    <th>Obbligatorio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($avviso->documentiRichiesti as $doc): ?>
                                    <tr>
                                        <td>
                                            <strong><?= Html::encode($doc->nome) ?></strong>
                                            <?php if ($doc->descrizione): ?>
                                                <br><small class="text-secondary"><?= Html::encode($doc->descrizione) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= Html::encode($doc->formati_ammessi) ?></code></td>
                                        <td><?= $doc->dimensione_max_mb ?> MB</td>
                                        <td>
                                            <?php if ($doc->obbligatorio): ?>
                                                <span class="badge bg-red">Obbligatorio</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Facoltativo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Lista Moduli -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-book me-2"></i> Moduli Disponibili</h3>
            </div>
            <?php if (empty($moduli)): ?>
                <div class="card-body text-center py-5">
                    <p class="text-secondary">Nessun modulo disponibile per le iscrizioni.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($moduli as $modulo): ?>
                        <?php $giaIscritto = isset($iscrizioniEffettuate[$modulo->id]); ?>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar bg-blue-lt"><?= Html::encode(mb_substr($modulo->codice, 0, 2)) ?></span>
                                </div>
                                <div class="col">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="badge bg-blue-lt me-2"><?= Html::encode($modulo->codice) ?></span>
                                            <strong><?= Html::encode($modulo->titolo) ?></strong>
                                        </div>
                                        <?php if ($giaIscritto): ?>
                                            <span class="badge bg-success"><i class="ti ti-check me-1"></i> Iscrizione inviata</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-secondary mt-1">
                                        <span class="me-3"><i class="ti ti-clock me-1"></i> <?= $modulo->ore_totali ?> ore</span>
                                        <?php if ($modulo->compenso_orario): ?>
                                            <span class="me-3"><i class="ti ti-currency-euro me-1"></i> <?= Yii::$app->formatter->asCurrency($modulo->compenso_orario) ?>/ora</span>
                                        <?php endif; ?>
                                        <?php if ($modulo->sede): ?>
                                            <span><i class="ti ti-map-pin me-1"></i> <?= Html::encode($modulo->sede->nome) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($modulo->descrizione): ?>
                                        <p class="text-secondary mt-2 mb-0 small"><?= Html::encode(mb_substr($modulo->descrizione, 0, 200)) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto">
                                    <?php if ($giaIscritto): ?>
                                        <span class="btn btn-ghost-success disabled">
                                            <i class="ti ti-check me-1"></i> Già iscritto
                                        </span>
                                    <?php else: ?>
                                        <?= Html::a('<i class="ti ti-send me-1"></i> Iscriviti', ['iscrizione', 'moduloId' => $modulo->id], ['class' => 'btn btn-primary']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-3">
            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna ai bandi', ['bandi'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
</div>
