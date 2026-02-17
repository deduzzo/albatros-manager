<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\TagCurriculum */

$this->title = 'Tag: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Gestione Tag', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->nome;
?>

<div class="tag-curriculum-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= $model->getBadgeHtml() ?>
                        <span class="ms-2"><?= Html::encode($this->title) ?></span>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo tag?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Dettagli Tag</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered'],
                                'attributes' => [
                                    'id',
                                    [
                                        'attribute' => 'categoria_id',
                                        'format' => 'raw',
                                        'value' => $model->categoria
                                            ? '<span class="badge" style="background-color: ' . Html::encode($model->categoria->colore) . '; color: #fff;">'
                                                . Html::encode($model->categoria->nome) . '</span>'
                                                . ' ' . Html::a('<i class="ti ti-external-link"></i>', ['/tag-categoria/view', 'id' => $model->categoria_id])
                                            : '-',
                                    ],
                                    [
                                        'attribute' => 'codice',
                                        'format' => 'raw',
                                        'value' => '<span class="badge bg-blue-lt">' . Html::encode($model->codice) . '</span>',
                                    ],
                                    'nome',
                                    'descrizione:ntext',
                                    'keywords',
                                    'ordine',
                                    [
                                        'attribute' => 'attivo',
                                        'format' => 'raw',
                                        'value' => $model->attivo
                                            ? '<span class="badge bg-green text-green-fg">Si</span>'
                                            : '<span class="badge bg-default text-default-fg">No</span>',
                                    ],
                                    'created_at:datetime',
                                    'updated_at:datetime',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                Docenti con questo tag
                                <span class="badge bg-blue-lt ms-2"><?= $model->getDocentiCount() ?></span>
                            </h3>
                        </div>
                        <?php $docenteTags = $model->getDocenteTags()->with('docente')->limit(20)->all(); ?>
                        <?php if ($docenteTags): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($docenteTags as $dt): ?>
                            <?php if ($dt->docente): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <?= Html::a(
                                        '<strong>' . Html::encode($dt->docente->getNomeCompleto()) . '</strong>',
                                        ['/docente/view', 'id' => $dt->docente->codice_fiscale]
                                    ) ?>
                                    <br>
                                    <small class="text-muted"><?= Html::encode($dt->docente->codice_fiscale) ?></small>
                                </div>
                                <div>
                                    <?php if ($dt->livello): ?>
                                        <small class="text-muted" title="<?= $dt->getLivelloLabel() ?>">
                                            <?= $dt->getLivelloStarsHtml() ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($model->getDocentiCount() > 20): ?>
                        <div class="card-footer text-center">
                            <small class="text-muted">Mostrati 20 di <?= $model->getDocentiCount() ?> docenti</small>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="card-body text-muted text-center">
                            <i class="ti ti-users mb-2" style="font-size: 2rem;"></i>
                            <p>Nessun docente associato a questo tag</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
</div>
