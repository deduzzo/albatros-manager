# Demo Area Studenti - Albatros Manager

Questa è una **demo statica** dell'area studenti. I form sono completamente navigabili ma **nessun dato viene salvato** nel database. Ideale per mostrare al cliente il flusso di iscrizione studenti.

---

## Link Demo

| Pagina | URL | Descrizione |
|--------|-----|-------------|
| **Dashboard Bandi** | http://localhost:8000/demo-studenti | Dashboard con tutti i bandi aperti |
| **Dettaglio Bando** | http://localhost:8000/demo-studenti/bando?id=1 | Pagina dettaglio singolo bando |
| **Iscrizione Corso** | http://localhost:8000/demo-studenti/iscrizione?id=1 | Form completo iscrizione |
| **Conferma** | http://localhost:8000/demo-studenti/conferma | Pagina conferma iscrizione |
| **Login** | http://localhost:8000/demo-studenti/login | Form login studenti (demo) |
| **Registrazione** | http://localhost:8000/demo-studenti/registrazione | Form registrazione (demo) |

---

## Flusso Demo Consigliato

### 1. Dashboard Bandi Aperti
- URL: http://localhost:8000/demo-studenti
- **Hero section** con gradiente viola e messaggio chiave
- **4 statistiche** calcolate dinamicamente: bandi aperti, corsi, posti liberi, 100% gratuiti
- **Card complete per ogni bando** con:
  - Codice e titolo del programma
  - Fonte di finanziamento (FSE, PNRR, MIM, GOL)
  - Obiettivi numerati del programma
  - Prospettive professionali (badge)
  - Destinatari (chi può partecipare)
  - Requisiti di ammissione
  - Certificazioni rilasciate
  - Scadenza iscrizioni
  - Lista corsi con posti disponibili e progress bar

### 2. Dettaglio Bando (opzionale)
- URL: http://localhost:8000/demo-studenti/bando?id=1
- Pagina dedicata con tutte le informazioni del bando
- Sidebar sticky con info e lista corsi
- Pulsanti "Iscriviti" per ogni corso

### 3. Form Iscrizione
- URL: http://localhost:8000/demo-studenti/iscrizione?id=1
- Form completo in 6 sezioni:
  1. **Dati Anagrafici**: CF, nome, cognome, nascita, contatti
  2. **Residenza**: Indirizzo completo
  3. **Titolo di Studio**: Selezione titolo, anno, istituto
  4. **Situazione Lavorativa**: Stato occupazionale, settore
  5. **Documenti**: Upload multipli (documento ID, CF, titolo, CV)
  6. **Privacy e Consensi**: Checkbox obbligatori
- Sidebar sticky con riepilogo corso selezionato

### 4. Conferma
- Pagina di conferma con prossimi passi (verifica email, valutazione, conferma)

---

## Bandi Demo Disponibili

La demo include 4 bandi realistici con dati completi:

| Codice | Titolo | Fonte | Corsi |
|--------|--------|-------|-------|
| FSE-2024-DIGITAL | Competenze Digitali per il Lavoro 4.0 | FSE | Python, Data Analysis, Cloud |
| PNRR-2024-GREEN | Professioni per la Transizione Ecologica | PNRR | Energie Rinnovabili, Audit, Economia Circolare |
| MIM-2024-STEM | STEM Academy - Scienze e Tecnologie | MIM | AI/ML, Cybersecurity, Robotica |
| GOL-2024-WORK | Garanzia Occupabilità Lavoratori | GOL-PNRR | Amministrazione, E-commerce, OSS |

Ogni bando ha:
- Descrizione completa del programma
- 4 obiettivi formativi
- 4 prospettive professionali
- Destinatari specifici
- Requisiti di ammissione
- Certificazioni rilasciate
- 3 corsi con posti, ore, sede, date

---

## Caratteristiche Demo

- **Badge DEMO** fisso in alto a destra
- **Header colorato** con gradiente viola
- **Dashboard pubblica** accessibile senza login
- **Card bandi complete** con tutte le informazioni
- **Progress bar** per posti disponibili (verde/giallo/rosso)
- **Form responsive** mobile-first
- **Validazione HTML5** sui campi obbligatori
- **Sidebar sticky** con riepilogo corso
- **Nessuna interazione DB** - i dati non vengono salvati

---

## Screenshot Consigliati

Per la presentazione al cliente, consiglio di mostrare:

1. **Dashboard bandi** - Mostra la ricchezza di informazioni per ogni bando
2. **Dettaglio bando** - Pagina dedicata con tutti i corsi
3. **Form iscrizione** - Scroll completo del form con 6 sezioni
4. **Conferma** - Mostra il feedback e i prossimi passi

---

## Note per il Cliente

Questa demo mostra:

- **UI/UX** dell'area studenti pubblica
- **Dashboard bandi** con tutte le informazioni visibili
- **Campi richiesti** per l'iscrizione
- **Documenti necessari** (configurabili per ogni corso)
- **Flusso di iscrizione** completo

Il sistema finale avrà:
- Bandi e corsi gestiti dal database (come l'area docenti)
- Autenticazione reale con verifica email
- Salvataggio dati nel database
- Gestione documenti con upload sicuro
- Notifiche email automatiche
- Dashboard studente con storico iscrizioni
- Integrazione con anagrafica allievi

---

## Avvio Demo

```bash
# Assicurati che Docker sia in esecuzione
cd ~/dev/albatros-manager-yii2
docker-compose up -d

# Apri nel browser
open http://localhost:8000/demo-studenti
```
