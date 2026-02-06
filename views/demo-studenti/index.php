<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $bandi array */

$this->title = 'Corsi di Formazione Gratuiti';
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Hero Section -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-center text-white py-5">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="ti ti-school me-2"></i>
                    Fondazione Albatros
                </h1>
                <p class="lead mb-0" style="max-width: 700px; margin: auto;">
                    Scopri i bandi aperti finanziati da FSE, PNRR e MIM. Corsi professionali gratuiti con certificazione riconosciuta per disoccupati, studenti e lavoratori.
                </p>
            </div>
        </div>

        <!-- Stats -->
        <div class="row row-cards mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar"><i class="ti ti-file-text"></i></span>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-3"><?= count($bandi) ?></div>
                                <div class="text-secondary">Bandi Aperti</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-success text-white avatar"><i class="ti ti-book"></i></span>
                            </div>
                            <div class="col">
                                <?php
                                $totaleCorsi = 0;
                                foreach ($bandi as $b) $totaleCorsi += count($b['corsi']);
                                ?>
                                <div class="fw-bold fs-3"><?= $totaleCorsi ?></div>
                                <div class="text-secondary">Corsi Disponibili</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-warning text-white avatar"><i class="ti ti-users"></i></span>
                            </div>
                            <div class="col">
                                <?php
                                $totalePosti = 0;
                                foreach ($bandi as $b) {
                                    foreach ($b['corsi'] as $c) $totalePosti += $c['disponibili'];
                                }
                                ?>
                                <div class="fw-bold fs-3"><?= $totalePosti ?></div>
                                <div class="text-secondary">Posti Liberi</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-info text-white avatar"><i class="ti ti-certificate"></i></span>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-3">100%</div>
                                <div class="text-secondary">Gratuiti</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="mb-4">
            <i class="ti ti-sparkles me-2 text-warning"></i>
            Bandi Aperti
        </h2>

        <!-- Lista Bandi -->
        <?php foreach ($bandi as $bando): ?>
            <div class="card mb-4">
                <div class="card-status-start" style="background-color: <?= $bando['colore'] ?>;"></div>
                <div class="row g-0">
                    <div class="col-lg-8">
                        <div class="card-body">
                            <!-- Header Bando -->
                            <div class="d-flex align-items-start mb-3">
                                <div>
                                    <span class="badge mb-2" style="background-color: <?= $bando['colore'] ?>;">
                                        <?= Html::encode($bando['codice']) ?>
                                    </span>
                                    <h2 class="card-title mb-1"><?= Html::encode($bando['titolo']) ?></h2>
                                    <p class="text-secondary mb-0"><?= Html::encode($bando['sottotitolo']) ?></p>
                                </div>
                            </div>

                            <!-- Descrizione -->
                            <p class="mb-3"><?= Html::encode($bando['descrizione']) ?></p>

                            <!-- Obiettivi -->
                            <div class="mb-3">
                                <h4 class="mb-2"><i class="ti ti-target me-2" style="color: <?= $bando['colore'] ?>;"></i>Obiettivi del Programma</h4>
                                <div class="row">
                                    <?php foreach ($bando['obiettivi'] as $i => $obiettivo): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-success-lt me-2"><?= $i + 1 ?></span>
                                                <span><?= Html::encode($obiettivo) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Prospettive -->
                            <div class="mb-3">
                                <h4 class="mb-2"><i class="ti ti-rocket me-2" style="color: <?= $bando['colore'] ?>;"></i>Prospettive Professionali</h4>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($bando['prospettive'] as $prospettiva): ?>
                                        <span class="badge bg-primary-lt">
                                            <i class="ti ti-arrow-right me-1"></i><?= Html::encode($prospettiva) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Destinatari -->
                            <div class="alert alert-info mb-3">
                                <div class="d-flex">
                                    <div>
                                        <i class="ti ti-users me-2"></i>
                                    </div>
                                    <div>
                                        <h4 class="alert-title">Chi può partecipare</h4>
                                        <div class="text-secondary"><?= Html::encode($bando['destinatari']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Requisiti -->
                            <div class="mb-3">
                                <h4 class="mb-2"><i class="ti ti-checklist me-2" style="color: <?= $bando['colore'] ?>;"></i>Requisiti di Ammissione</h4>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($bando['requisiti'] as $requisito): ?>
                                        <li class="mb-1">
                                            <i class="ti ti-check text-success me-2"></i><?= Html::encode($requisito) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <!-- Certificazioni -->
                            <div>
                                <h4 class="mb-2"><i class="ti ti-certificate me-2" style="color: <?= $bando['colore'] ?>;"></i>Certificazioni Rilasciate</h4>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($bando['certificazioni'] as $cert): ?>
                                        <span class="badge bg-warning-lt">
                                            <i class="ti ti-award me-1"></i><?= Html::encode($cert) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar con info e corsi -->
                    <div class="col-lg-4">
                        <div class="card-body bg-light h-100">
                            <!-- Info Bando -->
                            <div class="mb-4">
                                <div class="mb-3">
                                    <span class="text-secondary d-block mb-1">Fonte di Finanziamento</span>
                                    <strong><?= Html::encode($bando['fonte']) ?></strong>
                                </div>
                                <div class="mb-3">
                                    <span class="text-secondary d-block mb-1">Scadenza Iscrizioni</span>
                                    <span class="badge bg-danger fs-5">
                                        <i class="ti ti-calendar me-1"></i>
                                        <?= Yii::$app->formatter->asDate($bando['scadenza']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Lista Corsi -->
                            <h4 class="mb-3">
                                <i class="ti ti-book me-2"></i>
                                Corsi Disponibili (<?= count($bando['corsi']) ?>)
                            </h4>

                            <div class="list-group list-group-flush mb-3">
                                <?php foreach ($bando['corsi'] as $corso): ?>
                                    <?php
                                    $percentuale = round(($corso['posti'] - $corso['disponibili']) / $corso['posti'] * 100);
                                    $colorePosti = $corso['disponibili'] > 10 ? 'success' : ($corso['disponibili'] > 5 ? 'warning' : 'danger');
                                    ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-azure-lt mb-1"><?= Html::encode($corso['codice']) ?></span>
                                                <div class="fw-bold"><?= Html::encode($corso['titolo']) ?></div>
                                            </div>
                                        </div>
                                        <div class="text-secondary small mb-2">
                                            <span class="me-2"><i class="ti ti-clock me-1"></i><?= $corso['ore'] ?> ore</span>
                                            <span><i class="ti ti-map-pin me-1"></i><?= Html::encode($corso['sede']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small>Posti: <?= $corso['disponibili'] ?>/<?= $corso['posti'] ?></small>
                                            <small>Inizio: <?= Yii::$app->formatter->asDate($corso['inizio']) ?></small>
                                        </div>
                                        <div class="progress progress-sm mb-2">
                                            <div class="progress-bar bg-<?= $colorePosti ?>" style="width: <?= $percentuale ?>%"></div>
                                        </div>
                                        <?= Html::a(
                                            '<i class="ti ti-send me-1"></i> Iscriviti Ora',
                                            ['iscrizione', 'id' => $corso['id']],
                                            ['class' => 'btn btn-primary btn-sm w-100']
                                        ) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?= Html::a(
                                '<i class="ti ti-info-circle me-1"></i> Dettagli Completi',
                                ['bando', 'id' => $bando['id']],
                                ['class' => 'btn btn-outline-primary w-100']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- CTA finale -->
        <div class="card text-center" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="card-body py-5 text-white">
                <h2 class="mb-3">Hai bisogno di aiuto?</h2>
                <p class="mb-4">Contattaci per ricevere assistenza nella scelta del percorso formativo più adatto a te.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="tel:+390212345678" class="btn btn-lg btn-light">
                        <i class="ti ti-phone me-2"></i> 02 1234 5678
                    </a>
                    <a href="mailto:info@albatros.it" class="btn btn-lg btn-outline-light">
                        <i class="ti ti-mail me-2"></i> info@albatros.it
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
