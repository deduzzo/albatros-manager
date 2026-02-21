<?php

use yii\helpers\Html;
use app\components\widgets\AppActiveForm;
use app\models\Allievo;

/* @var $this yii\web\View */
/* @var $allievo app\models\Allievo */
/* @var $auth app\models\AllievoAuth */

$this->title = 'Il Mio Profilo';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-user me-2"></i> <?= Html::encode($this->title) ?>
            </h2>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Dati Anagrafici</h3>
                    </div>

                    <?php $form = AppActiveForm::begin(['id' => 'profilo-form']); ?>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Codice Fiscale</label>
                                    <input type="text" class="form-control" value="<?= Html::encode($allievo->codice_fiscale) ?>" disabled>
                                    <small class="form-hint">Il codice fiscale non puo essere modificato.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'cognome')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'nome')->textInput(['maxlength' => true]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'email')->textInput(['type' => 'email']) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'telefono')->textInput() ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'data_nascita')->textInput(['type' => 'date']) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'luogo_nascita')->textInput() ?>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h4 class="mb-3">Residenza</h4>

                        <div class="row">
                            <div class="col-12">
                                <?= $form->field($allievo, 'indirizzo')->textInput(['placeholder' => 'Via/Piazza e numero civico']) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?= $form->field($allievo, 'cap')->textInput(['maxlength' => 5]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'citta')->textInput() ?>
                            </div>
                            <div class="col-md-3">
                                <?= $form->field($allievo, 'provincia')->textInput(['maxlength' => 2, 'style' => 'text-transform: uppercase;']) ?>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h4 class="mb-3">Formazione e Lavoro</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'titolo_studio')->dropDownList(
                                    Allievo::getTitoloStudioLabels(),
                                    ['prompt' => '-- Seleziona --']
                                ) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'anno_conseguimento')->textInput(['type' => 'number', 'min' => 1950, 'max' => date('Y')]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <?= $form->field($allievo, 'istituto')->textInput(['placeholder' => 'Nome istituto o universita']) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'stato_occupazionale')->dropDownList(
                                    Allievo::getStatoOccupazionaleLabels(),
                                    ['prompt' => '-- Seleziona --']
                                ) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($allievo, 'settore_attivita')->textInput(['placeholder' => 'Es: Informatica, Ristorazione...']) ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva Modifiche', [
                            'class' => 'btn',
                            'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;',
                        ]) ?>
                    </div>

                    <?php AppActiveForm::end(); ?>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Info Account -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Info Account</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-6">Stato:</dt>
                            <dd class="col-6">
                                <span class="badge <?= $auth->getStatusBadgeClass() ?>"><?= $auth->getStatusLabel() ?></span>
                            </dd>

                            <dt class="col-6">Email verificata:</dt>
                            <dd class="col-6">
                                <?php if ($auth->email_verified_at): ?>
                                    <span class="text-green"><i class="ti ti-check"></i> Si</span>
                                <?php else: ?>
                                    <span class="text-red"><i class="ti ti-x"></i> No</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-6">Registrato il:</dt>
                            <dd class="col-6"><?= Yii::$app->formatter->asDate($auth->created_at) ?></dd>

                            <?php if ($auth->last_login_at): ?>
                                <dt class="col-6">Ultimo accesso:</dt>
                                <dd class="col-6"><?= Yii::$app->formatter->asDatetime($auth->last_login_at) ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>

                <!-- Riepilogo formazione -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Riepilogo</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-6">Titolo di studio:</dt>
                            <dd class="col-6">
                                <?php
                                $labels = Allievo::getTitoloStudioLabels();
                                echo Html::encode($labels[$allievo->titolo_studio] ?? 'Non specificato');
                                ?>
                            </dd>

                            <dt class="col-6">Occupazione:</dt>
                            <dd class="col-6">
                                <?php
                                $labels = Allievo::getStatoOccupazionaleLabels();
                                echo Html::encode($labels[$allievo->stato_occupazionale] ?? 'Non specificato');
                                ?>
                            </dd>

                            <dt class="col-6">Citta:</dt>
                            <dd class="col-6"><?= Html::encode($allievo->citta ?: '-') ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
