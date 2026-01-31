<?php

namespace app\components\widgets;

use Yii;
use yii\base\Widget;

/**
 * Widget Alert per visualizzare i flash messages con stile Tabler.
 *
 * Uso nel layout:
 * ```php
 * <?= Alert::widget() ?>
 * ```
 */
class Alert extends Widget
{
    /**
     * @var array mapping tra tipi di flash e classi CSS Tabler
     */
    public $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning',
    ];

    /**
     * @var array mapping tra tipi di flash e icone
     */
    public $alertIcons = [
        'error'   => 'ti ti-alert-circle',
        'danger'  => 'ti ti-alert-circle',
        'success' => 'ti ti-check',
        'info'    => 'ti ti-info-circle',
        'warning' => 'ti ti-alert-triangle',
    ];

    /**
     * @var bool se mostrare il pulsante di chiusura
     */
    public $closeButton = true;

    /**
     * @var int durata animazione fade out in ms (0 = no auto-hide)
     */
    public $autoHideDelay = 5000;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $html = '';

        foreach ($flashes as $type => $messages) {
            if (!isset($this->alertTypes[$type])) {
                continue;
            }

            $messages = (array) $messages;
            foreach ($messages as $message) {
                $html .= $this->renderAlert($type, $message);
            }

            $session->removeFlash($type);
        }

        if ($html && $this->autoHideDelay > 0) {
            $html .= $this->registerAutoHideScript();
        }

        return $html;
    }

    /**
     * Renderizza un singolo alert
     */
    protected function renderAlert($type, $message)
    {
        $class = $this->alertTypes[$type];
        $icon = $this->alertIcons[$type] ?? 'ti ti-info-circle';
        $closeBtn = '';

        if ($this->closeButton) {
            $closeBtn = '<a class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></a>';
        }

        return <<<HTML
<div class="alert {$class} alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div>
            <i class="{$icon} me-2"></i>
        </div>
        <div>
            {$message}
        </div>
    </div>
    {$closeBtn}
</div>
HTML;
    }

    /**
     * Registra script per auto-hide
     */
    protected function registerAutoHideScript()
    {
        $delay = $this->autoHideDelay;
        $js = <<<JS
setTimeout(function() {
    document.querySelectorAll('.alert.alert-dismissible').forEach(function(alert) {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, {$delay});
JS;
        return "<script>{$js}</script>";
    }
}
