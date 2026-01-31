# Linee Guida CRUD - Albatros Manager

Questo documento definisce le regole da seguire per tutte le pagine CRUD dell'applicazione.

---

## 1. Layout e Responsive Design

### 1.1 Mobile-First
- Progettare prima per mobile, poi espandere per desktop
- Breakpoint: mobile (<768px), tablet (768-1024px), desktop (>1024px)
- Testare sempre su dispositivi reali o emulatori

### 1.2 Struttura Pagina
```
┌─────────────────────────────────────┐
│ Header (navbar)                     │
├─────────────────────────────────────┤
│ Menu navigazione                    │
├─────────────────────────────────────┤
│ Breadcrumb                          │
├─────────────────────────────────────┤
│ Page Header (titolo + azioni)       │
├─────────────────────────────────────┤
│ Filtri attivi (badge rimuovibili)   │
├─────────────────────────────────────┤
│ Contenuto principale (tabella)      │
├─────────────────────────────────────┤
│ Summary + Paginazione               │
├─────────────────────────────────────┤
│ Footer                              │
└─────────────────────────────────────┘
```

---

## 2. Stack Tecnologico UI

### 2.1 Tabler UI Kit
- **Versione**: Tabler v1.4.0 (@tabler/core)
- **Icone**: Tabler Icons v3.36.1 (@tabler/icons-webfont)
- **Asset Bundle**: `app\components\assets\TablerAsset`

### 2.2 Icone
Usare sempre le icone Tabler con prefisso `ti ti-`:
```html
<i class="ti ti-home"></i>
<i class="ti ti-plus"></i>
<i class="ti ti-edit"></i>
```
Documentazione icone: https://tabler.io/icons

---

## 3. Componenti Tabler + Yii2

### 3.1 Card come Container
Ogni sezione deve essere racchiusa in una card Tabler:
```php
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Titolo</h3>
        <div class="card-actions">
            <!-- Azioni header -->
        </div>
    </div>
    <div class="card-body">
        <!-- Contenuto -->
    </div>
    <div class="card-footer">
        <!-- Footer opzionale -->
    </div>
</div>
```

### 3.2 AppGridView - GridView Stilizzata
Usare sempre `AppGridView` che include automaticamente:
- **Sorting con frecce**: icone ↑↓ nelle intestazioni colonne
- **Filtri attivi come badge**: tag blu con X per rimuovere singolo filtro
- **Pulsante "Elimina tutti i filtri"**: reset completo filtri
- **Stile Tabler**: hover, paginazione stilizzata
- **ActionColumn configurata**: pulsanti view/update/delete con icone

```php
<?= AppGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        'nome',
        [
            'attribute' => 'codice',
            'format' => 'raw',
            'content' => function($model) {
                return '<span class="badge bg-blue-lt">' . Html::encode($model->codice) . '</span>';
            },
        ],
        'email:email',
        ['class' => 'yii\grid\ActionColumn'],
    ],
]) ?>
```

### 3.3 Frecce di Ordinamento
Le intestazioni delle colonne ordinabili mostrano automaticamente:
- **Default**: icona ↕ semi-trasparente (ordinabile)
- **Hover**: icona più visibile
- **ASC attivo**: freccia ↑ blu
- **DESC attivo**: freccia ↓ blu

Il sorting è abilitato automaticamente su tutte le colonne DataColumn.

### 3.4 Badge Filtri Attivi
Quando sono applicati filtri, vengono mostrati come:
```
┌────────────────────────────────────────────────────┐
│ [Codice: FSE ✕] [Nome: Test ✕]  [Elimina tutti]   │
└────────────────────────────────────────────────────┘
```
- Ogni filtro è un badge blu con il valore e una X per rimuoverlo
- Pulsante "Elimina tutti i filtri" per reset completo

### 3.5 ActiveForm Stilizzato
Usare sempre la classe custom `AppActiveForm`:
```php
<?php $form = AppActiveForm::begin([
    'layout' => 'horizontal', // o 'default', 'inline'
]); ?>
    <?= $form->field($model, 'nome') ?>
    <?= $form->field($model, 'email')->textInput(['type' => 'email']) ?>
<?php AppActiveForm::end(); ?>
```

---

## 4. Schema Pulsanti Standard

### 4.1 Colori e Significati
| Azione | Colore | Classe | Icona (Tabler Icons) |
|--------|--------|--------|----------------------|
| Crea/Nuovo | Verde | `btn-success` | `ti ti-plus` |
| Modifica | Blu | `btn-primary` | `ti ti-edit` |
| Visualizza | Info | `btn-info` | `ti ti-eye` |
| Elimina | Rosso | `btn-danger` | `ti ti-trash` |
| Annulla | Grigio | `btn-secondary` | `ti ti-x` |
| Salva | Verde | `btn-success` | `ti ti-check` |
| Cerca/Filtra | Azzurro | `btn-primary` | `ti ti-search` |
| Reset Filtri | Grigio | `btn-outline-secondary` | `ti ti-filter-off` |
| Export | Giallo | `btn-warning` | `ti ti-download` |
| Stampa | Grigio | `btn-secondary` | `ti ti-printer` |
| Ripristina | Viola | `btn-purple` | `ti ti-rotate-clockwise` |

### 4.2 Posizionamento Pulsanti
- **Lista (Index)**: Pulsante "Nuovo" in alto a destra nel page-header
- **Dettaglio (View)**: Pulsanti "Modifica" e "Elimina" in alto a destra
- **Form (Create/Update)**: "Salva" a destra, "Annulla" a sinistra nel footer
- **Azioni riga**: Icone senza testo, tooltip obbligatorio

### 4.3 Dimensioni
- Desktop: `btn` (default) o `btn-sm` per azioni riga
- Mobile: `btn-sm` ovunque, icone senza testo

### 4.4 Template Azioni Riga GridView
AppGridView configura automaticamente ActionColumn con:
```php
'buttons' => [
    'view' => function ($url, $model) {
        return Html::a('<i class="ti ti-eye"></i>', $url, [
            'class' => 'btn btn-info btn-sm',
            'title' => 'Visualizza',
            'data-bs-toggle' => 'tooltip',
        ]);
    },
    'update' => function ($url, $model) {
        return Html::a('<i class="ti ti-edit"></i>', $url, [
            'class' => 'btn btn-primary btn-sm',
            'title' => 'Modifica',
            'data-bs-toggle' => 'tooltip',
        ]);
    },
    'delete' => function ($url, $model) {
        return Html::a('<i class="ti ti-trash"></i>', $url, [
            'class' => 'btn btn-danger btn-sm',
            'title' => 'Elimina',
            'data-bs-toggle' => 'tooltip',
            'data-confirm' => 'Sei sicuro di voler eliminare questo elemento?',
            'data-method' => 'post',
        ]);
    },
],
```

---

## 5. Breadcrumb

### 5.1 Sempre Presente
Ogni pagina deve definire il breadcrumb nel controller o nella view:
```php
$this->params['breadcrumbs'][] = ['label' => 'Docenti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
```

### 5.2 Struttura Standard
- Home → Sezione → Sottosezione → Pagina corrente
- L'ultimo elemento (pagina corrente) non è un link

---

## 6. Messaggi e Feedback

### 6.1 Flash Messages
Usare sempre i flash messages di Yii2 con stile Tabler:
```php
// Controller
Yii::$app->session->setFlash('success', 'Docente salvato con successo.');
Yii::$app->session->setFlash('error', 'Errore durante il salvataggio.');
Yii::$app->session->setFlash('warning', 'Attenzione: alcuni campi sono incompleti.');
Yii::$app->session->setFlash('info', 'Operazione completata.');
```

### 6.2 Classi Alert e Icone
| Tipo | Classe Tabler | Icona |
|------|---------------|-------|
| success | `alert alert-success` | `ti ti-check` |
| error/danger | `alert alert-danger` | `ti ti-alert-circle` |
| warning | `alert alert-warning` | `ti ti-alert-triangle` |
| info | `alert alert-info` | `ti ti-info-circle` |

### 6.3 Conferma Eliminazione
Sempre richiedere conferma per azioni distruttive:
```php
'data' => [
    'confirm' => 'Sei sicuro di voler eliminare questo elemento?',
    'method' => 'post',
],
```

---

## 7. Formattazione Dati

### 7.1 Date
- Formato visualizzazione: `dd/mm/yyyy` (es: 31/01/2026)
- Formato con ora: `dd/mm/yyyy HH:mm` (es: 31/01/2026 14:30)
- Usare `Yii::$app->formatter->asDate()` e `asDatetime()`

### 7.2 Numeri e Valute
- Decimali: virgola come separatore (es: 1.234,56)
- Valuta: simbolo € dopo il numero (es: 1.234,56 €)
- Usare `Yii::$app->formatter->asCurrency()` e `asDecimal()`

### 7.3 Codice Fiscale
- Sempre uppercase
- Visualizzare con spazi ogni 4 caratteri opzionale (RSSMRA80A01F205X)

---

## 8. Validazione

### 8.1 Client-Side
- Abilitare sempre la validazione Ajax in ActiveForm
- Feedback visivo immediato (bordo rosso/verde)

### 8.2 Campi Obbligatori
- Indicare con asterisco rosso (*) accanto alla label
- Usare `'template' => "{label} <span class='text-danger'>*</span>\n{input}..."`

### 8.3 Messaggi Errore
- In italiano
- Specifici (non generici)
- Posizionati sotto il campo

---

## 9. Filtri, Ordinamento e Ricerca

### 9.1 REGOLA FONDAMENTALE
**TUTTE le tabelle DEVONO essere:**
- **Ordinabili** su OGNI colonna (click sull'header per ordinare ASC/DESC)
- **Filtrabili** su OGNI campo (input filtro sotto l'header di ogni colonna)

Questo è OBBLIGATORIO per ogni GridView, senza eccezioni.

### 9.2 Ordinamento Colonne
- Click su header colonna per ordinare
- Freccia ↑ indica ordinamento ASC (A-Z, 1-9)
- Freccia ↓ indica ordinamento DESC (Z-A, 9-1)
- Freccia ↕ semi-trasparente indica colonna ordinabile

**Configurazione SearchModel per abilitare sorting:**
```php
$dataProvider = new ActiveDataProvider([
    'query' => $query,
    'sort' => [
        'defaultOrder' => ['nome' => SORT_ASC],
        'attributes' => [
            'id',
            'nome',
            'codice',
            'created_at',
            // ... tutti gli attributi ordinabili
        ],
    ],
]);
```

### 9.3 Filtri Attivi (Badge)
Quando sono applicati filtri, vengono mostrati sopra la tabella come badge:
- **Badge blu** con nome campo e valore
- **Icona X** per rimuovere singolo filtro
- **Pulsante "Elimina tutti i filtri"** per reset completo

### 9.4 Filtri Comuni
- Testo: input text con ricerca parziale (LIKE %...%)
- Date: date picker con range (da/a)
- Enum/Status: dropdown select
- Boolean: dropdown (Tutti/Sì/No)

---

## 10. Paginazione

### 10.1 Configurazione Standard
AppGridView configura automaticamente il pager Tabler:
```php
'pager' => [
    'options' => ['class' => 'pagination m-0 ms-auto'],
    'linkContainerOptions' => ['class' => 'page-item'],
    'linkOptions' => ['class' => 'page-link'],
    'firstPageLabel' => '<i class="ti ti-chevrons-left"></i>',
    'lastPageLabel' => '<i class="ti ti-chevrons-right"></i>',
    'prevPageLabel' => '<i class="ti ti-chevron-left"></i>',
    'nextPageLabel' => '<i class="ti ti-chevron-right"></i>',
    'maxButtonCount' => 5,
],
```

### 10.2 Elementi per Pagina
- Default: 20 elementi
- Opzioni: 10, 20, 50, 100
- Dropdown per cambio rapido

---

## 11. Stati e Badge

### 11.1 Colori Status
| Status | Colore | Classe |
|--------|--------|--------|
| Attivo/Completato | Verde | `badge bg-success` |
| In Attesa/Bozza | Giallo | `badge bg-warning` |
| Scaduto/Errore | Rosso | `badge bg-danger` |
| In Corso | Blu | `badge bg-primary` |
| Annullato | Grigio | `badge bg-secondary` |
| Info | Azzurro | `badge bg-info` |
| Codice/Tag | Blu chiaro | `badge bg-blue-lt` |

### 11.2 Helper Status Badge
Usare il metodo helper di AppGridView:
```php
AppGridView::statusBadge('attivo'); // → <span class="badge bg-success">Attivo</span>
```

### 11.3 Indicatore Soft Delete
- Record eliminati: riga con opacità 0.5 e badge "Archiviato"
- Visibili solo in modalità "Mostra archiviati"

---

## 12. Accessibilità

### 12.1 Labels e ARIA
- Ogni input deve avere una label associata
- Usare `aria-label` per icone senza testo
- Usare `aria-describedby` per hint

### 12.2 Tastiera
- Tab navigation funzionante
- Enter per submit form
- Escape per chiudere modal

### 12.3 Contrasto
- Rispettare WCAG 2.1 AA (minimo 4.5:1 per testo)

---

## 13. Loading e Performance

### 13.1 Indicatori Caricamento
- Spinner su pulsanti durante submit
- Overlay su tabella durante caricamento Ajax
- Skeleton loader per contenuti pesanti

### 13.2 Pjax
- Usare Pjax per aggiornamenti parziali (filtri, paginazione, ordinamento)
- Timeout: 10 secondi
- Fallback a reload completo in caso di errore

---

## 14. Modal

### 14.1 Quando Usare
- Form semplici (< 5 campi)
- Conferme
- Anteprima veloce

### 14.2 Quando NON Usare
- Form complessi
- Visualizzazione dettagliata
- Operazioni che richiedono navigazione

### 14.3 Struttura
```html
<div class="modal modal-blur" id="modal-example">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Titolo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Contenuto -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button class="btn btn-primary">Conferma</button>
            </div>
        </div>
    </div>
</div>
```

---

## 15. Export Dati

### 15.1 Formati Supportati
- Excel (.xlsx) - preferito
- CSV
- PDF (solo per report)

### 15.2 Posizione Pulsante
- In alto a destra, accanto a "Nuovo"
- Dropdown se più formati

---

## 16. Struttura File

### 16.1 Views
```
views/
└── docente/
    ├── index.php       # Lista con GridView
    ├── view.php        # Dettaglio singolo record
    ├── create.php      # Form creazione
    ├── update.php      # Form modifica
    ├── _form.php       # Form partial (usato da create/update)
    ├── _search.php     # Form ricerca/filtri (opzionale)
    └── _columns.php    # Definizione colonne GridView (opzionale)
```

### 16.2 Controller
- Un controller per entità
- Azioni standard: index, view, create, update, delete
- Azioni aggiuntive: export, restore, ecc.

---

## 17. Naming Conventions

### 17.1 URL
- Plurale per lista: `/docenti`
- Singolare con ID per dettaglio: `/docente/view?id=XXX`
- Azioni: `/docente/create`, `/docente/update?id=XXX`

### 17.2 Titoli Pagina
- Lista: "Docenti"
- Dettaglio: "Docente: Mario Rossi"
- Creazione: "Nuovo Docente"
- Modifica: "Modifica Docente: Mario Rossi"

---

## 18. Widget Disponibili

### 18.1 AppGridView
Widget GridView con stile Tabler integrato.

**Funzionalità automatiche:**
- Sorting con frecce su tutte le colonne
- Badge filtri attivi rimuovibili
- Pulsante "Elimina tutti i filtri"
- ActionColumn stilizzata
- Paginazione con icone Tabler
- Classi CSS Tabler (table-hover, table-vcenter)

**Metodi helper statici:**
```php
AppGridView::statusBadge('attivo');           // Badge status
AppGridView::createButton(['create']);         // Pulsante "Nuovo"
AppGridView::formatDate($date);               // Data formattata
AppGridView::formatCurrency($value);          // Valuta formattata
```

### 18.2 AppActiveForm
Widget ActiveForm con stile Tabler.

**Layout disponibili:**
- `default`: campi in verticale
- `horizontal`: label a sinistra, input a destra
- `inline`: campi in linea

**Metodi helper:**
```php
$form->fieldSelect($model, 'tipo', $items);   // Select stilizzato
$form->fieldDate($model, 'data');             // Input date
$form->fieldTextarea($model, 'note');         // Textarea
$form->fieldSwitch($model, 'attivo');         // Switch on/off
AppActiveForm::buttons('Salva', ['index']);   // Pulsanti footer
```

### 18.3 Alert
Widget per flash messages con auto-hide.

```php
<?= Alert::widget() ?>  // Nel layout, renderizza automaticamente i flash
```

---

## 19. Theme Toggle (Dark/Light Mode)

### 19.1 Funzionalità
L'applicazione supporta il cambio tema dark/light mode:
- **Icona nell'header**: accanto alle notifiche
- **Persistenza**: la preferenza viene salvata in localStorage
- **Icone**: `ti ti-moon` per passare a dark, `ti ti-sun` per passare a light

### 19.2 Implementazione
Il toggle usa l'attributo `data-bs-theme` di Bootstrap 5 sull'elemento `<html>`:
```html
<html data-bs-theme="light">  <!-- o "dark" -->
```

### 19.3 JavaScript
```javascript
const themeToggle = document.getElementById('theme-toggle');
const htmlElement = document.documentElement;
const savedTheme = localStorage.getItem('theme') || 'light';
htmlElement.setAttribute('data-bs-theme', savedTheme);

themeToggle.addEventListener('click', function(e) {
    const currentTheme = htmlElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    htmlElement.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
});
```

### 19.4 Compatibilità Componenti
Tabler e Bootstrap 5 gestiscono automaticamente i colori per dark mode:
- Card, tabelle, form, alert, badge, ecc. si adattano automaticamente
- Le classi colore custom potrebbero richiedere variabili CSS aggiuntive

---

## 20. Checklist Pre-Rilascio

Per ogni pagina CRUD verificare:

- [ ] Responsive su mobile, tablet, desktop
- [ ] Breadcrumb presente e corretto
- [ ] Pulsanti con colori/icone standard (Tabler Icons)
- [ ] Flash messages funzionanti
- [ ] Validazione client e server
- [ ] Conferma eliminazione
- [ ] Date/numeri formattati correttamente
- [ ] **Filtri funzionanti con badge rimuovibili**
- [ ] **Pulsante "Elimina tutti i filtri" presente**
- [ ] Paginazione funzionante con icone
- [ ] **Ordinamento colonne con frecce ↑↓**
- [ ] Soft delete gestito
- [ ] Accessibilità base (labels, tab navigation)
- [ ] Nessun errore console JS
- [ ] Nessun warning PHP
