# Déploiement de Solage sur Dokploy

Variante de déploiement via **Dokploy** (PaaS auto-hébergé sur VPS). Complète
`DEPLOYMENT.md`, qui décrit le déploiement « VPS nu » avec la pile `docker-compose.prod.yml`.

## Le point à comprendre d'abord : un seul Traefik

`docker-compose.prod.yml` **embarque son propre Traefik** qui prend les ports 80/443 et gère
Let's Encrypt. **Dokploy fait déjà exactement ça** : il tourne sur le VPS avec son propre
Traefik qui occupe 80/443 et gère les certificats. Déployer la pile prod telle quelle dans
Dokploy provoque une **collision de ports** — le déploiement échoue.

D'où deux chemins, à choisir consciemment :

| | A — Dokploy gère le routing (recommandé) | B — VPS nu, pile autonome |
|---|---|---|
| Traefik | Celui de Dokploy | Le tien (`docker-compose.prod.yml`) |
| TLS / Let's Encrypt | Dokploy | Tes labels ACME |
| Comment | Compose Dokploy **sans** service Traefik | `git clone` + `docker compose up` (voir `DEPLOYMENT.md`) |
| Quand | On veut se servir de Dokploy pour ce qu'il fait | On veut une pile qui tourne partout, Dokploy juste pour l'orchestrer |

> **Réponse jury.** « J'ai retiré mon Traefik et je m'appuie sur celui de Dokploy : une seule
> instance termine le TLS sur le VPS. Garder deux Traefik se battant pour les ports 80/443 serait
> une faute de conception. » Le chemin B revient à ne pas utiliser Dokploy (autant faire un
> `docker compose` en SSH) — le présent guide décrit donc **A**.

## 1. Pré-requis

- Un VPS avec **Dokploy installé** (`curl -sSL https://dokploy.com/install.sh | sh`).
- Le DNS du domaine (`APP_DOMAIN`) avec un enregistrement **A** vers l'IP du VPS.
- Le dépôt accessible par Dokploy (URL Git + branche `main`).

## 2. La compose dédiée Dokploy

Créer `docker-compose.dokploy.yml` à la racine. C'est une copie de la prod **sans Traefik**,
où seul le service `app` rejoint le réseau de Dokploy :

```yaml
name: solage

services:
  migrate:
    build: { context: ., dockerfile: Dockerfile }
    image: solage-app:prod
    command: ["php", "/app/bin/migrate.php"]
    environment:
      DB_HOST: postgres
      DB_PORT: 5432
      DB_NAME: ${DB_NAME}
      DB_USER: ${DB_USER}
      DB_PASSWORD: ${DB_PASSWORD}
    depends_on:
      postgres: { condition: service_healthy }
    restart: "no"
    networks: [solage]

  app:
    build: { context: ., dockerfile: Dockerfile }
    image: solage-app:prod
    restart: unless-stopped
    environment:
      DB_HOST: postgres
      DB_PORT: 5432
      DB_NAME: ${DB_NAME}
      DB_USER: ${DB_USER}
      DB_PASSWORD: ${DB_PASSWORD}
    depends_on:
      postgres: { condition: service_healthy }
      migrate: { condition: service_completed_successfully }
    labels:
      - traefik.enable=true
      - traefik.http.routers.solage.rule=Host(`${APP_DOMAIN}`)
      - traefik.http.routers.solage.entrypoints=websecure
      - traefik.http.routers.solage.tls.certresolver=letsencrypt
      - traefik.http.services.solage.loadbalancer.server.port=80
    networks: [solage, dokploy-network]

  postgres:
    image: postgres:16-alpine
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_NAME}
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres-data:/var/lib/postgresql/data
      - ./solage.pg.sql:/docker-entrypoint-initdb.d/01-schema.sql:ro
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U $${POSTGRES_USER} -d $${POSTGRES_DB}"]
      interval: 10s
      timeout: 3s
      retries: 10
    networks: [solage]

volumes:
  postgres-data:

networks:
  solage:
    driver: bridge
  dokploy-network:
    external: true
```

Les écarts avec `docker-compose.prod.yml`, et leur justification :

- **Plus de service `traefik`** → le Traefik de Dokploy termine le TLS. Une seule instance sur le VPS.
- **`dokploy-network` (externe)** sur `app` uniquement → c'est par ce réseau que le Traefik de
  Dokploy voit l'application. Postgres reste sur le réseau privé `solage`, donc **jamais exposé**.
- **`certresolver=letsencrypt`** → nom du resolver configuré dans le Traefik de Dokploy
  (le resolver local s'appelait `le`).
- **Plus de `ports: 80/443`** → ces ports appartiennent à Dokploy.

## 3. Configuration dans l'UI Dokploy

1. *Create Project* → *Create Service* → **Compose**.
2. Source : URL du dépôt Git, branche `main`, **Compose Path** = `docker-compose.dokploy.yml`.
3. Onglet **Environment** — y placer les secrets (ils ne sont **pas** dans `.env`, gitignored) :
   ```
   DB_NAME=solage
   DB_USER=solage
   DB_PASSWORD=<mot de passe fort>
   APP_DOMAIN=ton-domaine.fr
   ```
4. **Deploy**.

> **HTTPS.** Les labels Traefik étant écrits à la main, Dokploy n'a rien à générer : le
> certificat est obtenu automatiquement à la première requête HTTPS. *Variante* : retirer les
> labels et déclarer le domaine via l'onglet **Domains** de Dokploy (service `app`, port 80),
> qui génère les labels pour toi. Choisir **un seul** des deux, sinon doublon.

## 4. Points de vigilance

- **Ordre `migrate` → `app`** : garanti par `service_completed_successfully` (déjà câblé).
- **Schéma de base** : `solage.pg.sql` n'est chargé qu'au **premier** démarrage de Postgres
  (volume `postgres-data` vide). Les évolutions ultérieures passent par `bin/migrate.php`.
  Identique au comportement décrit dans `DEPLOYMENT.md` §3.
- **Secrets** : dans l'UI Dokploy (Environment), jamais dans un fichier commité.

## 5. Vérification

Mêmes contrôles que `DEPLOYMENT.md` §4 : `https://$APP_DOMAIN` répond en 200, le certificat est
délivré. En cas d'échec ACME, vérifier en priorité que le DNS pointe vers le VPS.
