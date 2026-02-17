# Design: Feature Mancanti Albatros Manager

**Data**: 2026-02-17
**Priorita**: Alta

## Obiettivo

Completare tutte le feature mancanti del sistema Albatros Manager, portandolo dal ~82% al ~100% di completamento funzionale.

## Feature da Implementare (7 aree)

### 1. CF Validato - Toggle Manuale + Validazione Automatica

**Stato attuale**: Campo `codice_fiscale_validato` (boolean) esiste in DB e model. Badge visibile in view. Manca nel form e nel controller.

**Implementazione**:
- **Form docente** (`views/docente/_form.php`): checkbox toggle switch Tabler + pulsante "Verifica CF"
- **Controller** (`DocenteController`): `actionValidaCf($id)` AJAX con algoritmo check digit
- **Helper**: metodo statico `Docente::verificaCodiceFiscale($cf)` con algoritmo standard italiano (peso pari/dispari, modulo 26)
- Auto-validazione al salvataggio se il CF passa check digit

### 2. Tag Curriculum - CRUD + Assegnazione Docenti

**Stato attuale**: Model TagCategoria, TagCurriculum, DocenteTag, IstanzaTag implementati. DB con 8 categorie e 40+ tag di default. Controller e view mancanti.

**Implementazione**:
- **TagCategoriaController**: CRUD standard (index con conteggio tag, create/update/delete)
- **TagCurriculumController**: CRUD con filtro per categoria (index con conteggio docenti)
- **Search model**: TagCategoriaSearch, TagCurriculumSearch
- **Assegnazione tag docente**: sezione in `views/docente/view.php` con card per categoria, stelle livello 1-5
- **Modale gestione**: checkbox multi-tag + slider livello, AJAX save via `DocenteController::actionGestisciTag($id)`
- **Menu**: fix href="#" righe 94-98

### 3. Storico Esiti Istanze

**Stato attuale**: Model StoricoEsito implementato con afterSave che aggiorna istanza.esito_cts. Manca UI per registrare esiti.

**Implementazione**:
- **IstanzaController**: `actionRegistraEsito($id)` AJAX POST
- **Vista istanza/view.php**: sezione "Cronologia Esiti" con timeline verticale Tabler
- **Modale**: dropdown esito (assegnato/idoneo/non_idoneo/rinuncia/subentro) + data + note

### 4. Report/Statistiche

**Stato attuale**: Menu "Report" con 3 voci href="#". Nessun controller.

**Implementazione**:
- **ReportController::actionStatistiche()**: dashboard con ApexCharts (gia' installato)
  - Torta: progetti per fonte finanziamento
  - Barre: budget contrattualizzato vs pagato per progetto
  - Linea: andamento pagamenti mensili
  - KPI cards: totale docenti, ore erogate, % completamento
- **Menu**: fix href="#" riga 160

### 5. Export Dati

**Implementazione**:
- **ReportController::actionExport()**: form con selezione entita' + formato
- **ReportController::actionExportCsv($entity)**: export CSV per docenti, pagamenti, contratti, moduli, progetti
- **ReportController::actionExportExcel($entity)**: export Excel con PhpSpreadsheet (gia' installato)
- **Menu**: fix href="#" riga 163

### 6. Audit Log Viewer

**Stato attuale**: Model AuditLog completo con metodi getRecordHistory, getUserActivity. Controller mancante.

**Implementazione**:
- **AuditLogController**: actionIndex (lista con filtri), actionView (dettaglio con diff JSON)
- **AuditLogSearch**: search model per GridView
- **Viste**: index con filtri (tabella, azione, utente, range date), view con diff colorato
- **Menu**: fix href="#" riga 166

### 7. Documenti Istanza + Snapshot

**Stato attuale**: Model IstanzaDocumento, IstanzaSnapshot, AvvisoDocumentoTipo implementati. Controller e view mancanti.

**Implementazione**:
- **IstanzaController esteso**:
  - `actionUploadDocumento($id)`: upload AJAX con validazione tipo/dimensione
  - `actionDownloadDocumento($id)`: download file
  - `actionDeleteDocumento($id)`: soft delete
- **Vista istanza/view.php**:
  - Sezione "Documenti" con lista + stato completamento per doc obbligatori
  - Drop zone drag-and-drop per upload
  - Sezione "Snapshot" read-only con dati docente al momento della candidatura
- **Trigger snapshot**: in Istanza afterSave() quando status cambia ad "assegnato"

## Approccio Implementativo

3 onde parallele con team di 4 agenti:

| Onda | Feature | Agenti |
|------|---------|--------|
| **Onda 1** | Tag CRUD + CF Validato + Storico Esiti | model + controller + ui + tester |
| **Onda 2** | Report/Statistiche + Export + Audit Log | model + controller + ui + tester |
| **Onda 3** | Documenti Istanza + Snapshot | model + controller + ui + tester |

## Convenzioni

- Model estendono BaseActiveRecord
- Soft delete su tutto
- Stile Tabler v1.4.0 + Tabler Icons
- AppActiveForm / AppGridView per i widget
- NON usare bg-secondary (usare bg-muted)
- NON usare joinWith (usare leftJoin per SoftDeleteTrait)
- CRUD Guidelines da docs/CRUD_GUIDELINES.md
