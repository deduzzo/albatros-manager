<?php
use yii\helpers\Html;

$this->title = 'Modifica Valutazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Valutazioni Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Valutazione #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>
<div class="valutazione-docente-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col"><h2 class="page-title"><?= Html::encode($this->title) ?></h2></div>
            </div>
        </div>
    </div>
    <div class="page-body"><div class="container-xl"><?= $this->render('_form', ['model' => $model, 'moduli' => $moduli, 'docenti' => $docenti]) ?></div></div>
</div>
