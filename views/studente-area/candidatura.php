<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppActiveForm;
use app\models\Iscrizione;
use app\models\Allievo;

/* @var $this yii\web\View */
/* @var $avviso app\models\Avviso */
/* @var $allievo app\models\Allievo */
/* @var $iscrizione app\models\Iscrizione */

$this->title = 'Candidatura - ' . $avviso->codice_avviso;
$this->params['breadcrumbs'][] = ['label' => 'Bandi Aperti', 'url' => ['bandi']];
$this->params['breadcrumbs'][] = ['label' => $avviso->codice_avviso, 'url' => ['bando', 'id' => $avviso->id]];
$this->params['breadcrumbs'][] = 'Candidatura';
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-send me-2"></i> Candidatura al Bando
            </h2>
            <div class="page-subtitle text-secondary">
                <?= Html::encode($avviso->codice_avviso) ?> - <?= Html::encode($avviso->titolo) ?>
            </div>
        </div>

        <?php $form = AppActiveForm::begin([
            'id' => 'candidatura-form',
            'options' => ['class' => 'row row-cards'],
        ]); ?>

        <div class="col-12">
            <!-- Sezione 1: Dati anagrafici (precompilati) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge bg-purple text-purple-fg me-2">1</span>
                        Dati Anagrafici
                    </h3>
                    <div class="card-actions">
                        <span class="badge bg-green-lt">Precompilati dal profilo</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Codice Fiscale</label>
                                <input type="text" class="form-control" value="<?= Html::encode($allievo->codice_fiscale) ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Cognome</label>
                                <input type="text" class="form-control" value="<?= Html::encode($allievo->cognome) ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control" value="<?= Html::encode($allievo->nome) ?>" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" value="<?= Html::encode($allievo->email ?: '-') ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" class="form-control" value="<?= Html::encode($allievo->telefono ?: '-') ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Citta</label>
                                <input type="text" class="form-control" value="<?= Html::encode($allievo->citta ?: '-') ?>" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <div class="d-flex">
                            <div><i class="ti ti-info-circle me-2"></i></div>
                            <div>I dati anagrafici sono precompilati dal tuo profilo. Per modificarli vai alla sezione <a href="<?= Url::to(['profilo']) ?>">Profilo</a>.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sezione 2: Formazione -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge bg-purple text-purple-fg me-2">2</span>
                        Formazione
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($iscrizione, 'titolo_studio')->dropDownList(
                                Iscrizione::getTitoloStudioLabels(),
                                ['prompt' => '-- Seleziona il titolo di studio --']
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($iscrizione, 'anno_conseguimento')->textInput([
                                'type' => 'number',
                                'min' => 1950,
                                'max' => date('Y'),
                                'placeholder' => 'Es: 2020',
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?= $form->field($iscrizione, 'istituto')->textInput([
                                'placeholder' => 'Nome dell\'istituto o dell\'universita dove hai conseguito il titolo',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sezione 3: Situazione lavorativa -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge bg-purple text-purple-fg me-2">3</span>
                        Situazione Lavorativa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($iscrizione, 'stato_occupazionale')->dropDownList(
                                Iscrizione::getStatoOccupazionaleLabels(),
                                ['prompt' => '-- Seleziona lo stato occupazionale --']
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($iscrizione, 'settore_attivita')->textInput([
                                'placeholder' => 'Es: Informatica, Ristorazione, Commercio...',
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?= $form->field($iscrizione, 'note')->textarea([
                                'rows' => 3,
                                'placeholder' => 'Eventuali note aggiuntive sulla tua candidatura (facoltativo)',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sezione 4: Privacy e consensi -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge bg-purple text-purple-fg me-2">4</span>
                        Privacy e Dichiarazioni
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <?= $form->field($iscrizione, 'accetta_privacy')->checkbox([
                            'class' => 'form-check-input',
                        ])->label('Dichiaro di aver letto e accettato l\'<a href="#" target="_blank">Informativa sulla Privacy</a> ai sensi del Regolamento UE 2016/679 (GDPR)') ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($iscrizione, 'dichiarazione_veridicita')->checkbox([
                            'class' => 'form-check-input',
                        ])->label('Dichiaro sotto la propria responsabilita che le informazioni fornite sono veritiere e complete, consapevole delle sanzioni penali previste dall\'art. 76 del DPR 445/2000') ?>
                    </div>

                    <div class="mb-0">
                        <?= $form->field($iscrizione, 'accetta_marketing')->checkbox([
                            'class' => 'form-check-input',
                        ])->label('Acconsento a ricevere comunicazioni informative su futuri corsi e opportunita formative (facoltativo)') ?>
                    </div>
                </div>
            </div>

            <!-- Pulsanti -->
            <div class="d-flex gap-2">
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna al bando', ['bando', 'id' => $avviso->id], ['class' => 'btn btn-secondary']) ?>
                <?= Html::submitButton('<i class="ti ti-send me-1"></i> Invia Candidatura', [
                    'class' => 'btn ms-auto',
                    'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;',
                    'data' => ['confirm' => 'Confermi di voler inviare la candidatura? Una volta inviata non potra essere modificata.'],
                ]) ?>
            </div>
        </div>

        <?php AppActiveForm::end(); ?>
    </div>
</div>
