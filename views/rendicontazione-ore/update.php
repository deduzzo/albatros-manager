<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RendicontazioneOre */
/* @var $assegnazione app\models\Assegnazione */

$modulo = $assegnazione->modulo;

$this->title = 'Modifica Rendicontazione #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Moduli/Corsi', 'url' => ['/modulo/index']];
if ($modulo) {
    $this->params['breadcrumbs'][] = ['label' => $modulo->codice, 'url' => ['/modulo/view', 'id' => $modulo->id]];
}
$this->params['breadcrumbs'][] = ['label' => 'Rendicontazione Ore', 'url' => ['index', 'assegnazione_id' => $assegnazione->id]];
$this->params['breadcrumbs'][] = 'Modifica';
?>

<div class="rendicontazione-ore-update">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-clock-edit me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Data: <?= Yii::$app->formatter->asDate($model->data) ?>
                        &mdash; <?= number_format($model->ore, 1, ',', '.') ?> ore
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= $this->render('_form', [
                'model' => $model,
                'assegnazione' => $assegnazione,
            ]) ?>
        </div>
    </div>
</div>
