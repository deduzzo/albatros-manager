<?php
use yii\helpers\Html;

$this->title = 'Nuova Lezione';
$this->params['breadcrumbs'][] = ['label' => 'Calendario Lezioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="calendario-create">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-plus me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Programma una nuova lezione</div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body"><div class="container-xl"><?= $this->render('_form', ['model' => $model, 'moduli' => $moduli, 'docenti' => $docenti]) ?></div></div>
</div>
