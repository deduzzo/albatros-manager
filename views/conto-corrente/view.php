<?php
use yii\helpers\Html;

$this->title = 'Conto Corrente: ' . $model->banca;
$this->params['breadcrumbs'][] = ['label' => 'Conti Correnti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conto-corrente-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title"><i class="ti ti-building-bank me-2"></i><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => ['confirm' => 'Sei sicuro di voler eliminare questo conto corrente?', 'method' => 'post'],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Dettagli</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID</div>
                            <div class="datagrid-content"><?= $model->id ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Banca</div>
                            <div class="datagrid-content"><?= Html::encode($model->banca) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">IBAN</div>
                            <div class="datagrid-content"><code><?= Html::encode($model->iban) ?></code></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Intestatario</div>
                            <div class="datagrid-content"><?= Html::encode($model->intestatario) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Creato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Aggiornato il</div>
                            <div class="datagrid-content"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></div>
                        </div>
                    </div>
                    <?php if ($model->note): ?>
                        <hr><div class="mb-2"><strong>Note:</strong></div>
                        <p class="text-muted"><?= nl2br(Html::encode($model->note)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-footer">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
