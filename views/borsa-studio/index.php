<?php

use yii\helpers\Html;
use app\components\widgets\AppGridView;
use app\models\BorsaStudio;
use app\models\Progetto;

/** @var yii\web\View $this */
/** @var app\models\BorsaStudioSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $stats */

$this->title = 'Borse di Studio';
$this->params['breadcrumbs'][] = $this->title;

$formatter = Yii::$app->formatter;
?>
<div class="borsa-studio-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-school me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Gestione borse di studio PNRR per studenti</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <?= Html::a('<i class="ti ti-refresh me-1"></i> Ricalcola Frequenze', ['ricalcola-frequenze'], ['class' => 'btn btn-secondary me-2']) ?>
                    <?= Html::a('<i class="ti ti-file-type-xml me-1"></i> Export SEPA XML', ['export-sepa'], ['class' => 'btn btn-azure me-2']) ?>
                    <?= Html::a('<i class="ti ti-download me-1"></i> Export Excel', ['export'], ['class' => 'btn btn-warning me-2']) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Borsa', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Stat Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Borse Attive</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h1 mb-0 me-2"><?= $stats['attive'] ?></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="text-muted">Approvate + In erogazione</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Importo Totale</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h1 mb-0 me-2"><?= $formatter->asCurrency($stats['importo_totale'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="text-muted">Somma importi finali (escluse revocate)</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Erogato</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h1 mb-0 me-2 text-green"><?= $formatter->asCurrency($stats['erogato'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <?php
                            $percTot = $stats['importo_totale'] > 0 ? round(($stats['erogato'] / $stats['importo_totale']) * 100, 1) : 0;
                            ?>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-green" style="width: <?= $percTot ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Da Erogare</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h1 mb-0 me-2 text-orange"><?= $formatter->asCurrency($stats['da_erogare'], 'EUR') ?></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="text-muted">Residuo da erogare</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella -->
            <div class="card">
                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['attribute' => 'id', 'headerOptions' => ['style' => 'width: 60px;']],
                        [
                            'attribute' => 'studente_nome',
                            'label' => 'Studente',
                            'format' => 'raw',
                            'content' => function ($model) {
                                if ($model->allievo) {
                                    return Html::a(
                                        Html::encode($model->allievo->cognome . ' ' . $model->allievo->nome),
                                        ['/allievo/view', 'id' => $model->allievo_codice_fiscale]
                                    );
                                }
                                return '<span class="text-muted">-</span>';
                            },
                        ],
                        [
                            'attribute' => 'progetto_nome',
                            'label' => 'Progetto',
                            'format' => 'raw',
                            'content' => function ($model) {
                                if ($model->progetto) {
                                    return Html::a(
                                        Html::encode($model->progetto->titolo),
                                        ['/progetto/view', 'id' => $model->progetto_id]
                                    );
                                }
                                return '<span class="text-muted">-</span>';
                            },
                        ],
                        [
                            'attribute' => 'anno_formativo',
                            'headerOptions' => ['style' => 'width: 110px;'],
                        ],
                        [
                            'attribute' => 'tipologia_sede',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => BorsaStudio::getTipologiaSedeList(),
                            'content' => fn($model) => $model->getTipologiaSedeBadge(),
                        ],
                        [
                            'attribute' => 'importo_finale',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'content' => fn($model) => '<strong>' . $formatter->asCurrency($model->importo_finale, 'EUR') . '</strong>',
                        ],
                        [
                            'attribute' => 'totale_erogato',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 150px;'],
                            'content' => function ($model) use ($formatter) {
                                $perc = $model->getPercentualeErogazione();
                                $colorClass = $perc >= 100 ? 'bg-green' : ($perc >= 50 ? 'bg-blue' : 'bg-azure');
                                return '<div class="d-flex flex-column">'
                                    . '<small>' . $formatter->asCurrency($model->totale_erogato, 'EUR') . '</small>'
                                    . '<div class="progress progress-sm mt-1"><div class="progress-bar ' . $colorClass . '" style="width: ' . $perc . '%"></div></div>'
                                    . '<small class="text-muted">' . $perc . '%</small>'
                                    . '</div>';
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'filter' => BorsaStudio::getStatusLabels(),
                            'content' => fn($model) => $model->getStatusBadge(),
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {eroga} {revoca}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a('<i class="ti ti-eye"></i>', $url, [
                                        'class' => 'btn btn-info btn-sm',
                                        'title' => 'Visualizza',
                                        'data-bs-toggle' => 'tooltip',
                                    ]);
                                },
                                'eroga' => function ($url, $model) {
                                    if (!in_array($model->status, [BorsaStudio::STATUS_APPROVATA, BorsaStudio::STATUS_IN_EROGAZIONE])) {
                                        return '';
                                    }
                                    return Html::a('<i class="ti ti-cash"></i>', ['eroga', 'id' => $model->id], [
                                        'class' => 'btn btn-success btn-sm',
                                        'title' => 'Eroga trimestre',
                                        'data-bs-toggle' => 'tooltip',
                                    ]);
                                },
                                'revoca' => function ($url, $model) {
                                    if ($model->status === BorsaStudio::STATUS_REVOCATA || $model->status === BorsaStudio::STATUS_COMPLETATA) {
                                        return '';
                                    }
                                    return Html::a('<i class="ti ti-ban"></i>', ['revoca', 'id' => $model->id], [
                                        'class' => 'btn btn-danger btn-sm',
                                        'title' => 'Revoca',
                                        'data-bs-toggle' => 'tooltip',
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">Totale: <?= $dataProvider->totalCount ?> borse di studio</p>
                </div>
            </div>
        </div>
    </div>
</div>
