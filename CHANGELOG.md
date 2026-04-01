# Changelog

Tutte le modifiche al portale Albatros Manager.

## [Unreleased]
### Novità {admin}
- Gestione Semplificata: nuova area con layout dedicato (accent indigo/viola) per gestione operativa dei corsi
- Gestione Semplificata: home con griglia card corsi, statistiche moduli/docenti/studenti e progress bar assegnamento
- Gestione Semplificata: sidebar navigazione con 8 sezioni (Panoramica, Piano Formativo, Personale, Studenti, Calendario, Contratti, Borse di Studio, Import/Export)
- Gestione Semplificata: selettore rapido corso nel navbar per navigazione veloce tra corsi
- Menu principale: aggiunto accesso rapido a Gestione Semplificata come prima voce
- Borse di studio: nuovo stato "Erogazione Bloccata" per studenti sotto la soglia di frequenza (80%)
- Borse di studio: pulsante "Ricalcola Frequenze" per aggiornare automaticamente la frequenza reale da calendario/presenze
- Borse di studio: export SEPA XML pain.001 per pagamento massivo tramite bonifico bancario
- Borse di studio: pulsante "Export SEPA XML" nella lista borse per generare file da inviare alla banca

- Contratti: upload allegati scansione (PDF/JPG/PNG) per contratti firmati a mano, visualizzabili nella vista contratto
- Dashboard admin: alert operativi automatici per docenti da contrattualizzare, presenze non acquisite e borse bloccate

### Tecnico {admin}
- `AlertWidget`: widget riutilizzabile per alert operativi (contratti/presenze/borse), supporta modalità compatta e filtro per corso
- Migration: aggiunto `docente_presente` (3 stati: null/1/0) e `docente_ore_effettive` alla tabella `calendario`
- Migration: campo `presente` in `presenza_allievo` ora nullable (3 stati: null=non acquisito, 1=presente, 0=assente)
- Migration: aggiunto `allegati_path` (JSON) alla tabella `contratto` per allegati multipli
- Migration: aggiunto valore `erogazione_bloccata` all'ENUM `status` della tabella `borsa_studio`
- Migration: aggiunte 4 configurazioni gruppo `banca` (IBAN fondazione, BIC, Creditor ID, nome istituto)

## [v1.0.30] - 2026-03-30
## [v1.0.29] - 2026-03-28
## [v1.0.28] - 2026-03-28
## [v1.0.27] - 2026-03-28
### Novità {admin}
- Nuova pagina Import Dati con 3 tab: Import Completo, Import Assegnazioni, Import Calendari
- Flusso preview/conferma per import assegnazioni e calendari: validazione con tabella dettagliata prima dell'import
- Badge colorati nella preview: nuovo (verde), esistente (azzurro), saltato (arancione), errore (rosso)
- Import modulare assegnazioni docenti da file Excel con preview/conferma
- Import calendario lezioni da file Excel con preview/conferma (foglio CALENDARIO)
- Template XLSX scaricabile per import assegnazioni (con istruzioni e riga esempio)
- Template XLSX scaricabile per import calendario lezioni
- Preview dettagliata prima dell'import: mostra docenti nuovi/esistenti, assegnazioni da creare/saltare
- Rilevamento automatico lezioni duplicate (stessa data+modulo+docente+ora) nell'import calendario
- Report "Docenti da Contrattualizzare": identifica docenti con lezioni in calendario ma senza contratto attivo
- Filtro per progetto, statistiche riepilogative e link diretto alla contrattualizzazione
- Generazione contratti Co.Co.Co. differenziata: template PDF bilaterale con 10 articoli (diverso da Prestazione Professionale)
- Template Co.Co.Co. per PNRR e FSE con clausole specifiche (art. 409 c.p.c., TUIR, INAIL, gestione separata INPS)

## [v1.0.26] - 2026-03-25
## [v1.0.25] - 2026-03-18
### Novità {admin}
- Inserimento e modifica rimborsi spesa da parte degli admin (prima solo approvazione/rifiuto)
- Dettaglio voci di spesa multiple per ogni rimborso (tabella rimborso_spesa_dettaglio)
- Collegamento opzionale a voci di bilancio (voce_spesa) per ogni dettaglio
- Voce "Generale" di default per spese non categorizzate

### Novità {docente}
- Voci di spesa multiple nel form rimborso (aggiunta/rimozione dinamica)
- Totale importo calcolato automaticamente dalla somma dei dettagli

### Miglioramenti {admin}
- Rimosso campo tipo fisso (ENUM) e importo singolo dai rimborsi, sostituiti da voci di spesa dinamiche

### Miglioramenti {tutti}
- Ricerca testuale nei dropdown select con Tom Select (auto su tutti i select con 5+ opzioni)

## [v1.0.24] - 2026-03-15
## [v1.0.23] - 2026-03-15
### Miglioramenti {tutti}
- Verifica firma digitale: estrazione completa dati (firmatario, tipologia, CA, certificato X.509, policy eIDAS)
- Risultato verifica organizzato in 5 sezioni con layout a 2 colonne

## [v1.0.22] - 2026-03-15
### Novità {tutti}
- Template email HTML brandizzato con logo Fondazione Albatros
- Invio email di reset password funzionante per docenti e studenti

### Novità {admin}
- Pagina test email (Configurazione → Test Email) con 3 template selezionabili

### Miglioramenti {admin}
- Lista studenti: aggiunti data nascita, conteggio iscrizioni, status badge
- Lista progetti: aggiunto stato finanziario con progress bar e conteggio studenti
- Lista assegnazioni: aggiunti link a istanza, contratto e preview note
- Lista contratti: aggiunte date lezioni, ore contrattualizzate e saldo rimanente
- Lista avvisi: aggiunte data pubblicazione e conteggio candidature
- Lista istanze: aggiunti punteggio titoli e punteggio esperienza
- Validazione automatica transizioni di stato per contratti e iscrizioni
- Auto-calcolo compenso totale contratto da compenso orario × ore
- Codici voce spesa configurabili (non più hardcoded)
- Contatti pagina studenti ora da Configurazione (non più hardcoded)
- Deploy FTP: upload automatico config mail solo alla prima volta

### Correzioni {admin}
- Fix relazioni SoftDelete via() in RendicontazioneOre
- Fix CSS inline in dashboard studenti (ora classi Tabler)
- Rimozione directory vuote orfane (docente-bandi, docente-profilo, docente-site)

## [v1.0.21] - 2026-03-14
### Miglioramenti {admin}
- Lista moduli: aggiunta colonna docente assegnato con link al profilo
- Lista moduli: aggiunta colonna studenti iscritti con conteggio
- Lista moduli: colonna ore mostra totali / rendicontate / liquidate con legenda e colori

## [v1.0.20] - 2026-03-14
### Novità {admin}
- Assegnazione docente d'ufficio dalla scheda modulo con ricerca nell'Albo Docenti
- Storico docenti precedenti visibile nella scheda modulo (revoche e sostituzioni)
- Pulsante "Revoca / Sostituisci" per docenti assegnati

### Novità {tutti}
- Sistema notifiche in-app con campanella e badge nell'header
- Pagina dedicata notifiche con stato letto/non letto

### Novità {admin}
- Invio notifiche con editor HTML (Summernote) a singoli utenti, categorie o tutti
- Invio email opzionale con le notifiche
- Pannello gestione notifiche con dettaglio letture
- Deploy automatico: composer install + migration via production.php (ephemeral)

## [v1.0.10] - 2026-03-14

### Novità {tutti}
- Aggiunta autenticazione a due fattori (2FA) con Google Authenticator
- Possibilità di cambiare password dal profilo
- Changelog in-app con notifiche novità nell'header

### Novità {admin}
- Nuova sezione "Gestione Utenti" per amministrare tutte le utenze del portale
- Reset password, disabilita/riattiva e forza cambio password per admin, docenti e studenti
- Gestione 2FA centralizzata: l'admin può disabilitare il 2FA per qualsiasi utente

### Novità {docente}
- Sezione sicurezza nel profilo con attivazione 2FA

### Novità {studente}
- Sezione sicurezza nel profilo con attivazione 2FA

## [v1.0.8] - 2026-03-11

### Novità {admin}
- Pulizia file dati reali dal repository

## [v1.0.6] - 2026-03-11

### Novità {admin}
- Profilo utente e pagina impostazioni con cambio password
- Deploy FTP integrato con gestione Git e GitHub Releases
- Sistema borse di studio
- Gestione movimenti spesa con progetto opzionale
- Aggiornamento manuali utente

### Novità {docente}
- Sezione contratti e documenti nell'area docenti

## [v1.0.4] - 2026-03-11

### Correzioni {admin}
- Fix visualizzazione versione nel footer

## [v1.0.3] - 2026-03-11

### Correzioni {admin}
- Fix controller deploy FTP

## [v1.0.2] - 2026-03-11

### Novità {admin}
- Deploy FTP con gestione versioni e GitHub Releases
- Importazione utenti reali e disabilitazione account demo
- Importazione 684 valutazioni docenti da CSV
- Sistema valutazione docenti personalizzabile

### Novità {tutti}
- Versione portale visualizzata nel footer

## [v1.0.0] - 2026-02-22

### Novità {tutti}
- Lancio del portale Albatros Manager
- Dashboard con statistiche
- Pagina di login unificata con 3 form (admin, docente, studente)
- Manuali utente integrati (admin, docente, studente)

### Novità {admin}
- Gestione completa progetti, avvisi, moduli (23 entità CRUD)
- Importazione dati da Excel
- Liquidazione massiva pagamenti
- Rendicontazione finanziaria su Progetto, Avviso e Modulo
- Questionario anonimo valutazione docenti con codici monouso
- Candidature studenti con criteri pesati e graduatoria
- Subentro docenti idonei con gestione rinunce
- Configurazione dati fondazione
- Soft delete e audit log su tutte le tabelle

### Novità {docente}
- Area docenti con dashboard, bandi aperti, iscrizione moduli
- Rendicontazione ore con inserimento/modifica giornaliero

### Novità {studente}
- Area studenti con candidatura ai corsi
- Sezione "Le mie candidature" con dettaglio punteggi
