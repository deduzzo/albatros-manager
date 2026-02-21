<?php

use yii\helpers\Html;
use app\models\Configurazione;

/* @var $this yii\web\View */
/* @var $gruppi array [gruppo => Configurazione[]] */

$this->title = 'Configurazione';
$this->params['breadcrumbs'][] = $this->title;

// Label nomi gruppo per visualizzazione
$gruppoLabels = [
    'fondazione' => 'Dati Fondazione',
    'contatti' => 'Contatti',
    'branding' => 'Branding e Aspetto',
    'email' => 'Email e Notifiche',
    'sistema' => 'Sistema',
    'altro' => 'Altro',
];

// Icone per gruppo
$gruppoIcons = [
    'fondazione' => 'ti ti-building',
    'contatti' => 'ti ti-address-book',
    'branding' => 'ti ti-palette',
    'email' => 'ti ti-mail',
    'sistema' => 'ti ti-settings',
    'altro' => 'ti ti-dots',
];
?>

<div class="configurazione-index">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h2 class="page-title">
                        <i class="ti ti-building me-2"></i><?= Html::encode($this->title) ?>
                    </h2>
                    <div class="text-muted mt-1">Gestisci i dati della fondazione e le impostazioni di sistema</div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?= Html::beginForm(['salva'], 'POST') ?>

            <?php foreach ($gruppi as $gruppo => $items): ?>
                <?php
                    $label = $gruppoLabels[$gruppo] ?? ucfirst($gruppo);
                    $icon = $gruppoIcons[$gruppo] ?? 'ti ti-settings';
                ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="<?= $icon ?> me-2"></i><?= Html::encode($label) ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($items as $config): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="config-<?= Html::encode($config->chiave) ?>">
                                        <?= Html::encode($config->etichetta ?: $config->chiave) ?>
                                    </label>
                                    <?php if ($config->descrizione): ?>
                                        <small class="form-hint d-block mb-1"><?= Html::encode($config->descrizione) ?></small>
                                    <?php endif; ?>

                                    <?php switch ($config->tipo):
                                        case Configurazione::TIPO_EMAIL: ?>
                                            <input type="email"
                                                   class="form-control"
                                                   id="config-<?= Html::encode($config->chiave) ?>"
                                                   name="Configurazione[<?= Html::encode($config->chiave) ?>]"
                                                   value="<?= Html::encode($config->valore) ?>"
                                                   placeholder="esempio@fondazione.it">
                                            <?php break;
                                        case Configurazione::TIPO_URL: ?>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ti ti-link"></i></span>
                                                <input type="url"
                                                       class="form-control"
                                                       id="config-<?= Html::encode($config->chiave) ?>"
                                                       name="Configurazione[<?= Html::encode($config->chiave) ?>]"
                                                       value="<?= Html::encode($config->valore) ?>"
                                                       placeholder="https://">
                                            </div>
                                            <?php break;
                                        case Configurazione::TIPO_PHONE: ?>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                                <input type="tel"
                                                       class="form-control"
                                                       id="config-<?= Html::encode($config->chiave) ?>"
                                                       name="Configurazione[<?= Html::encode($config->chiave) ?>]"
                                                       value="<?= Html::encode($config->valore) ?>"
                                                       placeholder="+39 000 0000000">
                                            </div>
                                            <?php break;
                                        case Configurazione::TIPO_IMAGE: ?>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ti ti-photo"></i></span>
                                                <input type="text"
                                                       class="form-control"
                                                       id="config-<?= Html::encode($config->chiave) ?>"
                                                       name="Configurazione[<?= Html::encode($config->chiave) ?>]"
                                                       value="<?= Html::encode($config->valore) ?>"
                                                       placeholder="URL immagine o percorso">
                                            </div>
                                            <?php if ($config->valore): ?>
                                                <div class="mt-2">
                                                    <img src="<?= Html::encode($config->valore) ?>" alt="Anteprima" class="img-thumbnail" style="max-height: 60px;">
                                                </div>
                                            <?php endif; ?>
                                            <?php break;
                                        default: ?>
                                            <input type="text"
                                                   class="form-control"
                                                   id="config-<?= Html::encode($config->chiave) ?>"
                                                   name="Configurazione[<?= Html::encode($config->chiave) ?>]"
                                                   value="<?= Html::encode($config->valore) ?>"
                                                   placeholder="<?= Html::encode($config->etichetta ?: '') ?>">
                                    <?php endswitch; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($gruppi)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-settings-off" style="font-size: 3rem;"></i>
                            </div>
                            <p class="empty-title">Nessuna configurazione trovata</p>
                            <p class="empty-subtitle text-muted">
                                Le impostazioni di sistema verranno create automaticamente al primo utilizzo.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-footer text-end">
                        <?= Html::submitButton('<i class="ti ti-check me-1"></i> Salva Impostazioni', ['class' => 'btn btn-success btn-lg']) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?= Html::endForm() ?>
        </div>
    </div>
</div>
