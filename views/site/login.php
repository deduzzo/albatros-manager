<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $loginForm */
/** @var app\models\forms\DocenteLoginForm $docenteLoginForm */
/** @var app\models\Avviso[] $bandiAperti */
/** @var app\models\Progetto[] $progettiAttivi */
/** @var string|null $loginError */
/** @var string|null $docenteError */
/** @var string|null $activeCard */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Accedi';
$this->context->layout = 'login';

// Determina se un utente e' gia autenticato (admin o docente)
$isAdminLogged = !Yii::$app->user->isGuest;
$isDocenteLogged = !Yii::$app->docenteUser->isGuest;
?>

<style>
    .login-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border-left-width: 3px !important;
        border-left-style: solid !important;
    }
    .login-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,.12);
    }
    .login-card.active {
        box-shadow: 0 4px 16px rgba(0,0,0,.12);
    }
    .login-card .card-body {
        transition: padding 0.3s ease;
    }
    .login-card-form {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, opacity 0.3s ease, padding 0.3s ease;
        padding: 0 1.25rem;
    }
    .login-card.active .login-card-form {
        max-height: 500px;
        opacity: 1;
        padding: 0 1.25rem 1.25rem;
    }
    .login-card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .login-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 1.5rem;
    }
    .login-card-desc {
        max-height: 40px;
        opacity: 1;
        transition: max-height 0.3s ease, opacity 0.2s ease;
        overflow: hidden;
    }
    .login-card.active .login-card-desc {
        max-height: 0;
        opacity: 0;
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

    <!-- LOGIN CARDS -->
    <div class="row g-3 mb-5">

        <!-- Card Amministratore -->
        <div class="col-12 col-lg-4">
            <div class="card login-card border-blue<?= $activeCard === 'admin' ? ' active' : '' ?>" id="card-admin" onclick="activateCard('admin')">
                <div class="card-body">
                    <div class="login-card-header">
                        <div class="login-card-icon bg-blue-lt text-blue">
                            <i class="ti ti-shield-lock"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 h4">Amministratore</h3>
                        </div>
                    </div>
                    <div class="login-card-desc mt-2">
                        <small class="text-secondary">Accesso al pannello di gestione completo</small>
                    </div>
                </div>
                <div class="login-card-form" id="form-admin">
                    <hr class="mt-0 mb-3">
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
                            'onclick' => 'event.stopPropagation()',
                        ]) ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <?= $form->field($loginForm, 'password')->passwordInput([
                            'class' => 'form-control',
                            'placeholder' => 'Inserisci password',
                            'onclick' => 'event.stopPropagation()',
                        ]) ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <?= Html::activeCheckbox($loginForm, 'rememberMe', [
                                'class' => 'form-check-input',
                                'label' => false,
                                'onclick' => 'event.stopPropagation()',
                            ]) ?>
                            <span class="form-check-label">Ricordami</span>
                        </label>
                    </div>

                    <div class="form-footer">
                        <?= Html::submitButton('<i class="ti ti-login me-1"></i> Accedi', [
                            'class' => 'btn btn-blue w-100',
                            'onclick' => 'event.stopPropagation()',
                        ]) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <!-- Card Docente -->
        <div class="col-12 col-lg-4">
            <div class="card login-card border-green<?= $activeCard === 'docente' ? ' active' : '' ?>" id="card-docente" onclick="activateCard('docente')">
                <div class="card-body">
                    <div class="login-card-header">
                        <div class="login-card-icon bg-green-lt text-green">
                            <i class="ti ti-school"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 h4">Docente</h3>
                        </div>
                    </div>
                    <div class="login-card-desc mt-2">
                        <small class="text-secondary">Gestisci candidature, ore e contratti</small>
                    </div>
                </div>
                <div class="login-card-form" id="form-docente">
                    <hr class="mt-0 mb-3">
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
                            'onclick' => 'event.stopPropagation()',
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
                            'onclick' => 'event.stopPropagation()',
                        ]) ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <?= Html::activeCheckbox($docenteLoginForm, 'rememberMe', [
                                'class' => 'form-check-input',
                                'label' => false,
                                'onclick' => 'event.stopPropagation()',
                            ]) ?>
                            <span class="form-check-label">Ricordami</span>
                        </label>
                    </div>

                    <div class="form-footer">
                        <?= Html::submitButton('<i class="ti ti-login me-1"></i> Accedi', [
                            'class' => 'btn btn-green w-100',
                            'onclick' => 'event.stopPropagation()',
                        ]) ?>
                    </div>
                    <?php ActiveForm::end(); ?>

                    <div class="text-center mt-3" onclick="event.stopPropagation()">
                        <a href="<?= Url::to(['/area-docenti/registrazione']) ?>" class="text-secondary">
                            <small>Non hai un account? <strong>Registrati</strong></small>
                        </a>
                        <span class="mx-1 text-secondary">|</span>
                        <a href="<?= Url::to(['/area-docenti/password-dimenticata']) ?>" class="text-secondary">
                            <small>Password dimenticata?</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Studente -->
        <div class="col-12 col-lg-4">
            <div class="card login-card border-purple<?= $activeCard === 'studente' ? ' active' : '' ?>" id="card-studente" onclick="activateCard('studente')">
                <div class="card-body">
                    <div class="login-card-header">
                        <div class="login-card-icon bg-purple-lt text-purple">
                            <i class="ti ti-users"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 h4">Studente</h3>
                        </div>
                    </div>
                    <div class="login-card-desc mt-2">
                        <small class="text-secondary">Consulta corsi, lezioni e materiali</small>
                    </div>
                </div>
                <div class="login-card-form" id="form-studente">
                    <hr class="mt-0 mb-3">
                    <div class="text-center py-3" onclick="event.stopPropagation()">
                        <div class="mb-3">
                            <span class="avatar avatar-lg bg-purple-lt text-purple">
                                <i class="ti ti-hourglass"></i>
                            </span>
                        </div>
                        <h4 class="text-secondary mb-1">Area in fase di attivazione</h4>
                        <p class="text-secondary mb-3">
                            <small>L'area studenti sara disponibile a breve. Nel frattempo puoi esplorare la versione demo.</small>
                        </p>
                        <a href="<?= Url::to(['/demo-studenti']) ?>" class="btn btn-purple">
                            <i class="ti ti-external-link me-1"></i> Esplora la Demo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BANDI APERTI -->
    <?php if (!empty($bandiAperti)): ?>
    <div class="mb-4">
        <div class="section-divider mb-3">
            <span><i class="ti ti-speakerphone me-1"></i> Bandi Aperti per Docenti</span>
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
                            <a href="<?= Url::to(['/area-docenti/bandi/' . $bando->id]) ?>" class="btn btn-sm btn-outline-green">
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
            <span><i class="ti ti-speakerphone me-1"></i> Bandi Aperti per Docenti</span>
        </div>
        <div class="card">
            <div class="card-body text-center py-4">
                <span class="avatar avatar-lg bg-default text-default-fg mb-2">
                    <i class="ti ti-clipboard-off"></i>
                </span>
                <p class="text-secondary mb-0">Nessun bando aperto al momento</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- CORSI DISPONIBILI -->
    <?php if (!empty($progettiAttivi)): ?>
    <div class="mb-4">
        <div class="section-divider mb-3">
            <span><i class="ti ti-school me-1"></i> Corsi Disponibili</span>
        </div>
        <div class="row g-3">
            <?php foreach ($progettiAttivi as $progetto): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card bando-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-2">
                            <span class="badge bg-azure-lt me-2">Attivo</span>
                            <?php if ($progetto->fonteFinanziamento): ?>
                            <small class="text-secondary ms-auto">
                                <?= Html::encode($progetto->fonteFinanziamento->descrizione) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        <h4 class="mb-1"><?= Html::encode($progetto->titolo) ?></h4>
                        <?php if ($progetto->codice_progetto): ?>
                        <p class="text-secondary mb-2">
                            <small><i class="ti ti-hash me-1"></i><?= Html::encode($progetto->codice_progetto) ?></small>
                        </p>
                        <?php endif; ?>
                        <div class="d-flex align-items-center justify-content-between">
                            <?php if ($progetto->numero_studenti): ?>
                            <small class="text-secondary">
                                <i class="ti ti-users me-1"></i><?= $progetto->numero_studenti ?> studenti
                            </small>
                            <?php else: ?>
                            <span></span>
                            <?php endif; ?>
                            <a href="<?= Url::to(['/demo-studenti', 'id' => $progetto->id]) ?>" class="btn btn-sm btn-outline-azure">
                                Scopri <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- FOOTER -->
    <div class="text-center text-secondary mt-4 mb-3">
        <small>&copy; <?= date('Y') ?> Fondazione ITS Albatros Academy</small>
    </div>
</div>

<script>
function activateCard(type) {
    var cards = document.querySelectorAll('.login-card');
    cards.forEach(function(card) {
        var cardType = card.id.replace('card-', '');
        if (cardType === type) {
            card.classList.toggle('active');
        } else {
            card.classList.remove('active');
        }
    });

    // Focus sul primo input del form attivo
    setTimeout(function() {
        var activeCard = document.querySelector('.login-card.active');
        if (activeCard) {
            var input = activeCard.querySelector('input[type="text"], input[type="password"]');
            if (input) input.focus();
        }
    }, 350);
}
</script>
