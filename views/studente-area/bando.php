<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $avviso app\models\Avviso */
/* @var $moduli app\models\Modulo[] */
/* @var $candidaturaEsistente app\models\Iscrizione|null */

$this->title = $avviso->codice_avviso;
$this->params['breadcrumbs'][] = ['label' => 'Bandi Aperti', 'url' => ['bandi']];
$this->params['breadcrumbs'][] = $this->title;

$colore = $avviso->getTablerColor();
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Header Bando -->
        <div class="card mb-4">
            <div class="card-status-top" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-purple-lt me-2"><?= Html::encode($avviso->codice_avviso) ?></span>
                            <span class="badge bg-green text-green-fg">Aperto</span>
                        </div>
                        <h2><?= Html::encode($avviso->titolo) ?></h2>
                        <?php if ($avviso->sottotitolo): ?>
                            <p class="text-purple mb-2"><?= Html::encode($avviso->sottotitolo) ?></p>
                        <?php endif; ?>
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
                                        <strong class="text-red"><?= Yii::$app->formatter->asDate($avviso->data_scadenza) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($avviso->progetto): ?>
                                    <div class="mb-2">
                                        <span class="text-secondary"><i class="ti ti-folder me-1"></i> Progetto:</span>
                                        <strong><?= Html::encode($avviso->progetto->titolo) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <span class="text-secondary"><i class="ti ti-book me-1"></i> Moduli:</span>
                                    <strong><?= count($moduli) ?></strong>
                                </div>

                                <?php if ($candidaturaEsistente): ?>
                                    <div class="alert alert-success mb-0">
                                        <div class="d-flex">
                                            <div><i class="ti ti-check me-2"></i></div>
                                            <div>
                                                <h4 class="alert-title">Candidatura gia presentata</h4>
                                                <div class="text-secondary">
                                                    Stato: <span class="badge <?= $candidaturaEsistente->getStatusBadgeClass() ?>"><?= $candidaturaEsistente->getStatusLabel() ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?= Html::a('<i class="ti ti-send me-1"></i> Invia Candidatura', ['candidatura', 'id' => $avviso->id], [
                                        'class' => 'btn w-100',
                                        'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;',
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informazioni aggiuntive -->
        <?php
        $obiettivi = $avviso->getJsonArray('obiettivi');
        $prospettive = $avviso->getJsonArray('prospettive');
        $requisiti = $avviso->getJsonArray('requisiti_ammissione');
        $certificazioni = $avviso->getJsonArray('certificazioni');
        $hasExtra = !empty($obiettivi) || !empty($prospettive) || !empty($requisiti) || !empty($certificazioni) || !empty($avviso->destinatari);
        ?>
        <?php if ($hasExtra): ?>
        <div class="row row-cards mb-4">
            <?php if (!empty($obiettivi)): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-target me-2"></i> Obiettivi</h3>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <?php foreach ($obiettivi as $obiettivo): ?>
                                <li><?= Html::encode($obiettivo) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($prospettive)): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-briefcase me-2"></i> Prospettive Professionali</h3>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <?php foreach ($prospettive as $prospettiva): ?>
                                <li><?= Html::encode($prospettiva) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($requisiti)): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-checklist me-2"></i> Requisiti di Ammissione</h3>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <?php foreach ($requisiti as $requisito): ?>
                                <li><?= Html::encode($requisito) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($avviso->destinatari)): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-users me-2"></i> Destinatari</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(Html::encode($avviso->destinatari)) ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Lista Moduli -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-book me-2"></i> Moduli del Corso</h3>
            </div>
            <?php if (empty($moduli)): ?>
                <div class="card-body text-center py-5">
                    <p class="text-secondary">Nessun modulo disponibile per questo bando.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($moduli as $modulo): ?>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar bg-purple-lt"><?= Html::encode(mb_substr($modulo->codice, 0, 2)) ?></span>
                                </div>
                                <div class="col">
                                    <div>
                                        <span class="badge bg-purple-lt me-2"><?= Html::encode($modulo->codice) ?></span>
                                        <strong><?= Html::encode($modulo->titolo) ?></strong>
                                    </div>
                                    <div class="text-secondary mt-1">
                                        <span class="me-3"><i class="ti ti-clock me-1"></i> <?= $modulo->ore_totali ?> ore</span>
                                        <?php if ($modulo->sede): ?>
                                            <span class="me-3"><i class="ti ti-map-pin me-1"></i> <?= Html::encode($modulo->sede->nome) ?></span>
                                        <?php endif; ?>
                                        <?php if ($modulo->data_inizio): ?>
                                            <span><i class="ti ti-calendar me-1"></i> Dal <?= Yii::$app->formatter->asDate($modulo->data_inizio) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($modulo->descrizione): ?>
                                        <p class="text-secondary mt-2 mb-0 small"><?= Html::encode(mb_substr($modulo->descrizione, 0, 200)) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-3 d-flex gap-2">
            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna ai bandi', ['bandi'], ['class' => 'btn btn-secondary']) ?>
            <?php if (!$candidaturaEsistente): ?>
                <?= Html::a('<i class="ti ti-send me-1"></i> Invia Candidatura', ['candidatura', 'id' => $avviso->id], [
                    'class' => 'btn',
                    'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;',
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
