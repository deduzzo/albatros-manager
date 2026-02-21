<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\Allievo;

/* @var $this yii\web\View */
/* @var $bando app\models\Avviso */
/* @var $allievo app\models\Allievo */
/* @var $iscrizione app\models\Iscrizione */

$this->title = 'Iscrizione al Bando';
$colore = $bando->getColore();
$moduli = $bando->moduli;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">Iscrizione al Bando</div>
                    <h2 class="page-title"><?= Html::encode($bando->titolo) ?></h2>
                </div>
            </div>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'iscrizione-form']); ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Step 1: Dati Anagrafici -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-user me-2"></i> 1. Dati Anagrafici</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <?= $form->field($allievo, 'codice_fiscale')->textInput([
                                        'placeholder' => 'Es: RSSMRA80A01F205X',
                                        'maxlength' => 16,
                                        'style' => 'text-transform: uppercase;',
                                    ])->hint('Il codice fiscale sara il tuo identificativo') ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'cognome')->textInput(['placeholder' => 'Rossi']) ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'nome')->textInput(['placeholder' => 'Mario']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'data_nascita')->input('date') ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'luogo_nascita')->textInput(['placeholder' => 'Milano']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'email')->input('email', ['placeholder' => 'mario.rossi@email.it'])
                                        ->hint('Riceverai le comunicazioni a questo indirizzo') ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'telefono')->input('tel', ['placeholder' => '+39 333 1234567']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Residenza -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-home me-2"></i> 2. Indirizzo di Residenza</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <?= $form->field($allievo, 'indirizzo')->textInput(['placeholder' => 'Via Roma, 10']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <?= $form->field($allievo, 'cap')->textInput(['placeholder' => '20100', 'maxlength' => 5]) ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'citta')->textInput(['placeholder' => 'Milano']) ?>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <?= $form->field($allievo, 'provincia')->textInput(['placeholder' => 'MI', 'maxlength' => 2, 'style' => 'text-transform: uppercase;']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Titolo di Studio -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-certificate me-2"></i> 3. Titolo di Studio</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'titolo_studio')->dropDownList(
                                        Allievo::getTitoloStudioLabels(),
                                        ['prompt' => '-- Seleziona --']
                                    ) ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'anno_conseguimento')->input('number', [
                                        'placeholder' => '2020',
                                        'min' => 1950,
                                        'max' => date('Y'),
                                    ]) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <?= $form->field($allievo, 'istituto')->textInput(['placeholder' => 'Universita degli Studi di Milano']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Situazione Lavorativa -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-briefcase me-2"></i> 4. Situazione Lavorativa</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'stato_occupazionale')->dropDownList(
                                        Allievo::getStatoOccupazionaleLabels(),
                                        ['prompt' => '-- Seleziona --']
                                    ) ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <?= $form->field($allievo, 'settore_attivita')->textInput(['placeholder' => 'Es: Informatica, Commercio...']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Privacy e Consensi -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-shield-check me-2"></i> 5. Privacy e Consensi</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <?= $form->field($iscrizione, 'accetta_privacy')->checkbox([
                                    'label' => 'Ho letto e accetto la <a href="#" target="_blank">Privacy Policy</a> e i <a href="#" target="_blank">Termini di Servizio</a> *',
                                    'labelOptions' => ['encode' => false],
                                ]) ?>
                            </div>
                            <div class="mb-3">
                                <?= $form->field($iscrizione, 'accetta_marketing')->checkbox([
                                    'label' => 'Acconsento a ricevere comunicazioni su futuri corsi e iniziative formative',
                                ]) ?>
                            </div>
                            <div class="mb-3">
                                <?= $form->field($iscrizione, 'dichiarazione_veridicita')->checkbox([
                                    'label' => 'Dichiaro che tutti i dati inseriti sono veritieri e mi assumo la responsabilita di eventuali dichiarazioni mendaci *',
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-notes me-2"></i> Note aggiuntive</h3>
                        </div>
                        <div class="card-body">
                            <?= $form->field($iscrizione, 'note')->textarea(['rows' => 3, 'placeholder' => 'Eventuali note o richieste particolari...'])->label(false) ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Riepilogo Bando -->
                    <div class="card sticky-top" style="top: 70px;">
                        <div class="card-header" style="background: linear-gradient(135deg, <?= Html::encode($colore) ?> 0%, <?= Html::encode($colore) ?>99 100%);">
                            <h3 class="card-title text-white">Riepilogo Bando</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-5">Codice:</dt>
                                <dd class="col-7"><span class="badge bg-blue-lt"><?= Html::encode($bando->codice_avviso) ?></span></dd>

                                <dt class="col-5">Titolo:</dt>
                                <dd class="col-7"><?= Html::encode($bando->titolo) ?></dd>

                                <dt class="col-5">Fonte:</dt>
                                <dd class="col-7"><?= Html::encode($bando->progetto->fonteFinanziamento->descrizione ?? '-') ?></dd>

                                <dt class="col-5">Scadenza:</dt>
                                <dd class="col-7">
                                    <span class="badge bg-red text-red-fg">
                                        <i class="ti ti-calendar me-1"></i>
                                        <?= Yii::$app->formatter->asDate($bando->data_scadenza) ?>
                                    </span>
                                </dd>

                                <dt class="col-5">Costo:</dt>
                                <dd class="col-7"><strong class="text-success">GRATUITO</strong></dd>
                            </dl>

                            <!-- Corsi inclusi -->
                            <hr>
                            <h4 class="mb-2"><i class="ti ti-book me-1"></i> Corsi inclusi (<?= count($moduli) ?>)</h4>
                            <?php
                            $oreTotali = 0;
                            foreach ($moduli as $modulo):
                                $oreTotali += $modulo->ore_totali;
                            ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    <div>
                                        <div class="fw-bold small"><?= Html::encode($modulo->titolo) ?></div>
                                        <div class="text-secondary" style="font-size: 0.75rem;"><?= $modulo->ore_totali ?> ore</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-secondary small mt-2">
                                <i class="ti ti-clock me-1"></i>Totale: <strong><?= $oreTotali ?> ore</strong> di formazione
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="ti ti-send me-2"></i> Invia Iscrizione
                            </button>
                            <div class="text-center mt-2">
                                <?= Html::a(
                                    '<i class="ti ti-arrow-left me-1"></i> Torna ai bandi',
                                    ['index'],
                                    ['class' => 'text-secondary']
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
