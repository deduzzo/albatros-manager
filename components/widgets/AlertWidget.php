<?php

namespace app\components\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use app\models\Assegnazione;
use app\models\Calendario;
use app\models\BorsaStudio;

/**
 * AlertWidget mostra alert operativi nella dashboard e nelle viste dei corsi.
 *
 * Uso base:
 * ```php
 * echo \app\components\widgets\AlertWidget::widget(['tipo' => 'tutti']);
 * echo \app\components\widgets\AlertWidget::widget(['tipo' => 'contratti', 'corso_id' => $id]);
 * echo \app\components\widgets\AlertWidget::widget(['tipo' => 'tutti', 'compact' => true]);
 * ```
 */
class AlertWidget extends Widget
{
    /** @var string Tipo di alert: 'tutti', 'contratti', 'presenze', 'borse' */
    public $tipo = 'tutti';

    /** @var int|null ID avviso/corso per filtrare gli alert */
    public $corso_id = null;

    /** @var bool Se true restituisce un badge numerico compatto invece dei box alert */
    public $compact = false;

    public function run()
    {
        $alerts = [];
        if (in_array($this->tipo, ['contratti', 'tutti'])) {
            $alerts['contratti'] = $this->getAlertContratti();
        }
        if (in_array($this->tipo, ['presenze', 'tutti'])) {
            $alerts['presenze'] = $this->getAlertPresenze();
        }
        if (in_array($this->tipo, ['borse', 'tutti'])) {
            $alerts['borse'] = $this->getAlertBorse();
        }
        return $this->compact ? $this->renderCompact($alerts) : $this->renderFull($alerts);
    }

    /**
     * Conta assegnazioni senza contratto associato.
     * @return int
     */
    protected function getAlertContratti()
    {
        $query = Assegnazione::find()
            ->alias('a')
            ->innerJoin('{{%modulo}} m', 'm.id = a.modulo_id AND m.deleted_at IS NULL')
            ->leftJoin('{{%contratto}} co', 'co.assegnazione_id = a.id AND co.deleted_at IS NULL')
            ->where(['a.deleted_at' => null, 'co.id' => null]);
        if ($this->corso_id) {
            $query->andWhere(['m.avviso_id' => $this->corso_id]);
        }
        return (int) $query->count();
    }

    /**
     * Conta lezioni passate senza presenza acquisita (docente_presente IS NULL).
     * @return int
     */
    protected function getAlertPresenze()
    {
        $query = Calendario::find()
            ->alias('c')
            ->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')
            ->where(['c.deleted_at' => null])
            ->andWhere(['<', 'c.data', date('Y-m-d')])
            ->andWhere(['c.docente_presente' => null]);
        if ($this->corso_id) {
            $query->andWhere(['m.avviso_id' => $this->corso_id]);
        }
        return (int) $query->count();
    }

    /**
     * Conta borse studio con status erogazione_bloccata.
     * @return int
     */
    protected function getAlertBorse()
    {
        $query = BorsaStudio::find()
            ->where(['status' => BorsaStudio::STATUS_EROGAZIONE_BLOCCATA, 'deleted_at' => null]);
        if ($this->corso_id) {
            $avviso = \app\models\Avviso::findOne($this->corso_id);
            if ($avviso === null) {
                return 0;
            }
            $query->andWhere(['progetto_id' => $avviso->progetto_id]);
        }
        return (int) $query->count();
    }

    /**
     * Render compatto: badge con totale alert.
     * @param array $alerts
     * @return string
     */
    protected function renderCompact($alerts)
    {
        $total = array_sum($alerts);
        if ($total === 0) return '';
        return Html::tag('span', (string) $total, ['class' => 'badge bg-red-lt']);
    }

    /**
     * Render completo: box alert colorati per tipo.
     * @param array $alerts
     * @return string
     */
    protected function renderFull($alerts)
    {
        $html = '';
        $config = [
            'contratti' => [
                'icon' => 'ti-file-alert',
                'color' => 'orange',
                'title' => 'Docenti da contrattualizzare',
                'link' => ['/contratto/index'],
            ],
            'presenze' => [
                'icon' => 'ti-clock-exclamation',
                'color' => 'red',
                'title' => 'Presenze non acquisite',
                'link' => ['/calendario/index'],
            ],
            'borse' => [
                'icon' => 'ti-alert-triangle',
                'color' => 'yellow',
                'title' => 'Borse con frequenza insufficiente',
                'link' => ['/borsa-studio/index'],
            ],
        ];
        foreach ($alerts as $tipo => $count) {
            if ($count === 0) continue;
            $c = $config[$tipo];
            $html .= '<div class="alert alert-' . $c['color'] . ' mb-3">'
                . '<div class="d-flex align-items-center">'
                . '<i class="ti ' . $c['icon'] . ' me-2" style="font-size:1.5rem;"></i>'
                . '<div class="flex-fill">'
                . '<h4 class="alert-title mb-0">' . Html::encode($c['title']) . ' <span class="badge bg-' . $c['color'] . ' text-' . $c['color'] . '-fg">' . $count . '</span></h4>'
                . '</div>'
                . Html::a('<i class="ti ti-arrow-right"></i>', $c['link'], ['class' => 'btn btn-sm btn-' . $c['color'] . ' ms-2'])
                . '</div>'
                . '</div>';
        }
        return $html;
    }
}
