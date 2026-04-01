<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Modulo */
/* @var $avvisiList array */
/* @var $sediList array */

$this->title = 'Modifica: ' . $model->codice;
$this->params['breadcrumbs'][] = ['label' => 'Moduli/Insegnamenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->codice, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="modulo-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
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
                'avvisiList' => $avvisiList,
                'sediList' => $sediList,
            ]) ?>
        </div>
    </div>
</div>
