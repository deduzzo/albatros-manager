<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;

/* @var $this yii\web\View */
/* @var $tag app\models\TagCurriculum */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Docenti con tag: ' . $tag->nome;
$this->params['breadcrumbs'][] = ['label' => 'Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$colore = $tag->categoria ? $tag->categoria->colore : '#206bc4';
?>

<div class="docente-by-tag">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg" style="background-color: <?= Html::encode($colore) ?>;">
                        <?php if ($tag->categoria && $tag->categoria->icona): ?>
                            <i class="<?= Html::encode($tag->categoria->icona) ?>"></i>
                        <?php else: ?>
                            <i class="ti ti-tag"></i>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        Docenti con competenza:
                        <span class="badge ms-2" style="background-color: <?= Html::encode($colore) ?>; color: #fff; font-size: 1rem;">
                            <?= Html::encode($tag->nome) ?>
                        </span>
                    </h2>
                    <div class="text-muted mt-1">
                        <?php if ($tag->categoria): ?>
                            Categoria: <?= Html::encode($tag->categoria->nome) ?>
                        <?php endif; ?>
                        <?php if ($tag->descrizione): ?>
                            <span class="mx-2">|</span>
                            <?= Html::encode($tag->descrizione) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?php if ($tag->keywords): ?>
                <div class="alert alert-info mb-3">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>Keywords correlate:</strong> <?= Html::encode($tag->keywords) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'codice_fiscale',
                            'headerOptions' => ['style' => 'width: 180px;'],
                            'format' => 'raw',
                            'content' => function($model) {
                                return '<code>' . Html::encode($model->codice_fiscale) . '</code>';
                            },
                        ],
                        [
                            'attribute' => 'cognome',
                            'format' => 'raw',
                            'content' => function($model) {
                                $avatar = '<span class="avatar avatar-sm bg-blue-lt me-2">' . Html::encode($model->getIniziali()) . '</span>';
                                $nome = Html::a(
                                    Html::encode($model->getNomeCompleto()),
                                    ['view', 'id' => $model->codice_fiscale],
                                    ['class' => 'text-reset']
                                );
                                return '<div class="d-flex align-items-center">' . $avatar . $nome . '</div>';
                            },
                        ],
                        [
                            'attribute' => 'email',
                            'format' => 'email',
                        ],
                        [
                            'attribute' => 'citta',
                            'format' => 'raw',
                            'content' => function($model) {
                                if (!$model->citta) return '-';
                                $prov = $model->provincia ? ' (' . $model->provincia . ')' : '';
                                return Html::encode($model->citta) . $prov;
                            },
                        ],
                        [
                            'label' => 'Livello',
                            'format' => 'raw',
                            'content' => function($model) use ($tag) {
                                $docenteTag = \app\models\DocenteTag::findOne([
                                    'docente_codice_fiscale' => $model->codice_fiscale,
                                    'tag_id' => $tag->id,
                                ]);
                                if ($docenteTag && $docenteTag->livello) {
                                    return $docenteTag->getLivelloStarsHtml();
                                }
                                return '<span class="text-muted">-</span>';
                            },
                            'headerOptions' => ['style' => 'width: 150px;'],
                        ],
                        [
                            'label' => 'Valutazione',
                            'format' => 'raw',
                            'content' => function($model) {
                                return $model->getValutazioneStarsHtml();
                            },
                            'headerOptions' => ['style' => 'width: 150px;'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                        ],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        <strong><?= $dataProvider->totalCount ?></strong> docenti con questa competenza
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
