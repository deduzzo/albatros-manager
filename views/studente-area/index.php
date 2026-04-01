<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $allievo app\models\Allievo */
/* @var $candidatureTotali int */
/* @var $bandiAperti int */
/* @var $candidatureAmmesse int */
/* @var $candidatureInAttesa int */
/* @var $bandiInScadenza app\models\Avviso[] */

$this->title = 'Dashboard';
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Benvenuto -->
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg bg-purple-lt">
                        <i class="ti ti-user"></i>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        Benvenuto, <?= Html::encode($allievo->nome) ?>!
                    </h2>
                    <div class="page-subtitle text-secondary">
                        <i class="ti ti-id me-1"></i> <?= Html::encode($allievo->codice_fiscale) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <?= Html::a('<i class="ti ti-file-text me-2"></i> Vedi Corsi Aperti', ['bandi'], [
                        'class' => 'btn bg-purple text-purple-fg',
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Statistiche -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Le mie candidature</div>
                            <div class="ms-auto">
                                <a href="<?= Url::to(['le-mie-candidature']) ?>" class="text-decoration-none">
                                    Vedi <i class="ti ti-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="h1 mb-0"><?= $candidatureTotali ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Corsi aperti</div>
                        </div>
                        <div class="h1 mb-0 text-purple"><?= $bandiAperti ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ammesse</div>
                        </div>
                        <div class="h1 mb-0 text-green"><?= $candidatureAmmesse ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">In attesa</div>
                        </div>
                        <div class="h1 mb-0 <?= $candidatureInAttesa > 0 ? 'text-orange' : 'text-muted' ?>"><?= $candidatureInAttesa ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <!-- Corsi in scadenza -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-clock-exclamation me-2"></i> Corsi in Scadenza</h3>
                    </div>
                    <?php if (empty($bandiInScadenza)): ?>
                        <div class="card-body text-center py-4">
                            <p class="text-secondary mb-0">Nessun corso in scadenza nei prossimi 7 giorni.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($bandiInScadenza as $avviso): ?>
                                <a href="<?= Url::to(['bando', 'id' => $avviso->id]) ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="badge bg-purple-lt me-2"><?= Html::encode($avviso->codice_avviso) ?></span>
                                            <strong><?= Html::encode($avviso->titolo) ?></strong>
                                        </div>
                                        <div class="ms-auto">
                                            <span class="badge bg-red text-red-fg">
                                                <i class="ti ti-calendar me-1"></i>
                                                Scade: <?= Yii::$app->formatter->asDate($avviso->data_scadenza) ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Azioni rapide -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-rocket me-2"></i> Azioni rapide</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="<?= Url::to(['bandi']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                                <span class="avatar bg-purple-lt me-3">
                                    <i class="ti ti-file-text"></i>
                                </span>
                                <div>
                                    <strong>Esplora Corsi Aperti</strong>
                                    <div class="text-secondary small">Scopri i corsi disponibili e candidati</div>
                                </div>
                                <i class="ti ti-chevron-right ms-auto"></i>
                            </a>
                            <a href="<?= Url::to(['le-mie-candidature']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3">
                                    <i class="ti ti-list-check"></i>
                                </span>
                                <div>
                                    <strong>Le Mie Candidature</strong>
                                    <div class="text-secondary small">Controlla lo stato delle tue domande</div>
                                </div>
                                <i class="ti ti-chevron-right ms-auto"></i>
                            </a>
                            <a href="<?= Url::to(['profilo']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                                <span class="avatar bg-yellow-lt me-3">
                                    <i class="ti ti-user"></i>
                                </span>
                                <div>
                                    <strong>Aggiorna Profilo</strong>
                                    <div class="text-secondary small">Modifica i tuoi dati anagrafici</div>
                                </div>
                                <i class="ti ti-chevron-right ms-auto"></i>
                            </a>
                            <a href="<?= Url::to(['/site/manuale-studente']) ?>" class="list-group-item list-group-item-action d-flex align-items-center" target="_blank">
                                <span class="avatar bg-azure-lt me-3">
                                    <i class="ti ti-book"></i>
                                </span>
                                <div>
                                    <strong>Manuale Studente</strong>
                                    <div class="text-secondary small">Guida all'utilizzo dell'area studenti</div>
                                </div>
                                <i class="ti ti-chevron-right ms-auto"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
