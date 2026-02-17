<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AuditLogSearch - Model di ricerca per AuditLog
 */
class AuditLogSearch extends AuditLog
{
    /**
     * @var string filtro data da
     */
    public $date_from;

    /**
     * @var string filtro data a
     */
    public $date_to;

    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['table_name', 'record_id', 'action', 'username', 'ip_address', 'request_url', 'request_method', 'note', 'session_id', 'created_at', 'date_from', 'date_to'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'date_from' => 'Data Da',
            'date_to' => 'Data A',
        ]);
    }

    public function search($params)
    {
        $query = AuditLog::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'table_name',
                    'action',
                    'username',
                    'created_at',
                    'ip_address',
                    'record_id',
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'table_name' => $this->table_name,
            'action' => $this->action,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'record_id', $this->record_id])
            ->andFilterWhere(['like', 'ip_address', $this->ip_address])
            ->andFilterWhere(['like', 'request_url', $this->request_url])
            ->andFilterWhere(['like', 'note', $this->note]);

        // Filtro range date su created_at
        if (!empty($this->date_from)) {
            $query->andWhere(['>=', 'created_at', $this->date_from . ' 00:00:00']);
        }
        if (!empty($this->date_to)) {
            $query->andWhere(['<=', 'created_at', $this->date_to . ' 23:59:59']);
        }

        return $dataProvider;
    }

    /**
     * Ritorna la lista distinta dei nomi tabella presenti in audit_log
     * @return array [table_name => table_name]
     */
    public static function getTableNameList()
    {
        return AuditLog::find()
            ->select('table_name')
            ->distinct()
            ->orderBy('table_name')
            ->column();
    }

    /**
     * Ritorna la lista delle azioni disponibili
     * @return array [action => label]
     */
    public static function getActionList()
    {
        return [
            AuditLog::ACTION_INSERT => 'Creato',
            AuditLog::ACTION_UPDATE => 'Modificato',
            AuditLog::ACTION_DELETE => 'Eliminato',
            AuditLog::ACTION_SOFT_DELETE => 'Archiviato',
            AuditLog::ACTION_RESTORE => 'Ripristinato',
        ];
    }
}
