<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;
use app\models\Docente;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DocenteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $totaleDocenti int */
/* @var $totaleCoordinatori int */
/* @var $totaleTutor int */
/* @var $conAssegnazioniAttive int */
/* @var $progetti app\models\Progetto[] */
/* @var $preselectedModulo app\models\Modulo|null */

$this->title = 'Albo Docenti';
$this->params['breadcrumbs'][] = ['label' => 'Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="docente-albo">
    <?php if (isset($preselectedModulo) && $preselectedModulo): ?>
    <div class="container-xl mt-3">
        <div class="alert alert-info d-flex align-items-center">
            <i class="ti ti-info-circle me-2" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Assegnazione d'ufficio</strong> — Cerca un docente da assegnare al modulo
                <span class="badge bg-blue text-blue-fg ms-1"><?= Html::encode($preselectedModulo->codice) ?></span>
                <span class="text-muted ms-1"><?= Html::encode($preselectedModulo->titolo) ?></span>
                <br><small>Clicca il pulsante <i class="ti ti-link"></i> sul docente scelto per assegnarlo.</small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-address-book me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Elenco completo dei docenti nel sistema con possibilita di assegnazione diretta
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-list me-1"></i> Lista Docenti', ['index'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Docente', ['create'], ['class' => 'btn btn-success']) ?>
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
                                <div class="subheader">Totale Docenti</div>
                            </div>
                            <div class="d-flex align-items-baseline mt-2">
                                <div class="h1 mb-0 me-2"><?= $totaleDocenti ?></div>
                                <div class="me-auto">
                                    <span class="text-muted"><i class="ti ti-users"></i> nel sistema</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Coordinatori</div>
                            </div>
                            <div class="d-flex align-items-baseline mt-2">
                                <div class="h1 mb-0 me-2"><?= $totaleCoordinatori ?></div>
                                <div class="me-auto">
                                    <span class="badge bg-purple text-purple-fg">coordinatore</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Tutor</div>
                            </div>
                            <div class="d-flex align-items-baseline mt-2">
                                <div class="h1 mb-0 me-2"><?= $totaleTutor ?></div>
                                <div class="me-auto">
                                    <span class="badge bg-teal text-teal-fg">tutor</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Con Assegnazioni Attive</div>
                            </div>
                            <div class="d-flex align-items-baseline mt-2">
                                <div class="h1 mb-0 me-2"><?= $conAssegnazioniAttive ?></div>
                                <div class="me-auto">
                                    <span class="text-muted"><i class="ti ti-briefcase"></i> impegnati</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtri rapidi -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="get" action="<?= Url::to(['albo']) ?>" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Ricerca (nome, CF, email, tag...)</label>
                            <?= Html::activeTextInput($searchModel, 'ricerca', [
                                'class' => 'form-control',
                                'placeholder' => 'Cerca nell\'albo...',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tipo</label>
                            <?= Html::activeDropDownList($searchModel, 'tipo', Docente::getTipoList(), [
                                'class' => 'form-select',
                                'prompt' => 'Tutti',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Provenienza</label>
                            <?= Html::activeDropDownList($searchModel, 'provenienza', Docente::getProvenienzaList(), [
                                'class' => 'form-select',
                                'prompt' => 'Tutti',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Con assegnazioni</label>
                            <?= Html::activeDropDownList($searchModel, 'con_assegnazioni', ['1' => 'Si', '0' => 'No'], [
                                'class' => 'form-select',
                                'prompt' => 'Tutti',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-search me-1"></i> Cerca
                            </button>
                        </div>
                        <div class="col-12">
                            <?= Html::a('<i class="ti ti-filter-off me-1"></i> Reset filtri', ['albo'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabella Albo -->
            <div class="card">
                <?= AppGridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'attribute' => 'cognome',
                            'label' => 'Nome Completo',
                            'format' => 'raw',
                            'content' => function($model) {
                                $avatar = '<span class="avatar avatar-sm bg-blue-lt me-2">' . Html::encode($model->getIniziali()) . '</span>';
                                $nome = Html::a(
                                    Html::encode($model->getNomeCompleto()),
                                    ['view', 'id' => $model->codice_fiscale],
                                    ['class' => 'text-reset fw-semibold']
                                );
                                return '<div class="d-flex align-items-center">' . $avatar . $nome . '</div>';
                            },
                        ],
                        [
                            'attribute' => 'tipo',
                            'format' => 'raw',
                            'content' => function($model) {
                                return $model->getTipoBadge();
                            },
                            'filter' => Docente::getTipoList(),
                            'headerOptions' => ['style' => 'width: 130px;'],
                        ],
                        [
                            'attribute' => 'provenienza',
                            'format' => 'raw',
                            'content' => function($model) {
                                return $model->getProvenienzaBadge();
                            },
                            'filter' => Docente::getProvenienzaList(),
                            'headerOptions' => ['style' => 'width: 100px;'],
                        ],
                        [
                            'attribute' => 'email',
                            'format' => 'email',
                        ],
                        [
                            'attribute' => 'telefono',
                            'headerOptions' => ['style' => 'width: 140px;'],
                            'content' => function($model) {
                                return $model->telefono ? Html::encode($model->telefono) : '<span class="text-muted">-</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'num_moduli_assegnati',
                            'label' => 'Moduli Assegnati',
                            'format' => 'raw',
                            'content' => function($model) {
                                $count = (int) $model->num_moduli_assegnati;
                                if ($count === 0) {
                                    return '<span class="badge bg-default text-default-fg">0</span>';
                                }
                                $class = $count > 5 ? 'bg-red text-red-fg' : ($count >= 3 ? 'bg-orange text-orange-fg' : 'bg-green text-green-fg');
                                return Html::a(
                                    '<span class="badge ' . $class . '">' . $count . '</span>',
                                    ['/assegnazione/index', 'AssegnazioneSearch[docente_codice_fiscale]' => $model->codice_fiscale],
                                    ['title' => 'Visualizza assegnazioni']
                                );
                            },
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'filter' => ['0' => 'Nessuna', '1' => '1+', '3' => '3+', '5' => '5+'],
                        ],
                        [
                            'attribute' => 'valutazione_media',
                            'label' => 'Valutazione',
                            'format' => 'raw',
                            'content' => function($model) {
                                $media = $model->valutazione_media;
                                if ($media === null) {
                                    return '<span class="text-muted">-</span>';
                                }
                                $media = (float) $media;
                                $mediaArrotondata = round($media);
                                $html = '';
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $mediaArrotondata) {
                                        $html .= '<i class="ti ti-star-filled text-yellow"></i>';
                                    } else {
                                        $html .= '<i class="ti ti-star text-muted"></i>';
                                    }
                                }
                                $html .= ' <span class="text-muted small">(' . number_format($media, 1) . ')</span>';
                                return $html;
                            },
                            'headerOptions' => ['style' => 'width: 170px;'],
                            'filter' => [1 => '1+', 2 => '2+', 3 => '3+', 4 => '4+', 5 => '5'],
                        ],
                        [
                            'label' => 'Competenze',
                            'format' => 'raw',
                            'content' => function($model) {
                                $tags = $model->getTags()->limit(3)->all();
                                if (empty($tags)) {
                                    return '<span class="text-muted">-</span>';
                                }
                                $html = '';
                                foreach ($tags as $tag) {
                                    $colore = $tag->categoria ? $tag->categoria->colore : '#206bc4';
                                    $html .= '<span class="badge me-1" style="background-color: ' . Html::encode($colore) . '; color: #fff;" title="' . Html::encode($tag->categoria ? $tag->categoria->nome : '') . '">'
                                        . Html::encode($tag->nome) . '</span>';
                                }
                                $count = $model->getDocenteTags()->count();
                                if ($count > 3) {
                                    $html .= '<span class="badge bg-default text-default-fg">+' . ($count - 3) . '</span>';
                                }
                                return $html;
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {assegna}',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    return Html::a('<i class="ti ti-eye"></i>', $url, [
                                        'class' => 'btn btn-info btn-sm me-1',
                                        'title' => 'Visualizza',
                                        'data-bs-toggle' => 'tooltip',
                                    ]);
                                },
                                'assegna' => function ($url, $model) {
                                    return '<button type="button" class="btn btn-primary btn-sm btn-assegna-docente"'
                                        . ' data-bs-toggle="modal" data-bs-target="#modal-assegna"'
                                        . ' data-cf="' . Html::encode($model->codice_fiscale) . '"'
                                        . ' data-nome="' . Html::encode($model->getNomeCompleto()) . '"'
                                        . ' title="Assegna a modulo">'
                                        . '<i class="ti ti-link"></i>'
                                        . '</button>';
                                },
                            ],
                        ],
                    ],
                ]); ?>

                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted small">
                        Totale: <?= $dataProvider->totalCount ?> docenti nell'albo
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Assegnazione Docente -->
<?= $this->render('_albo-assegna-modal', ['progetti' => $progetti]) ?>

<?php
$assegnaUrl = Url::to(['assegna-da-albo']);
$avvisiUrl = Url::to(['avvisi-progetto']);
$moduliUrl = Url::to(['moduli-disponibili']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
// Gestione apertura modal con dati docente
document.querySelectorAll('.btn-assegna-docente').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var cf = this.getAttribute('data-cf');
        var nome = this.getAttribute('data-nome');
        document.getElementById('assegna-docente-cf').value = cf;
        document.getElementById('assegna-docente-nome').textContent = nome;
        // Reset selects
        document.getElementById('assegna-progetto').value = '';
        document.getElementById('assegna-avviso').innerHTML = '<option value="">-- Seleziona prima un progetto --</option>';
        document.getElementById('assegna-avviso').disabled = true;
        document.getElementById('assegna-modulo').innerHTML = '<option value="">-- Seleziona prima un avviso --</option>';
        document.getElementById('assegna-modulo').disabled = true;
        document.getElementById('assegna-note').value = '';
        document.getElementById('assegna-submit').disabled = true;
        document.getElementById('assegna-result').innerHTML = '';
    });
});

// Cascading: Progetto -> Avviso
document.getElementById('assegna-progetto').addEventListener('change', function() {
    var progettoId = this.value;
    var avvisoSelect = document.getElementById('assegna-avviso');
    var moduloSelect = document.getElementById('assegna-modulo');

    avvisoSelect.innerHTML = '<option value="">Caricamento...</option>';
    avvisoSelect.disabled = true;
    moduloSelect.innerHTML = '<option value="">-- Seleziona prima un avviso --</option>';
    moduloSelect.disabled = true;
    document.getElementById('assegna-submit').disabled = true;

    if (!progettoId) {
        avvisoSelect.innerHTML = '<option value="">-- Seleziona prima un progetto --</option>';
        return;
    }

    fetch('{$avvisiUrl}?progetto_id=' + progettoId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var html = '<option value="">-- Seleziona un avviso --</option>';
        data.forEach(function(a) {
            html += '<option value="' + a.id + '">' + a.codice + ' - ' + a.titolo + ' [' + a.status + ']</option>';
        });
        avvisoSelect.innerHTML = html;
        avvisoSelect.disabled = false;
    })
    .catch(function() {
        avvisoSelect.innerHTML = '<option value="">Errore nel caricamento</option>';
    });
});

// Cascading: Avviso -> Modulo
document.getElementById('assegna-avviso').addEventListener('change', function() {
    var avvisoId = this.value;
    var moduloSelect = document.getElementById('assegna-modulo');

    moduloSelect.innerHTML = '<option value="">Caricamento...</option>';
    moduloSelect.disabled = true;
    document.getElementById('assegna-submit').disabled = true;

    if (!avvisoId) {
        moduloSelect.innerHTML = '<option value="">-- Seleziona prima un avviso --</option>';
        return;
    }

    fetch('{$moduliUrl}?avviso_id=' + avvisoId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var html = '<option value="">-- Seleziona un modulo --</option>';
        data.forEach(function(m) {
            if (m.disponibile) {
                html += '<option value="' + m.id + '">' + m.codice + ' - ' + m.titolo + ' (' + m.ore_totali + 'h)</option>';
            } else {
                html += '<option value="" disabled class="text-muted">' + m.codice + ' - ' + m.titolo + ' [Assegnato a: ' + m.docente_assegnato + ']</option>';
            }
        });
        moduloSelect.innerHTML = html;
        moduloSelect.disabled = false;
    })
    .catch(function() {
        moduloSelect.innerHTML = '<option value="">Errore nel caricamento</option>';
    });
});

// Abilita submit quando modulo selezionato
document.getElementById('assegna-modulo').addEventListener('change', function() {
    document.getElementById('assegna-submit').disabled = !this.value;
});

// Submit assegnazione
document.getElementById('assegna-submit').addEventListener('click', function() {
    var btn = this;
    var cf = document.getElementById('assegna-docente-cf').value;
    var moduloId = document.getElementById('assegna-modulo').value;
    var note = document.getElementById('assegna-note').value;
    var resultDiv = document.getElementById('assegna-result');

    if (!cf || !moduloId) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Assegnazione in corso...';
    resultDiv.innerHTML = '';

    var formData = new FormData();
    formData.append('docente_codice_fiscale', cf);
    formData.append('modulo_id', moduloId);
    formData.append('note', note);
    formData.append('{$csrfParam}', '{$csrfToken}');

    fetch('{$assegnaUrl}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success mt-3"><i class="ti ti-check me-2"></i>' + data.message + '</div>';
            btn.innerHTML = '<i class="ti ti-check me-1"></i> Assegnato!';
            setTimeout(function() {
                location.reload();
            }, 1500);
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger mt-3"><i class="ti ti-alert-circle me-2"></i>' + data.message + '</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-link me-1"></i> Assegna';
        }
    })
    .catch(function() {
        resultDiv.innerHTML = '<div class="alert alert-danger mt-3"><i class="ti ti-alert-circle me-2"></i>Errore di comunicazione con il server.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-link me-1"></i> Assegna';
    });
});
JS;
$this->registerJs($js);
?>
