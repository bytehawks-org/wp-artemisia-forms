# Artemisia Forms - Project History Log

Questo file tiene traccia delle decisioni architetturali, delle richieste effettuate e delle funzionalità implementate durante le sessioni di sviluppo, per facilitare la ripresa dei lavori in futuro.

---

## Sessione 1 (Iniziale)

### Richieste e Requisiti Iniziali
*   **Nome Plugin:** Artemisia Forms (By ByteHawks).
*   **Stack:** PHP 8.x (Namespace: `ByteHawks\ArtemisiaForms`, OOP rigorosa, KISS pattern), MariaDB (LONGTEXT/JSON validato), React (compilato via `@wordpress/scripts`), CSS/JS classici, API REST native.
*   **Obiettivo:** Creare un form builder avanzato, performante e sicuro, evoluzione del concetto di Ninja Forms.
*   **Funzionalità Core richieste:** Logica condizionale spaziale, form multistep, calcolo sui campi, integrazioni post-submission modulari (email, db, webhook, creazione post/custom fields), salvataggio dati JSON, UI builder in React drag&drop/dinamico, supporto multilingua (WPML/Polylang - aggiunto in seguito), compatibilità Gutenberg/Divi.

### Attività Completate
1.  **Fase 1: Scaffolding e Architettura**
    *   Definito il piano (`implementation_plan.md`) approvato.
    *   Creato filesystem in `wp-content/plugins/artemisia-forms`.
    *   Inizializzato `composer.json` (PSR-4 autoload) con autoloader di fallback nativo.
    *   Creato file base `artemisia-forms.php`.
2.  **Fase 2: Core PHP e Database**
    *   Create le classi `Activator.php` e `Deactivator.php`. All'attivazione vengono installate silenziosamente due tabelle: `wp_artemisia_forms` (configurazione JSON) e `wp_artemisia_submissions` (dati salvati JSON).
    *   Aggiunta infrastruttura Models OOP (classi `Form` e `Submission`) per astrarre la lettura/scrittura nel DB.
    *   Sviluppato strato astratto per il rendering/validazione campi form (`AbstractField.php`) e prima implementazione concreta (`TextField.php`).
    *   Sviluppati in `FormsController.php` tutti gli endpoints REST (`GET \forms`, `POST \forms`, `GET \forms\{id}`, `POST \forms\{id}`).
    *   Iniziata documentazione API in `docs/api.md`.
3.  **Fase 3: React Form Builder (MVP) e Render**
    *   Scaffolding React completato (`package.json`, `@wordpress/scripts`).
    *   `AdminMenu.php` agganciato per caricare React nella pagina admin di WordPress.
    *   React `App.js` strutturato per gestire il routing interno tra vista lista ed editor interattivo.
    *   Costruito costruttore in `FormBuilder.js` con aggiunta iterativa dei campi, rimozione, salvataggio e recupero dati.
    *   Inserito il sistema di **Visual Drag and Drop** (@dnd-kit) per l'ordinamento naturale dei campi con il cursore.
    *   Costruita **Settings Sidebar** per configurare fluidamente etichette, placeholder, required e class CSS in React.
    *   Creato Hook PHP (Classe `Shortcode`) per renderizzare dinamicamente in FrontEnd il JSON costruito: `[artemisia_form id="X"]`.

### Prossimi Passi (Da fare)
*   Finire l'endpoint POST `/forms/{id}/submissions` per salvare le Submission pubbliche.
*   Agganciare il submit JS sul frontend allo Shortcode nativo creato, facendolo comunicare con l'endpoint appena menzionato.
*   Sviluppare la dashboard React in Backend per navigare, leggere ed eliminare le Submissions ricevute (Entries).
*   Proseguire con l'Action Engine (Email asincrone all'invio) nella Fase 4.
