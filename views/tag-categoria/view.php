<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\TagCategoria */

$this->title = 'Categoria: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Categorie Tag', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->nome;
?>

<div class="tag-categoria-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?php if ($model->icona): ?>
                            <i class="<?= Html::encode($model->icona) ?> me-2" style="color: <?= Html::encode($model->colore) ?>;"></i>
                        <?php endif; ?>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questa categoria tag?',
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
                            <h3 class="card-title">Dettagli Categoria</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered'],
                                'attributes' => [
                                    'id',
                                    'nome',
                                    'descrizione:ntext',
                                    [
                                        'attribute' => 'colore',
                                        'format' => 'raw',
                                        'value' => '<span class="badge" style="background-color: ' . Html::encode($model->colore) . '; color: #fff;">' . Html::encode($model->colore) . '</span>'
                                            . ' <code>' . Html::encode($model->colore) . '</code>',
                                    ],
                                    [
                                        'attribute' => 'icona',
                                        'format' => 'raw',
                                        'value' => $model->icona
                                            ? '<i class="' . Html::encode($model->icona) . ' me-2" style="font-size: 1.5rem; color: ' . Html::encode($model->colore) . ';"></i> <code>' . Html::encode($model->icona) . '</code>'
                                            : '<span class="text-muted">Nessuna icona</span>',
                                    ],
                                    'ordine',
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
                                Tag in questa categoria
                                <span class="badge bg-blue-lt ms-2"><?= $model->getTagCount() ?></span>
                            </h3>
                            <div class="card-actions">
                                <?= Html::a('<i class="ti ti-plus me-1"></i> Nuovo Tag', ['/tag-curriculum/create', 'categoria_id' => $model->id], ['class' => 'btn btn-sm btn-success']) ?>
                            </div>
                        </div>
                        <?php $tags = $model->getTags()->where(['attivo' => true])->orderBy('ordine')->all(); ?>
                        <?php if ($tags): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($tags as $tag): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge" style="background-color: <?= Html::encode($model->colore) ?>; color: #fff;"><?= Html::encode($tag->codice) ?></span>
                                    <strong class="ms-2"><?= Html::encode($tag->nome) ?></strong>
                                    <?php if ($tag->keywords): ?>
                                        <br><small class="text-muted"><?= Html::encode($tag->keywords) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="badge bg-blue-lt" title="Docenti associati"><?= $tag->getDocentiCount() ?></span>
                                    <?= Html::a('<i class="ti ti-eye"></i>', ['/tag-curriculum/view', 'id' => $tag->id], [
                                        'class' => 'btn btn-sm btn-ghost-primary ms-1',
                                        'title' => 'Visualizza tag',
                                    ]) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="card-body text-muted text-center">
                            <i class="ti ti-tags mb-2" style="font-size: 2rem;"></i>
                            <p>Nessun tag in questa categoria</p>
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
