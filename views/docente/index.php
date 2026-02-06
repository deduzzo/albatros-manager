<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DocenteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $categorie array */
/* @var $tags array */

$this->title = 'Docenti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="docente-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-users me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione anagrafica docenti e competenze
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Docente', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filtri avanzati -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-filter me-2"></i>
                        Filtri di Ricerca
                    </h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#filtri-avanzati">
                            Mostra/Nascondi
                        </a>
                    </div>
                </div>
                <div class="collapse show" id="filtri-avanzati">
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'action' => ['index'],
                            'method' => 'get',
                            'options' => ['class' => 'row g-3'],
                        ]); ?>

                        <div class="col-md-4">
                            <label class="form-label">Ricerca Globale</label>
                            <?= Html::activeTextInput($searchModel, 'ricerca', [
                                'class' => 'form-control',
                                'placeholder' => 'Nome, cognome, CF, email, città...',
                            ]) ?>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Provincia</label>
                            <?= Html::activeTextInput($searchModel, 'provincia', [
                                'class' => 'form-control',
                                'placeholder' => 'Es: RM',
                                'maxlength' => 2,
                            ]) ?>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Categoria Competenza</label>
                            <?= Html::activeDropDownList($searchModel, 'categoria_id', $categorie, [
                                'class' => 'form-select',
                                'prompt' => 'Tutte le categorie',
                            ]) ?>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tag Specifico</label>
                            <?= Html::activeDropDownList($searchModel, 'tag_id', $tags, [
                                'class' => 'form-select',
                                'prompt' => 'Tutti i tag',
                            ]) ?>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i> Cerca
                            </button>
                            <?= Html::a('<i class="ti ti-filter-off me-1"></i> Reset', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <!-- Tabella docenti -->
            <div class="card">
                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
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
                            'label' => 'Tag',
                            'format' => 'raw',
                            'content' => function($model) {
                                $tags = $model->getTags()->limit(3)->all();
                                if (empty($tags)) {
                                    return '<span class="text-muted">-</span>';
                                }
                                $html = '';
                                foreach ($tags as $tag) {
                                    $colore = $tag->categoria ? $tag->categoria->colore : '#206bc4';
                                    $html .= Html::a(
                                        Html::encode($tag->nome),
                                        ['by-tag', 'tagId' => $tag->id],
                                        [
                                            'class' => 'badge me-1',
                                            'style' => "background-color: {$colore};",
                                            'title' => $tag->categoria ? $tag->categoria->nome : '',
                                            'data-bs-toggle' => 'tooltip',
                                        ]
                                    );
                                }
                                $count = $model->getDocenteTags()->count();
                                if ($count > 3) {
                                    $html .= '<span class="badge bg-secondary">+' . ($count - 3) . '</span>';
                                }
                                return $html;
                            },
                        ],
                        [
                            'label' => 'Valutazione',
                            'format' => 'raw',
                            'content' => function($model) {
                                return $model->getValutazioneStarsHtml();
                            },
                            'headerOptions' => ['style' => 'width: 150px;'],
                        ],
                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> docenti
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
