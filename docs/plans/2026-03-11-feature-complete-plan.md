# Piano Implementazione Feature Complete - Albatros Manager

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implementare 8 feature richieste dalla terza riunione con il committente: verifica P7M, generazione contratti PDF, richiesta fatturazione/cedolini, albo docenti, coordinatori/tutor, bilancio, rimborsi spese, borse di studio.

**Architecture:** Ogni feature aggiunge migration DB + model + controller + views seguendo i pattern esistenti (Tabler UI, AppGridView, SoftDeleteTrait, BaseActiveRecord). Le feature sono ordinate per dipendenze: prima le modifiche DB trasversali (tipo docente, voci spesa), poi le feature che le usano.

**Tech Stack:** PHP 8.4, Yii2, MySQL/MariaDB, Tabler v1.4.0, DOMPDF (nuovo), PhpSpreadsheet (esistente), yii2-symfonymailer (esistente)

---

## Ordine di implementazione

| # | Feature | Dipende da | Complessità |
|---|---------|------------|-------------|
| 1 | Campo `tipo` su Docente (coordinatori/tutor) | - | Bassa |
| 2 | Voci di spesa e Bilancio | - | Alta |
| 3 | Rimborsi spese docenti | #1 | Media |
| 4 | Richiesta fatturazione / Cedolini | - | Media |
| 5 | Generazione contratti PDF (DOMPDF) | - | Alta |
| 6 | Verifica firma P7M / PDF firmati | - | Media |
| 7 | Albo Docenti (Long List) | #1 | Media |
| 8 | Borse di studio studenti | - | Media |

---

## Chunk 1: Schema DB e Modifiche Trasversali

### Task 1.1: Migration - Campo `tipo` su tabella `docente`

**Files:**
- Create: `migrations/m260311_100000_add_tipo_to_docente.php`
- Modify: `models/Docente.php`
- Modify: `models/DocenteSearch.php`

- [ ] **Step 1: Creare migration**

```php
<?php
use yii\db\Migration;

class m260311_100000_add_tipo_to_docente extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%docente}}', 'tipo', "ENUM('docente','coordinatore','tutor') NOT NULL DEFAULT 'docente' AFTER provenienza");
    }

    public function safeDown()
    {
        $this->dropColumn('{{%docente}}', 'tipo');
    }
}
```

- [ ] **Step 2: Eseguire migration**

```bash
docker exec -w /app albatros-php php yii migrate --interactive=0
```

- [ ] **Step 3: Aggiornare model Docente.php**

Aggiungere costanti, regole, labels e metodi helper:
```php
const TIPO_DOCENTE = 'docente';
const TIPO_COORDINATORE = 'coordinatore';
const TIPO_TUTOR = 'tutor';

// In rules(): ['tipo', 'in', 'range' => ['docente', 'coordinatore', 'tutor']]
// In attributeLabels(): 'tipo' => 'Tipo'
// Metodo getTipoBadge() con colori: docente=blue, coordinatore=purple, tutor=teal
// Metodo statico getTipoList() per dropdown
```

- [ ] **Step 4: Aggiornare DocenteSearch.php**

Aggiungere filtro per `tipo` nella griglia docenti.

- [ ] **Step 5: Aggiornare views docente**

In `views/docente/index.php` e `views/docente/_form.php`: aggiungere colonna tipo nella griglia e campo select nel form.

- [ ] **Step 6: Commit**

```bash
git add migrations/ models/Docente.php models/DocenteSearch.php views/docente/
git commit -m "feat: add tipo field (docente/coordinatore/tutor) to docente table"
```

---

### Task 1.2: Migration - Tabelle voci di spesa e bilancio

**Files:**
- Create: `migrations/m260311_100100_create_voce_spesa_tables.php`

- [ ] **Step 1: Creare migration con 4 tabelle**

```sql
-- Categorie voci di spesa (configurabili dall'admin)
CREATE TABLE voce_spesa_categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codice VARCHAR(20) NOT NULL,          -- es. B1, B2, C1, D1
    nome VARCHAR(255) NOT NULL,           -- es. "Preparazione", "Realizzazione"
    sezione ENUM('costi_diretti','costi_indiretti','attivita_accessorie') NOT NULL,
    ordinamento INT DEFAULT 0,
    fonte_finanziamento_id INT NULL,      -- NULL = tutte le fonti
    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    created_by INT, updated_by INT, deleted_by INT
);

-- Sotto-voci specifiche
CREATE TABLE voce_spesa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    codice VARCHAR(20),                    -- es. B2.1, C1.3
    nome VARCHAR(255) NOT NULL,            -- es. "Docenza", "Consulenza del lavoro"
    descrizione TEXT,
    ordinamento INT DEFAULT 0,
    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    created_by INT, updated_by INT, deleted_by INT,
    FOREIGN KEY (categoria_id) REFERENCES voce_spesa_categoria(id)
);

-- Movimenti di spesa (registrazioni contabili)
CREATE TABLE movimento_spesa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    progetto_id INT NOT NULL,
    voce_spesa_id INT NOT NULL,
    descrizione VARCHAR(500) NOT NULL,
    importo DECIMAL(12,2) NOT NULL,
    data_movimento DATE NOT NULL,
    data_documento DATE,
    numero_documento VARCHAR(100),         -- n. fattura, mandato, etc.
    fornitore VARCHAR(255),
    tipo ENUM('spesa','entrata') NOT NULL DEFAULT 'spesa',
    riferimento_tipo VARCHAR(50) NULL,     -- 'pagamento', 'rimborso', null=manuale
    riferimento_id INT NULL,               -- ID del pagamento/rimborso collegato
    allegato_path VARCHAR(500),
    note TEXT,
    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    created_by INT, updated_by INT, deleted_by INT,
    FOREIGN KEY (progetto_id) REFERENCES progetto(id),
    FOREIGN KEY (voce_spesa_id) REFERENCES voce_spesa(id)
);

-- Tranche di finanziamento (entrate per progetto)
CREATE TABLE tranche_finanziamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    progetto_id INT NOT NULL,
    importo DECIMAL(12,2) NOT NULL,
    data_accredito DATE,
    data_prevista DATE,
    numero_tranche INT,                    -- 1, 2, 3...
    riferimento VARCHAR(255),              -- es. "Anticipo", "SAL 1", "Saldo"
    status ENUM('prevista','accreditata','parziale') NOT NULL DEFAULT 'prevista',
    note TEXT,
    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    created_by INT, updated_by INT, deleted_by INT,
    FOREIGN KEY (progetto_id) REFERENCES progetto(id)
);
```

- [ ] **Step 2: Eseguire migration**

- [ ] **Step 3: Seeder - Inserire voci di spesa dal Bilancio Preventivo 2025**

Creare migration/seeder con tutte le voci dal PDF:
- B1) Preparazione (5 sotto-voci)
- B2) Realizzazione (14 sotto-voci)
- B3) Diffusione (2 sotto-voci)
- B4) Direzione e controllo (8 sotto-voci)
- C1) Costi indiretti (6+ sotto-voci con consulenze specialistiche)
- D1) Attività accessorie (6 sotto-voci)

- [ ] **Step 4: Commit**

```bash
git add migrations/
git commit -m "feat: create voce_spesa, movimento_spesa, tranche_finanziamento tables"
```

---

### Task 1.3: Migration - Tabella rimborsi spese

**Files:**
- Create: `migrations/m260311_100200_create_rimborso_spesa_table.php`

- [ ] **Step 1: Creare migration**

```sql
CREATE TABLE rimborso_spesa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assegnazione_id INT NOT NULL,
    tipo ENUM('materiale','chilometrico','vitto_alloggio','viaggio','altro') NOT NULL,
    descrizione VARCHAR(500) NOT NULL,
    importo DECIMAL(10,2) NOT NULL,
    data_spesa DATE NOT NULL,
    allegato_path VARCHAR(500),            -- fattura/scontrino obbligatorio
    status ENUM('richiesto','approvato','rifiutato') NOT NULL DEFAULT 'richiesto',
    motivo_rifiuto TEXT,
    approvato_da INT NULL,
    approvato_at TIMESTAMP NULL,
    note TEXT,
    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    created_by INT, updated_by INT, deleted_by INT,
    FOREIGN KEY (assegnazione_id) REFERENCES assegnazione(id)
);
```

- [ ] **Step 2: Eseguire migration e commit**

---

### Task 1.4: Migration - Tabella richieste fatturazione/cedolini

**Files:**
- Create: `migrations/m260311_100300_create_richiesta_fatturazione_table.php`

- [ ] **Step 1: Creare migration**

```sql
CREATE TABLE richiesta_fatturazione (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contratto_id INT NOT NULL,
    tipo ENUM('fattura','cedolino') NOT NULL,   -- fattura=P.IVA, cedolino=CoCoCo
    importo DECIMAL(12,2) NOT NULL,
    periodo_riferimento VARCHAR(100),            -- es. "Febbraio 2026"
    data_richiesta DATE NOT NULL,
    data_scadenza DATE,
    destinatario_email VARCHAR(255) NOT NULL,    -- email docente o consulente
    destinatario_nome VARCHAR(255),
    oggetto_email VARCHAR(500),
    corpo_email TEXT,
    documento_ricevuto_path VARCHAR(500),         -- fattura/cedolino uploadato
    data_ricezione DATE,
    status ENUM('bozza','inviata','ricevuta','verificata') NOT NULL DEFAULT 'bozza',
    note TEXT,
    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    created_by INT, updated_by INT, deleted_by INT,
    FOREIGN KEY (contratto_id) REFERENCES contratto(id)
);
```

- [ ] **Step 2: Eseguire migration e commit**

---

### Task 1.5: Migration - Tabella borse di studio

**Files:**
- Create: `migrations/m260311_100400_create_borsa_studio_table.php`

- [ ] **Step 1: Creare migration**

```sql
CREATE TABLE borsa_studio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allievo_codice_fiscale CHAR(16) NOT NULL,
    progetto_id INT NOT NULL,
    anno_formativo VARCHAR(9) NOT NULL,          -- es. "2024/2025"

    -- Classificazione studente
    tipologia_sede ENUM('in_sede','pendolare','fuori_sede') NOT NULL,

    -- Dati ISEE/ISPE
    isee_importo DECIMAL(12,2),
    ispe_importo DECIMAL(12,2),
    isee_protocollo VARCHAR(100),

    -- Importo calcolato
    importo_base DECIMAL(10,2) NOT NULL,
    maggiorazione_tipo VARCHAR(50),              -- 'isee_basso', 'studentessa', 'disabilita', 'tirocinio_estero'
    maggiorazione_percentuale DECIMAL(5,2) DEFAULT 0,
    importo_maggiorato DECIMAL(10,2),
    importo_finale DECIMAL(10,2) NOT NULL,

    -- Erogazione trimestrale
    importo_q1 DECIMAL(10,2) DEFAULT 0,          -- Q1 erogato
    importo_q2 DECIMAL(10,2) DEFAULT 0,
    importo_q3 DECIMAL(10,2) DEFAULT 0,
    importo_q4 DECIMAL(10,2) DEFAULT 0,
    totale_erogato DECIMAL(10,2) DEFAULT 0,

    -- Requisiti di merito
    percentuale_frequenza DECIMAL(5,2) DEFAULT 0,
    verifiche_superate TINYINT(1) DEFAULT 0,

    -- IBAN per pagamento
    iban_studente VARCHAR(34),

    status ENUM('richiesta','approvata','in_erogazione','completata','revocata','sospesa') NOT NULL DEFAULT 'richiesta',
    motivo_revoca TEXT,
    data_approvazione DATE,
    note TEXT,

    created_at TIMESTAMP, updated_at TIMESTAMP, deleted_at TIMESTAMP NULL,
    created_by INT, updated_by INT, deleted_by INT,
    FOREIGN KEY (allievo_codice_fiscale) REFERENCES allievo(codice_fiscale),
    FOREIGN KEY (progetto_id) REFERENCES progetto(id)
);
```

- [ ] **Step 2: Eseguire migration e commit**

---

### Task 1.6: Migration - Campi aggiuntivi su contratto

**Files:**
- Create: `migrations/m260311_100500_add_firma_fields_to_contratto.php`

- [ ] **Step 1: Creare migration**

Aggiungere campi per gestione firma e documento generato:
```sql
ALTER TABLE contratto ADD COLUMN documento_generato_path VARCHAR(500) AFTER documento_path;
ALTER TABLE contratto ADD COLUMN firma_tipo ENUM('nessuna','p7m','pdf_firmato','otp') DEFAULT 'nessuna' AFTER documento_generato_path;
ALTER TABLE contratto ADD COLUMN firma_verificata TINYINT(1) DEFAULT 0 AFTER firma_tipo;
ALTER TABLE contratto ADD COLUMN firma_data TIMESTAMP NULL AFTER firma_verificata;
ALTER TABLE contratto ADD COLUMN firma_dettagli TEXT AFTER firma_data;
```

- [ ] **Step 2: Eseguire migration e commit**

---

## Chunk 2: Models e Logica Business

### Task 2.1: Models per Voci di Spesa e Bilancio

**Files:**
- Create: `models/VoceSpesaCategoria.php`
- Create: `models/VoceSpesa.php`
- Create: `models/MovimentoSpesa.php`
- Create: `models/MovimentoSpesaSearch.php`
- Create: `models/TrancheFinanziamento.php`
- Create: `models/TrancheFinanziamentoSearch.php`

- [ ] **Step 1: Creare VoceSpesaCategoria model**

Extends BaseActiveRecord. Relazione hasMany(VoceSpesa). Metodi: `getVociBySezione()`, `getSezioneLabel()`.

- [ ] **Step 2: Creare VoceSpesa model**

Extends BaseActiveRecord. Relazione belongsTo(VoceSpesaCategoria). Label composto: `"$codice - $nome"`.

- [ ] **Step 3: Creare MovimentoSpesa model**

Extends BaseActiveRecord. Relations: belongsTo(Progetto, VoceSpesa). Metodi:
- `getTipoLabel()` - spesa/entrata
- `isAutomatico()` - true se riferimento_tipo != null (collegato a pagamento/rimborso)
- Scopes: `perProgetto($id)`, `perAnno($anno)`, `perVoce($voceId)`

- [ ] **Step 4: Creare TrancheFinanziamento model**

Extends BaseActiveRecord. Relations: belongsTo(Progetto). Metodi: `getStatusBadge()`, `isAccreditata()`.

- [ ] **Step 5: Creare Search models**

MovimentoSpesaSearch con filtri: progetto, voce, tipo, periodo (da-a), fornitore.
TrancheFinanziamentoSearch con filtri: progetto, status, periodo.

- [ ] **Step 6: Commit**

---

### Task 2.2: Model RimborsoSpesa

**Files:**
- Create: `models/RimborsoSpesa.php`
- Create: `models/RimborsoSpesaSearch.php`

- [ ] **Step 1: Creare model**

Extends BaseActiveRecord. Costanti status (RICHIESTO, APPROVATO, RIFIUTATO). Costanti tipo (MATERIALE, CHILOMETRICO, etc.).
Relations: belongsTo(Assegnazione), through→Docente, through→Modulo.
Metodi: `getStatusBadge()`, `getTipoLabel()`, `approva($userId)`, `rifiuta($motivo, $userId)`.
Rules: allegato_path required, importo > 0.

- [ ] **Step 2: Aggiornare Assegnazione model**

Aggiungere relazione `getRimborsiSpesa()` e metodo `getTotaleRimborsiApprovati()`.
Il totale rimborsi approvati si somma ai pagamenti del docente.

- [ ] **Step 3: Commit**

---

### Task 2.3: Model RichiestaFatturazione

**Files:**
- Create: `models/RichiestaFatturazione.php`
- Create: `models/RichiestaFatturazioneSearch.php`

- [ ] **Step 1: Creare model**

Extends BaseActiveRecord. Status: BOZZA, INVIATA, RICEVUTA, VERIFICATA.
Tipo: FATTURA (per P.IVA), CEDOLINO (per CoCoCo).
Relations: belongsTo(Contratto), through→Docente.
Metodi: `invia()` (manda email + cambia status), `getStatusBadge()`, `getTipoLabel()`.
Metodo `generaCorpoEmail()` che compone l'email con dati contratto/docente/modulo.

- [ ] **Step 2: Commit**

---

### Task 2.4: Model BorsaStudio

**Files:**
- Create: `models/BorsaStudio.php`
- Create: `models/BorsaStudioSearch.php`

- [ ] **Step 1: Creare model**

Extends BaseActiveRecord. Status: RICHIESTA, APPROVATA, IN_EROGAZIONE, COMPLETATA, REVOCATA, SOSPESA.
Relations: belongsTo(Allievo), belongsTo(Progetto).
Metodi:
- `calcolaImporto()` - calcola importo base + maggiorazioni da tabella bando PNRR
- `getImportoBase($tipologiaSede)` - in_sede=2481.75, pendolare=3598.51, fuori_sede=6157.74
- `getMaggiorazione()` - +15% ISEE basso, +20% studentessa, +40% disabilità
- `erogaTrimestre($trimestre, $importo)` - registra erogazione
- `getTotaleErogato()` / `getResiduoDaErogare()`
- `getStatusBadge()`, `verificaRequisitiMerito()`

- [ ] **Step 2: Commit**

---

## Chunk 3: Feature 6 - Bilancio e Quadro Economico

### Task 3.1: Controller e CRUD Voci di Spesa

**Files:**
- Create: `controllers/VoceSpesaController.php`
- Create: `views/voce-spesa/index.php`
- Create: `views/voce-spesa/_form.php`

- [ ] **Step 1: Creare controller CRUD per gestione voci**

Azioni: index (albero categorie→voci), create, update, delete.
Gestione sia categorie che voci nella stessa pagina (accordion/tree).

- [ ] **Step 2: Creare views**

Index: accordion per sezione (Costi diretti, indiretti, accessorie) → categorie → voci.
Form: select categoria, codice, nome, descrizione.

- [ ] **Step 3: Aggiungere voce menu admin**

In `views/layouts/menu.php`: sezione "Contabilità" con sotto-voci.

- [ ] **Step 4: Commit**

---

### Task 3.2: Controller e CRUD Movimenti Spesa

**Files:**
- Create: `controllers/MovimentoSpesaController.php`
- Create: `views/movimento-spesa/index.php`
- Create: `views/movimento-spesa/view.php`
- Create: `views/movimento-spesa/_form.php`

- [ ] **Step 1: Creare controller**

Azioni: index, view, create, update, delete.
Filtri: progetto (dropdown), voce di spesa (dropdown gerarchico), tipo (spesa/entrata), periodo.
In index: totali per colonna.

- [ ] **Step 2: Creare views**

Index: AppGridView con colonne (data, progetto, voce, descrizione, importo, tipo, fornitore, documento).
Form: progetto (select), voce spesa (select2 gerarchico categoria→voce), importo, data, fornitore, upload allegato.
Badge per tipo: spesa=red, entrata=green.

- [ ] **Step 3: Auto-registrazione da Pagamento**

Quando un Pagamento viene segnato come `pagato`, creare automaticamente un MovimentoSpesa:
- In `Pagamento::afterSave()` o via behavior
- voce_spesa = "B2.1 - Docenza" (o lookup da mapping)
- riferimento_tipo = 'pagamento', riferimento_id = $pagamento->id

- [ ] **Step 4: Commit**

---

### Task 3.3: Controller e CRUD Tranche Finanziamento

**Files:**
- Create: `controllers/TrancheFinanziamentoController.php`
- Create: `views/tranche-finanziamento/index.php`
- Create: `views/tranche-finanziamento/_form.php`

- [ ] **Step 1: Creare controller CRUD**

Index filtrato per progetto. Form: progetto, importo, data prevista/accredito, numero tranche, riferimento, status.

- [ ] **Step 2: Commit**

---

### Task 3.4: Dashboard Bilancio / Quadro Economico

**Files:**
- Create: `controllers/BilancioController.php`
- Create: `views/bilancio/index.php`
- Create: `views/bilancio/quadro-economico.php`
- Create: `views/bilancio/progetto.php`

- [ ] **Step 1: Creare BilancioController**

Azioni:
- `actionIndex()` - Dashboard bilancio generale (tutti i progetti)
- `actionProgetto($id)` - Dettaglio per singolo progetto
- `actionQuadroEconomico($id)` - Format simile al preventivo Regione
- `actionExport($id, $formato)` - Export Excel

- [ ] **Step 2: View index - Dashboard bilancio generale**

Stat cards superiori: Totale Entrate, Totale Uscite, Saldo, Budget Residuo.
Tabella riepilogo per progetto: progetto, budget, entrate (tranche), uscite (movimenti), saldo.
Grafico a barre (ApexCharts): entrate vs uscite per progetto.
Grafico a torta: ripartizione uscite per categoria.

- [ ] **Step 3: View progetto - Dettaglio singolo progetto**

Stat cards: Budget totale, Tranche ricevute, Spese totali, Disponibilità residua.
Progress bar: % speso su budget.
Tabella movimenti raggruppati per categoria (B1, B2, B3, B4, C1, D1) con subtotali.
Sezione tranche: lista con status badge.

- [ ] **Step 4: View quadro economico - Format Regione**

Tabella simile al Bilancio Preventivo 2025:
- Colonne: una per avviso/sede
- Righe: voci di spesa raggruppate per sezione
- Subtotali per categoria
- Totali generali
- Confronto preventivo vs consuntivo

- [ ] **Step 5: Export Excel**

Usando PhpSpreadsheet, genera foglio simile al PDF del preventivo. Intestazioni colorate, formattazione valuta.

- [ ] **Step 6: Commit**

---

## Chunk 4: Feature 3 - Richiesta Fatturazione/Cedolini + Feature 7 - Rimborsi

### Task 4.1: Controller Richiesta Fatturazione

**Files:**
- Create: `controllers/RichiestaFatturazioneController.php`
- Create: `views/richiesta-fatturazione/index.php`
- Create: `views/richiesta-fatturazione/view.php`
- Create: `views/richiesta-fatturazione/_form.php`
- Create: `views/richiesta-fatturazione/invia.php`
- Create: `mail/richiesta-fatturazione-html.php`
- Create: `mail/richiesta-cedolino-html.php`

- [ ] **Step 1: Creare controller**

Azioni: index, view, create, update, delete, invia, ricevi-documento.
- `actionCreate($contratto_id)` - Pre-popola con dati contratto (tipo, importo, email docente)
- `actionInvia($id)` - Invia email e cambia status a 'inviata'
- `actionRiceviDocumento($id)` - Upload fattura/cedolino ricevuto + status='ricevuta'
- `actionInviaMassiva()` - Selezione multipla contratti → invio batch

- [ ] **Step 2: Template email fatturazione**

Template HTML per email al docente P.IVA:
- Oggetto: "Richiesta emissione fattura - [Modulo] - [Progetto]"
- Corpo: dati fondazione, dati docente, modulo, ore, importo, istruzioni fatturazione, scadenza

- [ ] **Step 3: Template email cedolino**

Template HTML per email al consulente:
- Oggetto: "Richiesta elaborazione cedolino - [Docente] - [Mese]"
- Corpo: lista docenti CoCoCo da elaborare, dati contratto, ore, importi

- [ ] **Step 4: Views CRUD**

Index: AppGridView con colonne (tipo badge, docente, contratto, importo, status, data invio).
Filtri: tipo (fattura/cedolino), status, progetto.
View: dettaglio con timeline (bozza→inviata→ricevuta→verificata).
Form: tipo (auto da contratto), importo, periodo, destinatario email.

- [ ] **Step 5: Configurazione SMTP produzione**

Aggiungere in `config/mail.php` (gitignored) la config SMTP reale. In `config/web.php` caricare da file se esiste.

- [ ] **Step 6: Commit**

---

### Task 4.2: Integrazione fatturazione in Contratto/Pagamento

**Files:**
- Modify: `views/contratto/view.php`
- Modify: `views/pagamento/index.php`
- Modify: `models/Contratto.php`

- [ ] **Step 1: Aggiungere tab/sezione in view contratto**

Nella view contratto, aggiungere sezione "Fatturazione" con:
- Lista richieste inviate per questo contratto
- Pulsante "Richiedi fattura/cedolino"
- Status dell'ultima richiesta

- [ ] **Step 2: Workflow pagamento**

Nel flusso pagamento: prima di poter segnare come "pagato", verificare che esista una richiesta fatturazione con status 'ricevuta' o 'verificata' (warning, non bloccante).

- [ ] **Step 3: Commit**

---

### Task 4.3: Controller Rimborso Spesa (Admin)

**Files:**
- Create: `controllers/RimborsoSpesaController.php`
- Create: `views/rimborso-spesa/index.php`
- Create: `views/rimborso-spesa/view.php`
- Create: `views/rimborso-spesa/_form.php`

- [ ] **Step 1: Creare controller admin**

Azioni: index (tutti i rimborsi), view, approva, rifiuta.
- `actionApprova($id)` - Approva rimborso, crea automaticamente MovimentoSpesa
- `actionRifiuta($id)` - Form con motivo rifiuto obbligatorio

- [ ] **Step 2: Views**

Index: AppGridView con colonne (docente, modulo, tipo, importo, data, status, allegato).
Filtri: status, progetto, docente.
View: dettaglio con preview allegato, pulsanti approva/rifiuta.

- [ ] **Step 3: Integrazione con pagamenti**

Quando rimborso approvato:
- Crea MovimentoSpesa automatico (riferimento_tipo='rimborso')
- L'importo si somma al saldo del docente per quel contratto
- In `Contratto::getSaldoRimanente()` sottrarre anche rimborsi approvati

- [ ] **Step 4: Commit**

---

### Task 4.4: Rimborsi nell'Area Docente

**Files:**
- Modify: `controllers/DocenteAreaController.php`
- Create: `views/docente-area/rimborsi.php`
- Create: `views/docente-area/rimborso-form.php`

- [ ] **Step 1: Aggiungere azioni al DocenteAreaController**

- `actionRimborsi()` - Lista rimborsi del docente loggato
- `actionNuovoRimborso($assegnazione_id)` - Form inserimento rimborso
- `actionModificaRimborso($id)` - Solo se status='richiesto'

- [ ] **Step 2: Views area docente**

Lista rimborsi con status badge. Form: select assegnazione (solo quelle attive), tipo (dropdown), importo, data, descrizione, upload allegato (required).

- [ ] **Step 3: Link nel menu docente**

Aggiungere "Rimborsi Spese" nel layout docente, con counter badge per rimborsi in attesa.

- [ ] **Step 4: Commit**

---

## Chunk 5: Feature 5 - Generazione Contratti PDF

### Task 5.1: Installazione DOMPDF e Setup

**Files:**
- Modify: `composer.json`
- Create: `components/PdfGenerator.php`
- Create: `views/contratto/templates/fse.php`
- Create: `views/contratto/templates/pnrr.php`

- [ ] **Step 1: Installare DOMPDF**

```bash
docker exec -w /app albatros-php composer require dompdf/dompdf
```

- [ ] **Step 2: Creare componente PdfGenerator**

```php
namespace app\components;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public static function generate(string $html, string $orientation = 'portrait'): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    public static function renderView(string $view, array $params, string $orientation = 'portrait'): string
    {
        $html = \Yii::$app->controller->renderPartial($view, $params);
        return self::generate($html, $orientation);
    }
}
```

- [ ] **Step 3: Creare template HTML contratto FSE**

Template basato su `contabilità.pdf` (Avviso 28 Barcellona):
- Header con loghi (Coesione Italia, UE, Ministero, FSE, Albatros, Sicilia 2020)
- Dati: numero protocollo, data, dati docente, oggetto, modulo, ore, compenso
- Corpo contrattuale con clausole standard
- Spazio firma Presidente + firma docente
- Footer con dati fondazione

- [ ] **Step 4: Creare template HTML contratto PNRR**

Template basato su contratto PNRR del PDF:
- Header diverso (Albatros, FUTURA, MIM, Italia Domani)
- Numero protocollo format: `N. XXD_ PNRR-M4C1I1.5/F/YYYY del DD/MM/YYYY`
- Corpo con riferimenti PNRR specifici
- Clausole aggiuntive PNRR (diario di bordo, documentazione PNRR)

- [ ] **Step 5: Commit**

---

### Task 5.2: Generazione e Download Contratto

**Files:**
- Modify: `controllers/ContrattoController.php`
- Modify: `views/contratto/view.php`

- [ ] **Step 1: Aggiungere azioni al ContrattoController**

- `actionGenera($id)` - Genera PDF, salva in `web/uploads/contratti/`, aggiorna `documento_generato_path`
- `actionDownload($id)` - Download PDF generato
- `actionAnteprima($id)` - Preview PDF in browser (inline)

- [ ] **Step 2: Aggiornare view contratto**

Aggiungere sezione "Documento":
- Se non generato: pulsante "Genera Contratto PDF"
- Se generato: pulsanti "Scarica PDF", "Anteprima", "Rigenera"
- Se firmato: badge "Firmato" + dettagli firma

- [ ] **Step 3: Logica selezione template**

In base a `Progetto→FonteFinanziamento→codice`:
- FSE → template FSE
- PNRR → template PNRR
- Default → template generico

- [ ] **Step 4: Upload contratto esterno**

Permettere anche upload di contratto firmato dall'esterno (per chi non usa la generazione automatica).

- [ ] **Step 5: Commit**

---

## Chunk 6: Feature 1 - Verifica Firma P7M/PDF

### Task 6.1: Utility Verifica Firma Digitale

**Files:**
- Create: `controllers/FirmaDigitaleController.php`
- Create: `views/firma-digitale/index.php`
- Create: `views/firma-digitale/risultato.php`
- Create: `components/FirmaVerificator.php`

- [ ] **Step 1: Creare componente FirmaVerificator**

```php
namespace app\components;

class FirmaVerificator
{
    /**
     * Verifica file P7M (PKCS#7 / CMS Signed Data)
     * Usa openssl per estrarre e verificare la firma
     */
    public static function verificaP7M(string $filePath): array
    {
        $result = [
            'valida' => false,
            'firmatario' => null,
            'data_firma' => null,
            'certificato' => [],
            'contenuto_path' => null,
            'errori' => [],
        ];

        // Estrai informazioni firma con openssl
        $cmd = sprintf('openssl pkcs7 -in %s -inform DER -print_certs -noout 2>&1',
            escapeshellarg($filePath));
        $output = shell_exec($cmd);

        // Parse certificato
        // Estrai subject (CN, O, C), issuer, validità, serial
        // Verifica integrità firma
        $cmdVerify = sprintf('openssl smime -verify -in %s -inform DER -noverify 2>&1',
            escapeshellarg($filePath));

        // Estrai contenuto originale
        $contenutoPath = tempnam(sys_get_temp_dir(), 'p7m_');
        $cmdExtract = sprintf('openssl smime -verify -in %s -inform DER -noverify -out %s 2>&1',
            escapeshellarg($filePath), escapeshellarg($contenutoPath));

        return $result;
    }

    /**
     * Verifica firma digitale su PDF (PAdES)
     */
    public static function verificaPDF(string $filePath): array
    {
        // Usa pdfsig (poppler-utils) o analisi manuale
        // Alternativa: parsing ASN.1 dei blocchi firma nel PDF
        $cmd = sprintf('pdfsig %s 2>&1', escapeshellarg($filePath));
        $output = shell_exec($cmd);

        // Parse output pdfsig
        return self::parsePdfsigOutput($output);
    }
}
```

- [ ] **Step 2: Creare controller pagina demo**

```php
// actionIndex: form upload file (P7M o PDF firmato)
// actionVerifica: elabora file, mostra risultato
// Accetta: .p7m, .pdf
// Mostra: validità firma, dati firmatario, data, certificato emittente, contenuto estratto
```

- [ ] **Step 3: View pagina verifica**

Pagina con:
- Card upload con drag & drop (accept: .p7m, .pdf)
- Dopo verifica: card risultato con:
  - Badge VALIDA (verde) / NON VALIDA (rosso)
  - Dati firmatario: Nome, Cognome, CF, Organizzazione
  - Certificato: Emittente, Validità (da/a), Seriale
  - Data firma
  - Download contenuto estratto (per P7M)
  - Anteprima documento firmato

- [ ] **Step 4: Integrazione con Contratto**

In `ContrattoController::actionUploadFirmato($id)`:
- Upload file P7M/PDF firmato
- Auto-verifica firma
- Salva risultato in `contratto.firma_tipo`, `firma_verificata`, `firma_dettagli`
- Se firma valida con CF = docente.codice_fiscale → auto-match

- [ ] **Step 5: Commit**

---

## Chunk 7: Feature 4 - Albo Docenti (Long List)

### Task 7.1: Pagina Albo Docenti

**Files:**
- Modify: `controllers/DocenteController.php`
- Create: `views/docente/albo.php`
- Modify: `models/DocenteSearch.php`

- [ ] **Step 1: Aggiungere azione albo al DocenteController**

```php
public function actionAlbo()
{
    // Mostra tutti i docenti presenti nel sistema
    // Filtri: tipo (docente/coordinatore/tutor), provenienza (TP/RU),
    //         sede (via moduli assegnati), competenze (tag), valutazione media
    // Per ogni docente: lista moduli assegnati passati e correnti, media valutazioni
}
```

- [ ] **Step 2: View albo**

AppGridView con colonne:
- Nome completo (link a profilo)
- Tipo badge (docente/coordinatore/tutor)
- Provenienza badge (TP/RU)
- Competenze (tag principali)
- Moduli assegnati (contatore)
- Media valutazioni (stelline)
- Stato (attivo/non attivo)

Filtri avanzati: sede, tipo contratto, tag/competenze, valutazione minima.

Pulsante "Assegna a modulo" → mostra moduli disponibili senza docente assegnato.

- [ ] **Step 3: Azione assegnazione diretta da albo**

```php
public function actionAssegnaDaAlbo($docente_cf, $modulo_id)
{
    // Crea Istanza fittizia con esito_cts='assegnato' + Assegnazione diretta
    // Salta il flusso bando completo
    // Registra in StoricoEsito come 'assegnazione_da_albo'
}
```

- [ ] **Step 4: Link nel menu**

Aggiungere "Albo Docenti" nel menu principale, sezione "Gestione".

- [ ] **Step 5: Commit**

---

## Chunk 8: Feature 8 - Borse di Studio

### Task 8.1: Controller e CRUD Borse di Studio

**Files:**
- Create: `controllers/BorsaStudioController.php`
- Create: `views/borsa-studio/index.php`
- Create: `views/borsa-studio/view.php`
- Create: `views/borsa-studio/_form.php`
- Create: `views/borsa-studio/eroga.php`

- [ ] **Step 1: Creare controller**

Azioni: index, view, create, update, delete, eroga, revoca, export.
- `actionCreate()` - Form: select studente (da iscrizioni attive), progetto, anno formativo, tipologia sede, dati ISEE/ISPE → calcolo automatico importo
- `actionEroga($id)` - Form erogazione trimestre (Q1-Q4), importo, data
- `actionRevoca($id)` - Form con motivo revoca
- `actionExport()` - Excel con tutti i beneficiari

- [ ] **Step 2: View index**

Stat cards: Totale borse attive, Importo totale, Erogato, Da erogare.
AppGridView: studente, progetto, tipologia, importo, erogato (progress bar), status badge.
Filtri: progetto, status, tipologia sede.

- [ ] **Step 3: View form creazione**

Select studente (da allievi iscritti al progetto selezionato).
Campi ISEE/ISPE con validazione limiti (max ISEE 27.948,60 / max ISPE 60.757,87).
Calcolo automatico JS importo base + maggiorazione in base a:
- tipologia_sede → importo base
- ISEE ≤ metà limite → +15%
- Genere (da allievo) → +20% se studentessa
- Flag disabilità → +40%

- [ ] **Step 4: View erogazione**

Card per ogni trimestre (Q1-Q4) con:
- Importo previsto (importo_finale / 4)
- Importo erogato
- Pulsante "Eroga" se non ancora erogato
- Riepilogo totale

- [ ] **Step 5: Link nel menu**

Sezione "Studenti" → "Borse di Studio".

- [ ] **Step 6: Commit**

---

## Chunk 9: Menu, Manuali e Finalizzazione

### Task 9.1: Aggiornamento Menu Principale

**Files:**
- Modify: `views/layouts/menu.php`

- [ ] **Step 1: Aggiungere nuove voci al menu admin**

Nuova sezione "Contabilità":
- Bilancio (dashboard)
- Movimenti Spesa
- Tranche Finanziamento
- Voci di Spesa (configurazione)

Nuova voce in "Gestione":
- Albo Docenti

Sotto "Docenti":
- Rimborsi Spese
- Richieste Fatturazione

Sotto "Studenti":
- Borse di Studio

Sotto "Strumenti":
- Verifica Firma Digitale

- [ ] **Step 2: Commit**

---

### Task 9.2: Aggiornamento Manuali

**Files:**
- Modify: `views/site/manuale-admin.php`
- Modify: `views/site/manuale-docente.php`
- Modify: `docs/MAPPA_FEATURE_MANUALE.md`

- [ ] **Step 1: Aggiungere sezioni nei manuali**

Manuale Admin: sezioni per Bilancio, Voci Spesa, Rimborsi, Fatturazione, Albo, Borse Studio, Firma Digitale.
Manuale Docente: sezione Rimborsi Spese.

- [ ] **Step 2: Aggiornare mappa feature-manuale**

- [ ] **Step 3: Commit**

---

### Task 9.3: Aggiornamento CLAUDE.md

**Files:**
- Modify: `CLAUDE.md`

- [ ] **Step 1: Aggiornare documentazione progetto**

Aggiungere sezioni per:
- Bilancio/Contabilità (tabelle, controller, workflow)
- Rimborsi spese (workflow docente→admin→pagamento)
- Richiesta fatturazione (workflow)
- Albo docenti
- Borse di studio
- Verifica firma digitale
- Campo tipo docente

- [ ] **Step 2: Commit finale**

---

## Riepilogo File da Creare/Modificare

### Nuovi file (~45)
- 6 migration
- 8 model + 4 search model
- 6 controller
- ~20 views
- 2 mail template
- 2 component (PdfGenerator, FirmaVerificator)

### File da modificare (~15)
- `models/Docente.php`, `DocenteSearch.php`
- `models/Assegnazione.php`, `Contratto.php`, `Pagamento.php`
- `controllers/DocenteController.php`, `ContrattoController.php`, `DocenteAreaController.php`
- `views/layouts/menu.php`
- `views/contratto/view.php`
- `views/site/manuale-admin.php`, `views/site/manuale-docente.php`
- `config/web.php`
- `composer.json`
- `CLAUDE.md`
