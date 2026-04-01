<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $loginForm */
/** @var app\models\forms\DocenteLoginForm $docenteLoginForm */
/** @var app\models\forms\AllievoLoginForm $studenteLoginForm */
/** @var app\models\Avviso[] $bandiAperti */
/** @var string|null $loginError */
/** @var string|null $docenteError */
/** @var string|null $activeCard */
/** @var array $demoDocenti */
/** @var array $demoAllievi */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Accedi';
$this->context->layout = 'login';

$isAdminLogged = !Yii::$app->user->isGuest;
$isDocenteLogged = !Yii::$app->docenteUser->isGuest;

// Tipo selezionato: default admin, preserva scelta dopo errore POST
$selectedType = $activeCard ?: 'admin';
?>

<style>
    .role-selector {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .role-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border: 2px solid #e6e7e9;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
    }
    .role-option:hover {
        border-color: #adb5bd;
        background: #f8f9fa;
    }
    .role-option.selected {
        border-color: var(--role-color, #206bc4);
        background: var(--role-bg, rgba(32,107,196,0.04));
        box-shadow: 0 0 0 1px var(--role-color, #206bc4);
    }
    .role-option .role-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .role-option .role-info {
        flex: 1;
        min-width: 0;
    }
    .role-option .role-name {
        font-weight: 600;
        font-size: 0.9375rem;
        line-height: 1.2;
    }
    .role-option .role-desc {
        font-size: 0.75rem;
        color: #667382;
        line-height: 1.3;
    }
    .role-option .role-check {
        width: 20px;
        height: 20px;
        border: 2px solid #e6e7e9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .role-option.selected .role-check {
        border-color: var(--role-color, #206bc4);
        background: var(--role-color, #206bc4);
        color: #fff;
    }
    .login-form-panel {
        display: none;
    }
    .login-form-panel.active {
        display: block;
    }
    .section-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: #667382;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }
    .section-divider::before,
    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e6e7e9;
    }
    .bando-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .bando-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
    }
</style>

<div class="container py-4" style="max-width: 960px;">

    <?php if ($isAdminLogged): ?>
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="ti ti-info-circle me-2"></i>
            <div>
                Sei collegato come <strong>Amministratore</strong> (<?= Html::encode(Yii::$app->user->identity->username) ?>).
                <a href="<?= Url::to(['/site/index']) ?>" class="alert-link ms-1">Vai alla Dashboard</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($isDocenteLogged): ?>
    <div class="alert alert-success mb-4">
        <div class="d-flex align-items-center">
            <i class="ti ti-info-circle me-2"></i>
            <div>
                Sei collegato come <strong>Docente</strong>.
                <a href="<?= Url::to(['/docente-area/index']) ?>" class="alert-link ms-1">Vai all'Area Docenti</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- HERO -->
    <div class="text-center mb-4">
        <a href="." class="navbar-brand navbar-brand-autodark d-inline-block mb-2">
            <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-badge-small.png" height="56" alt="Albatros">
        </a>
        <h1 class="h2 mb-1">Albatros Academy</h1>
        <p class="text-secondary">Sistema di Gestione Docenti FSE/PNRR</p>
    </div>

    <!-- LOGIN BOX -->
    <div class="card mb-5" style="max-width: 680px; margin: 0 auto;">
        <div class="row g-0">
            <!-- Selettore ruolo (colonna sinistra) -->
            <div class="col-12 col-md-5 border-end">
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-muted small text-uppercase fw-bold">Accedi come</span>
                    </div>
                    <div class="role-selector">
                        <div class="role-option <?= $selectedType === 'admin' ? 'selected' : '' ?>"
                             style="--role-color: #206bc4; --role-bg: rgba(32,107,196,0.04);"
                             data-role="admin" onclick="selectRole('admin')">
                            <div class="role-icon bg-blue-lt text-blue">
                                <i class="ti ti-shield-lock"></i>
                            </div>
                            <div class="role-info">
                                <div class="role-name">Amministratore</div>
                                <div class="role-desc">Pannello di gestione</div>
                            </div>
                            <div class="role-check">
                                <i class="ti ti-check" style="font-size: 12px;"></i>
                            </div>
                        </div>
                        <div class="role-option <?= $selectedType === 'docente' ? 'selected' : '' ?>"
                             style="--role-color: #2fb344; --role-bg: rgba(47,179,68,0.04);"
                             data-role="docente" onclick="selectRole('docente')">
                            <div class="role-icon bg-green-lt text-green">
                                <i class="ti ti-school"></i>
                            </div>
                            <div class="role-info">
                                <div class="role-name">Docente</div>
                                <div class="role-desc">Candidature e ore</div>
                            </div>
                            <div class="role-check">
                                <i class="ti ti-check" style="font-size: 12px;"></i>
                            </div>
                        </div>
                        <div class="role-option"
                             style="--role-color: #ae3ec9; --role-bg: rgba(174,62,201,0.04);"
                             data-role="studente" onclick="selectRole('studente')">
                            <div class="role-icon bg-purple-lt text-purple">
                                <i class="ti ti-users"></i>
                            </div>
                            <div class="role-info">
                                <div class="role-name">Studente</div>
                                <div class="role-desc">Corsi e iscrizioni</div>
                            </div>
                            <div class="role-check">
                                <i class="ti ti-check" style="font-size: 12px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form login (colonna destra) -->
            <div class="col-12 col-md-7">
                <div class="card-body">
                    <!-- Form Admin -->
                    <div class="login-form-panel <?= $selectedType === 'admin' ? 'active' : '' ?>" id="panel-admin">
                        <?php $form = ActiveForm::begin([
                            'id' => 'login-form-admin',
                            'action' => Url::to(['/site/login']),
                            'fieldConfig' => [
                                'template' => "{input}\n{error}",
                                'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            ],
                        ]); ?>
                        <input type="hidden" name="login_type" value="admin">

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <?= $form->field($loginForm, 'username')->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Inserisci username',
                                'autocomplete' => 'username',
                            ]) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <?= $form->field($loginForm, 'password')->passwordInput([
                                'class' => 'form-control',
                                'placeholder' => 'Inserisci password',
                            ]) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <?= Html::activeCheckbox($loginForm, 'rememberMe', [
                                    'class' => 'form-check-input',
                                    'label' => false,
                                ]) ?>
                                <span class="form-check-label">Ricordami</span>
                            </label>
                        </div>

                        <div class="form-footer">
                            <?= Html::submitButton('<i class="ti ti-login me-1"></i> Accedi come Amministratore', [
                                'class' => 'btn btn-blue w-100',
                            ]) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>

                    <!-- Form Docente -->
                    <div class="login-form-panel <?= $selectedType === 'docente' ? 'active' : '' ?>" id="panel-docente">
                        <?php $formDocente = ActiveForm::begin([
                            'id' => 'login-form-docente',
                            'action' => Url::to(['/site/login']),
                            'fieldConfig' => [
                                'template' => "{input}\n{error}",
                                'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            ],
                        ]); ?>
                        <input type="hidden" name="login_type" value="docente">

                        <div class="mb-3">
                            <label class="form-label">Codice Fiscale</label>
                            <?= $formDocente->field($docenteLoginForm, 'codice_fiscale')->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Es: RSSMRA80A01F205X',
                                'maxlength' => 16,
                                'style' => 'text-transform: uppercase;',
                            ]) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Password
                                <?php if (YII_DEBUG): ?>
                                <span class="form-label-description text-secondary">(vuota per test)</span>
                                <?php endif; ?>
                            </label>
                            <?= $formDocente->field($docenteLoginForm, 'password')->passwordInput([
                                'class' => 'form-control',
                                'placeholder' => YII_DEBUG ? 'Lascia vuoto per test' : 'La tua password',
                            ]) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-check">
                                <?= Html::activeCheckbox($docenteLoginForm, 'rememberMe', [
                                    'class' => 'form-check-input',
                                    'label' => false,
                                ]) ?>
                                <span class="form-check-label">Ricordami</span>
                            </label>
                        </div>

                        <div class="form-footer">
                            <?= Html::submitButton('<i class="ti ti-login me-1"></i> Accedi come Docente', [
                                'class' => 'btn btn-green w-100',
                            ]) ?>
                        </div>
                        <?php ActiveForm::end(); ?>

                        <div class="text-center mt-3">
                            <a href="<?= Url::to(['/area-docenti/registrazione']) ?>" class="text-secondary">
                                <small>Non hai un account? <strong>Registrati</strong></small>
                            </a>
                            <span class="mx-1 text-secondary">|</span>
                            <a href="<?= Url::to(['/area-docenti/password-dimenticata']) ?>" class="text-secondary">
                                <small>Password dimenticata?</small>
                            </a>
                        </div>
                    </div>

                    <!-- Form Studente -->
                    <div class="login-form-panel <?= $selectedType === 'studente' ? 'active' : '' ?>" id="panel-studente">
                        <?php $formStudente = ActiveForm::begin([
                            'id' => 'login-form-studente',
                            'action' => Url::to(['/site/login']),
                            'fieldConfig' => [
                                'template' => "{input}\n{error}",
                                'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            ],
                        ]); ?>
                        <input type="hidden" name="login_type" value="studente">

                        <div class="mb-3">
                            <label class="form-label">Codice Fiscale</label>
                            <?= $formStudente->field($studenteLoginForm, 'codice_fiscale')->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Es: RSSMRA90A01F205X',
                                'maxlength' => 16,
                                'style' => 'text-transform: uppercase;',
                            ]) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Password
                                <?php if (YII_DEBUG): ?>
                                <span class="form-label-description text-secondary">(vuota per test)</span>
                                <?php endif; ?>
                            </label>
                            <?= $formStudente->field($studenteLoginForm, 'password')->passwordInput([
                                'class' => 'form-control',
                                'placeholder' => YII_DEBUG ? 'Lascia vuoto per test' : 'La tua password',
                            ]) ?>
                        </div>

                        <div class="form-footer">
                            <?= Html::submitButton('<i class="ti ti-login me-1"></i> Accedi come Studente', [
                                'class' => 'btn btn-purple w-100',
                            ]) ?>
                        </div>
                        <?php ActiveForm::end(); ?>

                        <div class="text-center mt-3">
                            <a href="<?= Url::to(['/area-studenti/registrazione']) ?>" class="text-secondary">
                                <small>Non hai un account? <strong>Registrati</strong></small>
                            </a>
                            <span class="mx-1 text-secondary">|</span>
                            <a href="<?= Url::to(['/studenti']) ?>" class="text-secondary">
                                <small>Consulta i corsi</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD DEBUG CREDENZIALI -->
    <?php if (Yii::$app->params['demoMode'] ?? false): ?>
    <div class="mb-4">
        <div class="card border-orange">
            <div class="card-status-top bg-orange"></div>
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-bug me-2 text-orange"></i>
                    Credenziali di Accesso Demo
                </h3>
                <div class="card-actions">
                    <span class="badge bg-orange text-orange-fg">DEMO</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if (Yii::$app->params['demoModeAdmin'] ?? false): ?>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded bg-blue-lt">
                            <h4 class="mb-2"><i class="ti ti-shield-lock me-1 text-blue"></i> Amministratore</h4>
                            <div class="mb-1"><strong>Username:</strong> <code>admin</code></div>
                            <div><strong>Password:</strong> <code>admin</code></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded bg-green-lt">
                            <h4 class="mb-2"><i class="ti ti-school me-1 text-green"></i> Docente</h4>
                            <div class="mb-1"><strong>Password:</strong> <code>(vuota)</code></div>
                            <?php if (!empty($demoDocenti)): ?>
                            <div class="mt-2">
                                <small class="text-secondary d-block mb-1">CF di esempio:</small>
                                <?php foreach ($demoDocenti as $dd): ?>
                                <div class="mb-1">
                                    <code class="user-select-all" style="font-size: 0.7rem;"><?= Html::encode($dd['codice_fiscale']) ?></code>
                                    <small class="text-muted"><?= Html::encode($dd['cognome'] . ' ' . $dd['nome']) ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-secondary mt-1"><small>Inserire un CF esistente, password vuota</small></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded bg-purple-lt">
                            <h4 class="mb-2"><i class="ti ti-users me-1 text-purple"></i> Studente</h4>
                            <div class="mb-1"><strong>Accesso:</strong> Pubblico (iscrizione ai corsi)</div>
                            <?php if (!empty($demoAllievi)): ?>
                            <div class="mt-2">
                                <small class="text-secondary d-block mb-1">Allievi registrati:</small>
                                <?php foreach ($demoAllievi as $da): ?>
                                <div class="mb-1">
                                    <code class="user-select-all" style="font-size: 0.7rem;"><?= Html::encode($da['codice_fiscale']) ?></code>
                                    <small class="text-muted"><?= Html::encode($da['cognome'] . ' ' . $da['nome']) ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-secondary mt-1"><small>Nessun allievo registrato. Iscriviti a un corso.</small></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- CORSI APERTI -->
    <?php if (!empty($bandiAperti)): ?>
    <div class="mb-4">
        <div class="section-divider mb-3">
            <span><i class="ti ti-speakerphone me-1"></i> Corsi Aperti</span>
        </div>
        <div class="row g-3">
            <?php foreach ($bandiAperti as $bando): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card bando-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-2">
                            <span class="badge bg-green-lt me-2">Aperto</span>
                            <?php if ($bando->data_scadenza): ?>
                            <small class="text-secondary ms-auto">
                                <i class="ti ti-calendar me-1"></i>Scade il <?= Yii::$app->formatter->asDate($bando->data_scadenza) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        <h4 class="mb-1"><?= Html::encode($bando->titolo) ?></h4>
                        <?php if ($bando->progetto): ?>
                        <p class="text-secondary mb-2">
                            <small><i class="ti ti-folder me-1"></i><?= Html::encode($bando->progetto->titolo) ?></small>
                        </p>
                        <?php endif; ?>
                        <div class="d-flex align-items-center justify-content-between">
                            <small class="text-secondary">
                                <i class="ti ti-book me-1"></i><?= $bando->getModuliCount() ?> moduli
                            </small>
                            <a href="<?= Url::to(['/studenti/bando', 'id' => $bando->id]) ?>" class="btn btn-sm btn-outline-green">
                                Dettagli <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="mb-4">
        <div class="section-divider mb-3">
            <span><i class="ti ti-speakerphone me-1"></i> Corsi Aperti</span>
        </div>
        <div class="card">
            <div class="card-body text-center py-4">
                <span class="avatar avatar-lg bg-default text-default-fg mb-2">
                    <i class="ti ti-clipboard-off"></i>
                </span>
                <p class="text-secondary mb-0">Nessun corso aperto al momento</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- LINK RAPIDI -->
    <div class="mb-4">
        <div class="section-divider mb-3">
            <span><i class="ti ti-link me-1"></i> Link Rapidi</span>
        </div>
        <div class="row g-2 text-center">
            <div class="col-6 col-md-3">
                <a href="<?= Url::to(['/studenti']) ?>" class="card card-sm bando-card text-decoration-none">
                    <div class="card-body py-3">
                        <i class="ti ti-clipboard-list text-green mb-1" style="font-size: 1.5rem;"></i>
                        <div class="small">Corsi Aperti</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= Url::to(['/area-docenti/registrazione']) ?>" class="card card-sm bando-card text-decoration-none">
                    <div class="card-body py-3">
                        <i class="ti ti-user-plus text-green mb-1" style="font-size: 1.5rem;"></i>
                        <div class="small">Registrati Docente</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= Url::to(['/studenti']) ?>" class="card card-sm bando-card text-decoration-none">
                    <div class="card-body py-3">
                        <i class="ti ti-school text-purple mb-1" style="font-size: 1.5rem;"></i>
                        <div class="small">Iscriviti a un Corso</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= Url::to(['/site/supporto']) ?>" class="card card-sm bando-card text-decoration-none">
                    <div class="card-body py-3">
                        <i class="ti ti-help text-azure mb-1" style="font-size: 1.5rem;"></i>
                        <div class="small">Assistenza</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="text-center text-secondary mt-4 mb-3">
        <small>&copy; <?= date('Y') ?> Fondazione ITS Albatros Academy</small>
    </div>
</div>

<script>
function selectRole(type) {
    // Deseleziona tutti
    document.querySelectorAll('.role-option').forEach(function(el) {
        el.classList.remove('selected');
    });
    // Seleziona il cliccato
    document.querySelector('.role-option[data-role="' + type + '"]').classList.add('selected');

    // Mostra il pannello corretto
    document.querySelectorAll('.login-form-panel').forEach(function(el) {
        el.classList.remove('active');
    });
    var panel = document.getElementById('panel-' + type);
    if (panel) {
        panel.classList.add('active');
    }

    // Focus sul primo input
    setTimeout(function() {
        if (panel) {
            var input = panel.querySelector('input[type="text"], input[type="password"]');
            if (input) input.focus();
        }
    }, 100);
}
</script>
