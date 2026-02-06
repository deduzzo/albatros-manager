<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Model per la tabella istanza_snapshot.
 * Conserva i dati storici del docente al momento dell'iscrizione.
 *
 * @property int $id
 * @property int $istanza_id
 * @property array $dati_docente
 * @property array|null $tags_docente
 * @property array|null $tags_iscrizione
 * @property string $created_at
 *
 * @property Istanza $istanza
 */
class IstanzaSnapshot extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%istanza_snapshot}}';
    }

    public function rules()
    {
        return [
            [['istanza_id', 'dati_docente'], 'required'],
            [['istanza_id'], 'integer'],
            [['dati_docente', 'tags_docente', 'tags_iscrizione'], 'safe'],
            [['created_at'], 'safe'],
            [['istanza_id'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'istanza_id' => 'Istanza',
            'dati_docente' => 'Dati Docente',
            'tags_docente' => 'Tag Profilo',
            'tags_iscrizione' => 'Tag Iscrizione',
            'created_at' => 'Creato il',
        ];
    }

    public function getIstanza()
    {
        return $this->hasOne(Istanza::class, ['id' => 'istanza_id']);
    }

    /**
     * Crea uno snapshot dai dati attuali del docente e dell'iscrizione
     */
    public static function creaSnapshot(Istanza $istanza, array $tagsIscrizione = [])
    {
        $docente = $istanza->docente;
        if (!$docente) {
            return null;
        }

        // Dati anagrafici docente
        $datiDocente = [
            'codice_fiscale' => $docente->codice_fiscale,
            'cognome' => $docente->cognome,
            'nome' => $docente->nome,
            'data_nascita' => $docente->data_nascita,
            'luogo_nascita' => $docente->luogo_nascita,
            'indirizzo' => $docente->indirizzo,
            'cap' => $docente->cap,
            'citta' => $docente->citta,
            'provincia' => $docente->provincia,
            'telefono' => $docente->telefono,
            'email' => $docente->email,
            'titolo_studio' => $docente->titolo_studio,
            'specializzazioni' => $docente->specializzazioni,
            'partita_iva' => $docente->partita_iva,
        ];

        // Tag attuali del profilo docente
        $tagsDocenteAttuali = [];
        foreach ($docente->docenteTags as $dt) {
            $tagsDocenteAttuali[] = [
                'tag_id' => $dt->tag_id,
                'codice' => $dt->tag->codice ?? null,
                'nome' => $dt->tag->nome ?? null,
                'livello' => $dt->livello,
            ];
        }

        $snapshot = new self();
        $snapshot->istanza_id = $istanza->id;
        $snapshot->dati_docente = $datiDocente;
        $snapshot->tags_docente = $tagsDocenteAttuali;
        $snapshot->tags_iscrizione = $tagsIscrizione;

        if ($snapshot->save()) {
            return $snapshot;
        }
        return null;
    }

    /**
     * Restituisce un attributo dallo snapshot
     */
    public function getDatoDocente($attributo)
    {
        $dati = is_array($this->dati_docente) ? $this->dati_docente : json_decode($this->dati_docente, true);
        return $dati[$attributo] ?? null;
    }

    /**
     * Restituisce il nome completo dallo snapshot
     */
    public function getNomeCompletoSnapshot()
    {
        $dati = is_array($this->dati_docente) ? $this->dati_docente : json_decode($this->dati_docente, true);
        return ($dati['cognome'] ?? '') . ' ' . ($dati['nome'] ?? '');
    }
}
