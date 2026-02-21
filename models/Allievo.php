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

    const TITOLO_LICENZA_MEDIA = 'licenza_media';
    const TITOLO_DIPLOMA = 'diploma';
    const TITOLO_LAUREA_TRIENNALE = 'laurea_triennale';
    const TITOLO_LAUREA_MAGISTRALE = 'laurea_magistrale';
    const TITOLO_MASTER = 'master';
    const TITOLO_DOTTORATO = 'dottorato';

    const STATO_OCCUPATO = 'occupato';
    const STATO_DISOCCUPATO = 'disoccupato';
    const STATO_INOCCUPATO = 'inoccupato';
    const STATO_STUDENTE = 'studente';
    const STATO_PENSIONATO = 'pensionato';

    public function rules()
    {
        return [
            [['codice_fiscale', 'cognome', 'nome'], 'required'],
            [['codice_fiscale'], 'string', 'max' => 16],
            [['codice_fiscale'], 'unique'],
            [['cognome', 'nome', 'luogo_nascita', 'citta'], 'string', 'max' => 100],
            [['data_nascita'], 'date', 'format' => 'php:Y-m-d'],
            [['indirizzo', 'istituto'], 'string', 'max' => 255],
            [['cap'], 'string', 'max' => 5],
            [['provincia'], 'string', 'max' => 2],
            [['telefono'], 'string', 'max' => 20],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 100],
            [['settore_attivita'], 'string', 'max' => 100],
            [['note'], 'string'],
            [['titolo_studio'], 'in', 'range' => [self::TITOLO_LICENZA_MEDIA, self::TITOLO_DIPLOMA, self::TITOLO_LAUREA_TRIENNALE, self::TITOLO_LAUREA_MAGISTRALE, self::TITOLO_MASTER, self::TITOLO_DOTTORATO]],
            [['stato_occupazionale'], 'in', 'range' => [self::STATO_OCCUPATO, self::STATO_DISOCCUPATO, self::STATO_INOCCUPATO, self::STATO_STUDENTE, self::STATO_PENSIONATO]],
            [['anno_conseguimento'], 'integer', 'min' => 1950, 'max' => date('Y')],
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
            'titolo_studio' => 'Titolo di Studio',
            'anno_conseguimento' => 'Anno di Conseguimento',
            'istituto' => 'Istituto / Università',
            'stato_occupazionale' => 'Stato Occupazionale',
            'settore_attivita' => 'Settore di Attività',
        ];
    }

    public static function getTitoloStudioLabels()
    {
        return [
            self::TITOLO_LICENZA_MEDIA => 'Licenza Media',
            self::TITOLO_DIPLOMA => 'Diploma di Scuola Superiore',
            self::TITOLO_LAUREA_TRIENNALE => 'Laurea Triennale',
            self::TITOLO_LAUREA_MAGISTRALE => 'Laurea Magistrale',
            self::TITOLO_MASTER => 'Master / Specializzazione',
            self::TITOLO_DOTTORATO => 'Dottorato di Ricerca',
        ];
    }

    public static function getStatoOccupazionaleLabels()
    {
        return [
            self::STATO_OCCUPATO => 'Occupato',
            self::STATO_DISOCCUPATO => 'Disoccupato',
            self::STATO_INOCCUPATO => 'Inoccupato (mai lavorato)',
            self::STATO_STUDENTE => 'Studente',
            self::STATO_PENSIONATO => 'Pensionato',
        ];
    }

    public function getNomeCompleto()
    {
        return $this->cognome . ' ' . $this->nome;
    }

    public function getAuth()
    {
        return $this->hasOne(AllievoAuth::class, ['allievo_codice_fiscale' => 'codice_fiscale']);
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
