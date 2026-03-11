<?php
use yii\helpers\Html;

$this->title = 'Modifica Criterio: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Criteri Valutazione Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Modifica';
?>
<div class="criterio-valutazione-docente-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <h2 class="page-title"><i class="ti ti-edit me-2"></i><?= Html::encode($this->title) ?></h2>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', ['model' => $model]) ?>
        </div>
    </div>
</div>
