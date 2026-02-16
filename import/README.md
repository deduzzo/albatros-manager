# Import Dati - Albatros Manager

Questa cartella contiene i file necessari per l'importazione di dati da file Excel.

## File

### TEMPLATE_IMPORT_DATI.xlsx
Template Excel standardizzato per l'import di nuovi progetti.

**Struttura (4 fogli):**
1. **📖 ISTRUZIONI** - Guida completa all'utilizzo del template
2. **ℹ️ INFO PROGETTO** - Informazioni progetto, avviso e sede
3. **📚 MODULI** - Elenco moduli formativi
4. **👥 DOCENTI & ESITI** - Docenti, assegnazioni e valutazioni CTS

**Come usarlo:**
1. Scarica il template dall'interfaccia web (Gestione > Import Dati > Scarica Template)
2. Compila tutti i fogli seguendo le istruzioni
3. Carica il file compilato tramite interfaccia web
4. Il sistema validerà i dati e chiederà conferma prima di salvare

### AGRIFOOD_AVV2_MESSINA_IMPORT.xlsx
Esempio di file compilato con dati reali del progetto Agrifood Avviso 2 Messina.

Questo file è stato convertito dal formato originale nel formato del template standardizzato e include:
- 28 moduli formativi
- 105 righe di docenti/esiti (ogni docente ha la propria riga con CODICE_MODULO ripetuto)
- Gestione casi speciali: rinunce, compensi variabili, moduli senza assegnatari

### ORIGINALE_Agrifood_Avv2_Messina.xlsx
File originale fornito dal cliente per il progetto Agrifood.

Formato non standardizzato, da usare solo come riferimento. Per l'import usare il file convertito `AGRIFOOD_AVV2_MESSINA_IMPORT.xlsx`.

## Procedure Import

### Via Web Interface
1. Accedi al sistema come amministratore
2. Vai su **Gestione > Import Dati**
3. Clicca su **Scarica Template** (se necessario)
4. Compila il template Excel
5. Carica il file tramite il form di upload
6. Esegui prima un **test** (modalità dry-run) per validare
7. Se validazione OK, conferma per salvare nel database

### Via Script Python
Per conversioni da formati legacy o personalizzati, vedi gli script in `/tmp/`:
- `create_excel_template.py` - Genera il template vuoto
- `analisi_mapping_db.py` - Analizza mapping dati -> database
- `converti_dati_reali_in_template_v2.py` - Converte formato legacy in template

## Casi Speciali Supportati

### Rinunce
Aggiungere `(Rinuncia)` dopo il nome dell'assegnatario:
```
ASSEGNATARIO: Giacoponello Marco (Rinuncia)
```
Il sistema creerà l'istanza ma NON l'assegnazione/contratto.

### Compensi Variabili
Specificare l'importo esatto nella colonna `COMPENSO_ORARIO`:
```
COMPENSO_ORARIO: 65
```
Sovrascrive il compenso di default basato su provenienza (TP=60, RU=70).

### Moduli Senza Assegnatari
Lasciare vuoto `ASSEGNATARIO`, compilare solo `IDONEI`:
```
CODICE_MODULO: MOD-001
ASSEGNATARIO:
IDONEI: Rossi Mario
```

### Più Docenti per Stesso Modulo
Ripetere `CODICE_MODULO` in ogni riga:
```
MOD-001 | Rossi Mario    | | ...   <- assegnatario
MOD-001 | | Bianchi Luigi | ...   <- idoneo
MOD-001 | | Verdi Giuseppe | ...  <- altro idoneo
```

**IMPORTANTE**: Ogni riga deve avere tutte le celle valorizzate. Non lasciare `CODICE_MODULO` vuoto.

## Validazioni Automatiche

Il sistema verifica:
- ✓ Campi obbligatori compilati
- ✓ Provenienza valida (TP o RU)
- ✓ Compenso orario numerico
- ✓ Ore assegnate numeriche
- ✓ Date nel formato corretto (AAAA-MM-GG)
- ✓ Riferimenti modulo esistenti
- ✓ Codici fiscali validi (16 caratteri)

## Log e Tracciabilità

Ogni import viene tracciato:
- Log operazioni eseguite
- Errori e warning
- Statistiche (n. record creati per tabella)
- Utente che ha eseguito l'import
- Data e ora import
- Audit completo in tabella `audit_log`

## Supporto

Per problemi o domande sull'import:
1. Verifica il log nell'interfaccia web dopo l'upload
2. Consulta `docs/modifiche-database-feb-2025.md` per dettagli schema
3. Leggi `docs/CRUD_GUIDELINES.md` per convenzioni generali
