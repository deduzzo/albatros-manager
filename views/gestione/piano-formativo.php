<?php

/* @var $this \yii\web\View */
/* @var $corso \app\models\Avviso */
/* @var $section string */
/* @var $moduli \app\models\Modulo[] */

use yii\helpers\Html;
use yii\helpers\Url;

$statusColors = [
    'bozza'     => 'bg-orange-lt text-orange',
    'aperto'    => 'bg-blue-lt text-blue',
    'in_corso'  => 'bg-indigo-lt text-indigo',
    'concluso'  => 'bg-teal-lt text-teal',
    'annullato' => 'bg-red-lt text-red',
];
$statusLabels = [
    'bozza'     => 'Bozza',
    'aperto'    => 'Aperto',
    'in_corso'  => 'In Corso',
    'concluso'  => 'Concluso',
    'annullato' => 'Annullato',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="mb-1" style="font-size:1.3rem; font-weight:700; color:#1e293b;">Piano Formativo</h2>
        <p class="text-secondary mb-0" style="font-size:0.875rem;"><?= Html::encode($corso->codice_avviso . ' — ' . $corso->titolo) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['/istanza/create', 'avviso_id' => $corso->id]) ?>" class="btn btn-sm" style="background:#4263eb; color:#fff; border-color:#4263eb;">
            <i class="ti ti-user-plus me-1"></i> Nuova Candidatura
        </a>
        <a href="<?= Url::to(['/modulo/create', 'avviso_id' => $corso->id]) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-plus me-1"></i> Nuovo Modulo
        </a>
    </div>
</div>

<?php if (empty($moduli)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-book-off text-secondary" style="font-size:3rem;"></i>
            <h3 class="mt-3 text-secondary">Nessun modulo</h3>
            <p class="text-muted mb-4">Non ci sono moduli per questo corso.</p>
            <a href="<?= Url::to(['/modulo/create', 'avviso_id' => $corso->id]) ?>" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Crea il primo modulo
            </a>
        </div>
    </div>
<?php else: ?>
    <form method="post" action="<?= Url::to(['/gestione/metti-a-bando', 'corso_id' => $corso->id]) ?>">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">
                                <input type="checkbox" class="form-check-input" id="select-all-moduli">
                            </th>
                            <th>Codice</th>
                            <th>Titolo</th>
                            <th class="text-end">Ore</th>
                            <th class="text-end">Compenso/h</th>
                            <th>Docente</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($moduli as $modulo): ?>
                            <?php
                            $assegnazione = null;
                            foreach ($modulo->assegnazioni as $a) {
                                if (!$a->deleted_at) { $assegnazione = $a; break; }
                            }
                            $scoperto = $assegnazione === null;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($scoperto): ?>
                                        <input type="checkbox" name="moduli[]" value="<?= $modulo->id ?>"
                                               class="form-check-input modulo-checkbox">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code style="font-size:0.8rem;"><?= Html::encode($modulo->codice) ?></code>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= Html::encode($modulo->titolo) ?></div>
                                </td>
                                <td class="text-end"><?= $modulo->ore_totali ?></td>
                                <td class="text-end">
                                    <?= $modulo->compenso_orario ? '€ ' . number_format($modulo->compenso_orario, 2, ',', '.') : '-' ?>
                                </td>
                                <td>
                                    <?php if ($scoperto): ?>
                                        <span class="badge bg-orange-lt text-orange">
                                            <i class="ti ti-alert-circle me-1"></i>Scoperto
                                        </span>
                                    <?php else: ?>
                                        <?php $docente = $assegnazione->docente; ?>
                                        <?= $docente ? Html::encode($docente->cognome . ' ' . $docente->nome) : '—' ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusColors[$modulo->status] ?? 'bg-default-lt' ?>">
                                        <?= $statusLabels[$modulo->status] ?? $modulo->status ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= Url::to(['/modulo/view', 'id' => $modulo->id]) ?>"
                                           class="btn btn-sm btn-ghost-secondary" title="Dettaglio">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="<?= Url::to(['/modulo/update', 'id' => $modulo->id]) ?>"
                                           class="btn btn-sm btn-ghost-secondary" title="Modifica">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between align-items-center">
            <button type="submit" id="btn-metti-a-bando" class="btn btn-warning" disabled>
                <i class="ti ti-send me-1"></i> Metti a bando i moduli selezionati
            </button>
            <span class="text-muted" style="font-size:0.8rem;">Seleziona i moduli scoperti per metterli a bando</span>
        </div>
    </form>

    <script>
    (function() {
        var selectAll = document.getElementById('select-all-moduli');
        var checkboxes = document.querySelectorAll('.modulo-checkbox');
        var btnBando = document.getElementById('btn-metti-a-bando');

        function updateBtn() {
            var checked = document.querySelectorAll('.modulo-checkbox:checked').length;
            btnBando.disabled = checked === 0;
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
            updateBtn();
        });

        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                selectAll.checked = [...checkboxes].every(function(c) { return c.checked; });
                updateBtn();
            });
        });
    })();
    </script>
<?php endif; ?>
