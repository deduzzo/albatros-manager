<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MovimentoSpesa */
/* @var $progetti array */
/* @var $vociSpesa array */

$this->title = 'Modifica Movimento #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Movimenti di Spesa', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Movimento #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="movimento-spesa-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-arrows-exchange me-2"></i>
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
                'vociSpesa' => $vociSpesa,
            ]) ?>
        </div>
    </div>
</div>
