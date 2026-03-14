<?php

use yii\helpers\Html;
use app\models\Notifica;

/* @var $this yii\web\View */
/* @var $model app\models\Notifica */
/* @var $letture app\models\NotificaLettura[] */

$this->title = $model->titolo;
$this->params['breadcrumbs'][] = ['label' => 'Notifiche', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$destColors = [
    Notifica::TIPO_TUTTI => 'bg-blue-lt',
    Notifica::TIPO_ADMIN => 'bg-red-lt',
    Notifica::TIPO_DOCENTE => 'bg-green-lt',
    Notifica::TIPO_STUDENTE => 'bg-purple-lt',
    Notifica::TIPO_SINGOLO_ADMIN => 'bg-red-lt',
    Notifica::TIPO_SINGOLO_DOCENTE => 'bg-green-lt',
    Notifica::TIPO_SINGOLO_STUDENTE => 'bg-purple-lt',
];
$destClass = $destColors[$model->destinatario_tipo] ?? 'bg-default-lt';
?>

<div class="notifica-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-bell me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <span class="badge <?= $destClass ?>"><?= Html::encode($model->getDestinatariLabel()) ?></span>
                        <span class="ms-2"><?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '-' ?></span>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Indietro', ['index'], ['class' => 'btn btn-outline-secondary me-2']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-outline-red',
                        'data-method' => 'post',
                        'data-confirm' => 'Sei sicuro di voler eliminare questa notifica?',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <!-- Card Info -->
                <div class="col-lg-4 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Informazioni</h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Titolo</div>
                                    <div class="datagrid-content"><?= Html::encode($model->titolo) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Destinatari</div>
                                    <div class="datagrid-content">
                                        <span class="badge <?= $destClass ?>"><?= Html::encode($model->getDestinatariLabel()) ?></span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data invio</div>
                                    <div class="datagrid-content"><?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Mittente</div>
                                    <div class="datagrid-content"><?= Html::encode($model->getCreatoDaLabel()) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Link</div>
                                    <div class="datagrid-content">
                                        <?php if (!empty($model->link)): ?>
                                            <?= Html::a(Html::encode($model->link), $model->link, ['class' => 'text-reset']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Email</div>
                                    <div class="datagrid-content">
                                        <?php if (!$model->invia_email): ?>
                                            <span class="text-muted">Non richiesta</span>
                                        <?php elseif ($model->email_inviata): ?>
                                            <span class="badge bg-green text-green-fg"><i class="ti ti-check me-1"></i>Inviata</span>
                                        <?php else: ?>
                                            <span class="badge bg-red text-red-fg"><i class="ti ti-x me-1"></i>Non inviata</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 mb-3">
                    <!-- Card Contenuto -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-file-text me-2"></i>Contenuto</h3>
                        </div>
                        <div class="card-body">
                            <?= $model->contenuto ?>
                        </div>
                    </div>

                    <!-- Card Letture -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-eye me-2"></i>Chi ha letto
                                <span class="badge bg-blue-lt ms-2"><?= count($letture) ?></span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($letture)): ?>
                                <p class="text-muted mb-0">Nessuna lettura.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Tipo utente</th>
                                                <th>ID utente</th>
                                                <th>Letta il</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($letture as $lettura): ?>
                                                <tr>
                                                    <td>
                                                        <?php
                                                        $typeLabels = ['admin' => 'Amministratore', 'docente' => 'Docente', 'studente' => 'Studente'];
                                                        $typeColors = ['admin' => 'bg-red-lt', 'docente' => 'bg-green-lt', 'studente' => 'bg-purple-lt'];
                                                        $label = $typeLabels[$lettura->user_type] ?? $lettura->user_type;
                                                        $color = $typeColors[$lettura->user_type] ?? 'bg-default-lt';
                                                        ?>
                                                        <span class="badge <?= $color ?>"><?= Html::encode($label) ?></span>
                                                    </td>
                                                    <td><?= Html::encode($lettura->user_id) ?></td>
                                                    <td><?= Yii::$app->formatter->asDatetime($lettura->letta_at) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
