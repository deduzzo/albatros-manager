<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $docente app\models\Docente */
/* @var $istanzeCount int */
/* @var $bandiAperti int */
/* @var $moduliAperti int */
/* @var $assegnazioniAttive int */
/* @var $oreTotaliApprovate float */
/* @var $oreInAttesa float */

$this->title = 'Dashboard';
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Benvenuto -->
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg bg-blue-lt">
                        <i class="ti ti-user"></i>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        Benvenuto, <?= Html::encode($docente->nome) ?>!
                    </h2>
                    <div class="page-subtitle text-secondary">
                        <i class="ti ti-id me-1"></i> <?= Html::encode($docente->codice_fiscale) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <?= Html::a('<i class="ti ti-file-text me-2"></i> Vedi Bandi Aperti', ['bandi'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>

        <!-- Statistiche -->
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Le mie iscrizioni</div>
                            <div class="ms-auto">
                                <a href="<?= Url::to(['le-mie-iscrizioni']) ?>" class="text-decoration-none">
                                    Vedi tutte <i class="ti ti-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="h1 mb-0"><?= $istanzeCount ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Bandi aperti</div>
                        </div>
                        <div class="h1 mb-0 text-success"><?= $bandiAperti ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Moduli disponibili</div>
                        </div>
                        <div class="h1 mb-0 text-info"><?= $moduliAperti ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiche Rendicontazione Ore -->
        <?php if (isset($assegnazioniAttive) && $assegnazioniAttive > 0): ?>
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Le mie assegnazioni</div>
                            <div class="ms-auto">
                                <a href="<?= Url::to(['le-mie-assegnazioni']) ?>" class="text-decoration-none">
                                    Gestisci <i class="ti ti-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="h1 mb-0"><?= $assegnazioniAttive ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Ore da validare</div>
                        </div>
                        <div class="h1 mb-0 <?= $oreInAttesa > 0 ? 'text-warning' : 'text-muted' ?>"><?= number_format($oreInAttesa ?? 0, 1, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Azioni rapide -->
        <div class="row row-cards">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-rocket me-2"></i> Azioni rapide</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="<?= Url::to(['bandi']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                                <span class="avatar bg-blue-lt me-3">
                                    <i class="ti ti-file-text"></i>
                                </span>
                                <div>
                                    <strong>Visualizza Bandi Aperti</strong>
                                    <div class="text-secondary small">Esplora le opportunità disponibili</div>
                                </div>
                                <i class="ti ti-chevron-right ms-auto"></i>
                            </a>
                            <a href="<?= Url::to(['le-mie-iscrizioni']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                                <span class="avatar bg-green-lt me-3">
                                    <i class="ti ti-list-check"></i>
                                </span>
                                <div>
                                    <strong>Le Mie Iscrizioni</strong>
                                    <div class="text-secondary small">Controlla lo stato delle tue domande</div>
                                </div>
                                <i class="ti ti-chevron-right ms-auto"></i>
                            </a>
                            <?php if (isset($assegnazioniAttive) && $assegnazioniAttive > 0): ?>
                            <a href="<?= Url::to(['le-mie-assegnazioni']) ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                                <span class="avatar bg-cyan-lt me-3">
                                    <i class="ti ti-clock"></i>
                                </span>
                                <div>
                                    <strong>Rendicontazione Ore</strong>
                                    <div class="text-secondary small">Gestisci le ore delle tue assegnazioni</div>
                                </div>
                                <i class="ti ti-chevron-right ms-auto"></i>
                            </a>
                            <?php endif; ?>
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
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-info-circle me-2"></i> Il tuo profilo</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Nome completo:</dt>
                            <dd class="col-7"><?= Html::encode($docente->cognome . ' ' . $docente->nome) ?></dd>

                            <dt class="col-5">Email:</dt>
                            <dd class="col-7"><?= Html::encode($docente->email ?: '-') ?></dd>

                            <dt class="col-5">Telefono:</dt>
                            <dd class="col-7"><?= Html::encode($docente->telefono ?: '-') ?></dd>

                            <dt class="col-5">Città:</dt>
                            <dd class="col-7"><?= Html::encode($docente->citta ?: '-') ?></dd>

                            <dt class="col-5">Titolo di studio:</dt>
                            <dd class="col-7"><?= Html::encode($docente->titolo_studio ?: 'Non specificato') ?></dd>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <a href="<?= Url::to(['profilo']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-edit me-1"></i> Modifica profilo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
