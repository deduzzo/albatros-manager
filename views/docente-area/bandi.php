<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $bandi app\models\Avviso[] */

$this->title = 'Bandi Aperti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-file-text me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
            <div class="page-subtitle text-secondary">
                Esplora i bandi aperti e candidati ai moduli di tuo interesse
            </div>
        </div>

        <?php if (empty($bandi)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <span class="avatar avatar-xl bg-secondary-lt">
                            <i class="ti ti-file-off"></i>
                        </span>
                    </div>
                    <h3>Nessun bando aperto</h3>
                    <p class="text-secondary">Al momento non ci sono bandi aperti per le iscrizioni.<br>Torna a controllare presto!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row row-cards">
                <?php foreach ($bandi as $avviso): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-status-start bg-success"></div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-blue-lt"><?= Html::encode($avviso->codice_avviso) ?></span>
                                    <?php if ($avviso->data_scadenza): ?>
                                        <span class="ms-auto text-secondary small">
                                            <i class="ti ti-calendar me-1"></i>
                                            Scade: <?= Yii::$app->formatter->asDate($avviso->data_scadenza) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="card-title"><?= Html::encode($avviso->titolo) ?></h3>
                                <?php if ($avviso->descrizione): ?>
                                    <p class="text-secondary">
                                        <?= Html::encode(mb_substr($avviso->descrizione, 0, 150)) ?>...
                                    </p>
                                <?php endif; ?>
                                <?php
                                $moduliAperti = $avviso->getModuli()->where(['status' => 'aperto'])->count();
                                ?>
                                <div class="d-flex align-items-center mt-3">
                                    <span class="text-secondary">
                                        <i class="ti ti-book me-1"></i>
                                        <?= $moduliAperti ?> modul<?= $moduliAperti == 1 ? 'o' : 'i' ?> disponibil<?= $moduliAperti == 1 ? 'e' : 'i' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <?= Html::a('<i class="ti ti-eye me-1"></i> Vedi dettagli', ['bando', 'id' => $avviso->id], ['class' => 'btn btn-primary w-100']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
