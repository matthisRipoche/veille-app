# Veille

Archive des bilans quotidiens de veille technologique. Même fonctionnalités que la version Node (`veille-app`), réécrite en Laravel, avec MariaDB et un serveur MCP pour qu'une IA dépose les rapports.

Pages :
- `/` : les 7 derniers rapports
- `/reports` : tous les rapports, du plus récent au plus ancien, 10 par page
- `/reports/AAAA-MM-JJ` : détail d'un rapport

Stack : Laravel 13 (PHP 8.4), MariaDB 11.4, Blade, [laravel/mcp](https://github.com/laravel/mcp). Tout tourne dans Docker, PHP n'est pas nécessaire sur la machine.

## Démarrer

```bash
cp .env.example .env      # puis renseigner API_TOKEN et DB_PASSWORD
docker compose up -d --build
```

Le site écoute sur http://localhost:8080. Les migrations sont appliquées au démarrage ; la base vit dans le volume `db-data`.

Générer un jeton : `docker run --rm php:8.4-cli php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"`

## Déployer en production (VPS)

Prérequis : Docker + Docker Compose sur le serveur, un enregistrement DNS `A` du domaine vers l'IP du serveur, ports 80 et 443 ouverts.

```bash
git clone git@github.com:matthisRipoche/veille-app.git veille && cd veille
cp .env.example .env
# Renseigner : API_TOKEN, DB_PASSWORD, puis décommenter COMPOSE_PROFILES=prod,
# APP_DOMAIN et APP_URL (https://veille.matthisripoche.com)
docker compose up -d --build
```

Caddy obtient et renouvelle automatiquement le certificat HTTPS. L'application n'écoute que sur `127.0.0.1` ; seuls 80/443 sont publics.

Mise à jour : `git pull && docker compose up -d --build`.

## Variables d'environnement (fichier `.env`)

| Variable | Rôle |
|---|---|
| `API_TOKEN` | Obligatoire. Jeton exigé pour déposer un rapport (API REST et MCP HTTP). |
| `DB_PASSWORD` | Obligatoire. Mot de passe de l'utilisateur MariaDB. |
| `SITE_PASSWORD` | Optionnel. Protège la lecture du site par mot de passe (HTTP Basic, nom d'utilisateur libre). |
| `DB_DATABASE`, `DB_USERNAME` | Base et utilisateur MariaDB (`veille` par défaut). |
| `APP_PORT` | Port local de l'app, lié à `127.0.0.1` (8080 par défaut). |
| `COMPOSE_PROFILES` | `prod` active Caddy (HTTPS) en production. |
| `APP_DOMAIN` | Domaine servi par Caddy (obligatoire avec le profil `prod`). |
| `APP_URL` | URL publique, utilisée dans les liens renvoyés par le MCP. |
| `APP_KEY` | Optionnel. Générée au démarrage si vide. |

Derrière un reverse proxy HTTPS (Caddy, Nginx, Traefik…), `X-Forwarded-*` est pris en compte. Sans HTTPS, le jeton et le mot de passe circulent en clair.

Point de contrôle : `/healthz` (sans authentification).

## Serveur MCP

Trois outils, pour qu'une IA publie et consulte les bilans :

| Outil | Rôle |
|---|---|
| `save_report` | Crée le bilan d'une date, ou le remplace s'il existe (`date`, `title`, `content_md`, `summary` optionnel). |
| `list_reports` | Liste les derniers bilans (`limit`, 10 par défaut). |
| `get_report` | Renvoie un bilan à partir de sa date. |

### HTTP (client distant ou local)

Endpoint : `POST /mcp/veille` avec `Authorization: Bearer <API_TOKEN>`.

```bash
claude mcp add --transport http veille http://localhost:8080/mcp/veille \
  --header "Authorization: Bearer $API_TOKEN"
```

### stdio (via le conteneur, sans jeton)

```bash
claude mcp add veille -- docker compose -f /chemin/vers/veille-laravel/compose.yaml exec -T app php artisan mcp:start veille
```

Équivalent JSON pour tout client MCP :

```json
{
  "mcpServers": {
    "veille": {
      "command": "docker",
      "args": ["compose", "-f", "/chemin/vers/veille-laravel/compose.yaml", "exec", "-T", "app", "php", "artisan", "mcp:start", "veille"]
    }
  }
}
```

## API de dépôt REST

`POST /api/reports` avec `Authorization: Bearer <API_TOKEN>` et un corps JSON :

```json
{
  "date": "2026-10-11",
  "title": "Bilan de veille du 11 octobre 2026",
  "summary": "Résumé court en une ou deux phrases.",
  "content_md": "## IA / LLM\n\n- Point 1 ... [source](https://exemple.com)\n"
}
```

- `date` (AAAA-MM-JJ), `title` (200 car. max) et `content_md` (Markdown) sont obligatoires, `summary` (1000 car. max) est optionnel.
- Un seul rapport par date : renvoyer la même date remplace le rapport existant (la requête peut donc être rejouée sans doublon).
- Réponses : `201` création, `200` mise à jour, `400` données invalides, `401` jeton invalide.

## Développement

Aucun PHP local requis :

```bash
docker run --rm -v "$PWD:/app" -w /app composer:latest composer install
docker run --rm -v "$PWD:/app" -w /app composer:latest php artisan test
```

Structure utile : `app/Actions/SaveReport.php` (règles et écriture, partagées par l'API et le MCP), `app/Mcp/` (serveur et outils), `routes/ai.php` (routes MCP), `app/Support/Markdown.php` (rendu assaini).

## Sécurité

Le Markdown est converti en HTML sans HTML brut ni schémas de lien dangereux, les liens externes s'ouvrent en `noopener noreferrer nofollow`, et une politique CSP stricte est appliquée. La lecture du site n'est protégée que si `SITE_PASSWORD` est défini.
