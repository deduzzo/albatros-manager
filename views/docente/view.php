<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Docente */
/* @var $tagsByCategoria array */
/* @var $tagsDisponibili array */

$this->title = $model->getNomeCompleto();
$this->params['breadcrumbs'][] = ['label' => 'Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="docente-view">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-lg bg-blue-lt">
                        <?= Html::encode($model->getIniziali()) ?>
                    </span>
                </div>
                <div class="col">
                    <h2 class="page-title">
                        <?= Html::encode($this->title) ?>
                        <?= $model->getTipoBadge() ?>
                    </h2>
                    <div class="text-muted mt-1">
                        <code><?= Html::encode($model->codice_fiscale) ?></code>
                        <?= $model->getProvenienzaBadge() ?>
                        <?php if ($model->codice_fiscale_validato): ?>
                            <span class="badge bg-green text-green-fg ms-2"><i class="ti ti-check me-1"></i>CF Validato</span>
                        <?php else: ?>
                            <span class="badge bg-orange text-orange-fg ms-2"><i class="ti ti-alert-triangle me-1"></i>CF Non Validato</span>
                        <?php endif; ?>
                        <?php if ($model->email): ?>
                            <span class="mx-2">|</span>
                            <i class="ti ti-mail me-1"></i>
                            <?= Html::a($model->email, 'mailto:' . $model->email) ?>
                        <?php endif; ?>
                        <?php if ($model->telefono): ?>
                            <span class="mx-2">|</span>
                            <i class="ti ti-phone me-1"></i>
                            <?= Html::encode($model->telefono) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-edit me-1"></i> Modifica', ['update', 'id' => $model->codice_fiscale], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="ti ti-trash me-1"></i> Elimina', ['delete', 'id' => $model->codice_fiscale], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo docente?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?php if ($model->isSovraimpegnato()): ?>
                <div class="alert alert-warning mb-3">
                    <div class="d-flex">
                        <div><i class="ti ti-alert-triangle icon me-2"></i></div>
                        <div>
                            <h4 class="alert-title">Attenzione: Docente sovra-impegnato</h4>
                            <div class="text-muted">Questo docente ha <strong><?= $model->getModuliAttivi() ?></strong> moduli attivi con contratto in corso. Considerare di ridistribuire gli incarichi.</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row row-deck row-cards">
                <!-- Statistiche rapide -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Valutazione Media</div>
                            </div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->getValutazioneStarsHtml() ?>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= $model->getValutazioniCount() ?> valutazioni ricevute
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Ore di Docenza</div>
                            </div>
                            <div class="h1 mb-0 mt-2">
                                <?= number_format($model->getOreTotali(), 0, ',', '.') ?>
                            </div>
                            <div class="text-muted small mt-1">
                                ore completate
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <a href="#moduli-assegnati" class="card text-decoration-none" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Moduli Assegnati</div>
                            </div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->getModuliCount() ?>
                            </div>
                            <div class="text-muted small mt-1">
                                assegnazioni attive
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <a href="#candidature-presentate" class="card text-decoration-none" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Istanze</div>
                            </div>
                            <div class="h1 mb-0 mt-2">
                                <?= $model->getIstanzeCount() ?>
                            </div>
                            <div class="text-muted small mt-1">
                                domande presentate
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Anagrafica -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-user me-2"></i>
                                Dati Anagrafici
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Codice Fiscale</div>
                                    <div class="datagrid-content">
                                        <code><?= Html::encode($model->codice_fiscale) ?></code>
                                        <?php if ($model->codice_fiscale_validato): ?>
                                            <span class="badge bg-green text-green-fg ms-1"><i class="ti ti-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-orange text-orange-fg ms-1" title="Non validato"><i class="ti ti-alert-triangle"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nome Completo</div>
                                    <div class="datagrid-content"><?= Html::encode($model->getNomeCompleto()) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Tipo</div>
                                    <div class="datagrid-content"><?= $model->getTipoBadge() ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Provenienza</div>
                                    <div class="datagrid-content"><?= $model->getProvenienzaBadge() ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Data di Nascita</div>
                                    <div class="datagrid-content">
                                        <?= $model->data_nascita ? Yii::$app->formatter->asDate($model->data_nascita, 'long') : '-' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Luogo di Nascita</div>
                                    <div class="datagrid-content"><?= Html::encode($model->luogo_nascita) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Indirizzo</div>
                                    <div class="datagrid-content"><?= Html::encode($model->getIndirizzoCompleto()) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Telefono</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->telefono): ?>
                                            <i class="ti ti-phone me-1"></i>
                                            <?= Html::a($model->telefono, 'tel:' . $model->telefono) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Email</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->email): ?>
                                            <i class="ti ti-mail me-1"></i>
                                            <?= Html::a($model->email, 'mailto:' . $model->email) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dati Professionali -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-briefcase me-2"></i>
                                Dati Professionali
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Titolo di Studio</div>
                                    <div class="datagrid-content"><?= Html::encode($model->titolo_studio) ?: '-' ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Partita IVA</div>
                                    <div class="datagrid-content">
                                        <?= $model->partita_iva ? '<code>' . Html::encode($model->partita_iva) . '</code>' : '-' ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">IBAN</div>
                                    <div class="datagrid-content">
                                        <?php if ($model->iban): ?>
                                            <code class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= Html::encode($model->iban) ?>">
                                                <?= Html::encode($model->iban) ?>
                                            </code>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Creato il</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Ultimo aggiornamento</div>
                                    <div class="datagrid-content">
                                        <?= Yii::$app->formatter->asDatetime($model->updated_at) ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($model->specializzazioni): ?>
                                <hr>
                                <div class="mb-2"><strong>Specializzazioni:</strong></div>
                                <p class="text-muted"><?= nl2br(Html::encode($model->specializzazioni)) ?></p>
                            <?php endif; ?>
                            <?php if ($model->note): ?>
                                <hr>
                                <div class="mb-2"><strong>Note:</strong></div>
                                <p class="text-muted"><?= nl2br(Html::encode($model->note)) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tag Curriculum -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-tags me-2"></i>
                                Competenze e Tag Curriculum
                            </h3>
                            <div class="card-actions">
                                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal-add-tag">
                                    <i class="ti ti-plus me-1"></i> Aggiungi Tag
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($tagsByCategoria)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="ti ti-tags" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Nessun tag assegnato a questo docente.</p>
                                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-tag">
                                        <i class="ti ti-plus me-1"></i> Aggiungi il primo tag
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($tagsByCategoria as $categoriaId => $data): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card card-sm">
                                                <div class="card-header" style="background-color: <?= Html::encode($data['categoria']->colore) ?>15; border-left: 3px solid <?= Html::encode($data['categoria']->colore) ?>;">
                                                    <h4 class="card-title">
                                                        <?php if ($data['categoria']->icona): ?>
                                                            <i class="<?= Html::encode($data['categoria']->icona) ?> me-2" style="color: <?= Html::encode($data['categoria']->colore) ?>;"></i>
                                                        <?php endif; ?>
                                                        <?= Html::encode($data['categoria']->nome) ?>
                                                    </h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php foreach ($data['tags'] as $docenteTag): ?>
                                                            <div class="d-inline-flex align-items-center">
                                                                <?= Html::a(
                                                                    '<span class="badge" style="background-color: ' . Html::encode($data['categoria']->colore) . '; color: #fff;">' .
                                                                    Html::encode($docenteTag->tag->nome) .
                                                                    '</span>',
                                                                    ['by-tag', 'tagId' => $docenteTag->tag_id],
                                                                    [
                                                                        'class' => 'text-decoration-none',
                                                                        'title' => 'Clicca per vedere altri docenti con questo tag',
                                                                        'data-bs-toggle' => 'tooltip',
                                                                    ]
                                                                ) ?>
                                                                <?php if ($docenteTag->livello): ?>
                                                                    <small class="ms-1 text-muted" title="Livello: <?= $docenteTag->getLivelloLabel() ?>">
                                                                        <?= $docenteTag->getLivelloStarsHtml() ?>
                                                                    </small>
                                                                <?php endif; ?>
                                                                <?= Html::beginForm(['remove-tag'], 'post', ['class' => 'd-inline ms-1']) ?>
                                                                    <?= Html::hiddenInput('docente_codice_fiscale', $model->codice_fiscale) ?>
                                                                    <?= Html::hiddenInput('tag_id', $docenteTag->tag_id) ?>
                                                                    <button type="submit" class="btn btn-sm btn-ghost-danger p-0" title="Rimuovi tag" onclick="return confirm('Rimuovere questo tag?');">
                                                                        <i class="ti ti-x" style="font-size: 0.8rem;"></i>
                                                                    </button>
                                                                <?= Html::endForm() ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Moduli Assegnati -->
                <div class="col-12" id="moduli-assegnati">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-books me-2"></i>Moduli Assegnati</h3>
                        </div>
                        <?php $assegnazioni = $model->assegnazioni; ?>
                        <?php if ($assegnazioni): ?>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-hover">
                                <thead>
                                    <tr>
                                        <th>Codice Modulo</th>
                                        <th>Titolo</th>
                                        <th>Avviso</th>
                                        <th>Ore</th>
                                        <th>Compenso/h</th>
                                        <th>Ore Rendicontate</th>
                                        <th>Contratto</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assegnazioni as $assegnazione): ?>
                                    <?php $modulo = $assegnazione->modulo; ?>
                                    <tr>
                                        <td>
                                            <?php if ($modulo): ?>
                                                <?= Html::a('<span class="badge bg-blue-lt">' . Html::encode($modulo->codice) . '</span>', ['/modulo/view', 'id' => $modulo->id]) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $modulo ? Html::a(Html::encode($modulo->titolo), ['/modulo/view', 'id' => $modulo->id]) : '-' ?></td>
                                        <td>
                                            <?php if ($modulo && $modulo->avviso): ?>
                                                <?= Html::a(Html::encode($modulo->avviso->codice_avviso), ['/avviso/view', 'id' => $modulo->avviso_id]) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $modulo ? $modulo->ore_totali : '-' ?></td>
                                        <td><?= $modulo ? Yii::$app->formatter->asCurrency($modulo->compenso_orario) : '-' ?></td>
                                        <td>
                                            <?php
                                                $oreRendicontateApp = \app\models\RendicontazioneOre::getTotaleOreApprovate($assegnazione->id);
                                                $oreRendicontateIns = \app\models\RendicontazioneOre::getTotaleOreInserite($assegnazione->id);
                                                $oreTotModulo = $modulo ? $modulo->ore_totali : 0;
                                            ?>
                                            <?= Html::a(
                                                '<span class="text-success fw-bold">' . number_format($oreRendicontateApp, 1, ',', '.') . '</span>' .
                                                ($oreRendicontateIns > 0 ? ' <small class="text-warning">(+' . number_format($oreRendicontateIns, 1, ',', '.') . ')</small>' : '') .
                                                ' / ' . $oreTotModulo,
                                                ['/rendicontazione-ore/index', 'assegnazione_id' => $assegnazione->id],
                                                ['class' => 'text-decoration-none', 'title' => 'Dettaglio ore rendicontate']
                                            ) ?>
                                        </td>
                                        <td>
                                            <?php if ($assegnazione->contratto): ?>
                                                <?= Html::a('<span class="badge bg-green-lt">' . Html::encode($assegnazione->contratto->numero_contratto ?: 'Visualizza') . '</span>', ['/contratto/view', 'id' => $assegnazione->contratto->id]) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($modulo): ?>
                                                <?= Html::a('<i class="ti ti-eye"></i>', ['/modulo/view', 'id' => $modulo->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Dettaglio modulo']) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="card-body text-muted text-center">
                            <i class="ti ti-books mb-2" style="font-size: 2rem;"></i>
                            <p>Nessun modulo assegnato a questo docente</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Istanze/Candidature -->
                <div class="col-12" id="candidature-presentate">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-file-description me-2"></i>Candidature Presentate</h3>
                        </div>
                        <?php $istanze = $model->istanze; ?>
                        <?php if ($istanze): ?>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-hover">
                                <thead>
                                    <tr>
                                        <th>Modulo</th>
                                        <th>Avviso</th>
                                        <th>Punteggio</th>
                                        <th>Storico Esiti</th>
                                        <th>Stato Attuale</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($istanze as $istanza): ?>
                                    <tr>
                                        <td>
                                            <?php if ($istanza->modulo): ?>
                                                <?= Html::a(Html::encode($istanza->modulo->codice . ' - ' . $istanza->modulo->titolo), ['/modulo/view', 'id' => $istanza->modulo_id]) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($istanza->avviso): ?>
                                                <?= Html::a(Html::encode($istanza->avviso->codice_avviso), ['/avviso/view', 'id' => $istanza->avviso_id]) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $istanza->punteggio ?? '-' ?></td>
                                        <td>
                                            <?php foreach ($istanza->storicoEsiti as $esito): ?>
                                                <span class="badge <?= $esito->getEsitoBadgeClass() ?> me-1" title="<?= $esito->data_esito ? Yii::$app->formatter->asDate($esito->data_esito) : '' ?>">
                                                    <i class="<?= $esito->getEsitoIconClass() ?> me-1"></i><?= $esito->getEsitoLabel() ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (empty($istanza->storicoEsiti)): ?>
                                                <?= $istanza->getEsitoCtsBadgeHtml() ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge <?= $istanza->getStatusBadgeClass() ?>"><?= $istanza->getStatusLabel() ?></span></td>
                                        <td class="text-end">
                                            <?= Html::a('<i class="ti ti-eye"></i>', ['/istanza/view', 'id' => $istanza->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Dettaglio istanza']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="card-body text-muted text-center">
                            <i class="ti ti-file-off mb-2" style="font-size: 2rem;"></i>
                            <p>Nessuna candidatura presentata da questo docente</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer con azioni -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Torna alla lista', ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aggiungi Tag -->
<div class="modal modal-blur fade" id="modal-add-tag" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <?= Html::beginForm(['add-tag'], 'post') ?>
            <?= Html::hiddenInput('docente_codice_fiscale', $model->codice_fiscale) ?>
            <div class="modal-header">
                <h5 class="modal-title">Aggiungi Tag Competenza</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Tag</label>
                    <?= Html::dropDownList('tag_id', null, $tagsDisponibili, [
                        'class' => 'form-select',
                        'prompt' => 'Seleziona un tag...',
                        'required' => true,
                    ]) ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Livello Competenza</label>
                    <?= Html::dropDownList('livello', null, \app\models\DocenteTag::getLivelloLabels(), [
                        'class' => 'form-select',
                        'prompt' => 'Non specificato',
                    ]) ?>
                    <small class="form-hint">Opzionale: indica il livello di competenza del docente per questo tag.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Annulla
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="ti ti-plus me-1"></i> Aggiungi
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
