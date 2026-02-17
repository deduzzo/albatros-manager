<?php

namespace app\components\widgets;

use Yii;
use yii\grid\GridView;
use yii\grid\DataColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;

/**
 * AppGridView estende GridView con stili Tabler predefiniti.
 *
 * Caratteristiche:
 * - Sorting abilitato su tutte le colonne
 * - Filtri attivi mostrati come tag rimuovibili
 * - Pulsante "Elimina tutti i filtri"
 * - Stile Tabler integrato
 *
 * Uso:
 * ```php
 * <?= AppGridView::widget([
 *     'dataProvider' => $dataProvider,
 *     'filterModel' => $searchModel,
 *     'columns' => [
 *         'id',
 *         'nome',
 *         'email:email',
 *         ['class' => 'yii\grid\ActionColumn'],
 *     ],
 * ]) ?>
 * ```
 */
class AppGridView extends GridView
{
    /**
     * @var string template layout
     */
    public $layout = "{filters}\n{items}\n{summary}\n{pager}";

    /**
     * @var array opzioni container
     */
    public $options = ['class' => 'grid-view'];

    /**
     * @var array opzioni tabella
     */
    public $tableOptions = ['class' => 'table card-table table-vcenter table-hover'];

    /**
     * @var string classe CSS per intestazione
     */
    public $headerRowOptions = [];

    /**
     * @var string template summary
     */
    public $summaryOptions = ['class' => 'text-muted small py-2 px-3'];

    /**
     * @var bool mostra toolbar con contatore e paginazione
     */
    public $showToolbar = true;

    /**
     * @var bool abilita responsive wrapper
     */
    public $responsive = true;

    /**
     * @var bool mostra checkbox per selezione multipla
     */
    public $showCheckboxColumn = false;

    /**
     * @var string testo quando non ci sono dati
     */
    public $emptyText = 'Nessun elemento trovato.';

    /**
     * @var array opzioni empty text
     */
    public $emptyTextOptions = ['class' => 'text-muted text-center py-5'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Configura pager Tabler
        $this->pager = array_merge([
            'class' => 'yii\widgets\LinkPager',
            'options' => ['class' => 'pagination m-0 ms-auto'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link'],
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
            'firstPageLabel' => '<i class="ti ti-chevrons-left"></i>',
            'lastPageLabel' => '<i class="ti ti-chevrons-right"></i>',
            'prevPageLabel' => '<i class="ti ti-chevron-left"></i>',
            'nextPageLabel' => '<i class="ti ti-chevron-right"></i>',
        ], $this->pager ?? []);

        // Configura summary
        $this->summary = 'Elementi {begin}-{end} di {totalCount}';

        // Abilita sorting su tutte le colonne DataColumn
        $this->configureSorting();

        // Aggiungi ActionColumn con stile Tabler se non presente
        $this->configureActionColumn();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = parent::run();

        if ($this->responsive) {
            $content = Html::tag('div', $content, ['class' => 'table-responsive']);
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function renderSection($name)
    {
        if ($name === '{filters}') {
            return $this->renderActiveFilters();
        }
        return parent::renderSection($name);
    }

    /**
     * Renderizza i filtri attivi come tag rimuovibili
     */
    protected function renderActiveFilters()
    {
        if ($this->filterModel === null) {
            return '';
        }

        $filters = [];
        $params = Yii::$app->request->queryParams;
        $formName = $this->filterModel->formName();
        $route = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

        // Ottieni i filtri dal modello
        if (isset($params[$formName])) {
            foreach ($params[$formName] as $attribute => $value) {
                if ($value !== '' && $value !== null) {
                    $label = $this->filterModel->getAttributeLabel($attribute);

                    // Crea URL senza questo filtro
                    $removeParams = $params;
                    unset($removeParams[$formName][$attribute]);

                    // Pulisci parametri vuoti
                    if (isset($removeParams[$formName])) {
                        $removeParams[$formName] = array_filter($removeParams[$formName], function($v) {
                            return $v !== '' && $v !== null;
                        });
                        if (empty($removeParams[$formName])) {
                            unset($removeParams[$formName]);
                        }
                    }

                    // Costruisci URL
                    $urlParams = [$route];
                    if (!empty($removeParams[$formName])) {
                        $urlParams[$formName] = $removeParams[$formName];
                    }
                    $removeUrl = Url::to($urlParams);

                    $filters[] = Html::tag('span',
                        Html::encode($label . ': ' . $value) . ' ' .
                        Html::a('<i class="ti ti-x"></i>', $removeUrl, [
                            'class' => 'ms-1 text-reset',
                            'title' => 'Rimuovi filtro',
                        ]),
                        ['class' => 'badge bg-blue-lt me-1 mb-1 d-inline-flex align-items-center']
                    );
                }
            }
        }

        if (empty($filters)) {
            return '';
        }

        // Pulsante elimina tutti i filtri
        $clearUrl = Url::to([$route]);
        $clearButton = Html::a(
            '<i class="ti ti-filter-off me-1"></i> Elimina tutti i filtri',
            $clearUrl,
            ['class' => 'btn btn-sm btn-outline-secondary']
        );

        return Html::tag('div',
            Html::tag('div', implode('', $filters), ['class' => 'd-inline']) .
            Html::tag('div', $clearButton, ['class' => 'd-inline ms-2']),
            ['class' => 'active-filters px-3 py-2 bg-light border-bottom d-flex align-items-center flex-wrap']
        );
    }

    /**
     * Configura sorting su tutte le colonne
     */
    protected function configureSorting()
    {
        foreach ($this->columns as $i => $column) {
            // Se la colonna è una stringa (es: 'nome'), la converti in array
            if (is_string($column)) {
                $this->columns[$i] = [
                    'attribute' => preg_replace('/:.+$/', '', $column),
                    'enableSorting' => true,
                ];
                // Mantieni eventuale formato
                if (strpos($column, ':') !== false) {
                    $parts = explode(':', $column);
                    $this->columns[$i]['format'] = $parts[1] ?? 'text';
                    if (isset($parts[2])) {
                        $this->columns[$i]['label'] = $parts[2];
                    }
                }
            }
            // Se è un array e non ha enableSorting specificato
            elseif (is_array($column) && !isset($column['class']) && !isset($column['enableSorting'])) {
                $this->columns[$i]['enableSorting'] = true;
            }
        }
    }

    /**
     * Configura ActionColumn con stile Tabler
     */
    protected function configureActionColumn()
    {
        foreach ($this->columns as $i => $column) {
            if ($column === ['class' => ActionColumn::class] ||
                $column === ['class' => 'yii\grid\ActionColumn'] ||
                (is_array($column) && isset($column['class']) &&
                 ($column['class'] === ActionColumn::class || $column['class'] === 'yii\\grid\\ActionColumn'))) {

                $this->columns[$i] = array_merge([
                    'class' => ActionColumn::class,
                    'header' => 'Azioni',
                    'headerOptions' => ['class' => 'text-end', 'style' => 'width: 130px;'],
                    'contentOptions' => ['class' => 'text-end'],
                    'template' => '{view} {update} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a(
                                '<i class="ti ti-eye"></i>',
                                $url,
                                [
                                    'class' => 'btn btn-sm btn-info',
                                    'title' => 'Visualizza',
                                    'data-bs-toggle' => 'tooltip',
                                ]
                            );
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a(
                                '<i class="ti ti-edit"></i>',
                                $url,
                                [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'Modifica',
                                    'data-bs-toggle' => 'tooltip',
                                ]
                            );
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a(
                                '<i class="ti ti-trash"></i>',
                                $url,
                                [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Elimina',
                                    'data-bs-toggle' => 'tooltip',
                                    'data-confirm' => 'Sei sicuro di voler eliminare questo elemento?',
                                    'data-method' => 'post',
                                ]
                            );
                        },
                    ],
                ], is_array($column) ? $column : []);
            }
        }
    }

    /**
     * Renderizza wrapper card Tabler
     * @param string $title
     * @param string $content
     * @param array $actions
     * @return string
     */
    public static function card($title, $content, $actions = [])
    {
        $actionsHtml = '';
        if (!empty($actions)) {
            $actionsHtml = '<div class="card-actions">' . implode(' ', $actions) . '</div>';
        }

        return <<<HTML
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{$title}</h3>
        {$actionsHtml}
    </div>
    <div class="card-body border-bottom py-3">
        {$content}
    </div>
</div>
HTML;
    }

    /**
     * Genera pulsante "Nuovo" standard
     * @param string $url
     * @param string $label
     * @return string
     */
    public static function createButton($url, $label = 'Nuovo')
    {
        return Html::a(
            '<i class="ti ti-plus me-1"></i> ' . $label,
            $url,
            ['class' => 'btn btn-success']
        );
    }

    /**
     * Genera badge status
     * @param string $status
     * @param array $statusMap
     * @return string
     */
    public static function statusBadge($status, $statusMap = [])
    {
        $defaultMap = [
            'attivo' => ['class' => 'bg-green text-green-fg', 'label' => 'Attivo'],
            'bozza' => ['class' => 'bg-orange text-orange-fg', 'label' => 'Bozza'],
            'inattivo' => ['class' => 'bg-default text-default-fg', 'label' => 'Inattivo'],
            'concluso' => ['class' => 'bg-azure text-azure-fg', 'label' => 'Concluso'],
            'annullato' => ['class' => 'bg-red text-red-fg', 'label' => 'Annullato'],
            'pubblicato' => ['class' => 'bg-green text-green-fg', 'label' => 'Pubblicato'],
            'chiuso' => ['class' => 'bg-default text-default-fg', 'label' => 'Chiuso'],
            'sospeso' => ['class' => 'bg-orange text-orange-fg', 'label' => 'Sospeso'],
        ];

        $map = array_merge($defaultMap, $statusMap);
        $config = $map[$status] ?? ['class' => 'bg-default text-default-fg', 'label' => $status];

        return Html::tag('span', $config['label'], ['class' => 'badge ' . $config['class']]);
    }

    /**
     * Formatta data in italiano
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function formatDate($date, $format = 'd/m/Y')
    {
        if (empty($date)) {
            return '-';
        }
        return Yii::$app->formatter->asDate($date, 'php:' . $format);
    }

    /**
     * Formatta valuta
     * @param float $value
     * @return string
     */
    public static function formatCurrency($value)
    {
        if ($value === null) {
            return '-';
        }
        return Yii::$app->formatter->asCurrency($value, 'EUR');
    }
}
