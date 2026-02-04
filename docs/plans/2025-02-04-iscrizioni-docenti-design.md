# Design: Sistema Iscrizioni Docenti ai Bandi

Data: 2025-02-04

## Obiettivo

Implementare il sistema completo di iscrizione docenti ai bandi formativi, includendo:
- Area riservata docenti con registrazione e login
- CRUD Progetti, Avvisi/Bandi, Moduli
- Flusso iscrizione docenti con upload documenti
- Gestione tag competenze per iscrizione
- Snapshot storico dati per ogni iscrizione
- Dati di esempio per demo

---

## 1. Architettura Autenticazione Docenti

### Nuova tabella `docente_auth`

```sql
CREATE TABLE docente_auth (
    id INT PRIMARY KEY AUTO_INCREMENT,
    docente_codice_fiscale CHAR(16) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    auth_key VARCHAR(32) NOT NULL,
    email_verify_token VARCHAR(64) NULL,
    password_reset_token VARCHAR(64) NULL,
    status ENUM('pending_verification', 'active', 'suspended') NOT NULL DEFAULT 'pending_verification',
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,
    deleted_by INT NULL,
    FOREIGN KEY (docente_codice_fiscale) REFERENCES docente(codice_fiscale) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES utente(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES utente(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES utente(id) ON DELETE SET NULL
);
```

### Flusso registrazione

1. Docente accede a `/docente/registrazione`
2. Compila form: codice fiscale, email, password, dati anagrafici
3. Se CF già esistente in `docente`: precompila dati, chiede solo password
4. Se CF nuovo: inserisce tutti i dati anagrafici
5. Sistema crea record in `docente` (se nuovo) e `docente_auth` con status `pending_verification`
6. Invia email con link di verifica (token valido 48 ore)
7. Click su link → status diventa `active`
8. Docente può ora accedere a `/docente/login`

### Gestione password

- Admin può resettare password da pannello → genera token, invia email
- Docente può richiedere reset da login → "Password dimenticata"
- Link reset valido 24 ore

---

## 2. Sistema Documenti Flessibile

### Nuova tabella `avviso_documento_tipo`

```sql
CREATE TABLE avviso_documento_tipo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    avviso_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT NULL,
    formati_ammessi VARCHAR(100) NOT NULL DEFAULT 'pdf',
    dimensione_max_mb INT NOT NULL DEFAULT 5,
    obbligatorio TINYINT(1) NOT NULL DEFAULT 1,
    ordine SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,
    deleted_by INT NULL,
    FOREIGN KEY (avviso_id) REFERENCES avviso(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES utente(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES utente(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES utente(id) ON DELETE SET NULL
);
```

### Nuova tabella `istanza_documento`

```sql
CREATE TABLE istanza_documento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    istanza_id INT NOT NULL,
    avviso_documento_tipo_id INT NOT NULL,
    nome_file_originale VARCHAR(255) NOT NULL,
    nome_file_salvato VARCHAR(100) NOT NULL,
    path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    dimensione_bytes INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (istanza_id) REFERENCES istanza(id) ON DELETE CASCADE,
    FOREIGN KEY (avviso_documento_tipo_id) REFERENCES avviso_documento_tipo(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES utente(id) ON DELETE SET NULL
);
```

### Struttura filesystem

```
web/uploads/
└── istanze/
    └── {anno}/
        └── {istanza_id}/
            └── {uuid}.{ext}
```

---

## 3. Tag Iscrizione e Snapshot

### Nuova tabella `istanza_tag`

```sql
CREATE TABLE istanza_tag (
    id INT PRIMARY KEY AUTO_INCREMENT,
    istanza_id INT NOT NULL,
    tag_id INT NOT NULL,
    livello SMALLINT NULL,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    promosso_at TIMESTAMP NULL,
    promosso_by INT NULL,
    UNIQUE KEY idx_istanza_tag_unique (istanza_id, tag_id),
    FOREIGN KEY (istanza_id) REFERENCES istanza(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tag_curriculum(id) ON DELETE CASCADE,
    FOREIGN KEY (promosso_by) REFERENCES utente(id) ON DELETE SET NULL
);
```

### Nuova tabella `istanza_snapshot`

```sql
CREATE TABLE istanza_snapshot (
    id INT PRIMARY KEY AUTO_INCREMENT,
    istanza_id INT NOT NULL UNIQUE,
    dati_docente JSON NOT NULL,
    tags_docente JSON NULL,
    tags_iscrizione JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (istanza_id) REFERENCES istanza(id) ON DELETE CASCADE
);
```

### Struttura JSON `dati_docente`

```json
{
  "codice_fiscale": "RSSMRA80A01F205X",
  "cognome": "Rossi",
  "nome": "Mario",
  "data_nascita": "1980-01-01",
  "luogo_nascita": "Milano",
  "indirizzo": "Via Roma 1",
  "cap": "20100",
  "citta": "Milano",
  "provincia": "MI",
  "telefono": "333-1234567",
  "email": "mario.rossi@email.it",
  "titolo_studio": "Laurea in Informatica",
  "specializzazioni": "Web Development, Data Science",
  "partita_iva": "12345678901"
}
```

### Promozione tag (admin)

1. Admin visualizza istanza → vede tag selezionati
2. Pulsante "Promuovi al profilo" (singolo o tutti)
3. Sistema copia in `docente_tag` se non già presente
4. Aggiorna `promosso_at` e `promosso_by`

---

## 4. Routes Area Docente

```
/docente/registrazione          → Form registrazione
/docente/login                  → Login CF + password
/docente/logout                 → Logout
/docente/verifica-email?token=  → Conferma email
/docente/password-dimenticata   → Richiesta reset
/docente/reset-password?token=  → Form nuova password
/docente/dashboard              → Homepage (futura)
/docente/profilo                → Visualizza/modifica dati
/docente/bandi                  → Lista bandi aperti
/docente/bandi/{id}             → Dettaglio bando + moduli
/docente/iscrizione/{modulo_id} → Wizard iscrizione
/docente/iscrizioni             → Storico personale
/docente/iscrizioni/{id}        → Dettaglio iscrizione
```

### Flusso iscrizione (wizard 4 step)

1. **Dati personali**: Form precompilato, modificabile. Checkbox "Aggiorna profilo"
2. **Competenze**: Tag attuali + selezione nuovi tag per iscrizione
3. **Documenti**: Upload documenti richiesti dal bando
4. **Riepilogo**: Preview, checkbox accettazione, invio

---

## 5. Area Admin

### Menu navigazione

```
Dashboard
Anagrafiche
  └── Docenti
  └── Sedi
  └── Fonti Finanziamento
Formazione
  └── Progetti
  └── Avvisi/Bandi
  └── Moduli/Corsi
Selezione
  └── Iscrizioni Docenti
```

### CRUD Progetti (`/progetto/*`)

- Index con filtri: fonte, status, date
- View: dettaglio + avvisi collegati
- Create/Update: fonte finanziamento, conto corrente, budget, date

### CRUD Avvisi (`/avviso/*`)

- Index con filtri: progetto, status, scadenza
- View: dettaglio + moduli + iscrizioni + documenti richiesti
- Create/Update: progetto, date, status
- Tab documenti: CRUD inline `avviso_documento_tipo`

### CRUD Moduli (`/modulo/*`)

- Index con filtri: avviso, sede, status
- View: dettaglio + iscrizioni
- Create/Update: avviso, sede, ore, compenso, requisiti

### Gestione Iscrizioni (`/istanza/*`)

- Index con filtri: bando, modulo, docente, status
- View completa:
  - Dati attuali docente
  - Snapshot storico (accordion)
  - Tag con promozione
  - Documenti con download
  - Cambio status

---

## 6. Dati di Esempio

### Sedi

| ID | Codice | Nome |
|----|--------|------|
| 1 | SEDE-01 | Sede Centrale - Via Garibaldi 10 |
| 2 | LAB-NORD | Laboratorio Nord - Via Mazzini 5 |
| 3 | LAB-SUD | Laboratorio Sud - Via Verdi 20 |

### Progetti

| Codice | Titolo | Fonte | Status |
|--------|--------|-------|--------|
| FSE-2024-001 | Competenze Digitali per il Lavoro | FSE | attivo |
| PNRR-2024-001 | STEM e Innovazione Didattica | PNRR | attivo |
| MIM-2024-001 | Aggiornamento Docenti Scuola | MIM | concluso |

### Avvisi

| Progetto | Codice | Titolo | Scadenza | Status |
|----------|--------|--------|----------|--------|
| FSE-2024-001 | AVV-001 | Selezione Esperti ICT | 2024-06-30 | chiuso |
| FSE-2024-001 | AVV-002 | Selezione Docenti Web Dev | 2024-12-31 | chiuso |
| PNRR-2024-001 | AVV-003 | Esperti Formazione STEM 2025 | 2025-06-30 | pubblicato |

### Moduli (AVV-003 - bando aperto)

| Codice | Titolo | Ore | €/ora | Sede |
|--------|--------|-----|-------|------|
| MOD-PYTHON | Python per Data Science | 40 | 70.00 | Sede Centrale |
| MOD-WEB | Sviluppo Web Full Stack | 60 | 70.00 | Laboratorio Nord |
| MOD-AI | Introduzione all'Intelligenza Artificiale | 30 | 80.00 | Sede Centrale |
| MOD-CLOUD | Cloud Computing Base | 40 | 75.00 | Laboratorio Nord |

### Documenti richiesti (AVV-003)

| Nome | Formati | Obbligatorio |
|------|---------|--------------|
| Curriculum Vitae | pdf | Sì |
| Documento di identità | pdf,jpg,png | Sì |
| Certificazioni professionali | pdf | No |

### Docenti

| CF | Nome | Email | Tag principali |
|----|------|-------|----------------|
| RSSMRA80A01F205X | Mario Rossi | mario.rossi@email.it | PHP, Laravel, MySQL |
| VRDLGI85B15H501Y | Luigi Verdi | luigi.verdi@email.it | Python, AI, Data Science |
| BNCLRA78C20F839Z | Laura Bianchi | laura.bianchi@email.it | JavaScript, React, Node.js |
| NRIGPP82D10A944W | Giuseppe Neri | giuseppe.neri@email.it | Java, Spring, AWS |
| GLLMRC75E25L219V | Marco Gialli | marco.gialli@email.it | Cloud, DevOps, Docker |
| RSSSFN88F30G273U | Sofia Russo | sofia.russo@email.it | UX, Teaching, Communication |

---

## 7. File da Creare/Modificare

### Migrations (ordine)

1. `m250204_000001_create_docente_auth_table.php`
2. `m250204_000002_create_avviso_documento_tipo_table.php`
3. `m250204_000003_create_istanza_documento_table.php`
4. `m250204_000004_create_istanza_tag_table.php`
5. `m250204_000005_create_istanza_snapshot_table.php`
6. `m250204_000006_insert_sample_data.php`

### Models

- `DocenteAuth.php` - Autenticazione docenti
- `DocenteIdentity.php` - IdentityInterface per docenti
- `Progetto.php` + `ProgettoSearch.php`
- `Avviso.php` + `AvvisoSearch.php`
- `Modulo.php` + `ModuloSearch.php`
- `AvvisoDocumentoTipo.php`
- `IstanzaDocumento.php`
- `IstanzaTag.php`
- `IstanzaSnapshot.php`
- `IstanzaSearch.php`
- Aggiornare `Istanza.php` con relazioni

### Controllers

- `DocenteSiteController.php` - Login/registrazione/password
- `DocenteBandiController.php` - Lista bandi e iscrizione
- `DocenteProfiloController.php` - Gestione profilo
- `ProgettoController.php` - CRUD admin
- `AvvisoController.php` - CRUD admin
- `ModuloController.php` - CRUD admin
- `IstanzaController.php` - Gestione iscrizioni admin

### Views

```
views/
├── docente-site/
│   ├── login.php
│   ├── registrazione.php
│   ├── verifica-email.php
│   ├── password-dimenticata.php
│   └── reset-password.php
├── docente-bandi/
│   ├── index.php
│   ├── view.php
│   ├── iscrizione.php (wizard)
│   └── iscrizioni.php
├── docente-profilo/
│   ├── index.php
│   └── update.php
├── progetto/
│   ├── index.php, view.php, create.php, update.php, _form.php
├── avviso/
│   ├── index.php, view.php, create.php, update.php, _form.php
│   └── _documenti.php (gestione documenti richiesti)
├── modulo/
│   ├── index.php, view.php, create.php, update.php, _form.php
└── istanza/
    ├── index.php, view.php
    └── _snapshot.php (visualizza storico)
```

### Layouts

- `views/layouts/docente.php` - Layout area docenti (senza menu admin)

### Config

- `config/web.php` - Aggiungere componente `docenteUser` per multi-identity
- `config/params.php` - Parametri email, upload path

---

## 8. Priorità Implementazione

### Fase 1 - Base (Migrations + Models)
1. Tutte le migrations
2. Tutti i models base
3. Eseguire migrations e verificare DB

### Fase 2 - CRUD Admin
1. ProgettoController + views
2. AvvisoController + views (con documenti)
3. ModuloController + views
4. Aggiornare menu

### Fase 3 - Area Docenti
1. DocenteAuth model + identity
2. Config multi-identity
3. DocenteSiteController (login/registrazione)
4. Layout docente

### Fase 4 - Iscrizioni
1. Wizard iscrizione docente
2. Upload documenti
3. Gestione tag iscrizione
4. Snapshot automatico

### Fase 5 - Admin Iscrizioni
1. IstanzaController completo
2. Visualizzazione snapshot
3. Promozione tag
4. Download documenti

---

## Note Tecniche

### Multi-Identity Yii2

```php
// config/web.php
'components' => [
    'user' => [...], // Admin (esistente)
    'docenteUser' => [
        'class' => 'yii\web\User',
        'identityClass' => 'app\models\DocenteIdentity',
        'enableAutoLogin' => true,
        'loginUrl' => ['docente-site/login'],
        'identityCookie' => ['name' => '_docenteIdentity'],
    ],
],
```

### Email

Configurare Mailer in `config/web.php` per invio email verifica/reset.
Per sviluppo: `'useFileTransport' => true` salva email in `runtime/mail/`.

### Upload

- Validazione MIME type lato server
- Generare UUID per nome file salvato
- Creare directory se non esiste
- Impostare permessi corretti
