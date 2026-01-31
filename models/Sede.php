<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella sede.
 *
 * @property int $id
 * @property string $nome
 * @property string|null $indirizzo
 * @property string|null $cap
 * @property string|null $citta
 * @property string|null $provincia
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 *
 * @property Modulo[] $moduli
 * @property User $createdByUser
 * @property User $updatedByUser
 * @property User $deletedByUser
 */
class Sede extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sede}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nome'], 'required'],
            [['note'], 'string'],
            [['nome', 'citta'], 'string', 'max' => 100],
            [['indirizzo'], 'string', 'max' => 255],
            [['cap'], 'string', 'max' => 5],
            [['provincia'], 'string', 'max' => 2],
            [['telefono'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 100],
            [['email'], 'email'],
            [['cap'], 'match', 'pattern' => '/^\d{5}$/', 'message' => 'Il CAP deve essere di 5 cifre'],
            [['provincia'], 'match', 'pattern' => '/^[A-Z]{2}$/', 'message' => 'La provincia deve essere di 2 lettere maiuscole'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Nome',
            'indirizzo' => 'Indirizzo',
            'cap' => 'CAP',
            'citta' => 'Città',
            'provincia' => 'Provincia',
            'telefono' => 'Telefono',
            'email' => 'Email',
            'note' => 'Note',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
            'deleted_at' => 'Eliminato il',
            'created_by' => 'Creato da',
            'updated_by' => 'Modificato da',
            'deleted_by' => 'Eliminato da',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModuli()
    {
        return $this->hasMany(Modulo::class, ['sede_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'deleted_by']);
    }

    /**
     * Restituisce lista per dropdown
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select(['nome', 'id'])
            ->orderBy('nome')
            ->indexBy('id')
            ->column();
    }

    /**
     * Restituisce indirizzo completo
     * @return string
     */
    public function getIndirizzoCompleto()
    {
        $parts = array_filter([
            $this->indirizzo,
            $this->cap,
            $this->citta,
            $this->provincia ? '(' . $this->provincia . ')' : null,
        ]);

        return implode(' ', $parts) ?: '-';
    }
}
