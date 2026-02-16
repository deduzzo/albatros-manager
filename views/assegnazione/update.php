<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Assegnazione */
/* @var $moduli array */
/* @var $docenti array */
/* @var $istanze array */

$docenteNome = $model->docente ? $model->docente->cognome . ' ' . $model->docente->nome : 'N/D';

$this->title = 'Modifica Assegnazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Assegnazioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Assegnazione #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="assegnazione-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <?= Html::encode($docenteNome) ?>
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
