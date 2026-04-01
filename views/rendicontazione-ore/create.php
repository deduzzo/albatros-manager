<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RendicontazioneOre */
/* @var $assegnazione app\models\Assegnazione */

$modulo = $assegnazione->modulo;

$this->title = 'Nuova Rendicontazione Ore';
$this->params['breadcrumbs'][] = ['label' => 'Moduli/Insegnamenti', 'url' => ['/modulo/index']];
if ($modulo) {
    $this->params['breadcrumbs'][] = ['label' => $modulo->codice, 'url' => ['/modulo/view', 'id' => $modulo->id]];
}
$this->params['breadcrumbs'][] = ['label' => 'Rendicontazione Ore', 'url' => ['index', 'assegnazione_id' => $assegnazione->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rendicontazione-ore-create">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-clock-plus me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Inserisci le ore lavorate per la giornata
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
