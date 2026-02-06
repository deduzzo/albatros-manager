<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Istanza */

$this->title = 'Snapshot Storico - Istanza #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Istanze', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Istanza #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Snapshot';

$snapshot = $model->snapshot;
$datiDocente = is_array($snapshot->dati_docente) ? $snapshot->dati_docente : json_decode($snapshot->dati_docente, true);
$tagsDocente = is_array($snapshot->tags_docente) ? $snapshot->tags_docente : json_decode($snapshot->tags_docente ?? '[]', true);
$tagsIscrizione = is_array($snapshot->tags_iscrizione) ? $snapshot->tags_iscrizione : json_decode($snapshot->tags_iscrizione ?? '[]', true);
?>

<div class="istanza-snapshot">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <small class="text-muted">Dati salvati il <?= Yii::$app->formatter->asDatetime($snapshot->created_at) ?></small>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna all\'istanza', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i>
                Questo snapshot contiene i dati del docente al momento della presentazione dell'istanza (<?= Yii::$app->formatter->asDate($model->data_presentazione) ?>).
                I dati potrebbero essere diversi da quelli attuali nel profilo del docente.
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Dati Anagrafici -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-user me-2"></i> Dati Anagrafici (al momento dell'iscrizione)</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">Codice Fiscale</td>
                                            <td><strong><?= Html::encode($datiDocente['codice_fiscale'] ?? '-') ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Cognome</td>
                                            <td><?= Html::encode($datiDocente['cognome'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Nome</td>
                                            <td><?= Html::encode($datiDocente['nome'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Data Nascita</td>
                                            <td><?= $datiDocente['data_nascita'] ? Yii::$app->formatter->asDate($datiDocente['data_nascita']) : '-' ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Luogo Nascita</td>
                                            <td><?= Html::encode($datiDocente['luogo_nascita'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Email</td>
                                            <td><?= Html::encode($datiDocente['email'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Telefono</td>
                                            <td><?= Html::encode($datiDocente['telefono'] ?? '-') ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">Indirizzo</td>
                                            <td><?= Html::encode($datiDocente['indirizzo'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">CAP</td>
                                            <td><?= Html::encode($datiDocente['cap'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Citta</td>
                                            <td><?= Html::encode($datiDocente['citta'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Provincia</td>
                                            <td><?= Html::encode($datiDocente['provincia'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">P.IVA</td>
                                            <td><?= Html::encode($datiDocente['partita_iva'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Titolo Studio</td>
                                            <td><?= Html::encode($datiDocente['titolo_studio'] ?? '-') ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if (!empty($datiDocente['specializzazioni'])): ?>
                                <div class="mt-3">
                                    <label class="form-label text-muted">Specializzazioni</label>
                                    <p><?= nl2br(Html::encode($datiDocente['specializzazioni'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Tag del Profilo al momento dell'iscrizione -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-tags me-2"></i> Tag Profilo (storici)</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($tagsDocente)): ?>
                                <p class="text-muted">Nessun tag nel profilo al momento dell'iscrizione.</p>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($tagsDocente as $tag): ?>
                                        <span class="badge bg-secondary-lt">
                                            <?= Html::encode($tag['nome'] ?? 'Tag #' . $tag['tag_id']) ?>
                                            <?php if (!empty($tag['livello'])): ?>
                                                <small>(Liv. <?= $tag['livello'] ?>)</small>
                                            <?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tag selezionati per l'iscrizione -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-tag me-2"></i> Tag Iscrizione</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($tagsIscrizione)): ?>
                                <p class="text-muted">Nessun tag selezionato per questa iscrizione.</p>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($tagsIscrizione as $tag): ?>
                                        <span class="badge bg-azure-lt">
                                            <?= Html::encode($tag['nome'] ?? 'Tag #' . ($tag['tag_id'] ?? '?')) ?>
                                            <?php if (!empty($tag['livello'])): ?>
                                                <small>(Liv. <?= $tag['livello'] ?>)</small>
                                            <?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna all\'istanza', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
</div>
