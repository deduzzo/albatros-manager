# Changelog

Tutte le modifiche al portale Albatros Manager.

## [1.5.0] - 2026-03-14

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

## [1.4.0] - 2026-03-11

### Novità {admin}
- Deploy FTP integrato con gestione Git dal portale
- Importazione utenti reali e disabilitazione account demo
- Importazione 684 valutazioni docenti da CSV
- Sistema valutazione docenti personalizzabile con importazione CSV
- Profilo utente e pagina impostazioni
- Pagina di deploy per pubblicazione su server di produzione

### Novità {tutti}
- Miglioramenti generali di stabilità e completamento feature

## [1.3.0] - 2026-03-04

### Novità {admin}
- Aggiornamento .gitignore e manuali feature
- Script importazione dati per Avviso 28

## [1.2.0] - 2026-02-21

### Novità {tutti}
- Pagina di login unificata con 3 form (admin, docente, studente)
- Manuali utente integrati (admin, docente, studente) navigabili e stampabili
- Questionario anonimo valutazione docenti con codici monouso

### Novità {admin}
- Sistema candidature studenti con criteri di valutazione pesati e graduatoria
- Subentro docenti idonei con gestione rinunce
- Configurazione dati fondazione
- Quick link e registrazione studenti nella pagina di login

### Novità {docente}
- Area docenti con dashboard, bandi aperti, iscrizione moduli
- Rendicontazione ore con inserimento/modifica giornaliero

### Novità {studente}
- Area studenti con candidatura ai corsi
- Sezione "Le mie candidature" con dettaglio punteggi

## [1.1.0] - 2026-02-17

### Novità {admin}
- Liquidazione massiva pagamenti con selezione multipla contratti
- Rendicontazione finanziaria su Progetto, Avviso e Modulo
- Completamento CRUD per tutte le 23 entità del sistema
- Rendicontazione ore docenti con doppia approvazione e ore extra

### Correzioni {admin}
- Fix proprietà FonteFinanziamento
- Fix conversione RichText in importazione Excel
- Fix validazione CSRF per upload AJAX
- Fix importazione dati con gestione errori migliorata

## [1.0.0] - 2026-02-06

### Novità {tutti}
- Lancio del portale Albatros Manager
- Dashboard con statistiche
- Gestione progetti, avvisi, moduli con CRUD completo

### Novità {admin}
- Sistema importazione dati da Excel (progetti, moduli, docenti)
- Soft delete e audit log su tutte le tabelle
- Autenticazione DB con 4 ruoli (admin, segreteria, coordinatore, operatore)
- Interfaccia Tabler Dashboard con tema chiaro/scuro

## [0.1.0] - 2026-01-31

### Novità {admin}
- Commit iniziale: app Yii2 con Tabler Dashboard
- Schema database con 36 tabelle
- Sistema soft delete e audit logging
