<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Istanza */

$this->title = 'Istanza #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Istanze', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$tagsNonPromossi = $model->getTagsNonPromossiCount();
?>

<div class="istanza-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= $model->getStatusLabel() ?></span>
                </div>
                <div class="col-auto ms-auto">
                    <?php if ($model->snapshot): ?>
                        <?= Html::a('<i class="ti ti-history me-1"></i> Snapshot Storico', ['snapshot', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questa istanza?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Dettagli Istanza -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Dettagli Istanza</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered'],
                                'attributes' => [
                                    'id',
                                    'data_presentazione:date',
                                    [
                                        'attribute' => 'docente_codice_fiscale',
                                        'format' => 'raw',
                                        'value' => $model->docente
                                            ? Html::a(
                                                '<strong>' . $model->docente->cognome . ' ' . $model->docente->nome . '</strong><br><small class="text-muted">' . $model->docente_codice_fiscale . '</small>',
                                                ['/docente/view', 'id' => $model->docente_codice_fiscale]
                                            )
                                            : $model->docente_codice_fiscale,
                                    ],
                                    [
                                        'attribute' => 'avviso_id',
                                        'format' => 'raw',
                                        'value' => $model->avviso
                                            ? Html::a($model->avviso->codice_avviso . ' - ' . $model->avviso->titolo, ['/avviso/view', 'id' => $model->avviso_id])
                                            : '-',
                                    ],
                                    [
                                        'attribute' => 'modulo_id',
                                        'format' => 'raw',
                                        'value' => $model->modulo
                                            ? Html::a($model->modulo->codice . ' - ' . $model->modulo->titolo, ['/modulo/view', 'id' => $model->modulo_id])
                                            : '-',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'format' => 'raw',
                                        'value' => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>',
                                    ],
                                    'note:ntext',
                                    'created_at:datetime',
                                    'updated_at:datetime',
                                ],
                            ]) ?>
                        </div>
                    </div>

                    <!-- Valutazione -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Valutazione</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-blue text-blue-fg avatar"><i class="ti ti-school"></i></span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">Punteggio Titoli</div>
                                                    <div class="text-secondary"><?= $model->punteggio_titoli !== null ? number_format($model->punteggio_titoli, 2) : '-' ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-green text-green-fg avatar"><i class="ti ti-briefcase"></i></span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">Punteggio Esperienza</div>
                                                    <div class="text-secondary"><?= $model->punteggio_esperienza !== null ? number_format($model->punteggio_esperienza, 2) : '-' ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-yellow text-yellow-fg avatar"><i class="ti ti-calculator"></i></span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">Punteggio Totale</div>
                                                    <div class="text-secondary fw-bold"><?= $model->punteggio_totale !== null ? number_format($model->punteggio_totale, 2) : '-' ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($model->posizione_graduatoria): ?>
                                <div class="mt-3 text-center">
                                    <span class="h3">Posizione in graduatoria: <span class="badge bg-blue text-blue-fg fs-3">#<?= $model->posizione_graduatoria ?></span></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($model->note_valutazione): ?>
                                <div class="mt-3">
                                    <label class="form-label">Note Valutazione</label>
                                    <div class="text-muted"><?= nl2br(Html::encode($model->note_valutazione)) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Storico Esiti -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="ti ti-history me-2"></i>Storico Esiti CTS</h3>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-nuovo-esito">
                                <i class="ti ti-plus me-1"></i> Nuovo Esito
                            </button>
                        </div>
                        <?php $storicoEsiti = $model->storicoEsiti; ?>
                        <?php if ($storicoEsiti): ?>
                        <div class="card-body">
                            <ul class="timeline">
                                <?php foreach ($storicoEsiti as $esito): ?>
                                <li class="timeline-event">
                                    <div class="timeline-event-icon <?= $esito->getEsitoBadgeClass() ?>">
                                        <i class="<?= $esito->getEsitoIconClass() ?>"></i>
                                    </div>
                                    <div class="card timeline-event-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge <?= $esito->getEsitoBadgeClass() ?>"><?= $esito->getEsitoLabel() ?></span>
                                                <small class="text-muted">
                                                    <?php if ($esito->data_esito): ?>
                                                        <?= Yii::$app->formatter->asDate($esito->data_esito) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <?php if ($esito->note): ?>
                                                <p class="text-muted mb-1 small"><?= Html::encode($esito->note) ?></p>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                Registrato: <?= Yii::$app->formatter->asDatetime($esito->created_at) ?>
                                                <?php if ($esito->createdByUser): ?>
                                                    da <?= Html::encode($esito->createdByUser->username) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php else: ?>
                        <div class="card-body text-muted text-center">
                            <i class="ti ti-history-off mb-2" style="font-size: 2rem;"></i>
                            <p>Nessun esito registrato</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Documenti Caricati -->
                    <?php
                    // Calcolo stato completamento documenti
                    $documentiRichiesti = $model->avviso ? $model->avviso->documentiRichiesti : [];
                    $documentiCaricati = \app\models\IstanzaDocumento::find()->where(['istanza_id' => $model->id])->all();
                    $tipiCaricati = array_map(function($d) { return $d->avviso_documento_tipo_id; }, $documentiCaricati);
                    $docObbligatoriTotali = 0;
                    $docObbligatoriCaricati = 0;
                    foreach ($documentiRichiesti as $docReq) {
                        if ($docReq->obbligatorio) {
                            $docObbligatoriTotali++;
                            if (in_array($docReq->id, $tipiCaricati)) {
                                $docObbligatoriCaricati++;
                            }
                        }
                    }
                    if ($docObbligatoriTotali > 0) {
                        if ($docObbligatoriCaricati >= $docObbligatoriTotali) {
                            $docBadgeClass = 'bg-green text-green-fg';
                            $docBadgeLabel = 'Completo';
                        } elseif ($docObbligatoriCaricati > 0) {
                            $docBadgeClass = 'bg-orange text-orange-fg';
                            $docBadgeLabel = $docObbligatoriCaricati . '/' . $docObbligatoriTotali . ' obbligatori';
                        } else {
                            $docBadgeClass = 'bg-red text-red-fg';
                            $docBadgeLabel = 'Mancanti';
                        }
                    }
                    ?>
                    <div class="card mb-3" id="card-documenti">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="ti ti-file-upload me-2"></i>Documenti
                                <?php if ($docObbligatoriTotali > 0): ?>
                                    <span class="badge <?= $docBadgeClass ?> ms-2"><?= $docBadgeLabel ?></span>
                                <?php endif; ?>
                            </h3>
                            <button class="btn btn-sm btn-success" id="btn-carica-documento">
                                <i class="ti ti-upload me-1"></i> Carica Documento
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Drop zone -->
                            <div id="drop-zone" class="drop-zone">
                                <i class="ti ti-cloud-upload drop-zone-icon"></i>
                                <p class="text-muted mt-2 mb-0">Trascina qui i file oppure <strong>clicca per selezionare</strong></p>
                                <small class="text-muted">PDF, DOC, DOCX, JPG, PNG - Max 10MB</small>
                            </div>
                            <!-- Progress bar (nascosta di default) -->
                            <div id="upload-progress" class="mb-3" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">Caricamento in corso...</small>
                            </div>
                            <!-- Lista documenti -->
                            <div id="lista-documenti">
                                <?php
                                $documenti = \app\models\IstanzaDocumento::find()->where(['istanza_id' => $model->id])->orderBy(['created_at' => SORT_DESC])->all();
                                ?>
                                <?php if (empty($documenti)): ?>
                                    <div class="text-center py-3" id="no-documenti">
                                        <i class="ti ti-file-off d-block mb-2" style="font-size: 2rem;"></i>
                                        <p class="text-muted mb-0">Nessun documento caricato</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($documenti as $doc): ?>
                                        <div class="list-group-item doc-item" data-id="<?= $doc->id ?>">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <?php
                                                    $iconClass = 'ti ti-file';
                                                    $bgClass = 'bg-azure-lt';
                                                    $textClass = 'text-muted';
                                                    if (str_contains($doc->mime_type, 'pdf')) {
                                                        $iconClass = 'ti ti-file-type-pdf';
                                                        $bgClass = 'bg-red-lt';
                                                        $textClass = 'text-red';
                                                    } elseif (str_contains($doc->mime_type, 'word') || str_contains($doc->mime_type, 'document')) {
                                                        $iconClass = 'ti ti-file-type-doc';
                                                        $bgClass = 'bg-blue-lt';
                                                        $textClass = 'text-blue';
                                                    } elseif (str_contains($doc->mime_type, 'image')) {
                                                        $iconClass = 'ti ti-photo';
                                                        $bgClass = 'bg-green-lt';
                                                        $textClass = 'text-green';
                                                    }
                                                    ?>
                                                    <span class="avatar <?= $bgClass ?>">
                                                        <i class="<?= $iconClass ?> <?= $textClass ?>"></i>
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <div class="d-block text-truncate">
                                                        <strong><?= Html::encode($doc->nome_file_originale) ?></strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= $doc->documentoTipo ? Html::encode($doc->documentoTipo->nome) : 'Documento' ?>
                                                        &bull; <?= $doc->getDimensioneFormattata() ?>
                                                        &bull; <?= Yii::$app->formatter->asDate($doc->created_at) ?>
                                                    </small>
                                                </div>
                                                <div class="col-auto">
                                                    <?php if ($doc->fileExists()): ?>
                                                        <?= Html::a('<i class="ti ti-download"></i>', ['download-documento', 'docId' => $doc->id], ['class' => 'btn btn-info btn-sm me-1', 'title' => 'Scarica']) ?>
                                                    <?php else: ?>
                                                        <span class="text-danger me-1" title="File non trovato"><i class="ti ti-alert-triangle"></i></span>
                                                    <?php endif; ?>
                                                    <button class="btn btn-danger btn-sm btn-delete-doc" data-id="<?= $doc->id ?>" title="Elimina">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Form upload nascosto -->
                    <input type="file" id="file-input-hidden" style="display: none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

                    <!-- Snapshot Dati Docente -->
                    <?php
                    $snapshot = \app\models\IstanzaSnapshot::findOne(['istanza_id' => $model->id]);
                    ?>
                    <?php if ($snapshot): ?>
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="ti ti-camera me-2"></i>Dati al momento dell'assegnazione</h3>
                            <span class="badge bg-azure text-azure-fg">Snapshot storico</span>
                        </div>
                        <div class="card-body">
                            <?php
                            $dati = is_array($snapshot->dati_docente) ? $snapshot->dati_docente : json_decode($snapshot->dati_docente, true);
                            // Dati attuali del docente per confronto visivo
                            $docAttuale = $model->docente;
                            $hasDiff = false;
                            // Funzione helper per verificare differenze
                            $isDifferent = function($campo, $valoreSnapshot) use ($docAttuale) {
                                if (!$docAttuale || $valoreSnapshot === null || $valoreSnapshot === '') return false;
                                $attuali = [
                                    'cognome' => $docAttuale->cognome ?? '',
                                    'nome' => $docAttuale->nome ?? '',
                                    'codice_fiscale' => $docAttuale->codice_fiscale ?? '',
                                    'email' => $docAttuale->email ?? '',
                                    'telefono' => $docAttuale->telefono ?? '',
                                    'data_nascita' => $docAttuale->data_nascita ?? '',
                                    'luogo_nascita' => $docAttuale->luogo_nascita ?? '',
                                    'indirizzo' => $docAttuale->indirizzo ?? '',
                                    'cap' => $docAttuale->cap ?? '',
                                    'citta' => $docAttuale->citta ?? '',
                                    'provincia' => $docAttuale->provincia ?? '',
                                    'titolo_studio' => $docAttuale->titolo_studio ?? '',
                                    'specializzazioni' => $docAttuale->specializzazioni ?? '',
                                    'partita_iva' => $docAttuale->partita_iva ?? '',
                                ];
                                return isset($attuali[$campo]) && (string)$attuali[$campo] !== (string)$valoreSnapshot;
                            };
                            ?>
                            <table class="table table-bordered table-sm">
                                <tbody>
                                    <?php if (!empty($dati['cognome']) || !empty($dati['nome'])): ?>
                                    <?php
                                    $nomeSnap = ($dati['cognome'] ?? '') . ' ' . ($dati['nome'] ?? '');
                                    $nomeDiff = $isDifferent('cognome', $dati['cognome'] ?? '') || $isDifferent('nome', $dati['nome'] ?? '');
                                    if ($nomeDiff) $hasDiff = true;
                                    ?>
                                    <tr<?= $nomeDiff ? ' class="table-warning"' : '' ?>>
                                        <th style="width: 30%;" class="fw-bold">Nome Completo</th>
                                        <td>
                                            <?= Html::encode($nomeSnap) ?>
                                            <?php if ($nomeDiff && $docAttuale): ?>
                                                <br><small class="text-warning"><i class="ti ti-arrow-right me-1"></i>Attuale: <?= Html::encode($docAttuale->cognome . ' ' . $docAttuale->nome) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['codice_fiscale'])): ?>
                                    <tr>
                                        <th class="fw-bold">Codice Fiscale</th>
                                        <td><code><?= Html::encode($dati['codice_fiscale']) ?></code></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['email'])): ?>
                                    <?php $emailDiff = $isDifferent('email', $dati['email']); if ($emailDiff) $hasDiff = true; ?>
                                    <tr<?= $emailDiff ? ' class="table-warning"' : '' ?>>
                                        <th class="fw-bold">Email</th>
                                        <td>
                                            <?= Html::encode($dati['email']) ?>
                                            <?php if ($emailDiff && $docAttuale): ?>
                                                <br><small class="text-warning"><i class="ti ti-arrow-right me-1"></i>Attuale: <?= Html::encode($docAttuale->email) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['telefono'])): ?>
                                    <?php $telDiff = $isDifferent('telefono', $dati['telefono']); if ($telDiff) $hasDiff = true; ?>
                                    <tr<?= $telDiff ? ' class="table-warning"' : '' ?>>
                                        <th class="fw-bold">Telefono</th>
                                        <td>
                                            <?= Html::encode($dati['telefono']) ?>
                                            <?php if ($telDiff && $docAttuale): ?>
                                                <br><small class="text-warning"><i class="ti ti-arrow-right me-1"></i>Attuale: <?= Html::encode($docAttuale->telefono) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['data_nascita'])): ?>
                                    <tr>
                                        <th class="fw-bold">Data di Nascita</th>
                                        <td><?= Yii::$app->formatter->asDate($dati['data_nascita']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['luogo_nascita'])): ?>
                                    <tr>
                                        <th class="fw-bold">Luogo di Nascita</th>
                                        <td><?= Html::encode($dati['luogo_nascita']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['indirizzo'])): ?>
                                    <?php $indDiff = $isDifferent('indirizzo', $dati['indirizzo'] ?? ''); if ($indDiff) $hasDiff = true; ?>
                                    <tr<?= $indDiff ? ' class="table-warning"' : '' ?>>
                                        <th class="fw-bold">Indirizzo</th>
                                        <td>
                                            <?= Html::encode($dati['indirizzo']) ?><?= !empty($dati['cap']) ? ' - ' . Html::encode($dati['cap']) : '' ?>
                                            <?php if ($indDiff && $docAttuale): ?>
                                                <br><small class="text-warning"><i class="ti ti-arrow-right me-1"></i>Attuale: <?= Html::encode($docAttuale->indirizzo) ?><?= $docAttuale->cap ? ' - ' . Html::encode($docAttuale->cap) : '' ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['citta'])): ?>
                                    <?php $cittaDiff = $isDifferent('citta', $dati['citta'] ?? ''); if ($cittaDiff) $hasDiff = true; ?>
                                    <tr<?= $cittaDiff ? ' class="table-warning"' : '' ?>>
                                        <th class="fw-bold">Citta</th>
                                        <td>
                                            <?= Html::encode($dati['citta']) ?><?= !empty($dati['provincia']) ? ' (' . Html::encode($dati['provincia']) . ')' : '' ?>
                                            <?php if ($cittaDiff && $docAttuale): ?>
                                                <br><small class="text-warning"><i class="ti ti-arrow-right me-1"></i>Attuale: <?= Html::encode($docAttuale->citta) ?><?= $docAttuale->provincia ? ' (' . Html::encode($docAttuale->provincia) . ')' : '' ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['titolo_studio'])): ?>
                                    <?php $titDiff = $isDifferent('titolo_studio', $dati['titolo_studio']); if ($titDiff) $hasDiff = true; ?>
                                    <tr<?= $titDiff ? ' class="table-warning"' : '' ?>>
                                        <th class="fw-bold">Titolo di Studio</th>
                                        <td>
                                            <?= Html::encode($dati['titolo_studio']) ?>
                                            <?php if ($titDiff && $docAttuale): ?>
                                                <br><small class="text-warning"><i class="ti ti-arrow-right me-1"></i>Attuale: <?= Html::encode($docAttuale->titolo_studio) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['specializzazioni'])): ?>
                                    <?php $specDiff = $isDifferent('specializzazioni', $dati['specializzazioni']); if ($specDiff) $hasDiff = true; ?>
                                    <tr<?= $specDiff ? ' class="table-warning"' : '' ?>>
                                        <th class="fw-bold">Specializzazioni</th>
                                        <td>
                                            <?= Html::encode($dati['specializzazioni']) ?>
                                            <?php if ($specDiff && $docAttuale): ?>
                                                <br><small class="text-warning"><i class="ti ti-arrow-right me-1"></i>Attuale: <?= Html::encode($docAttuale->specializzazioni) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($dati['partita_iva'])): ?>
                                    <?php $pivaDiff = $isDifferent('partita_iva', $dati['partita_iva']); if ($pivaDiff) $hasDiff = true; ?>
                                    <tr<?= $pivaDiff ? ' class="table-warning"' : '' ?>>
                                        <th class="fw-bold">Partita IVA</th>
                                        <td>
                                            <?= Html::encode($dati['partita_iva']) ?>
                                            <?php if ($pivaDiff && $docAttuale): ?>
                                                <br><small class="text-warning"><i class="ti ti-arrow-right me-1"></i>Attuale: <?= Html::encode($docAttuale->partita_iva) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if ($hasDiff): ?>
                            <div class="alert alert-warning py-2 mb-0 mt-2">
                                <i class="ti ti-alert-triangle me-1"></i>
                                <small>Alcuni dati del docente sono cambiati rispetto al momento dello snapshot. Le righe evidenziate mostrano le differenze.</small>
                            </div>
                            <?php endif; ?>
                            <?php
                            $tagsDocente = is_array($snapshot->tags_docente) ? $snapshot->tags_docente : json_decode($snapshot->tags_docente, true);
                            $tagsIscrizione = is_array($snapshot->tags_iscrizione) ? $snapshot->tags_iscrizione : json_decode($snapshot->tags_iscrizione, true);
                            ?>
                            <?php if (!empty($tagsDocente)): ?>
                            <div class="mt-3">
                                <label class="form-label">Tag Profilo Docente</label>
                                <div>
                                    <?php foreach ($tagsDocente as $tag): ?>
                                        <span class="badge bg-azure-lt me-1 mb-1">
                                            <?= Html::encode($tag['nome'] ?? 'Tag #' . ($tag['tag_id'] ?? '')) ?>
                                            <?php if (!empty($tag['livello'])): ?>
                                                <small>(Liv. <?= Html::encode($tag['livello']) ?>)</small>
                                            <?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($tagsIscrizione)): ?>
                            <div class="mt-3">
                                <label class="form-label">Tag Iscrizione</label>
                                <div>
                                    <?php foreach ($tagsIscrizione as $tag): ?>
                                        <span class="badge bg-green-lt me-1 mb-1">
                                            <?= Html::encode($tag['nome'] ?? 'Tag #' . ($tag['tag_id'] ?? '')) ?>
                                            <?php if (!empty($tag['livello'])): ?>
                                                <small>(Liv. <?= Html::encode($tag['livello']) ?>)</small>
                                            <?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="mt-3 text-muted">
                                <small><i class="ti ti-clock me-1"></i>Snapshot creato il <?= Yii::$app->formatter->asDatetime($snapshot->created_at) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <!-- Tag Iscrizione -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Tag Iscrizione</h3>
                            <?php if ($tagsNonPromossi > 0): ?>
                                <?= Html::a(
                                    '<i class="ti ti-arrow-up me-1"></i> Promuovi tutti',
                                    ['promuovi-tutti-tag', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-sm btn-primary',
                                        'data' => [
                                            'confirm' => "Promuovere {$tagsNonPromossi} tag al profilo docente?",
                                            'method' => 'post',
                                        ],
                                    ]
                                ) ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($model->istanzaTags)): ?>
                                <p class="text-muted">Nessun tag selezionato per questa iscrizione.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($model->istanzaTags as $istanzaTag): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-azure-lt me-2"><?= Html::encode($istanzaTag->tag->categoria->nome ?? '') ?></span>
                                                <strong><?= Html::encode($istanzaTag->tag->nome ?? 'Tag #' . $istanzaTag->tag_id) ?></strong>
                                                <?php if ($istanzaTag->livello): ?>
                                                    <small class="text-muted ms-2">Liv. <?= $istanzaTag->livello ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if ($istanzaTag->isPromosso()): ?>
                                                    <span class="badge bg-green text-green-fg" title="Promosso il <?= Yii::$app->formatter->asDatetime($istanzaTag->promosso_at) ?>">
                                                        <i class="ti ti-check"></i> Promosso
                                                    </span>
                                                <?php else: ?>
                                                    <?= Html::a(
                                                        '<i class="ti ti-arrow-up"></i>',
                                                        ['promuovi-tag', 'id' => $model->id, 'tagId' => $istanzaTag->id],
                                                        [
                                                            'class' => 'btn btn-sm btn-ghost-primary',
                                                            'title' => 'Promuovi al profilo docente',
                                                            'data' => [
                                                                'confirm' => 'Promuovere questo tag al profilo docente?',
                                                                'method' => 'post',
                                                            ],
                                                        ]
                                                    ) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Info Docente -->
                    <?php if ($model->docente): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Docente</h3>
                        </div>
                        <div class="card-body">
                            <h4><?= Html::a($model->docente->cognome . ' ' . $model->docente->nome, ['/docente/view', 'id' => $model->docente_codice_fiscale]) ?></h4>
                            <p class="text-muted mb-2"><?= Html::encode($model->docente->codice_fiscale) ?></p>
                            <?php if ($model->docente->email): ?>
                                <p class="mb-1"><i class="ti ti-mail me-2"></i><?= Html::encode($model->docente->email) ?></p>
                            <?php endif; ?>
                            <?php if ($model->docente->telefono): ?>
                                <p class="mb-1"><i class="ti ti-phone me-2"></i><?= Html::encode($model->docente->telefono) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Info Bando -->
                    <?php if ($model->avviso): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bando di Riferimento</h3>
                        </div>
                        <div class="card-body">
                            <h4><?= Html::a($model->avviso->codice_avviso, ['/avviso/view', 'id' => $model->avviso_id]) ?></h4>
                            <p class="text-muted"><?= Html::encode($model->avviso->titolo) ?></p>
                            <div>
                                <span class="badge <?= $model->avviso->getStatusBadgeClass() ?>"><?= $model->avviso->getStatusLabel() ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-3">
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuovo Esito -->
<div class="modal modal-blur fade" id="modal-nuovo-esito" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?= Html::beginForm(['aggiungi-esito', 'id' => $model->id], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title">Registra Nuovo Esito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Esito</label>
                    <select name="StoricoEsito[esito]" class="form-select" required>
                        <option value="">-- Seleziona --</option>
                        <?php foreach (\app\models\StoricoEsito::getEsitoLabels() as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data Esito</label>
                    <input type="date" name="StoricoEsito[data_esito]" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea name="StoricoEsito[note]" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="ti ti-x me-1"></i> Annulla</button>
                <button type="submit" class="btn btn-success"><i class="ti ti-check me-1"></i> Salva Esito</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<style>
.timeline {
    list-style: none;
    padding: 0;
    position: relative;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e6e7e9;
}
.timeline-event {
    position: relative;
    padding-left: 45px;
    margin-bottom: 1rem;
}
.timeline-event-icon {
    position: absolute;
    left: 4px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 12px;
    z-index: 1;
}
.timeline-event-card {
    border-left: 3px solid #e6e7e9;
}
.drop-zone {
    border: 2px dashed #206bc4;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    background: #e9f0f8;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}
.drop-zone-icon {
    font-size: 2.5rem;
    color: #206bc4;
}
.drop-zone:hover {
    background: #dce6f1;
    border-color: #1a5bab;
}
.drop-zone.drag-over {
    border-style: solid;
    border-color: #206bc4;
    background: #cddcf0;
}
.doc-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e6e7e9;
}
.doc-item:last-child {
    border-bottom: none;
}
</style>

<?php
$csrfToken = Yii::$app->request->csrfToken;
$uploadUrl = \yii\helpers\Url::to(['upload-documento', 'id' => $model->id]);
$deleteUrl = \yii\helpers\Url::to(['delete-documento']);
$downloadUrl = \yii\helpers\Url::to(['download-documento']);
$js = <<<JS
(function() {
    var dropZone = document.getElementById('drop-zone');
    var fileInput = document.getElementById('file-input-hidden');
    var btnCarica = document.getElementById('btn-carica-documento');
    var progressBar = document.getElementById('upload-progress');
    var listaDocumenti = document.getElementById('lista-documenti');

    // Click sul pulsante o sulla drop zone apre il file picker
    btnCarica.addEventListener('click', function() { fileInput.click(); });
    dropZone.addEventListener('click', function() { fileInput.click(); });

    // Drag & Drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        if (e.dataTransfer.files.length > 0) {
            uploadFile(e.dataTransfer.files[0]);
        }
    });

    // File selezionato via input
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            uploadFile(fileInput.files[0]);
            fileInput.value = '';
        }
    });

    function uploadFile(file) {
        var formData = new FormData();
        formData.append('file', file);
        formData.append('_csrf', '{$csrfToken}');

        progressBar.style.display = 'block';

        fetch('{$uploadUrl}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': '{$csrfToken}' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            progressBar.style.display = 'none';
            if (data.success) {
                addDocumentoToList(data.documento);
                // Rimuovi "nessun documento" se presente
                var noDoc = document.getElementById('no-documenti');
                if (noDoc) noDoc.remove();
            } else {
                alert(data.error || 'Errore durante il caricamento.');
            }
        })
        .catch(function(err) {
            progressBar.style.display = 'none';
            alert('Errore di rete durante il caricamento.');
        });
    }

    function getIconForMime(mimeType) {
        if (mimeType.indexOf('pdf') !== -1) return { icon: 'ti ti-file-type-pdf', bg: 'bg-red-lt', text: 'text-red' };
        if (mimeType.indexOf('word') !== -1 || mimeType.indexOf('document') !== -1) return { icon: 'ti ti-file-type-doc', bg: 'bg-blue-lt', text: 'text-blue' };
        if (mimeType.indexOf('image') !== -1) return { icon: 'ti ti-photo', bg: 'bg-green-lt', text: 'text-green' };
        return { icon: 'ti ti-file', bg: 'bg-azure-lt', text: 'text-muted' };
    }

    function addDocumentoToList(doc) {
        var iconInfo = getIconForMime(doc.tipo);
        var html = '<div class="list-group-item doc-item" data-id="' + doc.id + '">' +
            '<div class="row align-items-center">' +
                '<div class="col-auto"><span class="avatar ' + iconInfo.bg + '"><i class="' + iconInfo.icon + ' ' + iconInfo.text + '"></i></span></div>' +
                '<div class="col">' +
                    '<div class="d-block text-truncate"><strong>' + escapeHtml(doc.nome) + '</strong></div>' +
                    '<small class="text-muted">Documento &bull; ' + doc.dimensione + ' &bull; ' + (doc.data || 'Adesso') + '</small>' +
                '</div>' +
                '<div class="col-auto">' +
                    '<a href="{$downloadUrl}?docId=' + doc.id + '" class="btn btn-info btn-sm me-1" title="Scarica"><i class="ti ti-download"></i></a>' +
                    '<button class="btn btn-danger btn-sm btn-delete-doc" data-id="' + doc.id + '" title="Elimina"><i class="ti ti-trash"></i></button>' +
                '</div>' +
            '</div></div>';
        listaDocumenti.insertAdjacentHTML('afterbegin', html);
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Delegazione eventi per eliminazione
    listaDocumenti.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-delete-doc');
        if (!btn) return;
        var docId = btn.getAttribute('data-id');
        if (!confirm('Sei sicuro di voler eliminare questo documento?')) return;

        fetch('{$deleteUrl}?docId=' + docId, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': '{$csrfToken}',
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var item = listaDocumenti.querySelector('.doc-item[data-id="' + docId + '"]');
                if (item) item.remove();
                // Se non ci sono piu documenti
                if (listaDocumenti.querySelectorAll('.doc-item').length === 0) {
                    listaDocumenti.innerHTML = '<div class="text-center py-3" id="no-documenti"><i class="ti ti-file-off d-block mb-2" style="font-size: 2rem;"></i><p class="text-muted mb-0">Nessun documento caricato</p></div>';
                }
            } else {
                alert(data.error || 'Errore durante l\'eliminazione.');
            }
        })
        .catch(function() {
            alert('Errore di rete durante l\'eliminazione.');
        });
    });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
