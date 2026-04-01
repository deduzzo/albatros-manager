<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Configurazione;

/* @var $this yii\web\View */
/* @var $bandiAperti app\models\Avviso[] */

$this->title = 'Corsi di Formazione Gratuiti';
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Hero Section -->
        <div class="card mb-4 bg-purple text-white">
            <div class="card-body text-center py-5">
                <h1 class="display-5 fw-bold mb-3 text-white">
                    <i class="ti ti-school me-2"></i>
                    Fondazione Albatros
                </h1>
                <p class="lead mb-0 text-white opacity-75" style="max-width: 700px; margin: auto;">
                    Scopri i corsi aperti finanziati da FSE, PNRR e MIM. Corsi professionali gratuiti con certificazione riconosciuta per disoccupati, studenti e lavoratori.
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
                                <span class="bg-blue text-blue-fg avatar"><i class="ti ti-file-text"></i></span>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-3"><?= count($bandiAperti) ?></div>
                                <div class="text-secondary">Corsi Aperti</div>
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
                                <span class="bg-green text-green-fg avatar"><i class="ti ti-book"></i></span>
                            </div>
                            <div class="col">
                                <?php
                                $totaleCorsi = 0;
                                foreach ($bandiAperti as $b) $totaleCorsi += count($b->moduli);
                                ?>
                                <div class="fw-bold fs-3"><?= $totaleCorsi ?></div>
                                <div class="text-secondary">Moduli in Erogazione</div>
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
                                <span class="bg-orange text-orange-fg avatar"><i class="ti ti-users"></i></span>
                            </div>
                            <div class="col">
                                <?php
                                $totalePosti = 0;
                                foreach ($bandiAperti as $b) {
                                    foreach ($b->moduli as $m) $totalePosti += $m->getPostiDisponibili();
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
                                <span class="bg-azure text-azure-fg avatar"><i class="ti ti-certificate"></i></span>
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
            Corsi Aperti
        </h2>

        <!-- Lista Corsi -->
        <?php foreach ($bandiAperti as $bando): ?>
            <?php
            $tc = $bando->getTablerColor();
            $obiettivi = $bando->getJsonArray('obiettivi');
            $prospettive = $bando->getJsonArray('prospettive');
            $requisiti = $bando->getJsonArray('requisiti_ammissione');
            $certificazioni = $bando->getJsonArray('certificazioni');
            $moduli = $bando->moduli;
            ?>
            <div class="card mb-4">
                <div class="card-status-start bg-<?= $tc ?>"></div>
                <div class="row g-0">
                    <div class="col-lg-8">
                        <div class="card-body">
                            <!-- Header Corso -->
                            <div class="d-flex align-items-start mb-3">
                                <div>
                                    <span class="badge bg-<?= $tc ?> text-<?= $tc ?>-fg mb-2">
                                        <?= Html::encode($bando->codice_avviso) ?>
                                    </span>
                                    <h2 class="card-title mb-1"><?= Html::encode($bando->titolo) ?></h2>
                                    <?php if ($bando->sottotitolo): ?>
                                    <p class="text-secondary mb-0"><?= Html::encode($bando->sottotitolo) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Descrizione -->
                            <?php if ($bando->descrizione): ?>
                            <p class="mb-3"><?= Html::encode($bando->descrizione) ?></p>
                            <?php endif; ?>

                            <!-- Obiettivi -->
                            <?php if (!empty($obiettivi)): ?>
                            <div class="mb-3">
                                <h4 class="mb-2"><i class="ti ti-target me-2 text-<?= $tc ?>"></i>Obiettivi del Programma</h4>
                                <div class="row">
                                    <?php foreach ($obiettivi as $i => $obiettivo): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex align-items-start">
                                                <span class="badge bg-green-lt me-2"><?= $i + 1 ?></span>
                                                <span><?= Html::encode($obiettivo) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Prospettive -->
                            <?php if (!empty($prospettive)): ?>
                            <div class="mb-3">
                                <h4 class="mb-2"><i class="ti ti-rocket me-2 text-<?= $tc ?>"></i>Prospettive Professionali</h4>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($prospettive as $prospettiva): ?>
                                        <span class="badge bg-blue-lt">
                                            <i class="ti ti-arrow-right me-1"></i><?= Html::encode($prospettiva) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Destinatari -->
                            <?php if ($bando->destinatari): ?>
                            <div class="alert alert-info mb-3">
                                <div class="d-flex">
                                    <div><i class="ti ti-users me-2"></i></div>
                                    <div>
                                        <h4 class="alert-title">Chi può partecipare</h4>
                                        <div class="text-secondary"><?= Html::encode($bando->destinatari) ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Requisiti -->
                            <?php if (!empty($requisiti)): ?>
                            <div class="mb-3">
                                <h4 class="mb-2"><i class="ti ti-checklist me-2 text-<?= $tc ?>"></i>Requisiti di Ammissione</h4>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($requisiti as $requisito): ?>
                                        <li class="mb-1">
                                            <i class="ti ti-check text-success me-2"></i><?= Html::encode($requisito) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <!-- Certificazioni -->
                            <?php if (!empty($certificazioni)): ?>
                            <div>
                                <h4 class="mb-2"><i class="ti ti-certificate me-2 text-<?= $tc ?>"></i>Certificazioni Rilasciate</h4>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($certificazioni as $cert): ?>
                                        <span class="badge bg-orange-lt">
                                            <i class="ti ti-award me-1"></i><?= Html::encode($cert) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sidebar con info e corsi -->
                    <div class="col-lg-4">
                        <div class="card-body bg-light h-100">
                            <!-- Info Bando -->
                            <div class="mb-4">
                                <div class="mb-3">
                                    <span class="text-secondary d-block mb-1">Fonte di Finanziamento</span>
                                    <strong><?= Html::encode($bando->progetto->fonteFinanziamento->descrizione ?? '-') ?></strong>
                                </div>
                                <div class="mb-3">
                                    <span class="text-secondary d-block mb-1">Scadenza Iscrizioni</span>
                                    <span class="badge bg-red text-red-fg fs-5">
                                        <i class="ti ti-calendar me-1"></i>
                                        <?= Yii::$app->formatter->asDate($bando->data_scadenza) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Lista Corsi -->
                            <h4 class="mb-3">
                                <i class="ti ti-book me-2"></i>
                                Moduli in Erogazione (<?= count($moduli) ?>)
                            </h4>

                            <div class="list-group list-group-flush mb-3">
                                <?php foreach ($moduli as $modulo): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <span class="badge bg-azure-lt mb-1"><?= Html::encode($modulo->codice) ?></span>
                                                <div class="fw-bold"><?= Html::encode($modulo->titolo) ?></div>
                                            </div>
                                        </div>
                                        <div class="text-secondary small">
                                            <span class="me-2"><i class="ti ti-clock me-1"></i><?= $modulo->ore_totali ?> ore</span>
                                            <span><i class="ti ti-map-pin me-1"></i><?= Html::encode($modulo->sede->nome ?? 'Da definire') ?></span>
                                            <?php if ($modulo->data_inizio): ?>
                                            <span class="ms-2"><i class="ti ti-calendar me-1"></i><?= Yii::$app->formatter->asDate($modulo->data_inizio) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?= Html::a(
                                '<i class="ti ti-send me-1"></i> Iscriviti al Corso',
                                ['iscrizione', 'id' => $bando->id],
                                ['class' => 'btn btn-primary w-100 mb-2']
                            ) ?>
                            <?= Html::a(
                                '<i class="ti ti-info-circle me-1"></i> Dettagli Completi',
                                ['bando', 'id' => $bando->id],
                                ['class' => 'btn btn-outline-primary w-100']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($bandiAperti)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <span class="avatar avatar-xl bg-default text-default-fg mb-3">
                    <i class="ti ti-clipboard-off" style="font-size: 2rem;"></i>
                </span>
                <h3 class="text-secondary">Nessun bando aperto al momento</h3>
                <p class="text-secondary">Torna a trovarci presto, nuovi corsi saranno disponibili!</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- CTA finale -->
        <?php
        $telefono = Configurazione::get('telefono', '');
        $email = Configurazione::get('email', '');
        ?>
        <div class="card text-center mt-4 bg-teal text-teal-fg">
            <div class="card-body py-5">
                <h2 class="mb-3">Hai bisogno di aiuto?</h2>
                <p class="mb-4">Contattaci per ricevere assistenza nella scelta del percorso formativo più adatto a te.</p>
                <div class="d-flex justify-content-center gap-3">
                    <?php if ($telefono): ?>
                    <a href="tel:<?= Html::encode($telefono) ?>" class="btn btn-lg btn-light">
                        <i class="ti ti-phone me-2"></i> <?= Html::encode($telefono) ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($email): ?>
                    <a href="mailto:<?= Html::encode($email) ?>" class="btn btn-lg btn-outline-light">
                        <i class="ti ti-mail me-2"></i> <?= Html::encode($email) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
