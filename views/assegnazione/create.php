<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Assegnazione */
/* @var $moduli array */
/* @var $docenti array */
/* @var $istanze array */

$this->title = 'Nuova Assegnazione';
$this->params['breadcrumbs'][] = ['label' => 'Assegnazioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="assegnazione-create">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-circle-plus me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Assegna un docente a un modulo
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
                'moduli' => $moduli,
                'docenti' => $docenti,
                'istanze' => $istanze,
            ]) ?>
        </div>
    </div>
</div>
