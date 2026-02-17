<?php

/**
 * Modifica ore - riusa il template inserisci-ore
 *
 * @var yii\web\View $this
 * @var app\models\RendicontazioneOre $model
 * @var app\models\Assegnazione $assegnazione
 * @var float $oreApprovate
 * @var float $oreInAttesa
 * @var bool $isModifica
 */

echo $this->render('inserisci-ore', [
    'model' => $model,
    'assegnazione' => $assegnazione,
    'oreApprovate' => $oreApprovate,
    'oreInAttesa' => $oreInAttesa,
    'isModifica' => $isModifica,
]);
