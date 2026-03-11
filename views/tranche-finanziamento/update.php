<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\TrancheFinanziamento */
/* @var $progetti array */

$this->title = 'Modifica Tranche #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tranche di Finanziamento', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Tranche #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="tranche-finanziamento-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-coins me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
                'progetti' => $progetti,
            ]) ?>
        </div>
    </div>
</div>
