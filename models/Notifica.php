<?php

namespace app\models;

use Yii;
use app\components\base\BaseActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Model per la tabella "notifica".
 *
 * @property int $id
 * @property string $titolo
 * @property string $contenuto
 * @property string|null $link
 * @property string $destinatario_tipo
 * @property string|null $destinatario_id
 * @property int $invia_email
 * @property int $email_inviata
 */
class Notifica extends BaseActiveRecord
{
    const TIPO_TUTTI = 'tutti';
    const TIPO_ADMIN = 'admin';
    const TIPO_DOCENTE = 'docente';
    const TIPO_STUDENTE = 'studente';
    const TIPO_SINGOLO_ADMIN = 'singolo_admin';
    const TIPO_SINGOLO_DOCENTE = 'singolo_docente';
    const TIPO_SINGOLO_STUDENTE = 'singolo_studente';

    public static function tableName()
    {
        return '{{%notifica}}';
    }

    public function rules()
    {
        return [
            [['titolo', 'contenuto', 'destinatario_tipo'], 'required'],
            ['titolo', 'string', 'max' => 255],
            ['contenuto', 'string'],
            ['link', 'string', 'max' => 500],
            ['destinatario_tipo', 'in', 'range' => [
                self::TIPO_TUTTI, self::TIPO_ADMIN, self::TIPO_DOCENTE, self::TIPO_STUDENTE,
                self::TIPO_SINGOLO_ADMIN, self::TIPO_SINGOLO_DOCENTE, self::TIPO_SINGOLO_STUDENTE,
            ]],
            ['destinatario_id', 'required', 'when' => function ($model) {
                return in_array($model->destinatario_tipo, [self::TIPO_SINGOLO_ADMIN, self::TIPO_SINGOLO_DOCENTE, self::TIPO_SINGOLO_STUDENTE]);
            }],
            ['destinatario_id', 'string', 'max' => 50],
            [['invia_email', 'email_inviata'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titolo' => 'Titolo',
            'contenuto' => 'Contenuto',
            'link' => 'Link',
            'destinatario_tipo' => 'Destinatari',
            'destinatario_id' => 'Destinatario',
            'invia_email' => 'Invia anche email',
            'email_inviata' => 'Email inviata',
            'created_at' => 'Data invio',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // Sanitizza HTML
        $this->contenuto = HtmlPurifier::process($this->contenuto);
        return true;
    }

    // --- Relazioni ---

    public function getLetture()
    {
        return $this->hasMany(NotificaLettura::class, ['notifica_id' => 'id']);
    }

    // --- Metodi business ---

    public function isLettaDa(string $userType, string $userId): bool
    {
        return NotificaLettura::find()
            ->andWhere(['notifica_id' => $this->id, 'user_type' => $userType, 'user_id' => $userId])
            ->exists();
    }

    public function segnaComeLetta(string $userType, string $userId): void
    {
        try {
            $lettura = new NotificaLettura([
                'notifica_id' => $this->id,
                'user_type' => $userType,
                'user_id' => $userId,
                'letta_at' => date('Y-m-d H:i:s'),
            ]);
            $lettura->save(false);
        } catch (\yii\db\IntegrityException $e) {
            // Già letta (unique constraint) — ignora
        }
    }

    public function getDestinatariLabel(): string
    {
        return match ($this->destinatario_tipo) {
            self::TIPO_TUTTI => 'Tutti gli utenti',
            self::TIPO_ADMIN => 'Amministratori',
            self::TIPO_DOCENTE => 'Docenti',
            self::TIPO_STUDENTE => 'Studenti',
            self::TIPO_SINGOLO_ADMIN => 'Admin: ' . $this->getDestinatarioNome(),
            self::TIPO_SINGOLO_DOCENTE => 'Docente: ' . $this->getDestinatarioNome(),
            self::TIPO_SINGOLO_STUDENTE => 'Studente: ' . $this->getDestinatarioNome(),
            default => $this->destinatario_tipo,
        };
    }

    private function getDestinatarioNome(): string
    {
        if (empty($this->destinatario_id)) {
            return '?';
        }
        if (in_array($this->destinatario_tipo, [self::TIPO_SINGOLO_DOCENTE])) {
            $docente = Docente::findOne(['codice_fiscale' => $this->destinatario_id]);
            return $docente ? "{$docente->cognome} {$docente->nome}" : $this->destinatario_id;
        }
        if (in_array($this->destinatario_tipo, [self::TIPO_SINGOLO_STUDENTE])) {
            $allievo = Allievo::findOne(['codice_fiscale' => $this->destinatario_id]);
            return $allievo ? "{$allievo->cognome} {$allievo->nome}" : $this->destinatario_id;
        }
        // Singolo admin
        $user = User::findOne($this->destinatario_id);
        return $user ? ($user->nominativo ?: $user->username) : $this->destinatario_id;
    }

    public function getCreatoDaLabel(): string
    {
        if ($this->created_by) {
            $user = User::findOne($this->created_by);
            return $user ? ($user->nominativo ?: $user->username) : "ID: {$this->created_by}";
        }
        return 'Sistema';
    }

    /**
     * Query notifiche destinate a un tipo utente specifico.
     * NON usa alias, quindi static::find() funziona correttamente con SoftDeleteTrait.
     */
    public static function queryPerUtente(string $userType, string $userId): \yii\db\ActiveQuery
    {
        $singolo = 'singolo_' . $userType;

        return static::find()
            ->andWhere(['or',
                ['destinatario_tipo' => self::TIPO_TUTTI],
                ['destinatario_tipo' => $userType],
                ['and',
                    ['destinatario_tipo' => $singolo],
                    ['destinatario_id' => $userId],
                ],
            ])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Conta notifiche non lette per un utente.
     * Usa findWithDeleted + alias per evitare conflitto SoftDeleteTrait.
     */
    public static function countNonLette(string $userType, string $userId): int
    {
        $singolo = 'singolo_' . $userType;

        return (int) static::findWithDeleted()
            ->alias('n')
            ->andWhere(['n.deleted_at' => null])
            ->leftJoin('notifica_lettura nl', 'nl.notifica_id = n.id AND nl.user_type = :ut AND nl.user_id = :uid', [
                ':ut' => $userType,
                ':uid' => $userId,
            ])
            ->andWhere(['nl.id' => null])
            ->andWhere(['or',
                ['n.destinatario_tipo' => self::TIPO_TUTTI],
                ['n.destinatario_tipo' => $userType],
                ['and',
                    ['n.destinatario_tipo' => $singolo],
                    ['n.destinatario_id' => $userId],
                ],
            ])
            ->count();
    }

    /**
     * Ultime N notifiche non lette per il dropdown header.
     * Usa findWithDeleted + alias per evitare conflitto SoftDeleteTrait.
     */
    public static function getUltimeNonLette(string $userType, string $userId, int $limit = 5): array
    {
        $singolo = 'singolo_' . $userType;

        return static::findWithDeleted()
            ->alias('n')
            ->andWhere(['n.deleted_at' => null])
            ->leftJoin('notifica_lettura nl', 'nl.notifica_id = n.id AND nl.user_type = :ut AND nl.user_id = :uid', [
                ':ut' => $userType,
                ':uid' => $userId,
            ])
            ->andWhere(['nl.id' => null])
            ->andWhere(['or',
                ['n.destinatario_tipo' => self::TIPO_TUTTI],
                ['n.destinatario_tipo' => $userType],
                ['and',
                    ['n.destinatario_tipo' => $singolo],
                    ['n.destinatario_id' => $userId],
                ],
            ])
            ->orderBy(['n.created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Opzioni per il dropdown destinatario nel form admin.
     */
    public static function getDestinatarioTipoOptions(): array
    {
        return [
            self::TIPO_TUTTI => 'Tutti gli utenti',
            self::TIPO_ADMIN => 'Solo Amministratori',
            self::TIPO_DOCENTE => 'Solo Docenti',
            self::TIPO_STUDENTE => 'Solo Studenti',
            self::TIPO_SINGOLO_ADMIN => 'Singolo Amministratore',
            self::TIPO_SINGOLO_DOCENTE => 'Singolo Docente',
            self::TIPO_SINGOLO_STUDENTE => 'Singolo Studente',
        ];
    }

    /**
     * Raccoglie le email per l'invio.
     * Usa findWithDeleted + alias per Docente/Allievo che estendono BaseActiveRecord.
     */
    public function getEmailDestinatari(): array
    {
        $emails = [];

        switch ($this->destinatario_tipo) {
            case self::TIPO_TUTTI:
                // Admin
                $admins = User::find()->andWhere(['status' => User::STATUS_ACTIVE])->all();
                foreach ($admins as $u) { if (!empty($u->email)) $emails[] = $u->email; }
                // Docenti
                $docenti = Docente::findWithDeleted()
                    ->alias('d')
                    ->andWhere(['d.deleted_at' => null])
                    ->leftJoin('docente_auth da', 'd.codice_fiscale = da.docente_codice_fiscale AND da.deleted_at IS NULL')
                    ->andWhere(['da.status' => 'active'])
                    ->all();
                foreach ($docenti as $d) { if (!empty($d->email)) $emails[] = $d->email; }
                // Studenti
                $allievi = Allievo::findWithDeleted()
                    ->alias('a')
                    ->andWhere(['a.deleted_at' => null])
                    ->leftJoin('allievo_auth aa', 'a.codice_fiscale = aa.allievo_codice_fiscale AND aa.deleted_at IS NULL')
                    ->andWhere(['aa.status' => 'active'])
                    ->all();
                foreach ($allievi as $a) { if (!empty($a->email)) $emails[] = $a->email; }
                break;

            case self::TIPO_ADMIN:
                $admins = User::find()->andWhere(['status' => User::STATUS_ACTIVE])->all();
                foreach ($admins as $u) { if (!empty($u->email)) $emails[] = $u->email; }
                break;

            case self::TIPO_DOCENTE:
                $docenti = Docente::findWithDeleted()
                    ->alias('d')
                    ->andWhere(['d.deleted_at' => null])
                    ->leftJoin('docente_auth da', 'd.codice_fiscale = da.docente_codice_fiscale AND da.deleted_at IS NULL')
                    ->andWhere(['da.status' => 'active'])
                    ->all();
                foreach ($docenti as $d) { if (!empty($d->email)) $emails[] = $d->email; }
                break;

            case self::TIPO_STUDENTE:
                $allievi = Allievo::findWithDeleted()
                    ->alias('a')
                    ->andWhere(['a.deleted_at' => null])
                    ->leftJoin('allievo_auth aa', 'a.codice_fiscale = aa.allievo_codice_fiscale AND aa.deleted_at IS NULL')
                    ->andWhere(['aa.status' => 'active'])
                    ->all();
                foreach ($allievi as $a) { if (!empty($a->email)) $emails[] = $a->email; }
                break;

            case self::TIPO_SINGOLO_ADMIN:
                $user = User::findOne($this->destinatario_id);
                if ($user && !empty($user->email)) $emails[] = $user->email;
                break;

            case self::TIPO_SINGOLO_DOCENTE:
                $docente = Docente::findOne(['codice_fiscale' => $this->destinatario_id]);
                if ($docente && !empty($docente->email)) $emails[] = $docente->email;
                break;

            case self::TIPO_SINGOLO_STUDENTE:
                $allievo = Allievo::findOne(['codice_fiscale' => $this->destinatario_id]);
                if ($allievo && !empty($allievo->email)) $emails[] = $allievo->email;
                break;
        }

        return array_unique($emails);
    }
}
