<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $corso array */
/* @var $documentiRichiesti array */

$this->title = 'Iscrizione al Corso';
?>

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">Iscrizione</div>
                    <h2 class="page-title"><?= Html::encode($corso['titolo']) ?></h2>
                </div>
            </div>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Step 1: Dati Anagrafici -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-user me-2"></i> 1. Dati Anagrafici</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label required">Codice Fiscale</label>
                                    <input type="text" class="form-control" name="codice_fiscale" placeholder="Es: RSSMRA80A01F205X" maxlength="16" style="text-transform: uppercase;" required>
                                    <small class="form-hint">Il codice fiscale sarà il tuo username per accedere</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Cognome</label>
                                    <input type="text" class="form-control" name="cognome" placeholder="Rossi" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nome</label>
                                    <input type="text" class="form-control" name="nome" placeholder="Mario" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Data di Nascita</label>
                                    <input type="date" class="form-control" name="data_nascita" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Luogo di Nascita</label>
                                    <input type="text" class="form-control" name="luogo_nascita" placeholder="Milano" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="mario.rossi@email.it" required>
                                    <small class="form-hint">Riceverai le comunicazioni a questo indirizzo</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Telefono</label>
                                    <input type="tel" class="form-control" name="telefono" placeholder="+39 333 1234567" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Residenza -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-home me-2"></i> 2. Indirizzo di Residenza</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label required">Indirizzo</label>
                                    <input type="text" class="form-control" name="indirizzo" placeholder="Via Roma, 10" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label required">CAP</label>
                                    <input type="text" class="form-control" name="cap" placeholder="20100" maxlength="5" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Città</label>
                                    <input type="text" class="form-control" name="citta" placeholder="Milano" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label required">Provincia</label>
                                    <input type="text" class="form-control" name="provincia" placeholder="MI" maxlength="2" style="text-transform: uppercase;" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Titolo di Studio -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-certificate me-2"></i> 3. Titolo di Studio</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Titolo di Studio</label>
                                    <select class="form-select" name="titolo_studio" required>
                                        <option value="">-- Seleziona --</option>
                                        <option value="licenza_media">Licenza Media</option>
                                        <option value="diploma">Diploma di Scuola Superiore</option>
                                        <option value="laurea_triennale">Laurea Triennale</option>
                                        <option value="laurea_magistrale">Laurea Magistrale</option>
                                        <option value="master">Master / Specializzazione</option>
                                        <option value="dottorato">Dottorato di Ricerca</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Anno di Conseguimento</label>
                                    <input type="number" class="form-control" name="anno_conseguimento" placeholder="2020" min="1950" max="2025">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Istituto / Università</label>
                                    <input type="text" class="form-control" name="istituto" placeholder="Università degli Studi di Milano">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Situazione Lavorativa -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-briefcase me-2"></i> 4. Situazione Lavorativa</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Stato Occupazionale</label>
                                    <select class="form-select" name="stato_occupazionale" required>
                                        <option value="">-- Seleziona --</option>
                                        <option value="occupato">Occupato</option>
                                        <option value="disoccupato">Disoccupato</option>
                                        <option value="inoccupato">Inoccupato (mai lavorato)</option>
                                        <option value="studente">Studente</option>
                                        <option value="pensionato">Pensionato</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Settore di attività</label>
                                    <input type="text" class="form-control" name="settore" placeholder="Es: Informatica, Commercio...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Documenti -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-file-upload me-2"></i> 5. Carica Documenti</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Documenti richiesti per l'iscrizione</strong>
                            </div>

                            <?php foreach ($documentiRichiesti as $index => $doc): ?>
                                <div class="mb-3">
                                    <label class="form-label <?= $doc['obbligatorio'] ? 'required' : '' ?>">
                                        <?= Html::encode($doc['nome']) ?>
                                        <?php if (!$doc['obbligatorio']): ?>
                                            <span class="badge bg-default text-default-fg ms-1">Facoltativo</span>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" class="form-control" name="documenti[<?= $index ?>]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" <?= $doc['obbligatorio'] ? 'required' : '' ?>>
                                    <small class="form-hint">Formati: <?= Html::encode($doc['formati']) ?> - Max: <?= $doc['max_mb'] ?>MB</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 6: Privacy -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-shield-check me-2"></i> 6. Privacy e Consensi</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="privacy" required>
                                    <span class="form-check-label">
                                        Ho letto e accetto la <a href="#" target="_blank">Privacy Policy</a> e i <a href="#" target="_blank">Termini di Servizio</a> *
                                    </span>
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="marketing">
                                    <span class="form-check-label">
                                        Acconsento a ricevere comunicazioni su futuri corsi e iniziative formative
                                    </span>
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="dati_veritieri" required>
                                    <span class="form-check-label">
                                        Dichiaro che tutti i dati inseriti sono veritieri e mi assumo la responsabilità di eventuali dichiarazioni mendaci *
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-notes me-2"></i> Note aggiuntive</h3>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" name="note" rows="3" placeholder="Eventuali note o richieste particolari..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Riepilogo Corso -->
                    <div class="card sticky-top" style="top: 70px;">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h3 class="card-title text-white">Riepilogo Corso</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-5">Codice:</dt>
                                <dd class="col-7"><span class="badge bg-blue-lt"><?= Html::encode($corso['codice']) ?></span></dd>

                                <dt class="col-5">Titolo:</dt>
                                <dd class="col-7"><?= Html::encode($corso['titolo']) ?></dd>

                                <dt class="col-5">Durata:</dt>
                                <dd class="col-7"><?= $corso['ore'] ?> ore</dd>

                                <dt class="col-5">Periodo:</dt>
                                <dd class="col-7">
                                    <?= Yii::$app->formatter->asDate($corso['data_inizio']) ?><br>
                                    <?= Yii::$app->formatter->asDate($corso['data_fine']) ?>
                                </dd>

                                <dt class="col-5">Sede:</dt>
                                <dd class="col-7"><?= Html::encode($corso['sede']) ?></dd>

                                <dt class="col-5">Docente:</dt>
                                <dd class="col-7"><?= Html::encode($corso['docente']) ?></dd>

                                <dt class="col-5">Posti:</dt>
                                <dd class="col-7">
                                    <span class="badge bg-green text-green-fg"><?= $corso['posti_disponibili'] ?> disponibili</span>
                                </dd>

                                <dt class="col-5">Costo:</dt>
                                <dd class="col-7"><strong class="text-success">GRATUITO</strong></dd>
                            </dl>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="ti ti-send me-2"></i> Invia Iscrizione
                            </button>
                            <div class="text-center mt-2">
                                <a href="<?= Url::to(['corsi']) ?>" class="text-secondary">
                                    <i class="ti ti-arrow-left me-1"></i> Torna ai corsi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
