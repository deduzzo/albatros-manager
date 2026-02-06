<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $bando array */

$this->title = $bando['titolo'];
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Breadcrumb -->
        <div class="page-pretitle mb-2">
            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Tutti i Bandi', ['index'], ['class' => 'text-decoration-none']) ?>
        </div>

        <!-- Header Bando -->
        <div class="card mb-4" style="background: linear-gradient(135deg, <?= $bando['colore'] ?> 0%, <?= $bando['colore'] ?>99 100%);">
            <div class="card-body text-white py-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-dark mb-3 fs-5">
                            <?= Html::encode($bando['codice']) ?>
                        </span>
                        <h1 class="display-6 fw-bold mb-2"><?= Html::encode($bando['titolo']) ?></h1>
                        <p class="lead mb-0 opacity-75"><?= Html::encode($bando['sottotitolo']) ?></p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                        <div class="d-inline-block bg-white bg-opacity-25 rounded-3 p-3">
                            <div class="text-white-50 small">Scadenza Iscrizioni</div>
                            <div class="fs-2 fw-bold">
                                <i class="ti ti-calendar me-2"></i>
                                <?= Yii::$app->formatter->asDate($bando['scadenza']) ?>
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
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-info-circle me-2" style="color: <?= $bando['colore'] ?>;"></i>
                            Descrizione del Programma
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="fs-4"><?= Html::encode($bando['descrizione']) ?></p>
                    </div>
                </div>

                <!-- Obiettivi -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-target me-2" style="color: <?= $bando['colore'] ?>;"></i>
                            Obiettivi del Programma
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($bando['obiettivi'] as $i => $obiettivo): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <span class="avatar avatar-sm me-3" style="background-color: <?= $bando['colore'] ?>;">
                                            <?= $i + 1 ?>
                                        </span>
                                        <div class="pt-1"><?= Html::encode($obiettivo) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Prospettive Professionali -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-rocket me-2" style="color: <?= $bando['colore'] ?>;"></i>
                            Prospettive Professionali
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($bando['prospettive'] as $prospettiva): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-arrow-right me-2" style="color: <?= $bando['colore'] ?>;"></i>
                                        <span><?= Html::encode($prospettiva) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Destinatari -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-users me-2" style="color: <?= $bando['colore'] ?>;"></i>
                            A Chi è Rivolto
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            <?= Html::encode($bando['destinatari']) ?>
                        </div>
                    </div>
                </div>

                <!-- Requisiti -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-checklist me-2" style="color: <?= $bando['colore'] ?>;"></i>
                            Requisiti di Ammissione
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($bando['requisiti'] as $requisito): ?>
                                <li class="mb-2 d-flex">
                                    <i class="ti ti-check text-success me-2 mt-1"></i>
                                    <span><?= Html::encode($requisito) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Certificazioni -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-certificate me-2" style="color: <?= $bando['colore'] ?>;"></i>
                            Certificazioni Rilasciate
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($bando['certificazioni'] as $cert): ?>
                                <span class="badge bg-warning-lt fs-5 py-2 px-3">
                                    <i class="ti ti-award me-1"></i><?= Html::encode($cert) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Info Box -->
                <div class="card mb-4 sticky-top" style="top: 1rem;">
                    <div class="card-header" style="background-color: <?= $bando['colore'] ?>10;">
                        <h3 class="card-title">
                            <i class="ti ti-info-square me-2"></i>
                            Informazioni
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-secondary small">Fonte di Finanziamento</div>
                            <div class="fw-bold"><?= Html::encode($bando['fonte']) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary small">Scadenza Iscrizioni</div>
                            <span class="badge bg-danger fs-5">
                                <i class="ti ti-calendar me-1"></i>
                                <?= Yii::$app->formatter->asDate($bando['scadenza']) ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="text-secondary small">Corsi Disponibili</div>
                            <div class="fw-bold fs-3"><?= count($bando['corsi']) ?></div>
                        </div>
                        <div>
                            <div class="text-secondary small">Posti Totali Disponibili</div>
                            <?php
                            $postiDisponibili = 0;
                            foreach ($bando['corsi'] as $c) $postiDisponibili += $c['disponibili'];
                            ?>
                            <div class="fw-bold fs-3 text-success"><?= $postiDisponibili ?></div>
                        </div>
                    </div>
                </div>

                <!-- Lista Corsi -->
                <div class="card">
                    <div class="card-header" style="background-color: <?= $bando['colore'] ?>10;">
                        <h3 class="card-title">
                            <i class="ti ti-book me-2"></i>
                            Corsi Disponibili
                        </h3>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($bando['corsi'] as $corso): ?>
                            <?php
                            $percentuale = round(($corso['posti'] - $corso['disponibili']) / $corso['posti'] * 100);
                            $colorePosti = $corso['disponibili'] > 10 ? 'success' : ($corso['disponibili'] > 5 ? 'warning' : 'danger');
                            ?>
                            <div class="list-group-item">
                                <div class="mb-2">
                                    <span class="badge bg-azure-lt"><?= Html::encode($corso['codice']) ?></span>
                                </div>
                                <h4 class="mb-2"><?= Html::encode($corso['titolo']) ?></h4>
                                <div class="row text-secondary small mb-2">
                                    <div class="col-6">
                                        <i class="ti ti-clock me-1"></i><?= $corso['ore'] ?> ore
                                    </div>
                                    <div class="col-6">
                                        <i class="ti ti-map-pin me-1"></i><?= Html::encode($corso['sede']) ?>
                                    </div>
                                </div>
                                <div class="row text-secondary small mb-2">
                                    <div class="col-6">
                                        <i class="ti ti-users me-1"></i><?= $corso['disponibili'] ?>/<?= $corso['posti'] ?> posti
                                    </div>
                                    <div class="col-6">
                                        <i class="ti ti-calendar me-1"></i><?= Yii::$app->formatter->asDate($corso['inizio']) ?>
                                    </div>
                                </div>
                                <div class="progress progress-sm mb-3">
                                    <div class="progress-bar bg-<?= $colorePosti ?>" style="width: <?= $percentuale ?>%"></div>
                                </div>
                                <?= Html::a(
                                    '<i class="ti ti-send me-1"></i> Iscriviti a questo corso',
                                    ['iscrizione', 'id' => $corso['id']],
                                    ['class' => 'btn btn-primary w-100']
                                ) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Contatti -->
        <div class="card text-center mt-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body py-4 text-white">
                <h3 class="mb-2">Hai domande su questo bando?</h3>
                <p class="mb-3">Il nostro team è a disposizione per aiutarti nella scelta del corso più adatto.</p>
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
