<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppGridView;
use app\models\User;
use app\models\DocenteAuth;
use app\models\AllievoAuth;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $docentiSearchModel app\models\DocenteAuthSearch */
/* @var $docentiDataProvider yii\data\ActiveDataProvider */
/* @var $studentiSearchModel app\models\AllievoAuthSearch */
/* @var $studentiDataProvider yii\data\ActiveDataProvider */

$this->title = 'Gestione Utenti';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="utente-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-users-cog me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione credenziali e sicurezza per tutti gli utenti del sistema
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Utente', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                        <li class="nav-item">
                            <a href="#tab-admin" class="nav-link active" data-bs-toggle="tab">
                                <i class="ti ti-shield me-2"></i>
                                Utenti Admin
                                <span class="badge bg-blue-lt ms-2"><?= $dataProvider->totalCount ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-docenti" class="nav-link" data-bs-toggle="tab">
                                <i class="ti ti-user me-2"></i>
                                Docenti
                                <span class="badge bg-green-lt ms-2"><?= $docentiDataProvider->totalCount ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-studenti" class="nav-link" data-bs-toggle="tab">
                                <i class="ti ti-school me-2"></i>
                                Studenti
                                <span class="badge bg-purple-lt ms-2"><?= $studentiDataProvider->totalCount ?></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <!-- Tab Utenti Admin -->
                    <div class="tab-pane active show" id="tab-admin">
                        <?= AppGridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'columns' => [
                                [
                                    'attribute' => 'nominativo',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        $iniziali = mb_substr($model->nominativo ?: $model->username, 0, 2);
                                        $avatar = '<span class="avatar avatar-sm bg-blue-lt me-2">' . Html::encode(mb_strtoupper($iniziali)) . '</span>';
                                        $nome = Html::a(
                                            Html::encode($model->nominativo ?: $model->username),
                                            ['view', 'id' => $model->id],
                                            ['class' => 'text-reset']
                                        );
                                        return '<div class="d-flex align-items-center">' . $avatar . $nome . '</div>';
                                    },
                                ],
                                'username',
                                [
                                    'attribute' => 'email',
                                    'format' => 'email',
                                ],
                                [
                                    'attribute' => 'ruolo',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        $colors = [
                                            User::RUOLO_ADMIN => 'bg-red text-red-fg',
                                            User::RUOLO_SEGRETERIA => 'bg-blue text-blue-fg',
                                            User::RUOLO_COORDINATORE => 'bg-purple text-purple-fg',
                                            User::RUOLO_OPERATORE => 'bg-default text-default-fg',
                                        ];
                                        $class = $colors[$model->ruolo] ?? 'bg-default text-default-fg';
                                        return '<span class="badge ' . $class . '">' . Html::encode($model->getRuoloLabel()) . '</span>';
                                    },
                                    'filter' => User::getRuoloOptions(),
                                    'headerOptions' => ['style' => 'width: 140px;'],
                                ],
                                [
                                    'attribute' => 'status',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                                    },
                                    'filter' => [
                                        User::STATUS_ACTIVE => 'Attivo',
                                        User::STATUS_INACTIVE => 'Disabilitato',
                                    ],
                                    'headerOptions' => ['style' => 'width: 110px;'],
                                ],
                                [
                                    'attribute' => 'totp_enabled',
                                    'label' => '2FA',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        if ($model->totp_enabled) {
                                            return '<i class="ti ti-shield-check text-green" title="2FA Attivo" data-bs-toggle="tooltip"></i>';
                                        }
                                        return '<i class="ti ti-shield-off text-muted" title="2FA Non attivo" data-bs-toggle="tooltip"></i>';
                                    },
                                    'filter' => [0 => 'No', 1 => 'Si'],
                                    'headerOptions' => ['style' => 'width: 60px;'],
                                    'contentOptions' => ['class' => 'text-center'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{view} {update} {delete}',
                                    'headerOptions' => ['style' => 'width: 100px;'],
                                    'buttons' => [
                                        'delete' => function($url, $model) {
                                            if ($model->id === Yii::$app->user->id) {
                                                return '';
                                            }
                                            return Html::a('<i class="ti ti-trash"></i>', $url, [
                                                'title' => 'Elimina',
                                                'data-method' => 'post',
                                                'data-confirm' => 'Sei sicuro di voler eliminare questo utente?',
                                            ]);
                                        },
                                    ],
                                ],
                            ],
                        ]); ?>

                        <div class="card-footer d-flex align-items-center">
                            <p class="m-0 text-muted small">
                                Totale: <?= $dataProvider->totalCount ?> utenti admin
                            </p>
                        </div>
                    </div>

                    <!-- Tab Docenti -->
                    <div class="tab-pane" id="tab-docenti">
                        <?= AppGridView::widget([
                            'dataProvider' => $docentiDataProvider,
                            'columns' => [
                                [
                                    'label' => 'Nominativo',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        $docente = $model->docente;
                                        if ($docente) {
                                            $nome = $docente->cognome . ' ' . $docente->nome;
                                            $iniziali = mb_strtoupper(mb_substr($docente->cognome, 0, 1) . mb_substr($docente->nome, 0, 1));
                                        } else {
                                            $nome = $model->docente_codice_fiscale;
                                            $iniziali = '??';
                                        }
                                        $avatar = '<span class="avatar avatar-sm bg-green-lt me-2">' . Html::encode($iniziali) . '</span>';
                                        $link = Html::a(
                                            Html::encode($nome),
                                            ['view-auth', 'type' => 'docente', 'id' => $model->id],
                                            ['class' => 'text-reset']
                                        );
                                        return '<div class="d-flex align-items-center">' . $avatar . $link . '</div>';
                                    },
                                ],
                                [
                                    'attribute' => 'docente_codice_fiscale',
                                    'label' => 'Codice Fiscale',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        return '<code>' . Html::encode($model->docente_codice_fiscale) . '</code>';
                                    },
                                    'headerOptions' => ['style' => 'width: 180px;'],
                                ],
                                [
                                    'label' => 'Email',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        $docente = $model->docente;
                                        return $docente && $docente->email ? Html::mailto(Html::encode($docente->email)) : '<span class="text-muted">-</span>';
                                    },
                                ],
                                [
                                    'attribute' => 'status',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                                    },
                                    'headerOptions' => ['style' => 'width: 110px;'],
                                ],
                                [
                                    'attribute' => 'totp_enabled',
                                    'label' => '2FA',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        if ($model->totp_enabled) {
                                            return '<i class="ti ti-shield-check text-green" title="2FA Attivo" data-bs-toggle="tooltip"></i>';
                                        }
                                        return '<i class="ti ti-shield-off text-muted" title="2FA Non attivo" data-bs-toggle="tooltip"></i>';
                                    },
                                    'headerOptions' => ['style' => 'width: 60px;'],
                                    'contentOptions' => ['class' => 'text-center'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{view}',
                                    'headerOptions' => ['style' => 'width: 50px;'],
                                    'buttons' => [
                                        'view' => function($url, $model) {
                                            return Html::a('<i class="ti ti-eye"></i>', ['view-auth', 'type' => 'docente', 'id' => $model->id], [
                                                'title' => 'Dettaglio',
                                            ]);
                                        },
                                    ],
                                ],
                            ],
                        ]); ?>

                        <div class="card-footer d-flex align-items-center">
                            <p class="m-0 text-muted small">
                                Totale: <?= $docentiDataProvider->totalCount ?> account docenti
                            </p>
                        </div>
                    </div>

                    <!-- Tab Studenti -->
                    <div class="tab-pane" id="tab-studenti">
                        <?= AppGridView::widget([
                            'dataProvider' => $studentiDataProvider,
                            'columns' => [
                                [
                                    'label' => 'Nominativo',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        $allievo = $model->allievo;
                                        if ($allievo) {
                                            $nome = $allievo->cognome . ' ' . $allievo->nome;
                                            $iniziali = mb_strtoupper(mb_substr($allievo->cognome, 0, 1) . mb_substr($allievo->nome, 0, 1));
                                        } else {
                                            $nome = $model->allievo_codice_fiscale;
                                            $iniziali = '??';
                                        }
                                        $avatar = '<span class="avatar avatar-sm bg-purple-lt me-2">' . Html::encode($iniziali) . '</span>';
                                        $link = Html::a(
                                            Html::encode($nome),
                                            ['view-auth', 'type' => 'studente', 'id' => $model->id],
                                            ['class' => 'text-reset']
                                        );
                                        return '<div class="d-flex align-items-center">' . $avatar . $link . '</div>';
                                    },
                                ],
                                [
                                    'attribute' => 'allievo_codice_fiscale',
                                    'label' => 'Codice Fiscale',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        return '<code>' . Html::encode($model->allievo_codice_fiscale) . '</code>';
                                    },
                                    'headerOptions' => ['style' => 'width: 180px;'],
                                ],
                                [
                                    'label' => 'Email',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        $allievo = $model->allievo;
                                        return $allievo && $allievo->email ? Html::mailto(Html::encode($allievo->email)) : '<span class="text-muted">-</span>';
                                    },
                                ],
                                [
                                    'attribute' => 'status',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                                    },
                                    'headerOptions' => ['style' => 'width: 110px;'],
                                ],
                                [
                                    'attribute' => 'totp_enabled',
                                    'label' => '2FA',
                                    'format' => 'raw',
                                    'content' => function($model) {
                                        if ($model->totp_enabled) {
                                            return '<i class="ti ti-shield-check text-green" title="2FA Attivo" data-bs-toggle="tooltip"></i>';
                                        }
                                        return '<i class="ti ti-shield-off text-muted" title="2FA Non attivo" data-bs-toggle="tooltip"></i>';
                                    },
                                    'headerOptions' => ['style' => 'width: 60px;'],
                                    'contentOptions' => ['class' => 'text-center'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{view}',
                                    'headerOptions' => ['style' => 'width: 50px;'],
                                    'buttons' => [
                                        'view' => function($url, $model) {
                                            return Html::a('<i class="ti ti-eye"></i>', ['view-auth', 'type' => 'studente', 'id' => $model->id], [
                                                'title' => 'Dettaglio',
                                            ]);
                                        },
                                    ],
                                ],
                            ],
                        ]); ?>

                        <div class="card-footer d-flex align-items-center">
                            <p class="m-0 text-muted small">
                                Totale: <?= $studentiDataProvider->totalCount ?> account studenti
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
