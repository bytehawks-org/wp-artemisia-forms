# Artemisia Forms REST API Documentation

Benvenuto nella documentazione delle API di Artemisia Forms. Il sistema sfrutta le WP REST API native per una comunicazione sicura e standardizzata.

> **Base URI:** `[SITO_WP_URL]/wp-json/artemisia/v1/`

---

## Modello Dati (Form)
Un oggetto *Form* restituito dall'API avrà la seguente struttura JSON essenziale:
```json
{
  "id": 1,
  "title": "Richiesta Preventivo",
  "status": "published",
  "config": {
    "fields": [],
    "styling": {},
    "conditionals": []
  },
  "created_at": "YYYY-MM-DD HH:MM:SS",
  "updated_at": "YYYY-MM-DD HH:MM:SS"
}
```

---

## 1. /forms

### 1.1 List Forms
Recupera l'elenco dei form creati.

*   **Endpoint:** `/forms`
*   **Method:** `GET`
*   **Parameters:** *(in lavorazione)*
*   **Permissions:** Pubblico temporaneamente (per embed su pagine frontend). 
*   **Returns:** Array di stringhe JSON valide che rappresentano gli oggetti `Form`.

## Prossimi Endpoints (Roadmap)
- `POST /forms`: Creazione nuovo form (richiederà autenticazione admin).
- `GET /forms/{id}`: Lettura singola (pubblico).
- `PATCH /forms/{id}`: Aggiornamento parziale configurazione.
- `POST /forms/{id}/submissions`: Invio nuovi dati tramite form frontend.
