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
    *   Agganciata rotta pioniere REST API: `GET /wp-json/artemisia/v1/forms` (gestita in `FormsController.php`).
    *   Iniziata documentazione API in `docs/api.md`.
3.  **Fase 3: React Form Builder (MVP)**
    *   Scaffolding React completato (`package.json`, `@wordpress/scripts`).
    *   `AdminMenu.php` agganciato per caricare React nella pagina admin di WordPress.
    *   React `App.js` strutturato per gestire il routing interno tra vista lista (che interroga le API) ed editor.
    *   Costruito mock-up del builder in `FormBuilder.js` con layout a 3 colonne CSS flexbox (disponibile in MVP testato positivamente dall'utente).

### Prossimi Passi (Da fare)
*   Finire i metodi `POST` e `GET (singolo)` nell'API Controller PHP per poter salvare definitivamente i form creati nel costruttore React.
*   Connettere i pulsanti "Save" e "Load" del builder React a questi nuovi endpoint.
*   Implementare il drag&drop e la gestione spaziale (aggiunta/rimozione) dei campi in React.
*   Proseguire con Validazioni e Actions (Email, Submissions) nella Fase 4.
