# Configurazione Team di Agenti - Albatros Manager

## Panoramica

Team di 4 agenti specializzati per lo sviluppo parallelo del progetto Yii2.

```
model-agent → controller-agent → ui-agent → tester-agent
   (DB)          (Logica)          (Design)     (QA Chrome)
```

## Comando Rapido

Per avviare il team, dire a Claude:
```
Avvia il team di agenti come da docs/AGENT_TEAM_CONFIG.md
```

---

## Agente 1: model-agent

**Ruolo**: Model & Database - Verifica conformita model, crea migration, gestisce schema DB

**Tipo**: `general-purpose`
**Permessi**: `bypassPermissions`

**Responsabilita**:
- Verificare che i model PHP riflettano lo schema DB (rules, relations, labels)
- Creare/aggiornare i model mancanti estendendo `BaseActiveRecord`
- Creare Search model per le GridView
- Creare migration se servono modifiche allo schema
- Verificare relazioni inverse tra model correlati

**Comandi DB utili**:
```bash
# Schema tabella
docker exec albatros-php php -r "\$pdo = new PDO('mysql:host=db;dbname=albatros', 'albatros', 'albatros'); \$stmt = \$pdo->query('DESCRIBE nome_tabella'); print_r(\$stmt->fetchAll(PDO::FETCH_ASSOC));"

# Eseguire migration
docker exec -w /app albatros-php php yii migrate

# Storico migration
docker exec -w /app albatros-php php yii migrate/history
```

**Priorita esecuzione**: PRIMO (gli altri agenti dipendono dai model)

---

## Agente 2: controller-agent

**Ruolo**: Controller & Logica - Crea controller CRUD e struttura base delle views

**Tipo**: `general-purpose`
**Permessi**: `bypassPermissions`

**Responsabilita**:
- Creare controller con azioni CRUD (index, view, create, update, delete)
- Creare struttura base delle views (index, view, create, update, _form)
- Gestire AccessControl (solo utenti autenticati)
- Aggiornare menu navigazione (`views/layouts/menu.php`)
- Gestire soft delete nelle azioni delete

**Riferimenti obbligatori**:
- Leggere controller esistenti come pattern (es: `DocenteController.php`)
- Usare widget custom: `AppGridView`, `AppActiveForm`, `Alert`
- Seguire `docs/CRUD_GUIDELINES.md`

**Priorita esecuzione**: SECONDO (dopo model-agent)

---

## Agente 3: ui-agent

**Ruolo**: UI & Design - Rende le interfacce conformi alle specifiche Tabler

**Tipo**: `general-purpose`
**Permessi**: `bypassPermissions`

**Responsabilita**:
- Revisionare TUTTE le views per conformita con `docs/CRUD_GUIDELINES.md`
- Applicare stile Tabler v1.4.0 (card, form, tabelle)
- Icone Tabler Icons (`<i class="ti ti-{nome}"></i>`)
- Badge colorati per stati/enum
- Formattazione italiana (date dd/mm/yyyy, importi 1.500,00)
- Layout responsive mobile-first
- Flash messages con Alert widget
- NON tocca i controller, solo le views

**Palette colori Tabler**:
- Primary: `#206bc4`
- Success: `#2fb344`
- Warning: `#f76707`
- Danger: `#d63939`

**Views di riferimento** (migliori esistenti):
- `views/docente/` - CRUD completo ben strutturato
- `views/progetto/` - Buon esempio con relazioni
- `views/avviso/` - Esempio con tab e documenti

**Priorita esecuzione**: TERZO (dopo controller-agent)

---

## Agente 4: tester-agent

**Ruolo**: QA & Testing via Chrome - Verifica funzionamento pagine e segnala errori

**Tipo**: `general-purpose`
**Permessi**: `bypassPermissions`

**Responsabilita**:
- Usare Chrome browser automation (`mcp__claude-in-chrome__*`)
- Navigare su http://localhost:8000 e fare login (admin/admin)
- Verificare che ogni pagina carichi senza errori PHP
- Testare form e operazioni CRUD quando necessario
- Controllare console browser per errori JavaScript
- Segnalare errori al team per la correzione

**Workflow test per pagina**:
1. Navigare alla pagina
2. Verificare HTTP 200 (no errori PHP/500)
3. Controllare contenuto renderizzato
4. Testare form se presente (create/update)
5. Verificare console JS per errori
6. Segnalare risultato al team-lead

**Priorita esecuzione**: ULTIMO (dopo ui-agent) oppure ON-DEMAND per debug

---

## Dipendenze Task

```
model-agent (Task 1, 2)
    ↓ sblocca
controller-agent (Task 3, 4, 5, 6)
    ↓ sblocca
ui-agent (Task 12)
    ↓ sblocca
tester-agent (Task 7)
    ↓ sblocca
Aggiornamento docs (Task 8)
```

## Entita per Priorita

### Prioritarie (Fase corrente)
1. **Allievo** - standalone, PK=codice_fiscale
2. **Assegnazione** - FK: modulo_id, docente_codice_fiscale, istanza_id
3. **Contratto** - FK: assegnazione_id
4. **Pagamento** - FK: contratto_id

### Successive (da implementare)
5. ContoCorrente
6. TipoDocumento
7. Documentazione
8. Calendario
9. Iscrizione
10. PresenzaAllievo
11. ValutazioneDocente

## Note Operative

- Tutti i model estendono `app\components\base\BaseActiveRecord`
- Soft delete attivo su tutte le tabelle (`deleted_at`)
- Audit automatico su tutte le operazioni (`AuditLogBehavior`)
- Campi `created_by`, `updated_by`, `deleted_by` popolati da `AuditBehavior`
- PK persone: `codice_fiscale` (CHAR 16), altre: `id` (INT auto-increment)
- Lingua UI: Italiano
