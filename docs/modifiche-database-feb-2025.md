# Modifiche Database - Febbraio 2025

**Data**: 16 Febbraio 2025
**Fonte**: Verbale seconda riunione (`doc/seconda riunione.txt`)
**Migration**: m250216_000001 → m250216_000004

---

## Riepilogo Modifiche

Sono state create **4 migration** per implementare i requisiti emersi dalla seconda riunione:

### 1. `m250216_000001_add_provenienza_to_docente.php`

**Tabella**: `docente`
**Campo aggiunto**: `provenienza` ENUM('TP', 'RU') NULL

**Motivazione**:
- Necessario distinguere docenti provenienti dal **mondo del lavoro** (TP = Tipo Professionista) da **ricercatori universitari** (RU)
- Il compenso orario varia in base a provenienza + tipo progetto
- Esempio PNRR: RU = €70/ora, TP = €60/ora
- Regole specifiche per FSE diverse da PNRR

**Note implementative**:
- Campo nullable per gestire dati legacy
- Indice creato per query di filtro

---

### 2. `m250216_000002_add_date_lezioni_to_contratto.php`

**Tabella**: `contratto`
**Campi aggiunti**:
- `data_inizio_lezioni` DATE NULL
- `data_fine_lezioni` DATE NULL

**Motivazione**:
- I campi `data_inizio` e `data_fine` esistenti rappresentano il **periodo contrattuale**
- Servono date **effettive delle lezioni** per:
  1. **Alert automatici** quando un docente sta per terminare
  2. **Pianificazione**: sapere quando un docente è disponibile per nuovi incarichi
  3. **Rendicontazione**: tracciare esattamente quando sono state erogate le ore

**Scenario reale**:
```
Contratto firmato:     01/09/2025 (data_inizio)
Inizio lezioni:        15/09/2025 (data_inizio_lezioni) ← 2 settimane dopo
Fine lezioni prevista: 20/12/2025 (data_fine_lezioni)
Fine contratto:        31/12/2025 (data_fine) ← include pagamenti
```

**Note implementative**:
- Campi nullable per contratti già esistenti
- Indice composto per ricerche per periodo

---

### 3. `m250216_000003_add_esito_cts_to_istanza.php`

**Tabella**: `istanza`
**Campo aggiunto**: `esito_cts` ENUM('assegnato', 'idoneo', 'non_idoneo') NULL

**Motivazione**:
La Commissione Tecnico Scientifica (CTS) valuta le candidature e produce **tre esiti distinti**:

1. **assegnato**: Docente selezionato → genera ASSEGNAZIONE → CONTRATTO
2. **idoneo**: Valutato positivamente ma non assegnato → entra in **long list**
3. **non_idoneo**: Valutazione negativa

**Workflow completo**:
```
ISTANZA presentata (status='presentata')
    ↓
CTS valuta (status='in_valutazione')
    ↓
CTS delibera (status='valutata')
    ├─→ esito_cts='assegnato' → ASSEGNAZIONE creata
    ├─→ esito_cts='idoneo' → in long list per futuri bandi
    └─→ esito_cts='non_idoneo' → respinto
```

**Storicità importante**:
- Tracciare docenti dichiarati "non idonei" in passato
- Possibile che stesso docente sia idoneo in un bando e non idoneo in un altro
- Sistema audit_log conserva tutta la cronologia

**Note implementative**:
- Campo separato da `status` (che gestisce workflow domanda)
- Nullable perché istanze non ancora valutate non hanno esito
- Indice per query "trova tutti gli idonei disponibili"

---

### 4. `m250216_000004_add_numero_studenti_to_progetto.php`

**Tabella**: `progetto`
**Campo aggiunto**: `numero_studenti` INT DEFAULT 20

**Motivazione**:
- Dalla riunione: "Ogni progetto ha 20 studenti" (standard)
- Utile per:
  - Statistiche e report
  - Calcolo capienza aule
  - Pianificazione docenti/materiali

**Note implementative**:
- Default 20 (valore standard dichiarato in riunione)
- Modificabile per progetti specifici

---

## Chiarimenti Terminologici

### Problema iniziale
Confusione tra "corso", "progetto" e "modulo" durante la progettazione.

### Terminologia corretta (dalla riunione)

| Termine Database | Nome Comune | Contesto | Esempio |
|------------------|-------------|----------|---------|
| `progetto` | **CORSO** (per studenti)<br>**PROGETTO** (per amministrazione) | Percorso formativo completo finanziato | "Health Food" |
| `avviso` | **BANDO** | Selezione pubblica docenti | "Avviso 2/2025" |
| `modulo` | **MATERIA**<br>**MODULO** | Singola disciplina | "Chimica degli Alimenti" |

### Citazioni dalla riunione
> "Un corso è un progetto." (riga 238)
> "Corso uguale progetto" (riga 242)
> "Modulo uguale materia" (riga 244)

### Struttura tipo
```
PROGETTO "Health Food" (PNRR)
├─ Sede: Messina
├─ Studenti: 20
├─ Budget: €500.000
└─ AVVISO 9/2024
   ├─ MODULO "Chimica degli Alimenti" (50 ore)
   ├─ MODULO "Sicurezza Alimentare" (30 ore)
   ├─ MODULO "Nutrizione" (40 ore)
   └─ ... (totale ~60 moduli, ~1800 ore)
```

---

## Workflow Compensi Docenti

### Regola generale emersa
Il compenso orario **non è fisso per modulo** ma dipende da:
1. **Tipo progetto**: FSE, PNRR, MIM (regole diverse)
2. **Tipo docente**: TP (€60/ora) vs RU (€70/ora) in PNRR

### Esempio PNRR
```
MODULO "Pari Opportunità" (10 ore)

Docente A (TP - mondo lavoro):
  10 ore × €60/ora = €600

Docente B (RU - universitario):
  10 ore × €70/ora = €700
```

### Implementazione
- `modulo.compenso_orario`: valore **base/previsto** (facoltativo)
- `contratto.compenso_orario`: valore **effettivo** (obbligatorio)
- Business logic: calcolare compenso in base a `docente.provenienza` + tipo progetto

---

## Rendicontazione

### FSE (dettagliata)
Richiede per ogni docente:
- ✅ Contratto firmato
- ✅ Fattura/ricevuta
- ✅ Bonifico bancario
- ✅ Curriculum vitae
- ✅ Allegati domanda
- ✅ Bando di selezione
- ✅ Verbale CTS assegnazione
- ✅ **Registro presenze giornaliero** ← da implementare

### PNRR (semplificata)
- Meno vincolante
- Documentazione ridotta
- Più flessibilità nelle modifiche

### Implementazione futura
**Registro elettronico docenti**:
- Area docente per inserimento ore
- Alternativa/integrazione con piattaforma e-learning (14/20)
- Export automatico per rendicontazione
- Tabella `calendario` già pronta per tracciare tutto

---

## Long List Docenti

### Concetto
Sistema per mantenere un **elenco qualificato** di docenti disponibili.

### Come funziona
1. Docente presenta candidatura (ISTANZA) per un MODULO
2. CTS valuta → `esito_cts='idoneo'`
3. Docente entra in **long list** per quel tipo di insegnamento
4. Futuri bandi: amministrazione può vedere chi è già stato valutato idoneo
5. Sistema mostra:
   - Numero contratti già svolti
   - Valutazioni studenti
   - Storico idoneità/non idoneità
   - Distanza dalla sede

### Query tipo
```sql
-- Trova docenti idonei per "Chimica degli Alimenti" disponibili
SELECT d.*, COUNT(c.id) as num_contratti
FROM docente d
INNER JOIN istanza i ON i.docente_codice_fiscale = d.codice_fiscale
LEFT JOIN assegnazione a ON a.istanza_id = i.id
LEFT JOIN contratto c ON c.assegnazione_id = a.id
WHERE i.modulo_id IN (
    SELECT id FROM modulo WHERE titolo LIKE '%Chimica%'
)
AND i.esito_cts IN ('assegnato', 'idoneo')
AND d.deleted_at IS NULL
GROUP BY d.codice_fiscale
ORDER BY num_contratti ASC, i.punteggio_totale DESC;
```

---

## Progetti Biennali

### Struttura temporale
- Tutti i progetti sono **biennali**
- Anno 1: consuntivo (cosa abbiamo speso)
- Anno 2: preventivo (cosa spenderemo)

### Complicazione rendicontazione
```
PROGETTO "Health Food" 2025-2027
├─ Anno 1 (2025-2026)
│  ├─ Budget previsto: €250.000
│  └─ Consuntivo da rendicontare
└─ Anno 2 (2026-2027)
   ├─ Budget previsto: €250.000
   └─ Preventivo da pianificare
```

### Implementazione
- Campo `progetto.data_inizio` e `progetto.data_fine` (2 anni)
- Contratti possono essere dentro anno 1 o anno 2
- Report separati per consuntivo/preventivo

---

## Piattaforma E-Learning (Futura)

### Acquisizione in corso
- Piattaforma già esistente da altro ITS
- Nome: **14/20** (da confermare)
- Integrazione prevista con questo gestionale

### Funzionalità previste
- **Aula virtuale** per ogni corso (progetto)
- Docenti caricano materiali
- Studenti accedono a lezioni/materiali
- **Tracciamento presenze** automatico
- Chat docente-studenti (eliminare WhatsApp)
- Registrazioni lezioni
- **Registro elettronico** integrato

### Integrazione con questo sistema
- Import/export studenti da gestionale a piattaforma
- Sincronizzazione presenze
- Possibile import CSV massivo (da richiedere alla Regione)

---

## TODO Futuri

### Priorità Alta
- [ ] Popolare database con progetto completo (scelto da cliente)
- [ ] Import demo docenti con dati reali
- [ ] Implementare filtri long list (competenze, distanza, valutazioni)
- [ ] Area docente per visualizzare moduli assegnati

### Priorità Media
- [ ] Alert scadenza contratti (`data_fine_lezioni`)
- [ ] Registro elettronico presenze docenti
- [ ] Calcolo automatico compensi (TP/RU + tipo progetto)
- [ ] Report rendicontazione FSE/PNRR

### Priorità Bassa
- [ ] Integrazione piattaforma e-learning 14/20
- [ ] Import/export massivo studenti
- [ ] Dashboard statistiche docenti (ore totali, guadagno, sedi)

---

## Esecuzione Migration

```bash
# Posizionarsi nella directory del progetto
cd /Users/deduzzo/dev/albatros-manager-yii2

# Eseguire le migration
docker exec -w /app albatros-php php yii migrate

# Verificare le modifiche
docker exec -w /app albatros-php php yii migrate/history

# In caso di problemi, rollback
docker exec -w /app albatros-php php yii migrate/down 4
```

---

## Note Finali

Tutte le modifiche sono state documentate in:
- ✅ Migration files (m250216_000001 → m250216_000004)
- ✅ CLAUDE.md (sezione Terminologia + Campi Specifici)
- ✅ Questo documento (doc/modifiche-database-feb-2025.md)
- ✅ Verbale riunione (doc/seconda riunione.txt)

Le modifiche sono **backward compatible**:
- Tutti i nuovi campi sono NULLABLE
- I dati esistenti non vengono alterati
- Sistema funziona anche senza compilare i nuovi campi
