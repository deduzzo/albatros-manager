<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\BorsaStudio $model */
/** @var array $progetti */

$studenteNome = $model->allievo ? $model->allievo->getNomeCompleto() : $model->allievo_codice_fiscale;
$this->title = 'Modifica Borsa: ' . $studenteNome;
$this->params['breadcrumbs'][] = ['label' => 'Borse di Studio', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Borsa #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>
<div class="borsa-studio-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title"><i class="ti ti-edit me-2"></i><?= Html::encode($this->title) ?></h2>
                    <div class="text-muted mt-1">Modifica dati borsa di studio</div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', ['model' => $model, 'progetti' => $progetti]) ?>
        </div>
    </div>
</div>
