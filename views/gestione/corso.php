<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $docentiSenzaContratto \app\models\Assegnazione[] */
/* @var $lezioniSenzaPresenze \app\models\Calendario[] */
/* @var $nModuli int */
/* @var $nDocenti int */
/* @var $nStudenti int */
/* @var $nLezioni int */

use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="mb-4">
    <h2 class="mb-1" style="font-size:1.4rem; font-weight:700; color:#1e293b;">
        <?= Html::encode($corso->codice_avviso) ?> — <?= Html::encode($corso->titolo) ?>
    </h2>
    <p class="text-secondary mb-0" style="font-size:0.875rem;">Panoramica del corso</p>
</div>

<!-- Alert: docenti senza contratto -->
<?php if (!empty($docentiSenzaContratto)): ?>
    <div class="alert alert-warning alert-dismissible mb-3" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="ti ti-alert-triangle" style="font-size:1.2rem; flex-shrink:0; margin-top:1px;"></i>
            <div>
                <strong><?= count($docentiSenzaContratto) ?> docent<?= count($docentiSenzaContratto) > 1 ? 'i assegnati senza' : 'e assegnato senza' ?> contratto</strong>
                <ul class="mb-0 mt-1" style="font-size:0.875rem;">
                    <?php foreach ($docentiSenzaContratto as $ass): ?>
                        <li>
                            <?= Html::encode($ass->docente ? $ass->docente->cognome . ' ' . $ass->docente->nome : $ass->docente_codice_fiscale) ?>
                            <?php if ($ass->modulo): ?>
                                — <em><?= Html::encode($ass->modulo->titolo) ?></em>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Alert: lezioni senza presenze acquisite -->
<?php if (!empty($lezioniSenzaPresenze)): ?>
    <?php $maxShow = 5; $total = count($lezioniSenzaPresenze); ?>
    <div class="alert alert-danger alert-dismissible mb-3" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="ti ti-calendar-x" style="font-size:1.2rem; flex-shrink:0; margin-top:1px;"></i>
            <div>
                <strong><?= $total ?> lezioni con presenze non acquisite</strong>
                <ul class="mb-0 mt-1" style="font-size:0.875rem;">
                    <?php foreach (array_slice($lezioniSenzaPresenze, 0, $maxShow) as $lezione): ?>
                        <li>
                            <?= Yii::$app->formatter->asDate($lezione->data, 'php:d/m/Y') ?>
                            <?php if ($lezione->modulo): ?>
                                — <em><?= Html::encode($lezione->modulo->titolo) ?></em>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($total > $maxShow): ?>
                        <li><em>e altre <?= $total - $maxShow ?> lezioni...</em></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Stat cards -->
<div class="row row-cards mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="bg-indigo-lt p-3 rounded-2"><i class="ti ti-book text-indigo" style="font-size:1.5rem;"></i></span>
                    <div>
                        <div class="text-muted" style="font-size:0.75rem;">Moduli</div>
                        <div class="fw-bold" style="font-size:1.75rem; line-height:1;"><?= $nModuli ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="bg-blue-lt p-3 rounded-2"><i class="ti ti-users text-blue" style="font-size:1.5rem;"></i></span>
                    <div>
                        <div class="text-muted" style="font-size:0.75rem;">Docenti</div>
                        <div class="fw-bold" style="font-size:1.75rem; line-height:1;"><?= $nDocenti ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="bg-green-lt p-3 rounded-2"><i class="ti ti-school text-green" style="font-size:1.5rem;"></i></span>
                    <div>
                        <div class="text-muted" style="font-size:0.75rem;">Studenti</div>
                        <div class="fw-bold" style="font-size:1.75rem; line-height:1;"><?= $nStudenti ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="bg-azure-lt p-3 rounded-2"><i class="ti ti-calendar text-azure" style="font-size:1.5rem;"></i></span>
                    <div>
                        <div class="text-muted" style="font-size:0.75rem;">Lezioni</div>
                        <div class="fw-bold" style="font-size:1.75rem; line-height:1;"><?= $nLezioni ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Azioni rapide -->
<div class="row row-cards">
    <div class="col-md-4">
        <a href="<?= Url::to(['/gestione/sezione', 'id' => $corso->id, 'section' => 'piano-formativo']) ?>" class="card card-link card-link-pop text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="ti ti-book text-indigo" style="font-size:2rem;"></i>
                <h4 class="mt-2 mb-1" style="font-size:1rem;">Piano Formativo</h4>
                <p class="text-muted mb-0" style="font-size:0.8rem;">Gestisci moduli e assegnazioni</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= Url::to(['/istanza/create', 'avviso_id' => $corso->id]) ?>" class="card card-link card-link-pop text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="ti ti-user-plus text-blue" style="font-size:2rem;"></i>
                <h4 class="mt-2 mb-1" style="font-size:1rem;">Nuova Candidatura</h4>
                <p class="text-muted mb-0" style="font-size:0.8rem;">Inserisci una candidatura docente</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= Url::to(['/avviso/view', 'id' => $corso->id]) ?>" class="card card-link card-link-pop text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="ti ti-external-link text-secondary" style="font-size:2rem;"></i>
                <h4 class="mt-2 mb-1" style="font-size:1rem;">Dettaglio Avanzato</h4>
                <p class="text-muted mb-0" style="font-size:0.8rem;">Apri in modalità avanzata</p>
            </div>
        </a>
    </div>
</div>
