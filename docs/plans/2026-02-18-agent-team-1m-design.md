# Design: Team Agenti per Contesto 1M

**Data**: 2026-02-18
**Obiettivo**: Massimizzare velocita e parallelismo sfruttando il contesto 1M

## Problema

Il team precedente (model → controller → ui → tester) era sequenziale. Con il contesto 1M, ogni agente puo caricare l'intero progetto e lavorare in autonomia.

## Struttura Team

```
Lead ──→ db-agent ──────→ Migration + schema + model base    ← PRIMO
       │
       ├── full-stack-1 ──→ Task A (ctrl+views+logic)
       ├── full-stack-2 ──→ Task B (ctrl+views+logic)         ← PARALLELO
       ├── full-stack-3 ──→ Task C (ctrl+views+logic)
       │
       ├── tester ────────→ QA via Chrome                     ← ON-DEMAND
       └── reviewer ──────→ Code review                       ← ON-DEMAND
```

## Ruoli

| Ruolo | Quando | Quanti | Tipo | Permessi |
|-------|--------|--------|------|----------|
| **db-agent** | Primo, se servono modifiche DB | 1 | general-purpose | bypassPermissions |
| **full-stack** | Dopo db-agent | 1-6 | general-purpose | bypassPermissions |
| **tester** | Dopo i full-stack | 0-1 | general-purpose | bypassPermissions |
| **reviewer** | On-demand | 0-1 | feature-dev:code-reviewer | - |

## Prompt Templates

### DB-Agent

Responsabilita:
- Creare migration Yii2 per modifiche schema
- Creare/aggiornare model PHP (tableName, rules, relations, labels)
- Creare Search model per GridView
- Verificare relazioni inverse
- Eseguire migration via docker

Convenzioni obbligatorie:
- PK persone: `codice_fiscale` (CHAR 16)
- Soft delete: `deleted_at` su ogni tabella
- Audit: `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_by`
- Model estendono `BaseActiveRecord`

### Full-Stack Agent

Contesto da caricare:
1. `/CLAUDE.md`
2. `/docs/CRUD_GUIDELINES.md`
3. File riferimento: `controllers/DocenteController.php`, `models/Docente.php`, `views/docente/`

Responsabilita:
- Controller CRUD (index, view, create, update, delete)
- Views con Tabler v1.4.0
- Logica model (metodi custom, relazioni)
- Search model se non creato dal db-agent

Regole:
- Badge: SOLO classi Tabler native (`bg-green text-green-fg`)
- Widget: `AppGridView`, `AppActiveForm`, `Alert`
- NON usare `joinWith()` con SoftDeleteTrait
- NON creare file `.md` in sottocartelle
- Lingua UI: italiano

### Tester

Workflow:
1. Login su http://localhost:8000 (admin/admin)
2. Per ogni pagina: HTTP 200, layout, form, console JS
3. Segnala errori al lead

Tools: `mcp__claude-in-chrome__*`

### Reviewer

Usa `feature-dev:code-reviewer` per verificare:
- Aderenza a CRUD_GUIDELINES.md
- Coerenza stilistica tra file
- Bug, vulnerabilita, code quality

## Workflow Lead

1. Analizza il task ricevuto dall'utente
2. Se servono modifiche DB → lancia db-agent (1 solo, aspetta completamento)
3. Identifica task parallelizzabili → lancia N full-stack agent contemporaneamente
4. Quando tutti finiscono → lancia tester (opzionale)
5. Se necessario → lancia reviewer
6. Riporta risultati all'utente

## Vantaggi vs Team Precedente

| Aspetto | Prima (4 agenti seq.) | Ora (N agenti paralleli) |
|---------|----------------------|--------------------------|
| Parallelismo | Zero (catena) | Massimo (full-stack indipendenti) |
| Tempo per 3 entita | ~3x (sequenziale) | ~1x (parallelo) |
| Flessibilita | Fisso a 4 | Dinamico 1-8 |
| Contesto per agente | Limitato per ruolo | Intero progetto (1M) |
| Dipendenze | model→ctrl→ui→test | Solo db→full-stack |
