<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RimborsoSpesa */
/* @var $assegnazioni array */
/* @var $vociSpesa array */

$this->title = 'Nuovo Rimborso Spesa';
$this->params['breadcrumbs'][] = ['label' => 'Rimborsi Spesa', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rimborso-spesa-create">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-receipt-2 me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Inserisci un nuovo rimborso spesa
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
                'assegnazioni' => $assegnazioni,
                'vociSpesa' => $vociSpesa,
            ]) ?>
        </div>
    </div>
</div>
