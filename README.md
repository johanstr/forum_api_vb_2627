# Forum API

Een REST API voor een forum, geschreven in vanilla PHP (zonder framework). De API werkt met drie resources: **threads**, **topics** (binnen een thread) en **replies** (binnen een topic). Alle responses zijn JSON.
  
# *LET OP!!!!* Dit is slechts een voorbeeld. Het is niet bedoeld om dit te gebruiken in een productieomgeving.  
Dit is niet een veilige API. Niet alle belangrijke gegevens zijn veilig, en er is geen authenticatie of autorisatie. Het is alleen bedoeld om te laten zien hoe je een REST API kunt schrijven in PHP, en hoe je dat kunt testen met een API-client.  
## Vereisten

- PHP 8.1 of hoger, met de extensies `pdo` en `json`
- MySQL / MariaDB
- Apache met `mod_rewrite` (bijv. MAMP of XAMPP)
- Composer (alleen voor de autoloader)

## Installatie

1. **Database importeren.** Maak een database `forum_api_vb` aan en importeer `forum_api_vb.sql`. Daarmee krijg je de tabellen `threads`, `topics`, `replies` en `users`, met testdata.
2. **Databaseverbinding instellen.** Pas zo nodig de gegevens bovenin `app/Database/Database.php` aan (`$dbHost`, `$dbName`, `$dbUser`, `$dbPass`).
3. **Autoloader genereren:**
   ```sh
   composer dump-autoload
   ```
4. **Virtual host aanmaken.** Laat een lokaal domein (bijv. `https://forum-api-vb.local`) naar de root van dit project wijzen. Zorg dat `AllowOverride All` aan staat, zodat `.htaccess` gebruikt wordt.
5. **Testen.** Open `https://forum-api-vb.local/threads` in de browser of in een API-client zoals Bruno of Postman.

## Projectstructuur

```
forum-api/
├── .htaccess                 Stuurt elke URL door naar index.php (resource + id)
├── index.php                 Startpunt: laadt de routes en de RequestHandler
├── forum_api_vb.sql          Database met testdata
├── openapi.yaml              API-documentatie (OpenAPI 3)
├── docs/index.html           Swagger UI voor openapi.yaml
└── app/
    ├── Routes/routes.php     Koppelt methode + URL aan controller + methode
    ├── Http/
    │   ├── RequestHandler.php    Leest het request en roept de juiste controller aan
    │   ├── ApiResponse.php       Bouwt de JSON-responses en statuscodes
    │   └── Controllers/          Thread-, Topic- en ReplyController (+ validatie)
    ├── Models/               Thread-, Topic- en ReplyModel (CRUD-queries)
    └── Database/Database.php PDO-verbinding
```

### Hoe een request loopt

1. `.htaccess` herschrijft `/threads/5` naar `index.php?resource=threads&id=5`.
2. `RequestHandler` maakt daar de route-sleutel `threads/{id}` van en zoekt die op in `routes.php`.
3. De controller-methode wordt aangeroepen, bijvoorbeeld `ThreadController::show(5)`.
4. De controller haalt de data op via het model en stuurt die terug met `ApiResponse`.

## Documentatie voor frontend developers

De volledige API-documentatie staat in `openapi.yaml`. Je kunt die op twee manieren bekijken:

- **In de browser:** open [`https://forum-api-vb.local/docs/`](https://forum-api-vb.local/docs/). Met **Try it out** kun je per endpoint direct een request uitvoeren. Let op: dat zijn echte requests op de database.
- **In Bruno of Postman:** importeer `openapi.yaml` als collectie.

Pas je de API aan, werk dan ook `openapi.yaml` bij.

## Endpoints

Hetzelfde patroon geldt voor `threads`, `topics` en `replies`:

| Methode  | URL                | Actie                                       | Succes |
|----------|--------------------|---------------------------------------------|--------|
| `GET`    | `/{resource}`      | Alle records ophalen                        | 200    |
| `GET`    | `/{resource}/{id}` | Eén record ophalen                          | 200    |
| `POST`   | `/{resource}`      | Nieuw record aanmaken                       | 201    |
| `PUT`    | `/{resource}/{id}` | Record volledig aanpassen (alle velden verplicht) | 200    |
| `PATCH`  | `/{resource}/{id}` | Record gedeeltelijk aanpassen (alleen meegestuurde velden) | 200    |
| `DELETE` | `/{resource}/{id}` | Record verwijderen                          | 204    |

`GET /` geeft hetzelfde terug als `GET /threads`.

### Velden

| Resource  | Velden                                      |
|-----------|---------------------------------------------|
| `threads` | `title` (max. 100 tekens), `content`, `user_id` |
| `topics`  | `title` (max. 100 tekens), `content`, `thread_id`, `user_id` |
| `replies` | `content`, `topic_id`, `user_id`            |

Alle velden zijn verplicht bij `POST` en `PUT`. Id-velden moeten een positief geheel getal zijn. Voor `thread_id` en `topic_id` controleert de API ook of die thread of dat topic bestaat (regel `exists`); zo niet, dan volgt een `422`. `id`, `created_at` en `updated_at` worden door de API zelf ingevuld.

De body mag als JSON (`Content-Type: application/json`) of als formulierdata (`application/x-www-form-urlencoded`) worden meegestuurd.

## Voorbeelden

### Een thread aanmaken

```http
POST /threads
Content-Type: application/json

{
  "title": "Mijn eerste thread",
  "content": "Hallo allemaal!",
  "user_id": 1
}
```

Response `201 Created`, met een `Location`-header naar de nieuwe thread:

```json
{
    "meta": {
        "api_version": "1.0",
        "api_name": "forum_api",
        "status": 201,
        "status_message": "Created"
    },
    "data": {
        "id": 23,
        "title": "Mijn eerste thread",
        "content": "Hallo allemaal!",
        "user_id": 1,
        "created_at": "2026-10-02 12:00:00",
        "updated_at": "2026-10-02 12:00:00"
    }
}
```

Bij een lijst bevat `meta` ook een `count` met het aantal records.

### Alleen de titel aanpassen

```http
PATCH /threads/23
Content-Type: application/json

{ "title": "Nieuwe titel" }
```

### Foutmelding

Bij een fout staat er een `error` in de response in plaats van `data`:

```json
{
    "meta": {
        "api_version": "1.0",
        "api_name": "forum_api",
        "status": 422,
        "status_message": "Unprocessable Content"
    },
    "error": {
        "message": "De invoer is niet geldig.",
        "details": {
            "title": "Het veld title is verplicht."
        }
    }
}
```

## Statuscodes

| Code | Betekenis |
|------|-----------|
| 200 | OK |
| 201 | Aangemaakt (met `Location`-header) |
| 204 | Verwijderd, geen body |
| 400 | Ongeldig id (bijv. `/threads/abc`) of ongeldige JSON in de body |
| 404 | Route of record bestaat niet |
| 405 | Methode niet toegestaan voor deze URL (met `Allow`-header) |
| 422 | Validatie mislukt; per veld staat de fout in `error.details` |
| 500 | Serverfout; de echte foutmelding staat in de error-log van de server |

## CORS

Elke response stuurt `Access-Control-Allow-Origin: *`. Een `OPTIONS`-request (preflight) krijgt `204` terug, met de toegestane methodes en headers. De API is dus ook vanuit JavaScript in de browser te gebruiken.

## Bekende beperkingen

- Er is geen authenticatie: iedereen kan records aanmaken, aanpassen en verwijderen.
- Er wordt niet gecontroleerd of `user_id` echt bestaat. De database heeft ook geen foreign keys.
- Er is (nog) geen endpoint voor `users`.
- Als je een thread of topic verwijdert, blijven de bijbehorende topics en replies bestaan.

# Lesboek
Het lesboek, welke je in de repo aantreft komt niet geheel met de code in deze repo overeen.  
Zo is bijvoorbeeld de file ***.htaccess*** aangepast t.o.v. de uitwerking in het lesboek.  
