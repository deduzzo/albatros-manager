<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Docente */

$this->title = 'Nuovo Docente';
$this->params['breadcrumbs'][] = ['label' => 'Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="docente-create">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-user-plus me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Inserisci i dati del nuovo docente
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>
