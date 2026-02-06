<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

/**
 * Controller DEMO per l'area studenti.
 * Questa è una demo statica senza interazione con il database.
 */
class DemoStudentiController extends Controller
{
    public $layout = 'studenti-demo';

    /**
     * Dati demo dei bandi
     */
    protected function getBandiDemo()
    {
        return [
            [
                'id' => 1,
                'codice' => 'FSE-2024-DIGITAL',
                'titolo' => 'Competenze Digitali per il Lavoro 4.0',
                'sottotitolo' => 'Programma formativo finanziato dal Fondo Sociale Europeo',
                'fonte' => 'FSE - Fondo Sociale Europeo',
                'scadenza' => '2025-04-30',
                'descrizione' => 'Il programma "Competenze Digitali per il Lavoro 4.0" nasce dall\'esigenza di colmare il gap di competenze digitali nel mercato del lavoro italiano. Attraverso percorsi formativi mirati, i partecipanti acquisiranno le competenze necessarie per affrontare le sfide della trasformazione digitale.',
                'obiettivi' => [
                    'Sviluppare competenze digitali avanzate richieste dal mercato del lavoro',
                    'Fornire strumenti pratici per l\'utilizzo delle tecnologie emergenti',
                    'Preparare i partecipanti alle certificazioni professionali riconosciute',
                    'Facilitare l\'inserimento o il reinserimento nel mondo del lavoro',
                ],
                'destinatari' => 'Disoccupati, inoccupati e lavoratori che necessitano di riqualificazione professionale. Età compresa tra 18 e 65 anni. Residenti in Lombardia.',
                'prospettive' => [
                    'Inserimento presso aziende partner del programma',
                    'Avvio di attività freelance nel settore digitale',
                    'Progressione di carriera per chi è già occupato',
                    'Accesso a ulteriori percorsi formativi avanzati',
                ],
                'corsi' => [
                    ['id' => 1, 'codice' => 'DIG-PYTHON', 'titolo' => 'Programmazione Python', 'ore' => 60, 'posti' => 25, 'disponibili' => 12, 'sede' => 'Milano - Via Torino 42', 'inizio' => '2025-03-15'],
                    ['id' => 2, 'codice' => 'DIG-DATA', 'titolo' => 'Data Analysis & Visualization', 'ore' => 40, 'posti' => 20, 'disponibili' => 8, 'sede' => 'Milano - Via Torino 42', 'inizio' => '2025-04-01'],
                    ['id' => 3, 'codice' => 'DIG-CLOUD', 'titolo' => 'Cloud Computing Fundamentals', 'ore' => 50, 'posti' => 20, 'disponibili' => 15, 'sede' => 'Online', 'inizio' => '2025-03-20'],
                ],
                'requisiti' => [
                    'Diploma di scuola secondaria superiore',
                    'Conoscenza base dell\'uso del computer',
                    'Disponibilità a frequentare almeno l\'80% delle lezioni',
                    'Motivazione e interesse per il settore digitale',
                ],
                'certificazioni' => ['Attestato di frequenza', 'Certificazione competenze digitali', 'Open Badge verificabile'],
                'colore' => '#206bc4',
            ],
            [
                'id' => 2,
                'codice' => 'PNRR-2024-GREEN',
                'titolo' => 'Professioni per la Transizione Ecologica',
                'sottotitolo' => 'Formazione finanziata dal Piano Nazionale di Ripresa e Resilienza',
                'fonte' => 'PNRR - Piano Nazionale Ripresa e Resilienza',
                'scadenza' => '2025-05-15',
                'descrizione' => 'Il programma forma figure professionali specializzate nella sostenibilità ambientale e nella transizione ecologica. I partecipanti acquisiranno competenze tecniche e gestionali per operare nei settori delle energie rinnovabili, dell\'efficienza energetica e dell\'economia circolare.',
                'obiettivi' => [
                    'Formare esperti in energie rinnovabili e efficienza energetica',
                    'Sviluppare competenze per la gestione sostenibile delle risorse',
                    'Preparare professionisti per la certificazione ambientale',
                    'Promuovere l\'innovazione green nelle imprese',
                ],
                'destinatari' => 'Giovani NEET (18-34 anni), disoccupati di lunga durata, lavoratori in settori in transizione. Priorità a residenti in aree svantaggiate.',
                'prospettive' => [
                    'Energy Manager presso aziende e PA',
                    'Consulente per la sostenibilità ambientale',
                    'Tecnico installatore impianti rinnovabili',
                    'Responsabile economia circolare',
                ],
                'corsi' => [
                    ['id' => 4, 'codice' => 'GREEN-ENERGY', 'titolo' => 'Energie Rinnovabili e Fotovoltaico', 'ore' => 80, 'posti' => 20, 'disponibili' => 6, 'sede' => 'Roma - Via Nazionale 50', 'inizio' => '2025-04-10'],
                    ['id' => 5, 'codice' => 'GREEN-AUDIT', 'titolo' => 'Audit Energetico e Certificazioni', 'ore' => 60, 'posti' => 15, 'disponibili' => 10, 'sede' => 'Roma - Via Nazionale 50', 'inizio' => '2025-04-20'],
                    ['id' => 6, 'codice' => 'GREEN-CIRCULAR', 'titolo' => 'Economia Circolare e Sostenibilità', 'ore' => 40, 'posti' => 25, 'disponibili' => 20, 'sede' => 'Online', 'inizio' => '2025-05-01'],
                ],
                'requisiti' => [
                    'Età compresa tra 18 e 45 anni',
                    'Diploma tecnico o laurea (preferibile in materie scientifiche)',
                    'Interesse per le tematiche ambientali',
                    'Residenza in Italia',
                ],
                'certificazioni' => ['Qualifica professionale regionale', 'Certificazione Energy Manager', 'Attestato sicurezza sul lavoro'],
                'colore' => '#2fb344',
            ],
            [
                'id' => 3,
                'codice' => 'MIM-2024-STEM',
                'titolo' => 'STEM Academy - Scienze e Tecnologie',
                'sottotitolo' => 'Programma del Ministero dell\'Istruzione e del Merito',
                'fonte' => 'MIM - Ministero Istruzione e Merito',
                'scadenza' => '2025-06-01',
                'descrizione' => 'STEM Academy è un programma innovativo che mira a colmare il divario di genere nelle discipline scientifiche e tecnologiche, offrendo percorsi formativi di eccellenza in matematica, fisica, informatica e ingegneria. Il programma prevede anche attività di mentoring e stage presso aziende leader del settore.',
                'obiettivi' => [
                    'Aumentare la partecipazione femminile nelle discipline STEM',
                    'Fornire competenze avanzate in matematica e scienze computazionali',
                    'Sviluppare il pensiero critico e la capacità di problem solving',
                    'Creare una rete di professioniste STEM come role model',
                ],
                'destinatari' => 'Studentesse universitarie, neolaureate e giovani professioniste (18-35 anni) interessate a carriere STEM. Aperto anche a uomini con quote riservate.',
                'prospettive' => [
                    'Ricerca e sviluppo in aziende tecnologiche',
                    'Carriera accademica e di ricerca',
                    'Imprenditoria tecnologica e startup',
                    'Consulenza strategica in ambito tech',
                ],
                'corsi' => [
                    ['id' => 7, 'codice' => 'STEM-AI', 'titolo' => 'Intelligenza Artificiale e Machine Learning', 'ore' => 100, 'posti' => 30, 'disponibili' => 18, 'sede' => 'Torino - Politecnico', 'inizio' => '2025-05-15'],
                    ['id' => 8, 'codice' => 'STEM-CYBER', 'titolo' => 'Cybersecurity e Ethical Hacking', 'ore' => 80, 'posti' => 20, 'disponibili' => 14, 'sede' => 'Milano - Università Bicocca', 'inizio' => '2025-06-01'],
                    ['id' => 9, 'codice' => 'STEM-ROBOT', 'titolo' => 'Robotica e Automazione Industriale', 'ore' => 70, 'posti' => 15, 'disponibili' => 9, 'sede' => 'Bologna - CINECA', 'inizio' => '2025-05-20'],
                ],
                'requisiti' => [
                    'Laurea triennale o iscrizione a corso di laurea STEM',
                    'Conoscenza base di un linguaggio di programmazione',
                    'Livello B2 di inglese (alcune lezioni in lingua)',
                    'Forte motivazione e disponibilità full-time durante il corso',
                ],
                'certificazioni' => ['Certificazione universitaria', 'Badge competenze specialistiche', 'Lettera di raccomandazione'],
                'colore' => '#ae3ec9',
            ],
            [
                'id' => 4,
                'codice' => 'GOL-2024-WORK',
                'titolo' => 'Garanzia Occupabilità Lavoratori',
                'sottotitolo' => 'Programma GOL - Percorsi di riqualificazione professionale',
                'fonte' => 'Programma GOL - PNRR',
                'scadenza' => '2025-12-31',
                'descrizione' => 'Il Programma GOL (Garanzia di Occupabilità dei Lavoratori) è la più grande riforma delle politiche attive del lavoro degli ultimi anni. Offre percorsi personalizzati di formazione e accompagnamento al lavoro per disoccupati, percettori di ammortizzatori sociali e lavoratori fragili.',
                'obiettivi' => [
                    'Reinserimento lavorativo rapido ed efficace',
                    'Aggiornamento delle competenze professionali',
                    'Riqualificazione per nuovi settori occupazionali',
                    'Supporto personalizzato nella ricerca di lavoro',
                ],
                'destinatari' => 'Beneficiari NASpI e DIS-COLL, percettori RdC, lavoratori in CIGS, disoccupati da oltre 6 mesi, over 55, persone con disabilità, donne in condizioni di svantaggio.',
                'prospettive' => [
                    'Inserimento immediato presso aziende convenzionate',
                    'Tirocini retribuiti con possibilità di assunzione',
                    'Supporto all\'autoimprenditorialità',
                    'Accesso prioritario a offerte di lavoro',
                ],
                'corsi' => [
                    ['id' => 10, 'codice' => 'GOL-ADMIN', 'titolo' => 'Addetto Amministrazione e Contabilità', 'ore' => 120, 'posti' => 20, 'disponibili' => 5, 'sede' => 'Napoli - Centro Formazione', 'inizio' => '2025-03-01'],
                    ['id' => 11, 'codice' => 'GOL-COMMERCE', 'titolo' => 'Operatore E-commerce e Digital Marketing', 'ore' => 80, 'posti' => 25, 'disponibili' => 12, 'sede' => 'Bari - Fiera del Levante', 'inizio' => '2025-03-15'],
                    ['id' => 12, 'codice' => 'GOL-CARE', 'titolo' => 'Assistente Familiare e OSS', 'ore' => 150, 'posti' => 30, 'disponibili' => 22, 'sede' => 'Palermo - ASL', 'inizio' => '2025-04-01'],
                ],
                'requisiti' => [
                    'Stato di disoccupazione certificato',
                    'Iscrizione al Centro per l\'Impiego',
                    'Patto di Servizio Personalizzato attivo',
                    'Età lavorativa (18-67 anni)',
                ],
                'certificazioni' => ['Qualifica professionale', 'Attestato competenze', 'Certificazione settoriale'],
                'colore' => '#d63939',
            ],
        ];
    }

    /**
     * Trova un corso per ID
     */
    protected function findCorsoById($corsoId)
    {
        foreach ($this->getBandiDemo() as $bando) {
            foreach ($bando['corsi'] as $corso) {
                if ($corso['id'] == $corsoId) {
                    return array_merge($corso, [
                        'bando_id' => $bando['id'],
                        'bando_codice' => $bando['codice'],
                        'bando_titolo' => $bando['titolo'],
                        'fonte' => $bando['fonte'],
                    ]);
                }
            }
        }
        return null;
    }

    /**
     * Dashboard con tutti i bandi aperti
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'bandi' => $this->getBandiDemo(),
        ]);
    }

    /**
     * Dettaglio bando con corsi
     */
    public function actionBando($id)
    {
        $bandi = $this->getBandiDemo();
        $bando = null;
        foreach ($bandi as $b) {
            if ($b['id'] == $id) {
                $bando = $b;
                break;
            }
        }

        if (!$bando) {
            return $this->redirect(['index']);
        }

        return $this->render('bando', [
            'bando' => $bando,
        ]);
    }

    /**
     * Form login studenti (demo)
     */
    public function actionLogin()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->session->setFlash('info', 'Questa è una demo. Il login non è attivo.');
            return $this->redirect(['index']);
        }

        return $this->render('login');
    }

    /**
     * Form registrazione studenti (demo)
     */
    public function actionRegistrazione()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->session->setFlash('success', 'Demo: Registrazione simulata con successo! Ti abbiamo inviato un\'email di verifica (simulata).');
            return $this->redirect(['login']);
        }

        return $this->render('registrazione');
    }

    /**
     * Lista corsi disponibili (demo) - redirect a index
     */
    public function actionCorsi()
    {
        return $this->redirect(['index']);
    }

    /**
     * Form iscrizione corso (demo)
     */
    public function actionIscrizione($id = 1)
    {
        $corso = $this->findCorsoById($id);

        if (!$corso) {
            return $this->redirect(['index']);
        }

        // Documenti richiesti demo
        $documentiRichiesti = [
            ['nome' => 'Documento di identità (fronte/retro)', 'obbligatorio' => true, 'formati' => 'PDF, JPG, PNG', 'max_mb' => 5],
            ['nome' => 'Codice fiscale / Tessera sanitaria', 'obbligatorio' => true, 'formati' => 'PDF, JPG, PNG', 'max_mb' => 2],
            ['nome' => 'Titolo di studio', 'obbligatorio' => true, 'formati' => 'PDF', 'max_mb' => 10],
            ['nome' => 'Curriculum Vitae aggiornato', 'obbligatorio' => false, 'formati' => 'PDF, DOC, DOCX', 'max_mb' => 5],
            ['nome' => 'Certificato stato disoccupazione (se applicabile)', 'obbligatorio' => false, 'formati' => 'PDF', 'max_mb' => 5],
        ];

        if (Yii::$app->request->isPost) {
            Yii::$app->session->setFlash('success', 'Demo: Iscrizione simulata con successo! Riceverai una conferma via email (simulata).');
            return $this->redirect(['conferma']);
        }

        return $this->render('iscrizione', [
            'corso' => $corso,
            'documentiRichiesti' => $documentiRichiesti,
        ]);
    }

    /**
     * Pagina conferma iscrizione (demo)
     */
    public function actionConferma()
    {
        return $this->render('conferma');
    }
}
