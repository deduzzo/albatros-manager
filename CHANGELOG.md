# Changelog

Tutte le modifiche al portale Albatros Manager.

## [Unreleased]

## [v1.0.10] - 2026-03-14

## [v1.0.9] - 2026-03-14

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
