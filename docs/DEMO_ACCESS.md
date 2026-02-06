# Albatros Manager - Accesso Demo

Questo documento contiene i link e le credenziali per accedere alle diverse aree del sistema demo.

## Ambiente Demo

**URL Base**: http://localhost:8000

---

## Area Amministrazione

### Accesso Admin

| Campo | Valore |
|-------|--------|
| **URL** | http://localhost:8000/site/login |
| **Username** | `admin` |
| **Password** | `admin` |
| **Email** | admin@albatros.it |
| **Ruolo** | Amministratore |

### Sezioni Disponibili (Admin)

| Sezione | URL | Descrizione |
|---------|-----|-------------|
| Dashboard | http://localhost:8000 | Panoramica generale |
| Progetti | http://localhost:8000/progetto | Gestione progetti |
| Avvisi/Bandi | http://localhost:8000/avviso | Gestione bandi |
| Moduli/Corsi | http://localhost:8000/modulo | Gestione corsi |
| Istanze | http://localhost:8000/istanza | Domande docenti |
| Docenti | http://localhost:8000/docente | Anagrafica docenti |
| Fonti Finanziamento | http://localhost:8000/fonte-finanziamento | Configurazione fonti |
| Sedi | http://localhost:8000/sede | Configurazione sedi |

---

## Area Docenti

### Accesso Docenti

| Campo | Valore |
|-------|--------|
| **URL Login** | http://localhost:8000/area-docenti/login |
| **URL Registrazione** | http://localhost:8000/area-docenti/registrazione |

### Credenziali Docenti Demo

| Docente | Codice Fiscale | Password | Stato |
|---------|----------------|----------|-------|
| Mario Rossi | `RSSMRA80A01F205X` | `docente123` | Attivo |
| Laura Bianchi | `BNCLRA78C20F839Z` | `docente123` | Attivo |
| Luigi Verdi | `VRDLGI85B15H501Y` | `docente123` | Attivo |
| Giuseppe Neri | `NRIGPP82D10A944W` | `docente123` | Attivo |
| Marco Gialli | `GLLMRC75E25L219V` | `docente123` | Attivo |
| Sofia Russo | `RSSSFN88F30G273U` | `docente123` | Attivo |

### Sezioni Disponibili (Area Docenti)

| Sezione | URL | Descrizione |
|---------|-----|-------------|
| Dashboard | http://localhost:8000/area-docenti | Panoramica personale |
| Bandi Aperti | http://localhost:8000/area-docenti/bandi | Lista bandi disponibili |
| Le Mie Iscrizioni | http://localhost:8000/area-docenti/le-mie-iscrizioni | Storico candidature |
| Profilo | http://localhost:8000/area-docenti/profilo | Modifica dati anagrafici |

---

## Dati Demo Disponibili

### Progetti
- **PROG-FSE-2024-001** - Formazione Digitale Avanzata (Attivo)
- **PROG-PNRR-2024-002** - Competenze per il Futuro (Attivo)

### Bandi Aperti
- **AVVISO-2024-003** - Selezione Docenti Modulo Python (Aperto fino al 2025-03-31)
- **AVVISO-2024-004** - Selezione Docenti Web Development (Aperto fino al 2025-04-15)

### Moduli Aperti per Iscrizioni
| Codice | Titolo | Ore | Compenso/h |
|--------|--------|-----|------------|
| MOD-PYTHON-BASE | Python Base | 40 | €70.00 |
| MOD-PYTHON-ADV | Python Avanzato | 30 | €80.00 |
| MOD-WEB-HTML | HTML e CSS | 20 | €65.00 |
| MOD-WEB-JS | JavaScript | 30 | €75.00 |

---

## Tag Curriculum Demo

### Categorie Tag
- **Linguaggi di Programmazione**: Python, JavaScript, Java, PHP
- **Framework**: Django, React, Angular, Vue.js, Laravel
- **Certificazioni**: AWS, Azure, Google Cloud, Scrum Master

### Tag Assegnati ai Docenti
| Docente | Tag |
|---------|-----|
| Mario Rossi | Python (Liv. 5), Django (Liv. 4), AWS (Liv. 3) |
| Laura Bianchi | JavaScript (Liv. 5), React (Liv. 4), Node.js (Liv. 4) |
| Luigi Verdi | Java (Liv. 4), Spring Boot (Liv. 3), MySQL (Liv. 4) |

---

## Funzionalità Demo

### Gestione Progetti (Admin)
1. Visualizza lista progetti con stati
2. Crea nuovo progetto associato a fonte finanziamento
3. Modifica dati progetto
4. Visualizza bandi associati

### Gestione Bandi (Admin)
1. Visualizza lista bandi con stati e scadenze
2. Crea nuovo bando
3. Configura documenti richiesti per le domande
4. Visualizza moduli associati

### Gestione Moduli/Corsi (Admin)
1. Visualizza lista moduli con stati
2. Crea nuovo modulo associato a bando e sede
3. Verifica se modulo accetta iscrizioni
4. Visualizza statistiche iscrizioni

### Gestione Istanze (Admin)
1. Visualizza tutte le domande presentate
2. Valuta domande (punteggi, graduatoria)
3. Gestisci tag iscrizione
4. Promuovi tag al profilo docente
5. Visualizza snapshot storico dati

---

## Avvio Ambiente Demo

```bash
# Dalla directory del progetto
cd ~/dev/albatros-manager-yii2

# Avvia i container Docker
docker-compose up -d

# Verifica che i servizi siano attivi
docker-compose ps

# Esegui le migrazioni (se necessario)
docker exec -w /app albatros-php php yii migrate

# Accedi all'applicazione
open http://localhost:8000
```

---

## Servizi Aggiuntivi

| Servizio | URL | Credenziali |
|----------|-----|-------------|
| phpMyAdmin | http://localhost:8080 | root / root |
| MySQL (esterno) | localhost:3307 | albatros / albatros |

---

---

## Demo Area Studenti (Statica - No Database)

Demo completa del portale studenti **senza database**. Tutti i dati sono statici e i form sono navigabili ma non salvano nulla. Ideale per mostrare al cliente il flusso di iscrizione.

| Pagina | URL | Descrizione |
|--------|-----|-------------|
| **Dashboard Bandi** | http://localhost:8000/demo-studenti | Dashboard pubblica con tutti i bandi aperti |
| Dettaglio Bando FSE | http://localhost:8000/demo-studenti/bando?id=1 | Competenze Digitali 4.0 |
| Dettaglio Bando PNRR | http://localhost:8000/demo-studenti/bando?id=2 | Transizione Ecologica |
| Dettaglio Bando MIM | http://localhost:8000/demo-studenti/bando?id=3 | STEM Academy |
| Dettaglio Bando GOL | http://localhost:8000/demo-studenti/bando?id=4 | Garanzia Occupabilità |
| Form Iscrizione | http://localhost:8000/demo-studenti/iscrizione?id=1 | Form 6 sezioni (dati, documenti, privacy) |
| Conferma | http://localhost:8000/demo-studenti/conferma | Pagina post-iscrizione |

### Bandi Demo Inclusi

| Codice | Programma | Fonte | Corsi |
|--------|-----------|-------|-------|
| FSE-2024-DIGITAL | Competenze Digitali per il Lavoro 4.0 | FSE | 3 corsi (Python, Data, Cloud) |
| PNRR-2024-GREEN | Professioni per la Transizione Ecologica | PNRR | 3 corsi (Energie, Audit, Circolare) |
| MIM-2024-STEM | STEM Academy - Scienze e Tecnologie | MIM | 3 corsi (AI, Cyber, Robotica) |
| GOL-2024-WORK | Garanzia Occupabilità Lavoratori | GOL-PNRR | 3 corsi (Admin, Ecommerce, OSS) |

Documentazione completa: `docs/DEMO_STUDENTI.md`

---

## Note

- I dati demo sono inseriti tramite migration e possono essere ripristinati con `php yii migrate/down` + `php yii migrate`
- Le password dei docenti sono hashate con bcrypt
- L'area docenti è funzionante con autenticazione reale
- L'area studenti è una demo statica senza database
