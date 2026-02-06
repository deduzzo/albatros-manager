<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $corsi array */

$this->title = 'Corsi Disponibili';
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-book me-2"></i> <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-secondary">
                        Scegli il corso che fa per te e iscriviti gratuitamente
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <?php foreach ($corsi as $corso): ?>
                <?php
                $percentualeOccupazione = round(($corso['posti_totali'] - $corso['posti_disponibili']) / $corso['posti_totali'] * 100);
                $disponibilitaClass = $corso['posti_disponibili'] > 10 ? 'bg-success' : ($corso['posti_disponibili'] > 5 ? 'bg-warning' : 'bg-danger');
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-status-start <?= $disponibilitaClass ?>"></div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-blue-lt"><?= Html::encode($corso['codice']) ?></span>
                                <span class="badge bg-azure-lt ms-2"><?= $corso['ore'] ?> ore</span>
                            </div>
                            <h3 class="card-title"><?= Html::encode($corso['titolo']) ?></h3>
                            <p class="text-secondary">
                                <?= Html::encode(mb_substr($corso['descrizione'], 0, 120)) ?>...
                            </p>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-secondary">Posti disponibili</small>
                                    <small><strong><?= $corso['posti_disponibili'] ?></strong>/<?= $corso['posti_totali'] ?></small>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar <?= $disponibilitaClass ?>" style="width: <?= $percentualeOccupazione ?>%"></div>
                                </div>
                            </div>

                            <div class="text-secondary small mb-3">
                                <div><i class="ti ti-calendar me-1"></i> <?= Yii::$app->formatter->asDate($corso['data_inizio']) ?> - <?= Yii::$app->formatter->asDate($corso['data_fine']) ?></div>
                                <div><i class="ti ti-map-pin me-1"></i> <?= Html::encode($corso['sede']) ?></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <?php if ($corso['posti_disponibili'] > 0): ?>
                                <?= Html::a('<i class="ti ti-send me-1"></i> Iscriviti', ['iscrizione', 'id' => $corso['id']], ['class' => 'btn btn-primary w-100']) ?>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="ti ti-x me-1"></i> Posti esauriti
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
