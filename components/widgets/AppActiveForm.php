<?php

namespace app\components\widgets;

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * AppActiveForm estende ActiveForm con stili Tabler predefiniti.
 *
 * Uso:
 * ```php
 * <?php $form = AppActiveForm::begin(); ?>
 *     <?= $form->field($model, 'nome') ?>
 *     <?= $form->field($model, 'email')->textInput(['type' => 'email']) ?>
 * <?php AppActiveForm::end(); ?>
 * ```
 */
class AppActiveForm extends ActiveForm
{
    /**
     * @var string layout del form: 'default', 'horizontal', 'inline'
     */
    public $layout = 'default';

    /**
     * @var bool abilita validazione Ajax
     */
    public $enableAjaxValidation = false;

    /**
     * @var bool abilita validazione client-side
     */
    public $enableClientValidation = true;

    /**
     * @var array opzioni per campi obbligatori
     */
    public $requiredTemplate = '{label} <span class="text-danger">*</span>';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->options['class'])) {
            $this->options['class'] = '';
        }

        // Usa is-invalid (Bootstrap 5) invece del default has-error (Bootstrap 3)
        // In modalità VALIDATION_STATE_ON_INPUT, questa classe viene aggiunta all'input
        // quando il model ha errori sul campo, rendendo visibile il blocco invalid-feedback
        $this->errorCssClass = 'is-invalid';

        // Configura il layout
        switch ($this->layout) {
            case 'horizontal':
                $this->fieldConfig = array_merge([
                    'template' => "{label}\n<div class=\"col-sm-9\">{input}\n{hint}\n{error}</div>",
                    'labelOptions' => ['class' => 'col-sm-3 col-form-label'],
                    'inputOptions' => ['class' => 'form-control'],
                    'errorOptions' => ['class' => 'invalid-feedback'],
                    'hintOptions' => ['class' => 'form-text text-muted'],
                ], $this->fieldConfig);
                $this->options['class'] .= ' row';
                break;

            case 'inline':
                $this->fieldConfig = array_merge([
                    'template' => "{label}\n{input}",
                    'labelOptions' => ['class' => 'visually-hidden'],
                    'inputOptions' => ['class' => 'form-control'],
                ], $this->fieldConfig);
                $this->options['class'] .= ' row row-cols-auto g-3 align-items-center';
                break;

            default:
                $this->fieldConfig = array_merge([
                    'template' => "{label}\n{input}\n{hint}\n{error}",
                    'labelOptions' => ['class' => 'form-label'],
                    'inputOptions' => ['class' => 'form-control'],
                    'errorOptions' => ['class' => 'invalid-feedback'],
                    'hintOptions' => ['class' => 'form-text text-muted'],
                ], $this->fieldConfig);
        }

        // Opzioni di validazione
        $this->validationStateOn = self::VALIDATION_STATE_ON_INPUT;

        parent::init();
    }

    /**
     * Renderizza pulsanti form standard
     * @param string $submitLabel
     * @param string $cancelUrl
     * @return string
     */
    public static function buttons($submitLabel = 'Salva', $cancelUrl = null)
    {
        $cancelBtn = '';
        if ($cancelUrl !== null) {
            $cancelBtn = Html::a(
                '<i class="ti ti-x me-1"></i> Annulla',
                $cancelUrl,
                ['class' => 'btn btn-secondary']
            );
        }

        return <<<HTML
<div class="card-footer text-end">
    {$cancelBtn}
    <button type="submit" class="btn btn-success ms-2">
        <i class="ti ti-check me-1"></i> {$submitLabel}
    </button>
</div>
HTML;
    }

    /**
     * Renderizza un campo select con stile Tabler
     */
    public function fieldSelect($model, $attribute, $items, $options = [])
    {
        $options = array_merge([
            'class' => 'form-select',
            'prompt' => '-- Seleziona --',
        ], $options);

        return $this->field($model, $attribute)->dropDownList($items, $options);
    }

    /**
     * Renderizza un campo date con stile Tabler
     */
    public function fieldDate($model, $attribute, $options = [])
    {
        $options = array_merge([
            'class' => 'form-control',
            'type' => 'date',
        ], $options);

        return $this->field($model, $attribute)->textInput($options);
    }

    /**
     * Renderizza un campo textarea con stile Tabler
     */
    public function fieldTextarea($model, $attribute, $options = [])
    {
        $options = array_merge([
            'class' => 'form-control',
            'rows' => 4,
        ], $options);

        return $this->field($model, $attribute)->textarea($options);
    }

    /**
     * Renderizza un campo switch (checkbox stilizzato)
     */
    public function fieldSwitch($model, $attribute, $options = [])
    {
        return $this->field($model, $attribute, [
            'template' => "<div class=\"form-check form-switch\">{input} {label}</div>\n{hint}\n{error}",
            'labelOptions' => ['class' => 'form-check-label'],
        ])->checkbox(['class' => 'form-check-input'], false);
    }
}
