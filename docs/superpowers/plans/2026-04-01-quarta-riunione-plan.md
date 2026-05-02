# Quarta Riunione — Piano di Implementazione

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implementare le modifiche emerse dalla quarta riunione: fix validazione form, ridenominazione terminologia, vista semplificata corso-centrica, area tutor con presenze a 3 stati, miglioramenti borse di studio con export SEPA, e sistema alert.

**Architecture:** Nuovo `GestioneController` con layout dedicato `gestione.php` per la vista semplificata. L'area admin avanzata resta intatta. Tutor gestiti come docenti differenziati per tipo nell'area docenti esistente. AlertWidget riutilizzabile per dashboard e vista corso.

**Tech Stack:** Yii2, PHP 8.4, MySQL 8, Tabler Dashboard v1.4, PhpSpreadsheet, DOMPDF, Tom Select

**Spec:** `docs/superpowers/specs/2026-04-01-quarta-riunione-design.md`

---

## Mappa File

### File da creare
```
controllers/GestioneController.php          — Controller vista semplificata
views/layouts/gestione.php                  — Layout con sidebar corso
views/gestione/index.php                    — Home griglia corsi
views/gestione/corso.php                    — Dashboard corso (panoramica)
views/gestione/piano-formativo.php          — Moduli del corso
views/gestione/personale.php                — Docenti del corso
views/gestione/studenti.php                 — Studenti del corso
views/gestione/calendario.php               — Calendario lezioni
views/gestione/contratti.php                — Contratti del corso
views/gestione/borse-studio.php             — Borse di studio del corso
views/gestione/import-export.php            — Import/Export per sezione
views/gestione/_sidebar.php                 — Partial sidebar navigazione corso
views/gestione/_corso-card.php              — Partial card singolo corso per griglia
views/gestione/_candidatura-multi.php       — Form candidatura multi-modulo
views/docente-area/presenze.php             — Pagina presenze per lezione (tutor)
views/docente-area/_presenze-lezione.php    — Partial form presenze singola lezione
components/widgets/AlertWidget.php          — Widget alert riutilizzabile
components/SepaXmlGenerator.php             — Generatore SEPA XML pain.001
migrations/m260401_100000_add_docente_presenza_to_calendario.php
migrations/m260401_100100_modify_presenza_allievo_nullable.php
migrations/m260401_100200_add_allegati_to_contratto.php
migrations/m260401_100300_add_erogazione_bloccata_status_borsa.php
migrations/m260401_100400_add_banca_config.php
```

### File da modificare
```
views/layouts/menu.php                      — Ridenominazione + link vista semplificata
views/layouts/main.php                      — Link "Vista Semplificata" nell'header
views/site/index.php                        — Widget alert nella dashboard admin
config/web.php                              — Route /gestione/*
models/PresenzaAllievo.php                  — Campo presente nullable
models/Calendario.php                       — Nuovi campi docente_presente/ore_effettive
models/BorsaStudio.php                      — Nuovo status + metodo calcolo frequenza
models/Contratto.php                        — Campo allegati_path
controllers/DocenteAreaController.php       — Actions tutor (presenze, alert)
views/avviso/view.php                       — Pulsante candidatura multi-modulo
views/avviso/index.php                      — Ridenominazione
views/avviso/_form.php                      — Ridenominazione
views/avviso/create.php                     — Ridenominazione
views/avviso/update.php                     — Ridenominazione
views/modulo/index.php                      — Ridenominazione
views/modulo/_form.php                      — Ridenominazione
views/modulo/view.php                       — Ridenominazione
views/modulo/create.php                     — Ridenominazione
views/modulo/update.php                     — Ridenominazione
views/progetto/view.php                     — Ridenominazione
views/istanza/_form.php                     — Ridenominazione
views/contratto/view.php                    — Ridenominazione + allegati
views/contratto/_form.php                   — Ridenominazione
views/docente-area/index.php                — Dashboard tutor differenziata
views/layouts/docente.php                   — Menu tutor
+ tutti i 31 _form.php                      — Audit validazione
```

---

## Gruppi di Parallelizzazione per Agenti

```
Gruppo A (db-agent):      Task 1 — Migrations
Gruppo B (full-stack-1):  Task 2 — Fix validazione form
Gruppo C (full-stack-2):  Task 3 — Ridenominazione terminologia
Gruppo D (full-stack-3):  Task 4, 5, 6 — Layout gestione + home + sidebar (dopo Task 1)
Gruppo E (full-stack-4):  Task 7 — Area tutor presenze (dopo Task 1)
Gruppo F (full-stack-5):  Task 8, 9 — Borse di studio + SEPA (dopo Task 1)
Gruppo G (full-stack-6):  Task 10, 11, 12 — Candidature multi-modulo, contratti, alert (dopo Task 1)
```

---

## Task 1: Migrations Database

**Files:**
- Create: `migrations/m260401_100000_add_docente_presenza_to_calendario.php`
- Create: `migrations/m260401_100100_modify_presenza_allievo_nullable.php`
- Create: `migrations/m260401_100200_add_allegati_to_contratto.php`
- Create: `migrations/m260401_100300_add_erogazione_bloccata_status_borsa.php`
- Create: `migrations/m260401_100400_add_banca_config.php`

- [ ] **Step 1: Creare migration per campi presenza docente su calendario**

```php
<?php
use yii\db\Migration;

class m260401_100000_add_docente_presenza_to_calendario extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%calendario}}', 'docente_presente', $this->tinyInteger()->null()->defaultValue(null)->after('status')->comment('null=non acquisito, 1=presente, 0=assente'));
        $this->addColumn('{{%calendario}}', 'docente_ore_effettive', $this->decimal(5, 2)->null()->defaultValue(null)->after('docente_presente'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%calendario}}', 'docente_ore_effettive');
        $this->dropColumn('{{%calendario}}', 'docente_presente');
    }
}
```

- [ ] **Step 2: Creare migration per presenza_allievo nullable**

```php
<?php
use yii\db\Migration;

class m260401_100100_modify_presenza_allievo_nullable extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%presenza_allievo}}', 'presente', $this->tinyInteger()->null()->defaultValue(null)->comment('null=non acquisito, 1=presente, 0=assente'));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%presenza_allievo}}', 'presente', $this->boolean()->defaultValue(0));
    }
}
```

- [ ] **Step 3: Creare migration per allegati contratto**

```php
<?php
use yii\db\Migration;

class m260401_100200_add_allegati_to_contratto extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%contratto}}', 'allegati_path', $this->json()->null()->after('documento_path')->comment('JSON array di percorsi allegati'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%contratto}}', 'allegati_path');
    }
}
```

- [ ] **Step 4: Creare migration per status erogazione_bloccata su borsa_studio**

La tabella `borsa_studio` ha già il campo `status` come ENUM. Aggiungiamo il nuovo valore.

```php
<?php
use yii\db\Migration;

class m260401_100300_add_erogazione_bloccata_status_borsa extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%borsa_studio}}', 'status', "ENUM('richiesta','approvata','in_erogazione','erogazione_bloccata','completata','revocata','sospesa') NOT NULL DEFAULT 'richiesta'");
    }

    public function safeDown()
    {
        $this->alterColumn('{{%borsa_studio}}', 'status', "ENUM('richiesta','approvata','in_erogazione','completata','revocata','sospesa') NOT NULL DEFAULT 'richiesta'");
    }
}
```

- [ ] **Step 5: Creare migration per configurazione banca**

```php
<?php
use yii\db\Migration;

class m260401_100400_add_banca_config extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%configurazione}}', ['chiave', 'valore', 'gruppo', 'descrizione', 'created_at', 'updated_at'], [
            ['banca_iban_fondazione', '', 'banca', 'IBAN della Fondazione per pagamenti SEPA', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
            ['banca_bic', '', 'banca', 'BIC/SWIFT della banca', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
            ['banca_creditor_id', '', 'banca', 'Creditor Identifier per SEPA', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
            ['banca_nome_istituto', '', 'banca', 'Nome istituto bancario', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%configurazione}}', ['chiave' => ['banca_iban_fondazione', 'banca_bic', 'banca_creditor_id', 'banca_nome_istituto']]);
    }
}
```

- [ ] **Step 6: Eseguire migrations**

```bash
docker exec -w /app albatros-php php yii migrate --interactive=0
```

Expected: 5 migrations applicate con successo.

- [ ] **Step 7: Commit**

```bash
git add migrations/m260401_*.php
git commit -m "db: aggiungere campi per presenze 3-stati, allegati contratto, status borsa, config banca"
```

---

## Task 2: Fix Validazione Form

**Files:**
- Modify: tutti i 31 `views/*/_form.php`
- Modify: model corrispondenti se necessario

- [ ] **Step 1: Audit form docente — campo provincia**

Leggere `views/docente/_form.php` e verificare che il campo `provincia` usi `$form->field($model, 'provincia')`. Se usa input HTML manuale, convertire a ActiveForm field. Verificare che in `models/Docente.php` la rule per `provincia` esista (`/^[A-Z]{2}$/`).

- [ ] **Step 2: Audit form docente — campo partita_iva**

Verificare che `partita_iva` usi `$form->field($model, 'partita_iva')`. Se usa input HTML manuale, convertire. Verificare rule in model (`/^\d{11}$/`).

- [ ] **Step 3: Audit form docente — tutti gli altri campi**

Verificare ogni campo del form docente: codice_fiscale, cognome, nome, data_nascita, luogo_nascita, indirizzo, cap, citta, email, telefono, titolo_studio, provenienza, tipo, iban. Tutti devono usare `$form->field()`.

- [ ] **Step 4: Verificare CSS validazione Tabler**

In `views/layouts/main.php` o in un file CSS custom, verificare che le classi di validazione Yii2 (`has-error`, `help-block`) siano compatibili con Tabler. Se Tabler usa `is-invalid` e `invalid-feedback`, aggiungere CSS bridge:

```css
.form-group.has-error .form-control { border-color: #d63939; }
.form-group.has-error .help-block { color: #d63939; display: block; }
```

- [ ] **Step 5: Audit sistematico di tutti gli altri 30 form**

Per ogni `_form.php` nella lista:
1. Aprire il file
2. Verificare che ogni input usi `$form->field($model, 'attributo')`
3. Se ci sono input HTML manuali senza binding ActiveForm, convertirli
4. Verificare che il model corrispondente abbia le `rules()` per ogni campo nel form
5. Verificare che i messaggi di errore siano in italiano

Form da verificare (30 file): sede, fonte-finanziamento, progetto, avviso, modulo, assegnazione, allievo, conto-corrente, tipo-documento, documentazione, calendario, iscrizione, presenza-allievo, valutazione-docente, rendicontazione-ore, pagamento, tag-categoria, tag-curriculum, istanza, contratto, criterio-valutazione, voce-spesa, borsa-studio, richiesta-fatturazione, tranche-finanziamento, movimento-spesa, criterio-valutazione-docente, utente, notifica, rimborso-spesa.

- [ ] **Step 6: Testare nel browser**

Aprire http://localhost:8000, andare su Personale → Docenti → Nuovo Docente. Lasciare i campi vuoti e cliccare Salva. Verificare:
- Messaggi di errore testuali sotto ogni campo required
- Bordo rosso sui campi con errore
- Compilare solo provincia con "X" (non valido) → deve mostrare "La provincia deve essere di 2 lettere maiuscole"

- [ ] **Step 7: Commit**

```bash
git add views/ components/
git commit -m "fix: correggere validazione form con feedback visivo su tutti i campi"
```

---

## Task 3: Ridenominazione Terminologia UI

**Files:**
- Modify: `views/layouts/menu.php`
- Modify: `views/avviso/index.php`, `view.php`, `_form.php`, `create.php`, `update.php`
- Modify: `views/modulo/index.php`, `view.php`, `_form.php`, `create.php`, `update.php`
- Modify: `views/progetto/view.php`
- Modify: `views/istanza/_form.php`
- Modify: `views/contratto/_form.php`, `view.php`
- Modify: `views/assegnazione/_form.php`, `view.php`, `index.php`
- Modify: tutte le viste che contengono "Avviso", "Bando", "Avvisi/Bandi"

- [ ] **Step 1: Cercare tutte le occorrenze nelle viste**

```bash
cd /Users/deduzzo/dev/albatros-manager-yii2
grep -rl "Avvis[io]" views/ --include="*.php" | sort
grep -rl "Bando\|Bandi" views/ --include="*.php" | sort
grep -rl "Moduli/Corsi\|Moduli / Corsi" views/ --include="*.php" | sort
```

- [ ] **Step 2: Rinominare nel menu**

File `views/layouts/menu.php`:
- "Avvisi/Bandi" → "Corsi"
- "Moduli/Corsi" → "Moduli/Insegnamenti"
- Le icone restano uguali
- Le route restano uguali (`avviso/index`, `modulo/index`)

- [ ] **Step 3: Rinominare nelle viste avviso**

In `views/avviso/index.php`:
- Titolo pagina: "Avvisi e Bandi" → "Corsi"
- Breadcrumb: "Avvisi" → "Corsi"
- Pulsante: "Nuovo Avviso" → "Nuovo Corso"

In `views/avviso/view.php`:
- Titolo: "Avviso: {codice}" → "Corso: {codice}"
- Card title: "Dettagli Avviso" → "Dettagli Corso"
- Label: "Moduli dell'Avviso" → "Moduli del Corso"

In `views/avviso/_form.php`:
- Titolo card se presente
- Nessuna modifica a `$form->field()` (i nomi attributo restano)

In `views/avviso/create.php`:
- Titolo: "Nuovo Avviso" → "Nuovo Corso"
- Breadcrumb: "Avvisi" → "Corsi", "Crea" resta

In `views/avviso/update.php`:
- Titolo: "Modifica Avviso" → "Modifica Corso"
- Breadcrumb: "Avvisi" → "Corsi"

- [ ] **Step 4: Rinominare nelle viste modulo**

In `views/modulo/index.php`:
- Se c'è "Moduli/Corsi" → "Moduli/Insegnamenti"

In `views/modulo/view.php`, `_form.php`, `create.php`, `update.php`:
- Riferimenti a "avviso" nelle label → "corso"
- Es: "Avviso di riferimento" → "Corso di riferimento"

- [ ] **Step 5: Rinominare nelle viste correlate**

Cercare e sostituire in tutti i file trovati allo Step 1:
- "dell'avviso" → "del corso"
- "all'avviso" → "al corso"
- "per avviso" → "per corso"
- "l'avviso" → "il corso"
- "un avviso" → "un corso"
- "degli avvisi" → "dei corsi"
- "Avviso" (standalone, come label colonna) → "Corso"

Attenzione: NON modificare nomi di variabili PHP (`$avviso`), nomi model (`Avviso`), nomi route (`avviso/view`). Solo stringhe visualizzate all'utente.

- [ ] **Step 6: Verificare nel browser**

Navigare tutte le pagine rinominate e verificare coerenza:
- Menu principale
- Lista corsi (ex avvisi)
- Dettaglio corso
- Form creazione corso
- Vista modulo (label "Corso" al posto di "Avviso")
- Form istanza
- Form/vista contratto

- [ ] **Step 7: Commit**

```bash
git add views/
git commit -m "refactor: rinominare Avviso/Bando → Corso e Moduli/Corsi → Moduli/Insegnamenti in tutta la UI"
```

---

## Task 4: Layout Vista Semplificata

**Files:**
- Create: `views/layouts/gestione.php`
- Create: `views/gestione/_sidebar.php`
- Modify: `config/web.php`
- Modify: `views/layouts/main.php` (aggiungere link)
- Modify: `views/layouts/menu.php` (aggiungere link)

- [ ] **Step 1: Aggiungere route in config/web.php**

In `config/web.php`, sezione `urlManager.rules`, aggiungere PRIMA delle route CRUD generiche:

```php
// Vista Semplificata
'gestione' => 'gestione/index',
'gestione/corso/<id:\d+>' => 'gestione/corso',
'gestione/corso/<id:\d+>/<section:[\w-]+>' => 'gestione/sezione',
'gestione/nuova-candidatura/<corso_id:\d+>' => 'gestione/nuova-candidatura',
```

- [ ] **Step 2: Creare layout gestione.php**

File `views/layouts/gestione.php`. Layout Tabler con:
- Header indigo/viola (accent diverso dal blu admin)
- Selettore corso dropdown in alto a destra
- Sidebar sinistra se un corso è selezionato
- Area contenuto principale
- Link "Passa a modalità avanzata" nel footer sidebar

```php
<?php
use app\components\assets\TablerAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $content */

TablerAsset::register($this);

$corsoId = Yii::$app->request->get('id');
$corsi = \app\models\Avviso::find()->orderBy(['titolo' => SORT_ASC])->all();
$corsoCorrente = $corsoId ? \app\models\Avviso::findOne($corsoId) : null;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        :root { --tblr-primary: #4263eb; }
        .navbar-gestione { background: linear-gradient(135deg, #4263eb 0%, #7048e8 100%); }
        .sidebar-gestione { min-width: 260px; max-width: 260px; }
        .sidebar-gestione .nav-link.active { background: rgba(66, 99, 235, 0.1); color: #4263eb; font-weight: 600; }
        .sidebar-gestione .nav-link .badge { font-size: 0.7rem; }
    </style>
</head>
<body class="layout-fluid">
<?php $this->beginBody() ?>
<div class="page">
    <!-- Header -->
    <header class="navbar navbar-expand-md navbar-gestione d-print-none">
        <div class="container-xl">
            <a href="<?= Url::to(['/gestione']) ?>" class="navbar-brand navbar-brand-autodark d-none-navbar-btn">
                <span class="text-white fw-bold">Albatros</span>
                <span class="badge bg-white text-indigo ms-2">Gestione Semplificata</span>
            </a>
            <div class="navbar-nav flex-row order-md-last">
                <!-- Selettore rapido corso -->
                <?php if (!empty($corsi)): ?>
                <div class="nav-item dropdown me-3">
                    <a class="nav-link text-white dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="ti ti-switch-horizontal me-1"></i>
                        <?= $corsoCorrente ? Html::encode($corsoCorrente->titolo) : 'Seleziona corso' ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <?php foreach ($corsi as $c): ?>
                        <a class="dropdown-menu-item <?= $corsoId == $c->id ? 'active' : '' ?>"
                           href="<?= Url::to(['/gestione/corso', 'id' => $c->id]) ?>">
                            <?= Html::encode($c->titolo) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Link admin avanzata -->
                <a href="<?= Url::to(['/site/index']) ?>" class="nav-link text-white" title="Modalità avanzata">
                    <i class="ti ti-settings"></i>
                </a>
                <!-- Logout -->
                <a href="<?= Url::to(['/site/logout']) ?>" class="nav-link text-white" data-method="post">
                    <i class="ti ti-logout"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <?php if ($corsoCorrente): ?>
        <div class="d-flex">
            <!-- Sidebar -->
            <?= $this->render('//gestione/_sidebar', ['corso' => $corsoCorrente]) ?>
            <!-- Content -->
            <div class="flex-fill p-3">
                <div class="container-xl">
                    <?= $content ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="page-body">
            <div class="container-xl">
                <?= $content ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

- [ ] **Step 3: Creare sidebar _sidebar.php**

File `views/gestione/_sidebar.php`:

```php
<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var app\models\Avviso $corso */

$currentSection = Yii::$app->request->get('section', 'panoramica');
$corsoId = $corso->id;

$sections = [
    'panoramica' => ['label' => 'Panoramica', 'icon' => 'ti-dashboard'],
    'piano-formativo' => ['label' => 'Piano Formativo', 'icon' => 'ti-book'],
    'personale' => ['label' => 'Personale', 'icon' => 'ti-users'],
    'studenti' => ['label' => 'Studenti', 'icon' => 'ti-school'],
    'calendario' => ['label' => 'Calendario', 'icon' => 'ti-calendar'],
    'contratti' => ['label' => 'Contratti', 'icon' => 'ti-file-text'],
    'borse-studio' => ['label' => 'Borse di Studio', 'icon' => 'ti-award'],
    'import-export' => ['label' => 'Import / Export', 'icon' => 'ti-database-import'],
];
?>
<aside class="sidebar-gestione bg-white border-end d-flex flex-column py-3 d-print-none">
    <div class="px-3 mb-3">
        <div class="fw-bold text-truncate"><?= Html::encode($corso->titolo) ?></div>
        <small class="text-muted"><?= Html::encode($corso->codice ?? '') ?></small>
    </div>
    <nav class="nav flex-column px-2">
        <?php foreach ($sections as $key => $sec): ?>
        <a class="nav-link rounded px-3 py-2 mb-1 <?= $currentSection === $key ? 'active' : 'text-muted' ?>"
           href="<?= Url::to(['/gestione/sezione', 'id' => $corsoId, 'section' => $key]) ?>">
            <i class="<?= $sec['icon'] ?> me-2"></i>
            <?= $sec['label'] ?>
            <?php
            // Badge alert (sarà popolato da AlertWidget nel Task 12)
            $badgeCount = Yii::$app->params['alertCounts'][$corsoId][$key] ?? 0;
            if ($badgeCount > 0): ?>
                <span class="badge bg-red-lt ms-auto"><?= $badgeCount ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="mt-auto px-3 pt-3 border-top">
        <a href="<?= Url::to(['/site/index']) ?>" class="btn btn-ghost-indigo btn-sm w-100">
            <i class="ti ti-arrow-left me-1"></i> Modalità avanzata
        </a>
    </div>
</aside>
```

- [ ] **Step 4: Aggiungere link nel menu admin**

In `views/layouts/menu.php`, aggiungere come prima voce del menu (prima di Dashboard):

```php
<li class="nav-item">
    <a class="nav-link" href="<?= Url::to(['/gestione']) ?>">
        <span class="nav-link-icon"><i class="ti ti-layout-dashboard"></i></span>
        <span class="nav-link-title">Gestione Semplificata</span>
    </a>
</li>
```

- [ ] **Step 5: Commit**

```bash
git add views/layouts/ views/gestione/ config/web.php
git commit -m "feat: creare layout e sidebar per vista semplificata gestione"
```

---

## Task 5: GestioneController + Home Griglia Corsi

**Files:**
- Create: `controllers/GestioneController.php`
- Create: `views/gestione/index.php`
- Create: `views/gestione/_corso-card.php`

- [ ] **Step 1: Creare GestioneController**

```php
<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Avviso;
use app\models\Modulo;
use app\models\Istanza;
use app\models\Assegnazione;
use app\models\Contratto;
use app\models\Calendario;
use app\models\Iscrizione;
use app\models\BorsaStudio;
use app\models\PresenzaAllievo;

class GestioneController extends Controller
{
    public $layout = 'gestione';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    /**
     * Home: griglia card corsi
     */
    public function actionIndex()
    {
        $corsi = Avviso::find()
            ->with(['progetto', 'progetto.fonteFinanziamento'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $stats = [];
        foreach ($corsi as $corso) {
            $moduliTotali = Modulo::find()->where(['avviso_id' => $corso->id])->count();
            $moduliAssegnati = Modulo::find()
                ->alias('m')
                ->innerJoin('{{%assegnazione}} a', 'a.modulo_id = m.id AND a.deleted_at IS NULL')
                ->where(['m.avviso_id' => $corso->id, 'm.deleted_at' => null])
                ->count();
            $docenti = Assegnazione::find()
                ->alias('a')
                ->innerJoin('{{%modulo}} m', 'm.id = a.modulo_id AND m.deleted_at IS NULL')
                ->where(['m.avviso_id' => $corso->id, 'a.deleted_at' => null])
                ->count('DISTINCT a.docente_codice_fiscale');
            $studenti = Iscrizione::find()
                ->alias('i')
                ->innerJoin('{{%modulo}} m', 'm.id = i.modulo_id AND m.deleted_at IS NULL')
                ->where(['m.avviso_id' => $corso->id, 'i.deleted_at' => null])
                ->count('DISTINCT i.allievo_codice_fiscale');

            $stats[$corso->id] = [
                'moduli_totali' => $moduliTotali,
                'moduli_assegnati' => $moduliAssegnati,
                'docenti' => $docenti,
                'studenti' => $studenti,
                'percentuale' => $moduliTotali > 0 ? round($moduliAssegnati / $moduliTotali * 100) : 0,
            ];
        }

        return $this->render('index', [
            'corsi' => $corsi,
            'stats' => $stats,
        ]);
    }

    /**
     * Dashboard corso (panoramica)
     */
    public function actionCorso($id)
    {
        return $this->actionSezione($id, 'panoramica');
    }

    /**
     * Sezione generica del corso
     */
    public function actionSezione($id, $section = 'panoramica')
    {
        $corso = Avviso::findOne($id);
        if (!$corso) {
            throw new \yii\web\NotFoundHttpException('Corso non trovato.');
        }

        $viewMap = [
            'panoramica' => 'corso',
            'piano-formativo' => 'piano-formativo',
            'personale' => 'personale',
            'studenti' => 'studenti',
            'calendario' => 'calendario',
            'contratti' => 'contratti',
            'borse-studio' => 'borse-studio',
            'import-export' => 'import-export',
        ];

        $view = $viewMap[$section] ?? 'corso';
        $data = $this->loadSectionData($corso, $section);

        return $this->render($view, array_merge(['corso' => $corso], $data));
    }

    /**
     * Carica dati specifici per sezione
     */
    protected function loadSectionData($corso, $section)
    {
        $corsoId = $corso->id;

        switch ($section) {
            case 'panoramica':
                return $this->loadPanoramica($corsoId);
            case 'piano-formativo':
                return ['moduli' => Modulo::find()->where(['avviso_id' => $corsoId])->with(['assegnazioni', 'assegnazioni.docente'])->all()];
            case 'personale':
                return ['assegnazioni' => Assegnazione::find()->alias('a')->innerJoin('{{%modulo}} m', 'm.id = a.modulo_id AND m.deleted_at IS NULL')->where(['m.avviso_id' => $corsoId, 'a.deleted_at' => null])->with(['docente', 'modulo', 'contratto'])->all()];
            case 'studenti':
                return ['iscrizioni' => Iscrizione::find()->alias('i')->innerJoin('{{%modulo}} m', 'm.id = i.modulo_id AND m.deleted_at IS NULL')->where(['m.avviso_id' => $corsoId, 'i.deleted_at' => null])->with(['allievo'])->all()];
            case 'calendario':
                return ['lezioni' => Calendario::find()->alias('c')->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')->where(['m.avviso_id' => $corsoId, 'c.deleted_at' => null])->with(['modulo', 'docente'])->orderBy(['c.data' => SORT_DESC])->all()];
            case 'contratti':
                return ['contratti' => Contratto::find()->alias('co')->innerJoin('{{%assegnazione}} a', 'a.id = co.assegnazione_id AND a.deleted_at IS NULL')->innerJoin('{{%modulo}} m', 'm.id = a.modulo_id AND m.deleted_at IS NULL')->where(['m.avviso_id' => $corsoId, 'co.deleted_at' => null])->with(['assegnazione', 'assegnazione.docente', 'assegnazione.modulo'])->all()];
            case 'borse-studio':
                return ['borse' => BorsaStudio::find()->where(['progetto_id' => $corso->progetto_id])->with(['allievo'])->all()];
            default:
                return [];
        }
    }

    protected function loadPanoramica($corsoId)
    {
        // Docenti assegnati senza contratto
        $nonContrattualizzati = Assegnazione::find()
            ->alias('a')
            ->innerJoin('{{%modulo}} m', 'm.id = a.modulo_id AND m.deleted_at IS NULL')
            ->leftJoin('{{%contratto}} co', 'co.assegnazione_id = a.id AND co.deleted_at IS NULL')
            ->where(['m.avviso_id' => $corsoId, 'a.deleted_at' => null, 'co.id' => null])
            ->with(['docente', 'modulo'])
            ->all();

        // Lezioni con presenze non acquisite
        $presenzeMancanti = Calendario::find()
            ->alias('c')
            ->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')
            ->where(['m.avviso_id' => $corsoId, 'c.deleted_at' => null])
            ->andWhere(['<', 'c.data', date('Y-m-d')])
            ->andWhere(['or', ['c.docente_presente' => null], ['c.status' => 'programmata']])
            ->with(['modulo', 'docente'])
            ->orderBy(['c.data' => SORT_DESC])
            ->all();

        return [
            'nonContrattualizzati' => $nonContrattualizzati,
            'presenzeMancanti' => $presenzeMancanti,
        ];
    }
}
```

- [ ] **Step 2: Creare vista home index.php**

File `views/gestione/index.php`:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Avviso[] $corsi */
/** @var array $stats */

$this->title = 'Gestione Semplificata';
?>
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">I tuoi Corsi</h2>
            <div class="text-muted">Seleziona un corso per gestirlo</div>
        </div>
    </div>
</div>

<div class="row row-cards">
    <?php foreach ($corsi as $corso): ?>
        <?= $this->render('_corso-card', [
            'corso' => $corso,
            'stat' => $stats[$corso->id] ?? [],
        ]) ?>
    <?php endforeach; ?>
</div>

<?php if (empty($corsi)): ?>
<div class="empty">
    <div class="empty-icon"><i class="ti ti-folder-off" style="font-size: 3rem;"></i></div>
    <p class="empty-title">Nessun corso presente</p>
    <p class="empty-subtitle text-muted">Crea un nuovo corso dalla <a href="<?= Url::to(['/avviso/create']) ?>">modalità avanzata</a>.</p>
</div>
<?php endif; ?>
```

- [ ] **Step 3: Creare partial _corso-card.php**

File `views/gestione/_corso-card.php`:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\models\Avviso $corso */
/** @var array $stat */

$colore = $corso->getTablerColor() ?: 'indigo';
$percentuale = $stat['percentuale'] ?? 0;
$statusLabels = [
    'bozza' => ['label' => 'Bozza', 'color' => 'bg-default-lt'],
    'pubblicato' => ['label' => 'Pubblicato', 'color' => 'bg-green-lt'],
    'chiuso' => ['label' => 'Chiuso', 'color' => 'bg-red-lt'],
    'annullato' => ['label' => 'Annullato', 'color' => 'bg-orange-lt'],
];
$statusInfo = $statusLabels[$corso->status] ?? ['label' => $corso->status, 'color' => 'bg-default-lt'];
?>
<div class="col-md-6 col-lg-4">
    <a href="<?= Url::to(['/gestione/corso', 'id' => $corso->id]) ?>" class="card card-link card-link-pop">
        <div class="card-status-top bg-<?= $colore ?>"></div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div>
                    <h3 class="card-title mb-1"><?= Html::encode($corso->titolo) ?></h3>
                    <div class="text-muted"><?= Html::encode($corso->codice ?? '') ?></div>
                </div>
                <span class="badge <?= $statusInfo['color'] ?> ms-auto"><?= $statusInfo['label'] ?></span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-4 text-center">
                    <div class="fw-bold"><?= $stat['moduli_totali'] ?? 0 ?></div>
                    <small class="text-muted">Moduli</small>
                </div>
                <div class="col-4 text-center">
                    <div class="fw-bold"><?= $stat['docenti'] ?? 0 ?></div>
                    <small class="text-muted">Docenti</small>
                </div>
                <div class="col-4 text-center">
                    <div class="fw-bold"><?= $stat['studenti'] ?? 0 ?></div>
                    <small class="text-muted">Studenti</small>
                </div>
            </div>

            <div class="mb-1 d-flex justify-content-between">
                <small>Moduli assegnati</small>
                <small><?= $percentuale ?>%</small>
            </div>
            <div class="progress progress-sm">
                <div class="progress-bar bg-<?= $colore ?>" style="width: <?= $percentuale ?>%"></div>
            </div>
        </div>
    </a>
</div>
```

- [ ] **Step 4: Commit**

```bash
git add controllers/GestioneController.php views/gestione/
git commit -m "feat: creare GestioneController con home griglia corsi"
```

---

## Task 6: Viste Sezioni Corso (Semplificata)

**Files:**
- Create: `views/gestione/corso.php`
- Create: `views/gestione/piano-formativo.php`
- Create: `views/gestione/personale.php`
- Create: `views/gestione/studenti.php`
- Create: `views/gestione/calendario.php`
- Create: `views/gestione/contratti.php`
- Create: `views/gestione/borse-studio.php`
- Create: `views/gestione/import-export.php`

- [ ] **Step 1: Creare vista panoramica corso.php**

File `views/gestione/corso.php`:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\widgets\AlertWidget;

/** @var app\models\Avviso $corso */
/** @var app\models\Assegnazione[] $nonContrattualizzati */
/** @var app\models\Calendario[] $presenzeMancanti */

$this->title = 'Panoramica - ' . $corso->titolo;
?>
<div class="page-header d-print-none mb-4">
    <h2 class="page-title">Panoramica</h2>
</div>

<!-- Alert -->
<?php if (!empty($nonContrattualizzati)): ?>
<div class="alert alert-warning">
    <div class="d-flex">
        <i class="ti ti-file-alert me-2" style="font-size: 1.5rem;"></i>
        <div>
            <h4 class="alert-title">Docenti da contrattualizzare (<?= count($nonContrattualizzati) ?>)</h4>
            <div class="text-muted">
                <?php foreach ($nonContrattualizzati as $ass): ?>
                    <div><?= Html::encode($ass->docente->cognome . ' ' . $ass->docente->nome) ?> — <?= Html::encode($ass->modulo->titolo) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($presenzeMancanti)): ?>
<div class="alert alert-danger">
    <div class="d-flex">
        <i class="ti ti-clock-exclamation me-2" style="font-size: 1.5rem;"></i>
        <div>
            <h4 class="alert-title">Presenze non acquisite (<?= count($presenzeMancanti) ?>)</h4>
            <div class="text-muted">
                <?php foreach (array_slice($presenzeMancanti, 0, 5) as $lez): ?>
                    <div><?= Yii::$app->formatter->asDate($lez->data, 'short') ?> — <?= Html::encode($lez->modulo->titolo) ?></div>
                <?php endforeach; ?>
                <?php if (count($presenzeMancanti) > 5): ?>
                    <div class="mt-1"><em>...e altre <?= count($presenzeMancanti) - 5 ?> lezioni</em></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stat cards -->
<div class="row row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <span class="bg-blue-lt avatar me-3"><i class="ti ti-book"></i></span>
                    <div>
                        <div class="fw-bold"><?= \app\models\Modulo::find()->where(['avviso_id' => $corso->id])->count() ?></div>
                        <div class="text-muted">Moduli</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <span class="bg-green-lt avatar me-3"><i class="ti ti-users"></i></span>
                    <div>
                        <div class="fw-bold"><?= \app\models\Assegnazione::find()->alias('a')->innerJoin('{{%modulo}} m', 'm.id = a.modulo_id AND m.deleted_at IS NULL')->where(['m.avviso_id' => $corso->id, 'a.deleted_at' => null])->count('DISTINCT a.docente_codice_fiscale') ?></div>
                        <div class="text-muted">Docenti</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <span class="bg-purple-lt avatar me-3"><i class="ti ti-school"></i></span>
                    <div>
                        <div class="fw-bold"><?= \app\models\Iscrizione::find()->alias('i')->innerJoin('{{%modulo}} m', 'm.id = i.modulo_id AND m.deleted_at IS NULL')->where(['m.avviso_id' => $corso->id, 'i.deleted_at' => null])->count('DISTINCT i.allievo_codice_fiscale') ?></div>
                        <div class="text-muted">Studenti</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <span class="bg-orange-lt avatar me-3"><i class="ti ti-calendar"></i></span>
                    <div>
                        <div class="fw-bold"><?= \app\models\Calendario::find()->alias('c')->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')->where(['m.avviso_id' => $corso->id, 'c.deleted_at' => null])->count() ?></div>
                        <div class="text-muted">Lezioni</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Link rapidi -->
<div class="card">
    <div class="card-header"><h3 class="card-title">Azioni rapide</h3></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-auto">
                <a href="<?= Url::to(['/gestione/sezione', 'id' => $corso->id, 'section' => 'piano-formativo']) ?>" class="btn btn-outline-indigo">
                    <i class="ti ti-book me-1"></i> Piano Formativo
                </a>
            </div>
            <div class="col-auto">
                <a href="<?= Url::to(['/gestione/nuova-candidatura', 'corso_id' => $corso->id]) ?>" class="btn btn-outline-green">
                    <i class="ti ti-user-plus me-1"></i> Nuova Candidatura
                </a>
            </div>
            <div class="col-auto">
                <a href="<?= Url::to(['/avviso/view', 'id' => $corso->id]) ?>" class="btn btn-outline-default">
                    <i class="ti ti-settings me-1"></i> Dettaglio avanzato
                </a>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Creare vista piano-formativo.php**

File `views/gestione/piano-formativo.php`:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\models\Avviso $corso */
/** @var app\models\Modulo[] $moduli */

$this->title = 'Piano Formativo - ' . $corso->titolo;
?>
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Piano Formativo</h2>
            <div class="text-muted"><?= count($moduli) ?> moduli totali</div>
        </div>
        <div class="col-auto">
            <a href="<?= Url::to(['/gestione/nuova-candidatura', 'corso_id' => $corso->id]) ?>" class="btn btn-indigo">
                <i class="ti ti-user-plus me-1"></i> Nuova Candidatura
            </a>
        </div>
    </div>
</div>

<form method="post" action="<?= Url::to(['/gestione/metti-a-bando', 'corso_id' => $corso->id]) ?>">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Codice</th>
                        <th>Titolo</th>
                        <th>Ore</th>
                        <th>Compenso/h</th>
                        <th>Docente</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moduli as $modulo): ?>
                    <?php
                        $assegnazione = $modulo->assegnazioni[0] ?? null;
                        $docente = $assegnazione ? $assegnazione->docente : null;
                        $scoperto = !$docente;
                    ?>
                    <tr>
                        <td>
                            <?php if ($scoperto): ?>
                            <input type="checkbox" class="form-check-input modulo-check" name="moduli_ids[]" value="<?= $modulo->id ?>">
                            <?php endif; ?>
                        </td>
                        <td><?= Html::encode($modulo->codice ?? '-') ?></td>
                        <td>
                            <a href="<?= Url::to(['/modulo/view', 'id' => $modulo->id]) ?>">
                                <?= Html::encode($modulo->titolo) ?>
                            </a>
                        </td>
                        <td><?= $modulo->ore_totali ?></td>
                        <td><?= $modulo->compenso_orario ? '€' . number_format($modulo->compenso_orario, 2) : '-' ?></td>
                        <td>
                            <?php if ($docente): ?>
                                <a href="<?= Url::to(['/docente/view', 'cf' => $docente->codice_fiscale]) ?>">
                                    <?= Html::encode($docente->cognome . ' ' . $docente->nome) ?>
                                </a>
                            <?php else: ?>
                                <span class="badge bg-orange-lt">Scoperto</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($assegnazione): ?>
                                <span class="badge bg-green-lt">Assegnato</span>
                            <?php else: ?>
                                <span class="badge bg-default-lt">Da assegnare</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= Url::to(['/modulo/view', 'id' => $modulo->id]) ?>" class="btn btn-ghost-indigo btn-sm">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            <button type="submit" class="btn btn-warning" id="btn-bando" disabled>
                <i class="ti ti-speakerphone me-1"></i> Metti a bando selezionati
            </button>
        </div>
    </div>
</form>

<?php
$js = <<<JS
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.modulo-check').forEach(cb => cb.checked = this.checked);
    toggleBando();
});
document.querySelectorAll('.modulo-check').forEach(cb => cb.addEventListener('change', toggleBando));
function toggleBando() {
    const checked = document.querySelectorAll('.modulo-check:checked').length;
    document.getElementById('btn-bando').disabled = checked === 0;
}
JS;
$this->registerJs($js);
?>
```

- [ ] **Step 3: Creare viste personale.php, studenti.php, calendario.php, contratti.php, borse-studio.php, import-export.php**

Ogni vista segue lo stesso pattern: titolo sezione, tabella con dati filtrati per il corso, link alle azioni dei controller originali con `returnUrl`. Queste viste sono tabelle semplici che mostrano i dati caricati dal `GestioneController::loadSectionData()`.

**`views/gestione/personale.php`**: Tabella assegnazioni con colonne Docente, Modulo, Compenso/h, Ore, Stato Contratto. Link a dettaglio docente e contratto.

**`views/gestione/studenti.php`**: Tabella iscrizioni con colonne Studente, CF, Stato, % Frequenza. Link a dettaglio allievo.

**`views/gestione/calendario.php`**: Tabella lezioni con colonne Data, Modulo, Docente, Orario, Stato, Presenze (badge 3 colori). Link a dettaglio calendario.

**`views/gestione/contratti.php`**: Tabella contratti con colonne Docente, Modulo, Tipo, Stato, Importo. Pulsanti Genera/Upload/Allega. Link export consulente filtrato.

**`views/gestione/borse-studio.php`**: Tabella borse con colonne Studente, Tipologia, ISEE, Importo, Stato, % Frequenza. Pulsante export SEPA.

**`views/gestione/import-export.php`**: Card per ogni tipo di import/export disponibile con pulsanti che puntano alle action esistenti.

Per ogni vista, implementare come tabella HTML statica con i dati passati dal controller. Usare le stesse classi Tabler (card, table-responsive, badge) delle viste esistenti. I link di azione rimandano ai controller originali con parametro `returnUrl` per tornare alla gestione:

```php
<?= Html::a('Modifica', ['/contratto/update', 'id' => $co->id, 'returnUrl' => Url::current()], ['class' => 'btn btn-ghost-indigo btn-sm']) ?>
```

- [ ] **Step 4: Commit**

```bash
git add views/gestione/
git commit -m "feat: creare viste sezioni corso per gestione semplificata"
```

---

## Task 7: Area Tutor — Presenze per Lezione

**Files:**
- Modify: `controllers/DocenteAreaController.php`
- Modify: `models/PresenzaAllievo.php`
- Modify: `models/Calendario.php`
- Create: `views/docente-area/presenze.php`
- Create: `views/docente-area/_presenze-lezione.php`
- Modify: `views/docente-area/index.php`
- Modify: `views/layouts/docente.php`
- Modify: `config/web.php`

- [ ] **Step 1: Aggiornare model PresenzaAllievo**

In `models/PresenzaAllievo.php`, modificare la rule del campo `presente`:

Sostituire:
```php
[['presente', 'giustificato'], 'boolean'],
```

Con:
```php
[['presente'], 'in', 'range' => [null, 0, 1], 'strict' => false],
[['giustificato'], 'boolean'],
```

Aggiornare `getPresenzaBadgeClass()`:
```php
public function getPresenzaBadgeClass()
{
    if ($this->presente === null) {
        return 'bg-default-lt';
    }
    if ($this->presente) {
        return $this->giustificato ? 'bg-orange-lt' : 'bg-green-lt';
    }
    return 'bg-red-lt';
}

public function getPresenzaLabel()
{
    if ($this->presente === null) {
        return 'Non acquisito';
    }
    if ($this->presente) {
        return 'Presente';
    }
    return $this->giustificato ? 'Assente Giustificato' : 'Assente';
}
```

- [ ] **Step 2: Aggiornare model Calendario**

In `models/Calendario.php`, aggiungere rules per i nuovi campi:

```php
[['docente_presente'], 'in', 'range' => [null, 0, 1], 'strict' => false],
[['docente_ore_effettive'], 'number', 'min' => 0],
```

Aggiungere attributeLabels:
```php
'docente_presente' => 'Presenza Docente',
'docente_ore_effettive' => 'Ore Effettive Docente',
```

Aggiungere metodo:
```php
public function getDocentePresenzaLabel()
{
    if ($this->docente_presente === null) return 'Non acquisito';
    return $this->docente_presente ? 'Presente' : 'Assente';
}

public function getDocentePresenzaBadgeClass()
{
    if ($this->docente_presente === null) return 'bg-default-lt';
    return $this->docente_presente ? 'bg-green-lt' : 'bg-red-lt';
}
```

- [ ] **Step 3: Aggiungere route in config/web.php**

```php
'area-docenti/presenze' => 'docente-area/presenze',
'area-docenti/presenze/<calendario_id:\d+>' => 'docente-area/presenze-lezione',
'area-docenti/salva-presenze/<calendario_id:\d+>' => 'docente-area/salva-presenze',
```

- [ ] **Step 4: Aggiungere actions in DocenteAreaController**

Aggiungere `'presenze', 'presenze-lezione', 'salva-presenze'` alla lista action protette nel behavior AccessControl.

```php
/**
 * Lista lezioni per tutor con alert presenze mancanti
 */
public function actionPresenze()
{
    $docente = Yii::$app->docenteUser->identity->docente;

    // Lezioni dei moduli dove il docente è tutor (assegnato con tipo tutor)
    // oppure docente assegnato
    $lezioni = Calendario::find()
        ->alias('c')
        ->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')
        ->innerJoin('{{%assegnazione}} a', 'a.modulo_id = m.id AND a.deleted_at IS NULL')
        ->where(['a.docente_codice_fiscale' => $docente->codice_fiscale, 'c.deleted_at' => null])
        ->with(['modulo', 'docente', 'presenze'])
        ->orderBy(['c.data' => SORT_DESC])
        ->all();

    return $this->render('presenze', [
        'lezioni' => $lezioni,
        'docente' => $docente,
    ]);
}

/**
 * Form presenze per singola lezione
 */
public function actionPresenzeLezione($calendario_id)
{
    $calendario = Calendario::findOne($calendario_id);
    if (!$calendario) {
        throw new \yii\web\NotFoundHttpException('Lezione non trovata.');
    }

    // Studenti iscritti al modulo
    $iscrizioni = \app\models\Iscrizione::find()
        ->where(['modulo_id' => $calendario->modulo_id, 'deleted_at' => null])
        ->with(['allievo'])
        ->all();

    // Presenze esistenti
    $presenzeEsistenti = PresenzaAllievo::find()
        ->where(['calendario_id' => $calendario_id, 'deleted_at' => null])
        ->indexBy('allievo_codice_fiscale')
        ->all();

    return $this->render('_presenze-lezione', [
        'calendario' => $calendario,
        'iscrizioni' => $iscrizioni,
        'presenzeEsistenti' => $presenzeEsistenti,
    ]);
}

/**
 * Salva presenze (POST)
 */
public function actionSalvaPresenze($calendario_id)
{
    $calendario = Calendario::findOne($calendario_id);
    if (!$calendario) {
        throw new \yii\web\NotFoundHttpException('Lezione non trovata.');
    }

    $post = Yii::$app->request->post();
    $transaction = Yii::$app->db->beginTransaction();

    try {
        // Presenza docente
        $calendario->docente_presente = $post['docente_presente'] ?? null;
        $calendario->docente_ore_effettive = $post['docente_ore_effettive'] ?? null;
        $calendario->save(false);

        // Presenze studenti
        $studenti = $post['studenti'] ?? [];
        foreach ($studenti as $cf => $data) {
            $presenza = PresenzaAllievo::find()
                ->where(['calendario_id' => $calendario_id, 'allievo_codice_fiscale' => $cf, 'deleted_at' => null])
                ->one();

            if (!$presenza) {
                $presenza = new PresenzaAllievo();
                $presenza->calendario_id = $calendario_id;
                $presenza->allievo_codice_fiscale = $cf;
            }

            $val = $data['presente'] ?? '';
            $presenza->presente = $val === '' ? null : (int)$val;
            $presenza->ore_presenza = $data['ore_presenza'] ?? null;
            $presenza->note = $data['note'] ?? '';
            $presenza->save(false);
        }

        $transaction->commit();
        Yii::$app->session->setFlash('success', 'Presenze salvate con successo.');
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', 'Errore nel salvataggio: ' . $e->getMessage());
    }

    return $this->redirect(['presenze']);
}
```

- [ ] **Step 5: Creare vista presenze.php**

File `views/docente-area/presenze.php`:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\models\Calendario[] $lezioni */
/** @var app\models\Docente $docente */

$this->title = 'Gestione Presenze';
?>
<div class="page-header d-print-none mb-4">
    <h2 class="page-title">Gestione Presenze</h2>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Modulo</th>
                    <th>Docente</th>
                    <th>Orario</th>
                    <th>Presenze Studenti</th>
                    <th>Presenza Docente</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lezioni as $lez): ?>
                <?php
                    $totPresenze = count($lez->presenze);
                    $acquisite = 0;
                    foreach ($lez->presenze as $p) {
                        if ($p->presente !== null) $acquisite++;
                    }
                    $mancanti = $lez->data < date('Y-m-d') && ($lez->docente_presente === null || $acquisite < $totPresenze);
                ?>
                <tr class="<?= $mancanti ? 'bg-red-lt' : '' ?>">
                    <td><?= Yii::$app->formatter->asDate($lez->data, 'short') ?></td>
                    <td><?= Html::encode($lez->modulo->titolo ?? '-') ?></td>
                    <td><?= Html::encode($lez->docente ? $lez->docente->cognome . ' ' . $lez->docente->nome : '-') ?></td>
                    <td><?= substr($lez->ora_inizio, 0, 5) ?> - <?= substr($lez->ora_fine, 0, 5) ?></td>
                    <td>
                        <?php if ($totPresenze > 0): ?>
                            <span class="badge <?= $acquisite === $totPresenze ? 'bg-green-lt' : 'bg-orange-lt' ?>">
                                <?= $acquisite ?>/<?= $totPresenze ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-default-lt">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $lez->getDocentePresenzaBadgeClass() ?>">
                            <?= $lez->getDocentePresenzaLabel() ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= Url::to(['/area-docenti/presenze-lezione', 'calendario_id' => $lez->id]) ?>"
                           class="btn btn-sm <?= $mancanti ? 'btn-red' : 'btn-ghost-indigo' ?>">
                            <i class="ti ti-edit me-1"></i> Compila
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
```

- [ ] **Step 6: Creare vista _presenze-lezione.php**

File `views/docente-area/_presenze-lezione.php`:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\models\Calendario $calendario */
/** @var app\models\Iscrizione[] $iscrizioni */
/** @var app\models\PresenzaAllievo[] $presenzeEsistenti */

$this->title = 'Presenze - ' . Yii::$app->formatter->asDate($calendario->data, 'long');
?>
<div class="page-header d-print-none mb-4">
    <h2 class="page-title">Presenze del <?= Yii::$app->formatter->asDate($calendario->data, 'long') ?></h2>
    <div class="text-muted">
        <?= Html::encode($calendario->modulo->titolo ?? '') ?> |
        <?= substr($calendario->ora_inizio, 0, 5) ?> - <?= substr($calendario->ora_fine, 0, 5) ?> |
        <?= Html::encode($calendario->aula ?? '') ?>
    </div>
</div>

<form method="post" action="<?= Url::to(['/area-docenti/salva-presenze', 'calendario_id' => $calendario->id]) ?>">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <!-- Presenza Docente -->
    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-user me-2"></i>Docente: <?= Html::encode($calendario->docente ? $calendario->docente->cognome . ' ' . $calendario->docente->nome : '-') ?></h3></div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <?php $dp = $calendario->docente_presente; ?>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="docente_presente" value="" <?= $dp === null ? 'checked' : '' ?>>
                        <span class="form-check-label text-muted">Non acquisito</span>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="docente_presente" value="1" <?= $dp !== null && $dp == 1 ? 'checked' : '' ?>>
                        <span class="form-check-label text-green">Presente</span>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="docente_presente" value="0" <?= $dp !== null && $dp == 0 ? 'checked' : '' ?>>
                        <span class="form-check-label text-red">Assente</span>
                    </label>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Ore effettive</label>
                    <input type="number" step="0.5" min="0" class="form-control form-control-sm" name="docente_ore_effettive"
                           value="<?= $calendario->docente_ore_effettive ?? $calendario->ore ?>" style="width: 100px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Presenze Studenti -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-school me-2"></i>Studenti (<?= count($iscrizioni) ?>)</h3></div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Studente</th>
                        <th>Codice Fiscale</th>
                        <th>Presenza</th>
                        <th>Ore</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($iscrizioni as $iscr): ?>
                    <?php
                        $cf = $iscr->allievo_codice_fiscale;
                        $p = $presenzeEsistenti[$cf] ?? null;
                        $pVal = $p ? $p->presente : null;
                    ?>
                    <tr>
                        <td><?= Html::encode($iscr->allievo->cognome . ' ' . $iscr->allievo->nome) ?></td>
                        <td><code><?= Html::encode($cf) ?></code></td>
                        <td>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="studenti[<?= $cf ?>][presente]" value="" <?= $pVal === null ? 'checked' : '' ?>>
                                <span class="form-check-label text-muted">N/A</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="studenti[<?= $cf ?>][presente]" value="1" <?= $pVal !== null && $pVal == 1 ? 'checked' : '' ?>>
                                <span class="form-check-label text-green">P</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="studenti[<?= $cf ?>][presente]" value="0" <?= $pVal !== null && $pVal == 0 ? 'checked' : '' ?>>
                                <span class="form-check-label text-red">A</span>
                            </label>
                        </td>
                        <td>
                            <input type="number" step="0.5" min="0" class="form-control form-control-sm"
                                   name="studenti[<?= $cf ?>][ore_presenza]"
                                   value="<?= $p ? $p->ore_presenza : $calendario->ore ?>" style="width: 80px;">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   name="studenti[<?= $cf ?>][note]"
                                   value="<?= Html::encode($p ? $p->note : '') ?>">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-indigo">
                <i class="ti ti-device-floppy me-1"></i> Salva presenze
            </button>
            <a href="<?= Url::to(['/area-docenti/presenze']) ?>" class="btn btn-ghost-default ms-2">Annulla</a>
        </div>
    </div>
</form>
```

- [ ] **Step 7: Aggiornare dashboard docente per tutor**

In `views/docente-area/index.php`, aggiungere sezione condizionale per tutor. Dopo il recupero del docente, verificare `$docente->tipo === 'tutor'` e mostrare card aggiuntive:

```php
<?php if ($docente->tipo === \app\models\Docente::TIPO_TUTOR): ?>
<!-- Sezione Tutor -->
<div class="col-12">
    <div class="card bg-indigo-lt">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <i class="ti ti-clipboard-check me-3" style="font-size: 2rem;"></i>
                <div>
                    <h3 class="mb-0">Gestione Presenze</h3>
                    <div class="text-muted">Inserisci e verifica le presenze dei tuoi corsi</div>
                </div>
                <a href="<?= Url::to(['/area-docenti/presenze']) ?>" class="btn btn-indigo ms-auto">
                    Vai alle presenze
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
```

- [ ] **Step 8: Aggiornare menu docente per tutor**

In `views/layouts/docente.php`, nel menu laterale, aggiungere voce "Gestione Presenze" condizionale:

```php
<?php
$docente = Yii::$app->docenteUser->identity->docente ?? null;
if ($docente && $docente->tipo === \app\models\Docente::TIPO_TUTOR): ?>
<li class="nav-item">
    <a class="nav-link" href="<?= Url::to(['/area-docenti/presenze']) ?>">
        <span class="nav-link-icon"><i class="ti ti-clipboard-check"></i></span>
        <span class="nav-link-title">Gestione Presenze</span>
    </a>
</li>
<?php endif; ?>
```

- [ ] **Step 9: Commit**

```bash
git add controllers/DocenteAreaController.php models/PresenzaAllievo.php models/Calendario.php views/docente-area/ views/layouts/docente.php config/web.php
git commit -m "feat: area tutor con presenze per lezione a 3 stati (presente/assente/non acquisito)"
```

---

## Task 8: Borse di Studio — Miglioramenti

**Files:**
- Modify: `models/BorsaStudio.php`
- Modify: `controllers/BorsaStudioController.php`
- Modify: `views/borsa-studio/view.php`
- Modify: `views/borsa-studio/index.php`

- [ ] **Step 1: Aggiornare model BorsaStudio**

In `models/BorsaStudio.php`, aggiungere costante e aggiornare status:

```php
const STATUS_EROGAZIONE_BLOCCATA = 'erogazione_bloccata';
const SOGLIA_FREQUENZA = 80;
```

Aggiornare la rule `status` in `range`:
```php
[['status'], 'in', 'range' => [
    self::STATUS_RICHIESTA,
    self::STATUS_APPROVATA,
    self::STATUS_IN_EROGAZIONE,
    self::STATUS_EROGAZIONE_BLOCCATA,
    self::STATUS_COMPLETATA,
    self::STATUS_REVOCATA,
    self::STATUS_SOSPESA,
]],
```

Aggiungere metodo calcolo frequenza:
```php
/**
 * Calcola la percentuale di frequenza dello studente nel progetto
 * e aggiorna lo status se necessario
 */
public function ricalcolaFrequenza()
{
    // BorsaStudio.progetto_id punta a progetto, dobbiamo risalire agli avvisi del progetto
    $avvisoIds = \app\models\Avviso::find()
        ->select('id')
        ->where(['progetto_id' => $this->progetto_id, 'deleted_at' => null])
        ->column();

    $oreTotali = Calendario::find()
        ->alias('c')
        ->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')
        ->where(['m.avviso_id' => $avvisoIds, 'c.deleted_at' => null])
        ->sum('c.ore') ?: 0;

    $orePresenza = PresenzaAllievo::find()
        ->alias('pa')
        ->innerJoin('{{%calendario}} c', 'c.id = pa.calendario_id AND c.deleted_at IS NULL')
        ->innerJoin('{{%modulo}} m', 'm.id = c.modulo_id AND m.deleted_at IS NULL')
        ->where([
            'pa.allievo_codice_fiscale' => $this->allievo_codice_fiscale,
            'pa.presente' => 1,
            'pa.deleted_at' => null,
            'm.avviso_id' => $avvisoIds,
        ])
        ->sum('pa.ore_presenza') ?: 0;

    $this->percentuale_frequenza = $oreTotali > 0 ? round($orePresenza / $oreTotali * 100, 1) : 0;

    if ($this->status === self::STATUS_APPROVATA && $this->percentuale_frequenza < self::SOGLIA_FREQUENZA) {
        $this->status = self::STATUS_EROGAZIONE_BLOCCATA;
    } elseif ($this->status === self::STATUS_EROGAZIONE_BLOCCATA && $this->percentuale_frequenza >= self::SOGLIA_FREQUENZA) {
        $this->status = self::STATUS_APPROVATA;
    }

    return $this->save(false);
}

public function getStatusBadgeClass()
{
    return match ($this->status) {
        self::STATUS_RICHIESTA => 'bg-azure-lt',
        self::STATUS_APPROVATA => 'bg-green-lt',
        self::STATUS_IN_EROGAZIONE => 'bg-blue-lt',
        self::STATUS_EROGAZIONE_BLOCCATA => 'bg-orange-lt',
        self::STATUS_COMPLETATA => 'bg-teal-lt',
        self::STATUS_REVOCATA => 'bg-red-lt',
        self::STATUS_SOSPESA => 'bg-default-lt',
        default => 'bg-default-lt',
    };
}

public function getStatusLabel()
{
    return match ($this->status) {
        self::STATUS_RICHIESTA => 'Richiesta',
        self::STATUS_APPROVATA => 'Approvata',
        self::STATUS_IN_EROGAZIONE => 'In Erogazione',
        self::STATUS_EROGAZIONE_BLOCCATA => 'Erogazione Bloccata',
        self::STATUS_COMPLETATA => 'Completata (Erogata)',
        self::STATUS_REVOCATA => 'Revocata',
        self::STATUS_SOSPESA => 'Sospesa',
        default => $this->status,
    };
}
```

- [ ] **Step 2: Aggiungere action ricalcola nel controller**

In `controllers/BorsaStudioController.php`:

```php
/**
 * Ricalcola frequenza per tutte le borse di un progetto
 */
public function actionRicalcolaFrequenze($progetto_id = null)
{
    $query = BorsaStudio::find()->where(['in', 'status', [
        BorsaStudio::STATUS_APPROVATA,
        BorsaStudio::STATUS_EROGAZIONE_BLOCCATA,
    ]]);

    if ($progetto_id) {
        $query->andWhere(['progetto_id' => $progetto_id]);
    }

    $borse = $query->all();
    $count = 0;
    foreach ($borse as $borsa) {
        $borsa->ricalcolaFrequenza();
        $count++;
    }

    Yii::$app->session->setFlash('success', "Frequenze ricalcolate per {$count} borse di studio.");
    return $this->redirect(Yii::$app->request->referrer ?: ['index']);
}
```

- [ ] **Step 3: Aggiornare viste borsa-studio**

In `views/borsa-studio/index.php`, aggiungere pulsante "Ricalcola Frequenze" e colonna stato con badge colorato.

In `views/borsa-studio/view.php`, mostrare percentuale frequenza e stato con badge.

- [ ] **Step 4: Commit**

```bash
git add models/BorsaStudio.php controllers/BorsaStudioController.php views/borsa-studio/
git commit -m "feat: borse di studio con stato erogazione_bloccata e ricalcolo frequenza 80%"
```

---

## Task 9: Export SEPA XML pain.001

**Files:**
- Create: `components/SepaXmlGenerator.php`
- Modify: `controllers/BorsaStudioController.php`

- [ ] **Step 1: Creare SepaXmlGenerator**

File `components/SepaXmlGenerator.php`:

```php
<?php
namespace app\components;

use app\models\Configurazione;

class SepaXmlGenerator
{
    /**
     * Genera XML SEPA pain.001.001.03 per bonifici
     *
     * @param array $pagamenti Array di ['beneficiario' => string, 'iban' => string, 'importo' => float, 'causale' => string]
     * @return string XML content
     */
    public static function genera($pagamenti)
    {
        $ibanMittente = Configurazione::get('banca_iban_fondazione');
        $bicMittente = Configurazione::get('banca_bic');
        $nomeMittente = Configurazione::get('nome_fondazione') ?: 'Fondazione Albatros';
        $cfMittente = Configurazione::get('codice_fiscale_ente') ?: '';

        $msgId = 'ALB-' . date('YmdHis') . '-' . rand(1000, 9999);
        $pmtInfId = 'PMT-' . date('YmdHis');
        $totale = array_sum(array_column($pagamenti, 'importo'));
        $numTransazioni = count($pagamenti);

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        // Root
        $xml->startElement('Document');
        $xml->writeAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');
        $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $xml->startElement('CstmrCdtTrfInitn');

        // Group Header
        $xml->startElement('GrpHdr');
        $xml->writeElement('MsgId', $msgId);
        $xml->writeElement('CreDtTm', date('Y-m-d\TH:i:s'));
        $xml->writeElement('NbOfTxs', (string)$numTransazioni);
        $xml->writeElement('CtrlSum', number_format($totale, 2, '.', ''));
        $xml->startElement('InitgPty');
        $xml->writeElement('Nm', $nomeMittente);
        $xml->endElement(); // InitgPty
        $xml->endElement(); // GrpHdr

        // Payment Information
        $xml->startElement('PmtInf');
        $xml->writeElement('PmtInfId', $pmtInfId);
        $xml->writeElement('PmtMtd', 'TRF'); // Transfer
        $xml->writeElement('NbOfTxs', (string)$numTransazioni);
        $xml->writeElement('CtrlSum', number_format($totale, 2, '.', ''));

        // Payment Type
        $xml->startElement('PmtTpInf');
        $xml->startElement('SvcLvl');
        $xml->writeElement('Cd', 'SEPA');
        $xml->endElement(); // SvcLvl
        $xml->endElement(); // PmtTpInf

        $xml->writeElement('ReqdExctnDt', date('Y-m-d'));

        // Debtor (Fondazione)
        $xml->startElement('Dbtr');
        $xml->writeElement('Nm', $nomeMittente);
        $xml->endElement();

        $xml->startElement('DbtrAcct');
        $xml->startElement('Id');
        $xml->writeElement('IBAN', str_replace(' ', '', $ibanMittente));
        $xml->endElement();
        $xml->endElement();

        $xml->startElement('DbtrAgt');
        $xml->startElement('FinInstnId');
        $xml->writeElement('BIC', $bicMittente);
        $xml->endElement();
        $xml->endElement();

        // Transactions
        foreach ($pagamenti as $pag) {
            $xml->startElement('CdtTrfTxInf');

            $xml->startElement('PmtId');
            $xml->writeElement('EndToEndId', 'E2E-' . date('Ymd') . '-' . rand(10000, 99999));
            $xml->endElement();

            $xml->startElement('Amt');
            $xml->startElement('InstdAmt');
            $xml->writeAttribute('Ccy', 'EUR');
            $xml->text(number_format($pag['importo'], 2, '.', ''));
            $xml->endElement();
            $xml->endElement();

            $xml->startElement('Cdtr');
            $xml->writeElement('Nm', $pag['beneficiario']);
            $xml->endElement();

            $xml->startElement('CdtrAcct');
            $xml->startElement('Id');
            $xml->writeElement('IBAN', str_replace(' ', '', $pag['iban']));
            $xml->endElement();
            $xml->endElement();

            $xml->startElement('RmtInf');
            $xml->writeElement('Ustrd', $pag['causale']);
            $xml->endElement();

            $xml->endElement(); // CdtTrfTxInf
        }

        $xml->endElement(); // PmtInf
        $xml->endElement(); // CstmrCdtTrfInitn
        $xml->endElement(); // Document

        $xml->endDocument();
        return $xml->outputMemory();
    }
}
```

- [ ] **Step 2: Aggiungere action export SEPA nel controller**

In `controllers/BorsaStudioController.php`:

```php
use app\components\SepaXmlGenerator;

/**
 * Export SEPA XML pain.001 per borse approvate
 */
public function actionExportSepa($progetto_id = null)
{
    $query = BorsaStudio::find()
        ->where(['status' => BorsaStudio::STATUS_APPROVATA])
        ->andWhere(['not', ['iban_studente' => null]])
        ->andWhere(['!=', 'iban_studente', ''])
        ->with(['allievo']);

    if ($progetto_id) {
        $query->andWhere(['progetto_id' => $progetto_id]);
    }

    $borse = $query->all();

    if (empty($borse)) {
        Yii::$app->session->setFlash('warning', 'Nessuna borsa approvata con IBAN trovata.');
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    $pagamenti = [];
    foreach ($borse as $borsa) {
        $allievo = $borsa->allievo;
        $pagamenti[] = [
            'beneficiario' => $allievo->cognome . ' ' . $allievo->nome,
            'iban' => $borsa->iban_studente,
            'importo' => (float)$borsa->importo_finale,
            'causale' => 'Borsa di studio A.F. ' . $borsa->anno_formativo . ' - ' . $allievo->cognome . ' ' . $allievo->nome,
        ];
    }

    $xml = SepaXmlGenerator::genera($pagamenti);

    Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    Yii::$app->response->headers->set('Content-Type', 'application/xml');
    Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="SEPA_BorseStudio_' . date('Y-m-d') . '.xml"');
    return $xml;
}
```

- [ ] **Step 3: Aggiungere pulsante export nella vista index**

In `views/borsa-studio/index.php`, aggiungere pulsante:

```php
<a href="<?= Url::to(['/borsa-studio/export-sepa']) ?>" class="btn btn-outline-green">
    <i class="ti ti-file-export me-1"></i> Export SEPA XML
</a>
```

- [ ] **Step 4: Commit**

```bash
git add components/SepaXmlGenerator.php controllers/BorsaStudioController.php views/borsa-studio/
git commit -m "feat: export SEPA XML pain.001 per pagamento massivo borse di studio"
```

---

## Task 10: Candidatura Multi-modulo

**Files:**
- Modify: `controllers/GestioneController.php`
- Create: `views/gestione/_candidatura-multi.php`
- Modify: `views/avviso/view.php`

- [ ] **Step 1: Aggiungere action in GestioneController**

```php
/**
 * Form candidatura multi-modulo
 */
public function actionNuovaCandidatura($corso_id)
{
    $corso = Avviso::findOne($corso_id);
    if (!$corso) {
        throw new \yii\web\NotFoundHttpException('Corso non trovato.');
    }

    $moduli = Modulo::find()->where(['avviso_id' => $corso_id])->all();
    $docenti = \app\models\Docente::find()->orderBy(['cognome' => SORT_ASC, 'nome' => SORT_ASC])->all();

    if (Yii::$app->request->isPost) {
        $post = Yii::$app->request->post();
        $docenteCf = $post['docente_codice_fiscale'] ?? null;
        $moduliIds = $post['moduli_ids'] ?? [];

        if (!$docenteCf || empty($moduliIds)) {
            Yii::$app->session->setFlash('error', 'Selezionare un docente e almeno un modulo.');
            return $this->render('_candidatura-multi', compact('corso', 'moduli', 'docenti'));
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $count = 0;
            foreach ($moduliIds as $moduloId) {
                $exists = Istanza::find()->where([
                    'docente_codice_fiscale' => $docenteCf,
                    'modulo_id' => $moduloId,
                    'deleted_at' => null,
                ])->exists();

                if (!$exists) {
                    $istanza = new Istanza();
                    $istanza->docente_codice_fiscale = $docenteCf;
                    $istanza->modulo_id = $moduloId;
                    $istanza->status = Istanza::STATUS_PRESENTATA;
                    $istanza->data_presentazione = date('Y-m-d');
                    if (!$istanza->save()) {
                        throw new \Exception('Errore salvataggio istanza: ' . implode(', ', $istanza->getFirstErrors()));
                    }
                    $count++;
                }
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', "Candidatura inserita per {$count} moduli.");
            return $this->redirect(['/gestione/sezione', 'id' => $corso_id, 'section' => 'piano-formativo']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
    }

    return $this->render('_candidatura-multi', compact('corso', 'moduli', 'docenti'));
}
```

- [ ] **Step 2: Creare vista _candidatura-multi.php**

File `views/gestione/_candidatura-multi.php`:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/** @var app\models\Avviso $corso */
/** @var app\models\Modulo[] $moduli */
/** @var app\models\Docente[] $docenti */

$this->title = 'Nuova Candidatura - ' . $corso->titolo;
?>
<div class="page-header d-print-none mb-4">
    <h2 class="page-title">Nuova Candidatura Multi-modulo</h2>
    <div class="text-muted">Corso: <?= Html::encode($corso->titolo) ?></div>
</div>

<form method="post">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title">Seleziona Docente</h3></div>
        <div class="card-body">
            <select name="docente_codice_fiscale" class="form-select tom-select" required>
                <option value="">-- Seleziona docente --</option>
                <?php foreach ($docenti as $doc): ?>
                <option value="<?= Html::encode($doc->codice_fiscale) ?>">
                    <?= Html::encode($doc->cognome . ' ' . $doc->nome . ' (' . $doc->codice_fiscale . ')') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Seleziona Moduli</h3>
            <div class="card-actions">
                <label class="form-check">
                    <input type="checkbox" class="form-check-input" id="select-all-moduli">
                    <span class="form-check-label">Seleziona tutti</span>
                </label>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>Modulo</th>
                        <th>Ore</th>
                        <th>Compenso/h</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moduli as $m): ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input modulo-cb" name="moduli_ids[]" value="<?= $m->id ?>"></td>
                        <td><?= Html::encode($m->titolo) ?></td>
                        <td><?= $m->ore_totali ?></td>
                        <td><?= $m->compenso_orario ? '€' . number_format($m->compenso_orario, 2) : '-' ?></td>
                        <td>
                            <?php
                            $assegnato = \app\models\Assegnazione::find()->where(['modulo_id' => $m->id, 'deleted_at' => null])->exists();
                            echo $assegnato
                                ? '<span class="badge bg-green-lt">Assegnato</span>'
                                : '<span class="badge bg-default-lt">Da assegnare</span>';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-indigo">
                <i class="ti ti-send me-1"></i> Inserisci Candidatura
            </button>
            <a href="<?= Url::to(['/gestione/sezione', 'id' => $corso->id, 'section' => 'piano-formativo']) ?>"
               class="btn btn-ghost-default ms-2">Annulla</a>
        </div>
    </div>
</form>

<?php
$js = <<<JS
document.getElementById('select-all-moduli').addEventListener('change', function() {
    document.querySelectorAll('.modulo-cb').forEach(cb => cb.checked = this.checked);
});
JS;
$this->registerJs($js);
?>
```

- [ ] **Step 3: Aggiungere pulsante in avviso/view.php (area avanzata)**

In `views/avviso/view.php`, nella sezione pulsanti dell'header, aggiungere:

```php
<?= Html::a('<i class="ti ti-users-plus me-1"></i> Candidatura Multi-modulo',
    ['/gestione/nuova-candidatura', 'corso_id' => $model->id],
    ['class' => 'btn btn-outline-indigo']) ?>
```

- [ ] **Step 4: Commit**

```bash
git add controllers/GestioneController.php views/gestione/_candidatura-multi.php views/avviso/view.php
git commit -m "feat: candidatura multi-modulo per inserimento istanze su più moduli contemporaneamente"
```

---

## Task 11: Allegati Scansione Contratto

**Files:**
- Modify: `models/Contratto.php`
- Modify: `controllers/ContrattoController.php`
- Modify: `views/contratto/view.php`

- [ ] **Step 1: Aggiornare model Contratto**

In `models/Contratto.php`, aggiungere:

```php
public function rules()
{
    // Aggiungere alla lista rules esistente:
    // [['allegati_path'], 'safe'],
}

public function getAllegati()
{
    $json = $this->allegati_path;
    if (empty($json)) return [];
    $decoded = is_string($json) ? json_decode($json, true) : $json;
    return is_array($decoded) ? $decoded : [];
}

public function aggiungiAllegato($path, $nomeOriginale)
{
    $allegati = $this->getAllegati();
    $allegati[] = [
        'path' => $path,
        'nome' => $nomeOriginale,
        'data' => date('Y-m-d H:i:s'),
    ];
    $this->allegati_path = json_encode($allegati);
    return $this->save(false);
}
```

- [ ] **Step 2: Aggiungere action upload allegato**

In `controllers/ContrattoController.php`:

```php
/**
 * Upload allegato scansione contratto
 */
public function actionUploadAllegato($id)
{
    $model = $this->findModel($id);

    $file = \yii\web\UploadedFile::getInstanceByName('allegato');
    if (!$file) {
        Yii::$app->session->setFlash('error', 'Nessun file selezionato.');
        return $this->redirect(['view', 'id' => $id]);
    }

    $ext = strtolower($file->extension);
    if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
        Yii::$app->session->setFlash('error', 'Formato non supportato. Usare PDF, JPG o PNG.');
        return $this->redirect(['view', 'id' => $id]);
    }

    $dir = Yii::getAlias('@webroot/uploads/contratti/allegati');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = $id . '_allegato_' . time() . '.' . $ext;
    $path = $dir . '/' . $filename;

    if ($file->saveAs($path)) {
        $model->aggiungiAllegato('uploads/contratti/allegati/' . $filename, $file->baseName . '.' . $ext);
        Yii::$app->session->setFlash('success', 'Allegato caricato con successo.');
    } else {
        Yii::$app->session->setFlash('error', 'Errore nel caricamento del file.');
    }

    $returnUrl = Yii::$app->request->get('returnUrl');
    return $this->redirect($returnUrl ?: ['view', 'id' => $id]);
}
```

- [ ] **Step 3: Aggiornare vista contratto/view.php**

In `views/contratto/view.php`, aggiungere sezione allegati dopo i dettagli contratto:

```php
<!-- Allegati Scansione -->
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="ti ti-paperclip me-2"></i>Allegati</h3>
    </div>
    <div class="card-body">
        <?php $allegati = $model->getAllegati(); ?>
        <?php if (!empty($allegati)): ?>
        <div class="list-group list-group-flush">
            <?php foreach ($allegati as $all): ?>
            <div class="list-group-item d-flex align-items-center">
                <i class="ti ti-file me-2"></i>
                <div>
                    <a href="<?= Yii::$app->request->baseUrl . '/' . $all['path'] ?>" target="_blank">
                        <?= Html::encode($all['nome']) ?>
                    </a>
                    <small class="text-muted d-block"><?= $all['data'] ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-muted">Nessun allegato caricato.</div>
        <?php endif; ?>

        <form method="post" action="<?= Url::to(['/contratto/upload-allegato', 'id' => $model->id]) ?>"
              enctype="multipart/form-data" class="mt-3">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <div class="input-group">
                <input type="file" name="allegato" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                <button type="submit" class="btn btn-outline-indigo">
                    <i class="ti ti-upload me-1"></i> Carica
                </button>
            </div>
            <small class="text-muted">Formati: PDF, JPG, PNG</small>
        </form>
    </div>
</div>
```

- [ ] **Step 4: Commit**

```bash
git add models/Contratto.php controllers/ContrattoController.php views/contratto/view.php
git commit -m "feat: upload allegati scansione per contratti firmati a mano"
```

---

## Task 12: AlertWidget e Dashboard Admin

**Files:**
- Create: `components/widgets/AlertWidget.php`
- Modify: `views/site/index.php`

- [ ] **Step 1: Creare AlertWidget**

File `components/widgets/AlertWidget.php`:

```php
<?php
namespace app\components\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use app\models\Assegnazione;
use app\models\Calendario;
use app\models\BorsaStudio;

class AlertWidget extends Widget
{
    /** @var string Tipo alert: contratti, presenze, borse, tutti */
    public $tipo = 'tutti';

    /** @var int|null Filtra per corso (avviso_id). null = tutti */
    public $corso_id = null;

    /** @var bool Modalità compatta (solo conteggio) */
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

        if ($this->compact) {
            return $this->renderCompact($alerts);
        }

        return $this->renderFull($alerts);
    }

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

        return (int)$query->count();
    }

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

        return (int)$query->count();
    }

    protected function getAlertBorse()
    {
        $query = BorsaStudio::find()
            ->where(['status' => BorsaStudio::STATUS_EROGAZIONE_BLOCCATA]);

        if ($this->corso_id) {
            $query->andWhere(['progetto_id' => $this->corso_id]);
        }

        return (int)$query->count();
    }

    protected function renderCompact($alerts)
    {
        $total = array_sum($alerts);
        if ($total === 0) return '';
        return Html::tag('span', (string)$total, ['class' => 'badge bg-red-lt']);
    }

    protected function renderFull($alerts)
    {
        $html = '';
        $config = [
            'contratti' => ['icon' => 'ti-file-alert', 'color' => 'orange', 'title' => 'Docenti da contrattualizzare'],
            'presenze' => ['icon' => 'ti-clock-exclamation', 'color' => 'red', 'title' => 'Presenze non acquisite'],
            'borse' => ['icon' => 'ti-alert-triangle', 'color' => 'yellow', 'title' => 'Borse con frequenza insufficiente'],
        ];

        foreach ($alerts as $tipo => $count) {
            if ($count === 0) continue;
            $c = $config[$tipo];
            $html .= '<div class="alert alert-' . $c['color'] . ' mb-3">'
                . '<div class="d-flex align-items-center">'
                . '<i class="ti ' . $c['icon'] . ' me-2" style="font-size:1.5rem;"></i>'
                . '<div>'
                . '<h4 class="alert-title">' . $c['title'] . ' (' . $count . ')</h4>'
                . '</div>'
                . '</div></div>';
        }

        return $html;
    }
}
```

- [ ] **Step 2: Aggiungere alert nella dashboard admin**

In `views/site/index.php`, dopo il welcome card e prima delle stat cards finanziarie, aggiungere:

```php
<!-- Alert Operativi -->
<?= \app\components\widgets\AlertWidget::widget(['tipo' => 'tutti']) ?>
```

- [ ] **Step 3: Commit**

```bash
git add components/widgets/AlertWidget.php views/site/index.php
git commit -m "feat: AlertWidget riutilizzabile con alert in dashboard admin per contratti, presenze, borse"
```

---

## Riepilogo Dipendenze tra Task

```
Task 1 (DB Migrations) ─── nessuna dipendenza
Task 2 (Fix Validazione) ─ nessuna dipendenza
Task 3 (Ridenominazione) ─ nessuna dipendenza

Task 4 (Layout)         ← dipende da Task 1
Task 5 (Controller)     ← dipende da Task 1, Task 4
Task 6 (Viste sezioni)  ← dipende da Task 5

Task 7 (Tutor Presenze) ← dipende da Task 1
Task 8 (Borse Studio)   ← dipende da Task 1
Task 9 (SEPA XML)       ← dipende da Task 8
Task 10 (Candidature)   ← dipende da Task 5
Task 11 (Allegati)      ← dipende da Task 1
Task 12 (AlertWidget)   ← dipende da Task 1
```

## Parallelizzazione Agenti

```
Tempo 0:  [db-agent: Task 1] [full-stack-1: Task 2] [full-stack-2: Task 3]
Tempo 1:  [full-stack-3: Task 4→5→6] [full-stack-4: Task 7] [full-stack-5: Task 8→9] [full-stack-6: Task 11+12]
Tempo 2:  [full-stack-3: Task 10 (dopo Task 5)]
```
