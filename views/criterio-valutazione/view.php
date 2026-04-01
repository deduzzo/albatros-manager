<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\CriterioValutazioneStudente */

$this->title = 'Criterio: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Criteri di Valutazione', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->nome;
?>

<div class="criterio-valutazione-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-star me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo criterio di valutazione?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dettagli Criterio</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID</div>
                            <div class="datagrid-content"><?= $model->id ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nome Criterio</div>
                            <div class="datagrid-content"><?= Html::encode($model->nome) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Corso</div>
                            <div class="datagrid-content">
                                <?php if ($model->isGlobale()): ?>
                                    <span class="badge bg-azure-lt"><i class="ti ti-world me-1"></i>Globale (tutti i corsi)</span>
                                <?php elseif ($model->avviso): ?>
                                    <?= Html::a(Html::encode($model->avviso->titolo), ['/avviso/view', 'id' => $model->avviso_id]) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Peso</div>
                            <div class="datagrid-content"><strong><?= Yii::$app->formatter->asDecimal($model->peso, 2) ?></strong></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Punteggio Minimo</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDecimal($model->punteggio_min, 1) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Punteggio Massimo</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDecimal($model->punteggio_max, 1) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Ordine</div>
                            <div class="datagrid-content"><?= $model->ordine ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Attivo</div>
                            <div class="datagrid-content">
                                <?= $model->attivo
                                    ? '<span class="badge bg-green text-green-fg">Si</span>'
                                    : '<span class="badge bg-red text-red-fg">No</span>' ?>
                            </div>
                        </div>
                        <?php if ($model->descrizione): ?>
                        <div class="datagrid-item" style="grid-column: span 2;">
                            <div class="datagrid-title">Descrizione</div>
                            <div class="datagrid-content"><?= nl2br(Html::encode($model->descrizione)) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Creato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Aggiornato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
