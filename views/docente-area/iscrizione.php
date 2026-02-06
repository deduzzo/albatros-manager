<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AppActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\IscrizioneForm */
/* @var $modulo app\models\Modulo */
/* @var $docente app\models\Docente */
/* @var $tagCategorie app\models\TagCategoria[] */
/* @var $tagsDocente array */
/* @var $documentiRichiesti app\models\AvvisoDocumentoTipo[] */

$this->title = 'Iscrizione: ' . $modulo->codice;
$this->params['breadcrumbs'][] = ['label' => 'Bandi Aperti', 'url' => ['bandi']];
$this->params['breadcrumbs'][] = ['label' => $modulo->avviso->codice_avviso, 'url' => ['bando', 'id' => $modulo->avviso_id]];
$this->params['breadcrumbs'][] = 'Iscrizione';
?>

<div class="page-body">
    <div class="container-xl">
        <!-- Header -->
        <div class="page-header d-print-none mb-4">
            <h2 class="page-title">
                <i class="ti ti-send me-2"></i> Iscrizione al Modulo
            </h2>
            <div class="page-subtitle">
                <span class="badge bg-blue-lt me-2"><?= Html::encode($modulo->codice) ?></span>
                <?= Html::encode($modulo->titolo) ?>
            </div>
        </div>

        <?php $form = AppActiveForm::begin([
            'id' => 'iscrizione-form',
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- Step 1: Verifica Dati Anagrafici -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-user me-2"></i> 1. Verifica Dati Anagrafici</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            I tuoi dati anagrafici saranno allegati all'iscrizione. Verifica che siano corretti.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-4">Nome:</dt>
                                    <dd class="col-8"><?= Html::encode($docente->cognome . ' ' . $docente->nome) ?></dd>
                                    <dt class="col-4">C.F.:</dt>
                                    <dd class="col-8"><code><?= Html::encode($docente->codice_fiscale) ?></code></dd>
                                    <dt class="col-4">Email:</dt>
                                    <dd class="col-8"><?= Html::encode($docente->email ?: '-') ?></dd>
                                    <dt class="col-4">Telefono:</dt>
                                    <dd class="col-8"><?= Html::encode($docente->telefono ?: '-') ?></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-4">Indirizzo:</dt>
                                    <dd class="col-8"><?= Html::encode($docente->indirizzo ?: '-') ?></dd>
                                    <dt class="col-4">Città:</dt>
                                    <dd class="col-8"><?= Html::encode($docente->citta ?: '-') ?> (<?= Html::encode($docente->provincia ?: '-') ?>)</dd>
                                    <dt class="col-4">Titolo:</dt>
                                    <dd class="col-8"><?= Html::encode($docente->titolo_studio ?: 'Non specificato') ?></dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="<?= Url::to(['profilo']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="ti ti-edit me-1"></i> Modifica profilo
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Seleziona Tag Curriculum -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-tags me-2"></i> 2. Seleziona Tag Curriculum</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="ti ti-info-circle me-2"></i>
                            Seleziona i tag che descrivono le tue competenze per questa candidatura.
                            I tag selezionati qui saranno specifici per questa iscrizione.
                        </div>

                        <?php if (!empty($tagsDocente)): ?>
                            <div class="mb-3">
                                <label class="form-check">
                                    <?= Html::checkbox('IscrizioneForm[caricaTagPrecedenti]', false, ['class' => 'form-check-input', 'id' => 'carica-tag']) ?>
                                    <span class="form-check-label">Carica i tag dal mio profilo</span>
                                </label>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($tagCategorie as $categoria): ?>
                            <?php if (!empty($categoria->tags)): ?>
                                <div class="mb-3">
                                    <label class="form-label"><?= Html::encode($categoria->nome) ?></label>
                                    <div class="row g-2">
                                        <?php foreach ($categoria->tags as $tag): ?>
                                            <?php $isFromProfile = isset($tagsDocente[$tag->id]); ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-selectgroup-item">
                                                    <label class="form-selectgroup-label d-flex align-items-center p-2">
                                                        <?= Html::checkbox("IscrizioneForm[tags][]", false, [
                                                            'value' => $tag->id,
                                                            'class' => 'form-selectgroup-input tag-checkbox' . ($isFromProfile ? ' from-profile' : ''),
                                                            'data-tag-id' => $tag->id,
                                                        ]) ?>
                                                        <span class="form-selectgroup-label-content flex-fill">
                                                            <?= Html::encode($tag->nome) ?>
                                                            <?php if ($isFromProfile): ?>
                                                                <small class="text-success ms-1"><i class="ti ti-check"></i></small>
                                                            <?php endif; ?>
                                                        </span>
                                                        <select name="IscrizioneForm[tagsLivelli][<?= $tag->id ?>]" class="form-select form-select-sm ms-2" style="width: 80px;">
                                                            <option value="">Liv.</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 3: Carica Documenti -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-file-upload me-2"></i> 3. Carica Documenti</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($documentiRichiesti)): ?>
                            <div class="alert alert-warning mb-3">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>Documenti richiesti per questa candidatura:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($documentiRichiesti as $docTipo): ?>
                                        <li>
                                            <strong><?= Html::encode($docTipo->nome) ?></strong>
                                            <?php if ($docTipo->obbligatorio): ?>
                                                <span class="badge bg-red">Obbligatorio</span>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                (<?= Html::encode($docTipo->formati_ammessi) ?>, max <?= $docTipo->dimensione_max_mb ?>MB)
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?= $form->field($model, 'documenti[]')->fileInput([
                            'multiple' => true,
                            'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
                            'class' => 'form-control',
                        ])->hint('Puoi caricare più file contemporaneamente (PDF, DOC, DOCX, JPG, PNG). Max 10MB per file.') ?>
                    </div>
                </div>

                <!-- Step 4: Note -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-notes me-2"></i> 4. Note Aggiuntive</h3>
                    </div>
                    <div class="card-body">
                        <?= $form->field($model, 'note')->textarea([
                            'rows' => 3,
                            'placeholder' => 'Eventuali note o informazioni aggiuntive per la tua candidatura...',
                        ])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Riepilogo Modulo -->
                <div class="card mb-3 sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h3 class="card-title">Riepilogo Modulo</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5">Codice:</dt>
                            <dd class="col-7"><span class="badge bg-blue-lt"><?= Html::encode($modulo->codice) ?></span></dd>

                            <dt class="col-5">Titolo:</dt>
                            <dd class="col-7"><?= Html::encode($modulo->titolo) ?></dd>

                            <dt class="col-5">Bando:</dt>
                            <dd class="col-7"><?= Html::encode($modulo->avviso->codice_avviso) ?></dd>

                            <dt class="col-5">Ore totali:</dt>
                            <dd class="col-7"><?= $modulo->ore_totali ?> ore</dd>

                            <?php if ($modulo->compenso_orario): ?>
                                <dt class="col-5">Compenso/ora:</dt>
                                <dd class="col-7"><?= Yii::$app->formatter->asCurrency($modulo->compenso_orario) ?></dd>

                                <dt class="col-5">Compenso tot.:</dt>
                                <dd class="col-7"><strong class="text-success"><?= Yii::$app->formatter->asCurrency($modulo->getCompensoTotale()) ?></strong></dd>
                            <?php endif; ?>

                            <?php if ($modulo->sede): ?>
                                <dt class="col-5">Sede:</dt>
                                <dd class="col-7"><?= Html::encode($modulo->sede->nome) ?></dd>
                            <?php endif; ?>

                            <?php if ($modulo->data_inizio): ?>
                                <dt class="col-5">Periodo:</dt>
                                <dd class="col-7">
                                    <?= Yii::$app->formatter->asDate($modulo->data_inizio) ?>
                                    <?php if ($modulo->data_fine): ?>
                                        - <?= Yii::$app->formatter->asDate($modulo->data_fine) ?>
                                    <?php endif; ?>
                                </dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <?= Html::submitButton('<i class="ti ti-send me-2"></i> Invia Iscrizione', [
                            'class' => 'btn btn-primary w-100',
                        ]) ?>
                        <div class="mt-2">
                            <?= Html::a('Annulla', ['bando', 'id' => $modulo->avviso_id], ['class' => 'btn btn-ghost-secondary w-100']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php AppActiveForm::end(); ?>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    // Carica tag dal profilo
    $('#carica-tag').on('change', function() {
        if ($(this).is(':checked')) {
            $('.tag-checkbox.from-profile').prop('checked', true);
        }
    });
JS
);
?>
