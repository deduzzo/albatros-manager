<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CriterioValutazioneStudente */
/* @var $avvisi array */

$this->title = 'Modifica Criterio: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Criteri di Valutazione', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nome, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="criterio-valutazione-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-star me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
                'avvisi' => $avvisi,
            ]) ?>
        </div>
    </div>
</div>
