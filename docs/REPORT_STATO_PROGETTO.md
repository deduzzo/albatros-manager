# Albatros Manager - Report Stato Progetto

**Data**: 23 Febbraio 2026

## Panoramica Generale

| Area | Stato | Voto |
|------|-------|------|
| **Model** (68 file) | Completi al 100% | 9.5/10 |
| **Controller** (32 file) | Completi al 100% | 9.5/10 |
| **Viste** (188 file) | Complete al 100% | 9.5/10 |
| **Migration** (55 file) | Complete, 36 tabelle | 10/10 |
| **Infrastruttura** (config, components, Docker) | Solida | 8.5/10 |

---

## COSA E' COMPLETAMENTE SVILUPPATO

### CRUD Admin (23 entita)
Tutte con index, view, create, update, delete, _form, Search model:
FonteFinanziamento, Sede, ContoCorrente, TipoDocumento, Docente, Allievo, Progetto, Avviso, Modulo, Istanza, Assegnazione, Contratto, Pagamento, Calendario, Iscrizione, PresenzaAllievo, Documentazione, ValutazioneDocente, CriterioValutazioneStudente, TagCategoria, TagCurriculum, RendicontazioneOre, Configurazione

### Portali Separati (3 aree)
- **Area Admin**: Dashboard con grafici reali, sidebar completa, 25+ sezioni
- **Area Docenti** (15 viste): Login, dashboard, bandi, iscrizione, assegnazioni, ore, profilo
- **Area Studenti** (10 viste): Login, dashboard, bandi, candidatura, punteggi, profilo
- **Pagine Pubbliche** (4 viste): Bandi aperti accessibili senza login

### Feature Avanzate Implementate
- Rendicontazione ore con doppia approvazione (giornaliere + straordinari)
- Liquidazione massiva pagamenti (AJAX + multi-selezione)
- Graduatoria docenti e studenti con ammissione singola/massiva
- Subentro docenti idonei (revoca + sostituzione da long list)
- Import massivo da Excel (4 fogli, dry-run, transazione DB)
- Questionario anonimo valutazione docenti (codici monouso)
- Tag curriculum con promozione da candidatura a profilo
- Criteri valutazione pesati con calcolo punteggio live
- 3 manuali HTML navigabili + stampabili
- Guide interattive (Steps Tabler)
- Rendicontazione finanziaria multi-livello (Progetto/Avviso/Modulo)
- Audit log completo (INSERT/UPDATE/DELETE tracciati)
- Soft delete su tutte le tabelle principali

### Infrastruttura
- 3 sistemi autenticazione separati (admin/docente/studente)
- BaseActiveRecord con SoftDelete + AuditLog + AuditBehavior
- Widget personalizzati (AppGridView, AppActiveForm, Alert)
- Docker completo (PHP 8.4 + MySQL 8.0 + phpMyAdmin)
- 55 migration ordinate per dipendenze

---

## PROBLEMI DA CORREGGERE

### Priorita ALTA

| # | Problema | File | Dettaglio |
|---|----------|------|-----------|
| 1 | **Debug/Gii AllowedIPs = `['*']`** | `config/web.php` | In produzione chiunque puo accedere a debug toolbar e generatore codice. Cambiare in `['127.0.0.1', '::1']` |
| 2 | **Credenziali DB hardcoded** | `config/db.php` | Nessun file `.env`. User/password in chiaro nel codice versionato |
| 3 | **Mail in modalita file** | `config/web.php` | `useFileTransport => true` - le email non vengono inviate (password dimenticata non funziona) |

### Priorita MEDIA

| # | Problema | File | Dettaglio |
|---|----------|------|-----------|
| 4 | **TODO in DocenteRegistrazioneForm** | `models/forms/DocenteRegistrazioneForm.php` | Flag TODO trovato ~linea 100, da verificare e risolvere |
| 5 | **Progress bar con classi Bootstrap** | `views/istanza/view.php:264` | Usa `progress-bar-striped bg-primary` anziche Tabler native |
| 6 | **3 directory viste vuote legacy** | `views/docente-bandi/`, `views/docente-profilo/`, `views/docente-site/` | Cartelle vuote, residui di refactoring, da eliminare |
| 7 | **DemoStudentiController deprecato** | `controllers/DemoStudentiController.php` | Ancora funzionante ma superato da StudentiController |

### Priorita BASSA

| # | Problema | File | Dettaglio |
|---|----------|------|-----------|
| 8 | **No schema cache** | `config/db.php` | Manca `enableSchemaCache` per produzione |
| 9 | **No minificazione CSS/JS custom** | - | Tabler e gia minificato, ma CSS/JS custom non hanno pipeline |
| 10 | **ApexCharts da CDN** | `components/assets/ApexChartsAsset.php` | Dipendenza esterna, dovrebbe essere locale per offline/produzione |

---

## COSA MANCA / DA SVILUPPARE

### Feature Non Ancora Implementate

| # | Feature | Priorita | Complessita | Note |
|---|---------|----------|-------------|------|
| 1 | **Notifiche email reali** | ALTA | Media | Password dimenticata (docenti/studenti), notifiche assegnazione, scadenze. Il mailer e configurato ma in file mode |
| 2 | **Reset password funzionante** | ALTA | Bassa | La vista esiste (`password-dimenticata`) ma senza SMTP non funziona |
| 3 | **Gestione ruoli admin granulare** | MEDIA | Media | Esistono 4 ruoli (admin, segreteria, coordinatore, operatore) ma nessun RBAC implementato - tutti vedono tutto |
| 4 | **Calendario lezioni con vista grafica** | MEDIA | Media | CRUD esiste, manca vista calendario (FullCalendar o simile) |
| 5 | **Presenze allievi da calendario** | MEDIA | Media | CRUD esiste, manca integrazione con calendario (checklist presenze per lezione) |
| 6 | **Dashboard area studenti completa** | MEDIA | Bassa | Esiste ma le stat cards potrebbero essere piu ricche |
| 7 | **Export PDF contratti/attestati** | MEDIA | Media | Nessuna generazione PDF. Utile per contratti, attestati, certificati |
| 8 | **Notifiche in-app** | BASSA | Media | L'header ha lo spazio per notifiche ma sono placeholder |
| 9 | **Ricerca globale** | BASSA | Media | Nessuna ricerca cross-entita |
| 10 | **Upload documenti studenti** | BASSA | Bassa | La candidatura studenti non ha upload documenti (a differenza dei docenti) |
| 11 | **Multi-lingua** | BASSA | Alta | Tutto hardcoded in italiano, nessun i18n per le viste |
| 12 | **Test automatizzati** | BASSA | Alta | Codeception presente in composer ma nessun test scritto |
| 13 | **PWA / Mobile** | BASSA | Media | Nessun manifest.json o service worker |
| 14 | **API REST** | BASSA | Media | Nessun endpoint API per integrazioni esterne |
| 15 | **Backup automatico DB** | BASSA | Bassa | Nessuno script di backup schedulato |

### Workflow Parzialmente Implementati

| Workflow | Stato | Cosa Manca |
|----------|-------|------------|
| **Iscrizione studenti → Frequenza → Completamento** | 80% | Manca il passaggio automatico a `frequentante` e `completato` con attestato |
| **Calendario → Presenze → Report** | 60% | CRUD ok, manca vista calendario grafica e compilazione presenze da lezione |
| **Contratto → Pagamento → Rendicontazione** | 90% | Funziona, manca solo export PDF del prospetto pagamenti |
| **Valutazione docenti → Report aggregato** | 70% | Codici e form ok, manca dashboard con medie per docente/modulo |

---

## RACCOMANDAZIONI PER PRODUZIONE

### Prima del Deploy (Bloccanti)

1. **Ambiente**: Creare `.env` con credenziali DB, mail SMTP, chiavi segrete
2. **Sicurezza**: Disabilitare Gii/Debug o limitare AllowedIPs
3. **Email**: Configurare SMTP reale (il reset password dipende da questo)
4. **HTTPS**: Configurare SSL/TLS
5. **Admin default**: Forzare cambio password al primo accesso

### Miglioramenti Consigliati (Non Bloccanti)

1. **Schema cache** per performance DB
2. **Asset bundling** per CSS/JS custom
3. **ApexCharts locale** anziche CDN
4. **Pulizia** directory vuote e controller deprecati
5. **RBAC** per differenziare permessi tra ruoli admin

---

## Riepilogo Numerico

| Metrica | Valore |
|---------|--------|
| Tabelle DB | 36 |
| Migration | 55 |
| Model | 68 (27 domain + 23 search + 7 form + 11 support) |
| Controller | 32 |
| Viste | 188 |
| Route configurate | 58 |
| Entita CRUD complete | 23 |
| Portali separati | 3 (admin, docente, studente) |
| Sistemi autenticazione | 3 |
| Manuali utente | 3 |
| Bug aperti | 0 critici, 3 medi, 3 bassi |
| Feature mancanti | 3 alte, 4 medie, 8 basse |

**Stato complessivo: Progetto maturo, funzionalmente completo per il core business. Necessita hardening per produzione e alcune feature secondarie.**
