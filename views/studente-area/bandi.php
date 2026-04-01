<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $bandi app\models\Avviso[] */

$this->title = 'Corsi Aperti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-file-text me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
            <div class="page-subtitle text-secondary">
                Esplora i corsi aperti e candidati a quelli di tuo interesse
            </div>
        </div>

        <?php if (empty($bandi)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <span class="avatar avatar-xl bg-purple-lt">
                            <i class="ti ti-file-off"></i>
                        </span>
                    </div>
                    <h3>Nessun corso aperto</h3>
                    <p class="text-secondary">Al momento non ci sono corsi aperti per le candidature.<br>Torna a controllare presto!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row row-cards">
                <?php foreach ($bandi as $avviso): ?>
                    <?php
                    $moduliCount = $avviso->getModuli()
                        ->andWhere(['!=', 'status', 'annullato'])
                        ->count();
                    $giorniRimasti = max(0, (int) ((strtotime($avviso->data_scadenza) - strtotime('today')) / 86400));
                    $colore = $avviso->getTablerColor();
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-status-start bg-<?= $colore ?>"></div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-<?= $colore ?>-lt"><?= Html::encode($avviso->codice_avviso) ?></span>
                                    <?php if ($avviso->data_scadenza): ?>
                                        <span class="ms-auto text-secondary small">
                                            <?php if ($giorniRimasti <= 3): ?>
                                                <span class="badge bg-red text-red-fg">
                                                    <i class="ti ti-alert-triangle me-1"></i>
                                                    <?= $giorniRimasti ?> giorn<?= $giorniRimasti == 1 ? 'o' : 'i' ?> rimanent<?= $giorniRimasti == 1 ? 'e' : 'i' ?>
                                                </span>
                                            <?php else: ?>
                                                <i class="ti ti-calendar me-1"></i>
                                                Scade: <?= Yii::$app->formatter->asDate($avviso->data_scadenza) ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="card-title"><?= Html::encode($avviso->titolo) ?></h3>
                                <?php if ($avviso->sottotitolo): ?>
                                    <p class="text-purple small mb-2"><?= Html::encode($avviso->sottotitolo) ?></p>
                                <?php endif; ?>
                                <?php if ($avviso->descrizione): ?>
                                    <p class="text-secondary">
                                        <?= Html::encode(mb_substr($avviso->descrizione, 0, 150)) ?><?= mb_strlen($avviso->descrizione) > 150 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                <div class="d-flex align-items-center mt-3">
                                    <span class="text-secondary">
                                        <i class="ti ti-book me-1"></i>
                                        <?= $moduliCount ?> modul<?= $moduliCount == 1 ? 'o' : 'i' ?>
                                    </span>
                                    <?php if ($avviso->progetto): ?>
                                        <span class="ms-auto text-secondary small">
                                            <i class="ti ti-folder me-1"></i>
                                            <?= Html::encode($avviso->progetto->titolo) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <?= Html::a('<i class="ti ti-eye me-1"></i> Vedi dettagli', ['bando', 'id' => $avviso->id], [
                                    'class' => 'btn w-100',
                                    'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;',
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
