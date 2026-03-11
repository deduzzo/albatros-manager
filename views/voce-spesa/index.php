<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\VoceSpesaCategoria;

/* @var $this yii\web\View */
/* @var $bySezione array */

$this->title = 'Voci di Spesa';
$this->params['breadcrumbs'][] = $this->title;

$sezioneLabels = VoceSpesaCategoria::getSezioneList();
$sezioneIcons = [
    'costi_diretti' => 'ti-file-invoice',
    'costi_indiretti' => 'ti-file-dots',
    'attivita_accessorie' => 'ti-tool',
];
$sezioneColors = [
    'costi_diretti' => 'blue',
    'costi_indiretti' => 'purple',
    'attivita_accessorie' => 'teal',
];
?>

<div class="voce-spesa-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-list-tree me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">
                        Gestione categorie e voci di spesa del bilancio
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Categoria', ['create-categoria'], ['class' => 'btn btn-primary me-2']) ?>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Nuova Voce', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?php if (empty($bySezione)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-list-tree" style="font-size: 3rem;"></i>
                            </div>
                            <p class="empty-title">Nessuna voce di spesa configurata</p>
                            <p class="empty-subtitle text-muted">
                                Inizia creando una categoria e poi aggiungi le voci di spesa.
                            </p>
                            <div class="empty-action">
                                <?= Html::a('<i class="ti ti-plus me-1"></i> Crea Categoria', ['create-categoria'], ['class' => 'btn btn-primary']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach (['costi_diretti', 'costi_indiretti', 'attivita_accessorie'] as $sezione): ?>
                    <?php if (!isset($bySezione[$sezione])) continue; ?>
                    <?php
                        $categorie = $bySezione[$sezione];
                        $color = $sezioneColors[$sezione] ?? 'blue';
                        $icon = $sezioneIcons[$sezione] ?? 'ti-folder';
                        $totaleVoci = 0;
                        foreach ($categorie as $cat) {
                            $totaleVoci += count($cat->vociSpesa);
                        }
                    ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti <?= $icon ?> me-2 text-<?= $color ?>"></i>
                                <span class="badge bg-<?= $color ?> text-<?= $color ?>-fg me-2"><?= Html::encode($sezioneLabels[$sezione] ?? $sezione) ?></span>
                                <span class="text-muted small"><?= count($categorie) ?> categorie, <?= $totaleVoci ?> voci</span>
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="accordion" id="accordion-<?= $sezione ?>">
                                <?php foreach ($categorie as $i => $cat): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-<?= $cat->id ?>">
                                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $cat->id ?>">
                                                <span class="badge bg-<?= $color ?>-lt me-2"><?= Html::encode($cat->codice) ?></span>
                                                <?= Html::encode($cat->nome) ?>
                                                <span class="badge bg-default ms-2"><?= count($cat->vociSpesa) ?> voci</span>
                                            </button>
                                        </h2>
                                        <div id="collapse-<?= $cat->id ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#accordion-<?= $sezione ?>">
                                            <div class="accordion-body p-0">
                                                <!-- Azioni categoria -->
                                                <div class="d-flex justify-content-end p-2 bg-light border-bottom">
                                                    <?= Html::a('<i class="ti ti-edit"></i> Modifica Categoria', ['update-categoria', 'id' => $cat->id], [
                                                        'class' => 'btn btn-sm btn-outline-primary me-1',
                                                    ]) ?>
                                                    <?= Html::a('<i class="ti ti-trash"></i>', ['delete-categoria', 'id' => $cat->id], [
                                                        'class' => 'btn btn-sm btn-outline-danger',
                                                        'data-confirm' => 'Sei sicuro di voler eliminare la categoria "' . Html::encode($cat->codice . ' - ' . $cat->nome) . '"?',
                                                        'data-method' => 'post',
                                                        'title' => 'Elimina categoria',
                                                        'data-bs-toggle' => 'tooltip',
                                                    ]) ?>
                                                </div>
                                                <?php if (empty($cat->vociSpesa)): ?>
                                                    <div class="p-3 text-center text-muted">
                                                        <i class="ti ti-info-circle me-1"></i>
                                                        Nessuna voce in questa categoria.
                                                    </div>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-vcenter table-sm mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 80px;">Codice</th>
                                                                    <th>Nome</th>
                                                                    <th>Descrizione</th>
                                                                    <th style="width: 80px;">Ordine</th>
                                                                    <th style="width: 100px;" class="text-end">Azioni</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($cat->vociSpesa as $voce): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <code><?= Html::encode($voce->codice) ?></code>
                                                                        </td>
                                                                        <td><?= Html::encode($voce->nome) ?></td>
                                                                        <td class="text-muted small"><?= Html::encode($voce->descrizione) ?: '-' ?></td>
                                                                        <td class="text-center"><?= $voce->ordinamento ?></td>
                                                                        <td class="text-end">
                                                                            <?= Html::a('<i class="ti ti-edit"></i>', ['update', 'id' => $voce->id], [
                                                                                'class' => 'btn btn-sm btn-primary',
                                                                                'title' => 'Modifica',
                                                                                'data-bs-toggle' => 'tooltip',
                                                                            ]) ?>
                                                                            <?= Html::a('<i class="ti ti-trash"></i>', ['delete', 'id' => $voce->id], [
                                                                                'class' => 'btn btn-sm btn-danger',
                                                                                'title' => 'Elimina',
                                                                                'data-bs-toggle' => 'tooltip',
                                                                                'data-confirm' => 'Sei sicuro di voler eliminare questa voce di spesa?',
                                                                                'data-method' => 'post',
                                                                            ]) ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
