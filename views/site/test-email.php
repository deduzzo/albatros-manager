<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $risultato string|null */
/* @var $errore string|null */

$this->title = 'Test Invio Email';
$this->params['breadcrumbs'][] = ['label' => 'Configurazione', 'url' => ['/configurazione/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-test-email">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-mail-check me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <?php if ($risultato): ?>
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div><i class="ti ti-check me-2"></i></div>
                            <div><?= $risultato ?></div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    <?php endif; ?>

                    <?php if ($errore): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div><i class="ti ti-alert-circle me-2"></i></div>
                            <div><?= Html::encode($errore) ?></div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Invia Email di Test</h3>
                        </div>
                        <div class="card-body">
                            <?= Html::beginForm(['site/test-email'], 'post') ?>

                            <div class="mb-3">
                                <label class="form-label required">Destinatario</label>
                                <?= Html::textInput('destinatario', Yii::$app->request->post('destinatario', ''), [
                                    'class' => 'form-control',
                                    'placeholder' => 'email@esempio.it',
                                    'type' => 'email',
                                    'required' => true,
                                ]) ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Oggetto</label>
                                <?= Html::textInput('oggetto', Yii::$app->request->post('oggetto', 'Test Email - Albatros Manager'), [
                                    'class' => 'form-control',
                                ]) ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Template</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-selectgroup-item">
                                            <input type="radio" name="template" value="generico" class="form-selectgroup-input" checked>
                                            <span class="form-selectgroup-label">
                                                <i class="ti ti-mail me-2"></i>Generico
                                                <span class="d-block text-muted small">Test base SMTP</span>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-selectgroup-item">
                                            <input type="radio" name="template" value="password-reset" class="form-selectgroup-input">
                                            <span class="form-selectgroup-label">
                                                <i class="ti ti-key me-2"></i>Reset Password
                                                <span class="d-block text-muted small">Template reset</span>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-selectgroup-item">
                                            <input type="radio" name="template" value="notifica" class="form-selectgroup-input">
                                            <span class="form-selectgroup-label">
                                                <i class="ti ti-bell me-2"></i>Notifica
                                                <span class="d-block text-muted small">Template notifica</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <?= Html::submitButton('<i class="ti ti-send me-1"></i> Invia Email di Test', ['class' => 'btn btn-primary']) ?>
                                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Configurazione', ['/configurazione/index'], ['class' => 'btn btn-secondary']) ?>
                            </div>

                            <?= Html::endForm() ?>
                        </div>
                    </div>

                    <!-- Info configurazione -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-settings me-2"></i>Configurazione SMTP Attuale</h3>
                        </div>
                        <div class="card-body">
                            <?php
                            $transport = Yii::$app->mailer->transport ?? null;
                            $dsn = $transport ? (string) $transport->getDsn() : 'Non configurato';
                            // Maschera la password nel DSN
                            $dsn = preg_replace('/:([^@]+)@/', ':****@', $dsn);
                            $from = Yii::$app->mailer->messageConfig['from'] ?? 'Non configurato';
                            if (is_array($from)) {
                                $fromDisplay = key($from) . ' (' . current($from) . ')';
                            } else {
                                $fromDisplay = $from;
                            }
                            ?>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Mittente</div>
                                    <div class="datagrid-content"><?= Html::encode($fromDisplay) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">File Transport</div>
                                    <div class="datagrid-content">
                                        <?php if (Yii::$app->mailer->useFileTransport): ?>
                                            <span class="badge bg-orange text-orange-fg">Attivo (email salvate su file)</span>
                                        <?php else: ?>
                                            <span class="badge bg-green text-green-fg">Disattivo (invio reale)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Config</div>
                                    <div class="datagrid-content"><code>config/mail.php</code></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
