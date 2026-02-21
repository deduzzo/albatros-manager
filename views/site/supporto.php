<?php
/**
 * Form Segnalazione Problemi / Supporto
 * Accessibile a tutti (guest + autenticati)
 *
 * @var string $nome
 * @var string $email
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Segnala un Problema';
?>

<div class="container-xl py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <a href="javascript:history.back()" class="btn btn-outline-primary">
                            <i class="ti ti-arrow-left me-1"></i> Indietro
                        </a>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <i class="ti ti-bug me-2"></i> Segnala un Problema
                        </h2>
                        <div class="text-secondary">Aiutaci a migliorare il sistema</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Compila il modulo di segnalazione</h3>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label required">Nome</label>
                                <input type="text" name="nome" class="form-control"
                                       value="<?= Html::encode($nome) ?>"
                                       placeholder="Il tuo nome" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= Html::encode($email) ?>"
                                       placeholder="La tua email" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Tipo di segnalazione</label>
                            <select name="tipo" class="form-select" required>
                                <option value="">-- Seleziona --</option>
                                <option value="bug">Bug / Errore</option>
                                <option value="suggerimento">Suggerimento</option>
                                <option value="domanda">Domanda</option>
                                <option value="altro">Altro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Descrizione</label>
                            <textarea name="descrizione" class="form-control" rows="6"
                                      placeholder="Descrivi il problema nel modo piu dettagliato possibile: cosa stavi facendo, cosa ti aspettavi e cosa e successo invece."
                                      required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Screenshot (opzionale)</label>
                            <input type="file" name="screenshot" class="form-control"
                                   accept=".jpg,.jpeg,.png" id="screenshot-input">
                            <small class="form-hint">Formati accettati: JPG, PNG. Dimensione massima: 5 MB.</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="javascript:history.back()" class="btn">Annulla</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-send me-1"></i> Invia Segnalazione
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('screenshot-input');
    if (input) {
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                var file = this.files[0];
                var maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('Il file supera la dimensione massima di 5 MB.');
                    this.value = '';
                }
            }
        });
    }
});
</script>
