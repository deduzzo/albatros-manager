<?php
use yii\helpers\Html;

$this->title = 'Modifica: ' . $model->descrizione;
$this->params['breadcrumbs'][] = ['label' => 'Tipi Documento', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->descrizione, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>
<div class="tipo-documento-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col"><h2 class="page-title"><?= Html::encode($this->title) ?></h2></div>
            </div>
        </div>
    </div>
    <div class="page-body"><div class="container-xl"><?= $this->render('_form', ['model' => $model]) ?></div></div>
</div>
