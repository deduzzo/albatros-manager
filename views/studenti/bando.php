<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $bando app\models\Avviso */

$this->title = $bando->titolo;
$tc = $bando->getTablerColor(); // Nome colore Tabler (blue, green, purple, ecc.)
$obiettivi = $bando->getJsonArray('obiettivi');
$prospettive = $bando->getJsonArray('prospettive');
$requisiti = $bando->getJsonArray('requisiti_ammissione');
$certificazioni = $bando->getJsonArray('certificazioni');
$moduli = $bando->moduli;
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Breadcrumb -->
        <div class="page-pretitle mb-2">
            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Tutti i Corsi', ['index'], ['class' => 'text-decoration-none']) ?>
        </div>

        <!-- Header Corso -->
        <div class="card mb-4 bg-<?= $tc ?> text-<?= $tc ?>-fg">
            <div class="card-body py-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-dark mb-3 fs-5">
                            <?= Html::encode($bando->codice_avviso) ?>
                        </span>
                        <h1 class="display-6 fw-bold mb-2"><?= Html::encode($bando->titolo) ?></h1>
                        <?php if ($bando->sottotitolo): ?>
                        <p class="lead mb-0 opacity-75"><?= Html::encode($bando->sottotitolo) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                        <div class="d-inline-block bg-white bg-opacity-25 rounded-3 p-3">
                            <div class="small opacity-75">Scadenza Iscrizioni</div>
                            <div class="fs-2 fw-bold">
                                <i class="ti ti-calendar me-2"></i>
                                <?= Yii::$app->formatter->asDate($bando->data_scadenza) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Contenuto Principale -->
            <div class="col-lg-8">
                <!-- Descrizione -->
                <?php if ($bando->descrizione): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-info-circle me-2 text-<?= $tc ?>"></i>
                            Descrizione del Programma
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="fs-4"><?= Html::encode($bando->descrizione) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Obiettivi -->
                <?php if (!empty($obiettivi)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-target me-2 text-<?= $tc ?>"></i>
                            Obiettivi del Programma
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($obiettivi as $i => $obiettivo): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <span class="avatar avatar-sm me-3 bg-<?= $tc ?> text-<?= $tc ?>-fg">
                                            <?= $i + 1 ?>
                                        </span>
                                        <div class="pt-1"><?= Html::encode($obiettivo) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Prospettive -->
                <?php if (!empty($prospettive)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-rocket me-2 text-<?= $tc ?>"></i>
                            Prospettive Professionali
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($prospettive as $prospettiva): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-arrow-right me-2 text-<?= $tc ?>"></i>
                                        <span><?= Html::encode($prospettiva) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Destinatari -->
                <?php if ($bando->destinatari): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-users me-2 text-<?= $tc ?>"></i>
                            A Chi è Rivolto
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            <?= Html::encode($bando->destinatari) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Requisiti -->
                <?php if (!empty($requisiti)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-checklist me-2 text-<?= $tc ?>"></i>
                            Requisiti di Ammissione
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($requisiti as $requisito): ?>
                                <li class="mb-2 d-flex">
                                    <i class="ti ti-check text-green me-2 mt-1"></i>
                                    <span><?= Html::encode($requisito) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Certificazioni -->
                <?php if (!empty($certificazioni)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-certificate me-2 text-<?= $tc ?>"></i>
                            Certificazioni Rilasciate
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($certificazioni as $cert): ?>
                                <span class="badge bg-orange-lt fs-5 py-2 px-3">
                                    <i class="ti ti-award me-1"></i><?= Html::encode($cert) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Info Box -->
                <div class="card mb-4 sticky-top" style="top: 1rem;">
                    <div class="card-header bg-<?= $tc ?>-lt">
                        <h3 class="card-title">
                            <i class="ti ti-info-square me-2"></i>
                            Informazioni
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-secondary small">Fonte di Finanziamento</div>
                            <div class="fw-bold"><?= Html::encode($bando->progetto->fonteFinanziamento->descrizione ?? '-') ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary small">Scadenza Iscrizioni</div>
                            <span class="badge bg-red text-red-fg fs-5">
                                <i class="ti ti-calendar me-1"></i>
                                <?= Yii::$app->formatter->asDate($bando->data_scadenza) ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary small">Corsi Disponibili</div>
                            <div class="fw-bold fs-3"><?= count($moduli) ?></div>
                        </div>
                        <div>
                            <div class="text-secondary small">Posti Totali Disponibili</div>
                            <?php
                            $postiDisponibili = 0;
                            foreach ($moduli as $m) $postiDisponibili += $m->getPostiDisponibili();
                            ?>
                            <div class="fw-bold fs-3 text-green"><?= $postiDisponibili ?></div>
                        </div>
                    </div>
                </div>

                <!-- Lista Corsi -->
                <div class="card">
                    <div class="card-header bg-<?= $tc ?>-lt">
                        <h3 class="card-title">
                            <i class="ti ti-book me-2"></i>
                            Moduli Inclusi nel Corso
                        </h3>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($moduli as $modulo): ?>
                            <div class="list-group-item">
                                <div class="mb-2">
                                    <span class="badge bg-<?= $tc ?>-lt"><?= Html::encode($modulo->codice) ?></span>
                                </div>
                                <h4 class="mb-2"><?= Html::encode($modulo->titolo) ?></h4>
                                <?php if ($modulo->descrizione): ?>
                                <p class="text-secondary small mb-2"><?= Html::encode($modulo->descrizione) ?></p>
                                <?php endif; ?>
                                <div class="row text-secondary small mb-0">
                                    <div class="col-6">
                                        <i class="ti ti-clock me-1"></i><?= $modulo->ore_totali ?> ore
                                    </div>
                                    <div class="col-6">
                                        <i class="ti ti-map-pin me-1"></i><?= Html::encode($modulo->sede->nome ?? 'Da definire') ?>
                                    </div>
                                </div>
                                <?php if ($modulo->data_inizio): ?>
                                <div class="text-secondary small mt-1">
                                    <i class="ti ti-calendar me-1"></i>Inizio: <?= Yii::$app->formatter->asDate($modulo->data_inizio) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer text-center">
                        <?= Html::a(
                            '<i class="ti ti-send me-1"></i> Iscriviti a questo Corso',
                            ['iscrizione', 'id' => $bando->id],
                            ['class' => 'btn btn-' . $tc . ' btn-lg w-100']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Contatti -->
        <div class="card text-center mt-4 bg-indigo text-indigo-fg">
            <div class="card-body py-4">
                <h3 class="mb-2">Hai domande su questo corso?</h3>
                <p class="mb-3 opacity-75">Il nostro team è a disposizione per aiutarti nella scelta del corso più adatto.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="tel:+390212345678" class="btn btn-light">
                        <i class="ti ti-phone me-2"></i> 02 1234 5678
                    </a>
                    <a href="mailto:info@albatros.it" class="btn btn-outline-light">
                        <i class="ti ti-mail me-2"></i> info@albatros.it
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
