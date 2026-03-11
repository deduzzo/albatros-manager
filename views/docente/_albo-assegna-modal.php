<?php

use yii\helpers\Html;

/* @var $progetti app\models\Progetto[] */
?>

<div class="modal modal-blur fade" id="modal-assegna" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-link me-2"></i>
                    Assegna Docente a Modulo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assegna-docente-cf" value="">

                <!-- Info docente selezionato -->
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-user me-2"></i>
                        <div>
                            <strong>Docente selezionato:</strong>
                            <span id="assegna-docente-nome" class="fw-bold"></span>
                        </div>
                    </div>
                </div>

                <!-- Selezione Progetto -->
                <div class="mb-3">
                    <label class="form-label required">Progetto</label>
                    <select id="assegna-progetto" class="form-select">
                        <option value="">-- Seleziona un progetto --</option>
                        <?php foreach ($progetti as $progetto): ?>
                            <option value="<?= $progetto->id ?>">
                                <?= Html::encode($progetto->codice_progetto . ' - ' . $progetto->titolo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Selezione Avviso (cascading) -->
                <div class="mb-3">
                    <label class="form-label required">Avviso/Bando</label>
                    <select id="assegna-avviso" class="form-select" disabled>
                        <option value="">-- Seleziona prima un progetto --</option>
                    </select>
                </div>

                <!-- Selezione Modulo (cascading) -->
                <div class="mb-3">
                    <label class="form-label required">Modulo</label>
                    <select id="assegna-modulo" class="form-select" disabled>
                        <option value="">-- Seleziona prima un avviso --</option>
                    </select>
                    <small class="form-hint">Solo i moduli senza docente assegnato sono selezionabili.</small>
                </div>

                <!-- Note -->
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea id="assegna-note" class="form-control" rows="2" placeholder="Note aggiuntive sull'assegnazione (opzionale)..."></textarea>
                </div>

                <!-- Risultato operazione -->
                <div id="assegna-result"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Annulla
                </button>
                <button type="button" id="assegna-submit" class="btn btn-primary" disabled>
                    <i class="ti ti-link me-1"></i> Assegna
                </button>
            </div>
        </div>
    </div>
</div>
