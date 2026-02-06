<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Docente;
use app\models\DocenteAuth;

/**
 * Form per la registrazione docente
 */
class DocenteRegistrazioneForm extends Model
{
    public $codice_fiscale;
    public $cognome;
    public $nome;
    public $email;
    public $telefono;
    public $data_nascita;
    public $luogo_nascita;
    public $indirizzo;
    public $cap;
    public $citta;
    public $provincia;
    public $password;
    public $password_confirm;
    public $privacy_policy;

    public function rules()
    {
        return [
            [['codice_fiscale', 'cognome', 'nome', 'email', 'password', 'password_confirm'], 'required'],
            [['codice_fiscale'], 'string', 'length' => 16],
            [['codice_fiscale'], 'filter', 'filter' => 'strtoupper'],
            [['codice_fiscale'], 'match', 'pattern' => '/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i', 'message' => 'Codice fiscale non valido.'],
            [['codice_fiscale'], 'validateCodiceFiscaleNonRegistrato'],
            [['cognome', 'nome'], 'string', 'max' => 100],
            [['email'], 'email'],
            [['email'], 'validateEmailNonRegistrata'],
            [['telefono'], 'string', 'max' => 20],
            [['data_nascita'], 'date', 'format' => 'php:Y-m-d'],
            [['luogo_nascita', 'citta'], 'string', 'max' => 100],
            [['indirizzo'], 'string', 'max' => 255],
            [['cap'], 'string', 'max' => 5],
            [['provincia'], 'string', 'max' => 2],
            [['password'], 'string', 'min' => 6],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password', 'message' => 'Le password non coincidono.'],
            [['privacy_policy'], 'required', 'requiredValue' => 1, 'message' => 'Devi accettare la Privacy Policy.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'codice_fiscale' => 'Codice Fiscale',
            'cognome' => 'Cognome',
            'nome' => 'Nome',
            'email' => 'Email',
            'telefono' => 'Telefono',
            'data_nascita' => 'Data di Nascita',
            'luogo_nascita' => 'Luogo di Nascita',
            'indirizzo' => 'Indirizzo',
            'cap' => 'CAP',
            'citta' => 'Città',
            'provincia' => 'Provincia',
            'password' => 'Password',
            'password_confirm' => 'Conferma Password',
            'privacy_policy' => 'Privacy Policy',
        ];
    }

    /**
     * Verifica che il codice fiscale non sia già registrato
     */
    public function validateCodiceFiscaleNonRegistrato($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $exists = DocenteAuth::find()
                ->where(['docente_codice_fiscale' => $this->codice_fiscale])
                ->exists();

            if ($exists) {
                $this->addError($attribute, 'Questo codice fiscale è già registrato.');
            }
        }
    }

    /**
     * Verifica che l'email non sia già usata
     */
    public function validateEmailNonRegistrata($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $exists = Docente::find()
                ->where(['email' => $this->email])
                ->andWhere(['!=', 'codice_fiscale', $this->codice_fiscale])
                ->exists();

            if ($exists) {
                $this->addError($attribute, 'Questa email è già associata ad un altro account.');
            }
        }
    }

    /**
     * Registra il docente
     */
    public function registra()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Cerca o crea il docente
            $docente = Docente::findOne(['codice_fiscale' => $this->codice_fiscale]);

            if (!$docente) {
                $docente = new Docente();
                $docente->codice_fiscale = $this->codice_fiscale;
            }

            // Aggiorna dati docente
            $docente->cognome = $this->cognome;
            $docente->nome = $this->nome;
            $docente->email = $this->email;
            $docente->telefono = $this->telefono;
            $docente->data_nascita = $this->data_nascita;
            $docente->luogo_nascita = $this->luogo_nascita;
            $docente->indirizzo = $this->indirizzo;
            $docente->cap = $this->cap;
            $docente->citta = $this->citta;
            $docente->provincia = $this->provincia;

            if (!$docente->save()) {
                throw new \Exception('Errore nel salvataggio del profilo docente.');
            }

            // Crea auth
            $auth = new DocenteAuth();
            $auth->docente_codice_fiscale = $this->codice_fiscale;
            $auth->setPassword($this->password);
            $auth->generateAuthKey();
            $auth->generateEmailVerifyToken();
            $auth->status = DocenteAuth::STATUS_PENDING;

            if (!$auth->save()) {
                throw new \Exception('Errore nella creazione delle credenziali.');
            }

            // TODO: Inviare email di verifica
            // $this->sendVerificationEmail($auth);

            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Invia email di verifica
     */
    protected function sendVerificationEmail($auth)
    {
        $verifyLink = Yii::$app->urlManager->createAbsoluteUrl([
            'docente-area/verifica-email',
            'token' => $auth->email_verify_token
        ]);

        return Yii::$app->mailer->compose()
            ->setTo($this->email)
            ->setSubject('Verifica il tuo account - Albatros Manager')
            ->setHtmlBody("
                <h2>Benvenuto in Albatros Manager</h2>
                <p>Clicca sul link seguente per verificare il tuo account:</p>
                <p><a href='{$verifyLink}'>{$verifyLink}</a></p>
                <p>Il link scade tra 24 ore.</p>
            ")
            ->send();
    }
}
