<?php

namespace app\models;

use app\components\base\BaseActiveRecord;

/**
 * Model per la tabella allievo.
 *
 * @property string $codice_fiscale
 * @property string $cognome
 * @property string $nome
 * @property string|null $data_nascita
 * @property string|null $luogo_nascita
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
 * @property Iscrizione[] $iscrizioni
 * @property PresenzaAllievo[] $presenze
 * @property Documentazione[] $documentazioni
 */
class Allievo extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%allievo}}';
    }

    public static function primaryKey()
    {
        return ['codice_fiscale'];
    }

    public function rules()
    {
        return [
            [['codice_fiscale', 'cognome', 'nome'], 'required'],
            [['codice_fiscale'], 'string', 'max' => 16],
            [['codice_fiscale'], 'unique'],
            [['cognome', 'nome', 'luogo_nascita', 'citta'], 'string', 'max' => 100],
            [['data_nascita'], 'date', 'format' => 'php:Y-m-d'],
            [['indirizzo'], 'string', 'max' => 255],
            [['cap'], 'string', 'max' => 5],
            [['provincia'], 'string', 'max' => 2],
            [['telefono'], 'string', 'max' => 20],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 100],
            [['note'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'codice_fiscale' => 'Codice Fiscale',
            'cognome' => 'Cognome',
            'nome' => 'Nome',
            'data_nascita' => 'Data di Nascita',
            'luogo_nascita' => 'Luogo di Nascita',
            'indirizzo' => 'Indirizzo',
            'cap' => 'CAP',
            'citta' => 'Città',
            'provincia' => 'Provincia',
            'telefono' => 'Telefono',
            'email' => 'Email',
            'note' => 'Note',
        ];
    }

    public function getNomeCompleto()
    {
        return $this->cognome . ' ' . $this->nome;
    }

    public function getIscrizioni()
    {
        return $this->hasMany(Iscrizione::class, ['allievo_codice_fiscale' => 'codice_fiscale']);
    }

    public function getPresenze()
    {
        return $this->hasMany(PresenzaAllievo::class, ['allievo_codice_fiscale' => 'codice_fiscale']);
    }

    public function getDocumentazioni()
    {
        return $this->hasMany(Documentazione::class, ['allievo_codice_fiscale' => 'codice_fiscale']);
    }
}
