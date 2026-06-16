# Déploiement de Solage

Procédure de mise en production sur un serveur Linux, à partir de la pile
`docker-compose.prod.yml` (Traefik HTTPS, FrankenPHP, PostgreSQL, migrations).

## 1. Pré-requis

- Un serveur Linux avec une **IP publique** et Docker + le plugin Docker Compose installés.
- Un **nom de domaine** dont l'enregistrement DNS **A** pointe vers l'IP du serveur.
  Indispensable : Traefik obtient le certificat TLS via le challenge `TLS-ALPN-01`, qui exige
  que le domaine résolve vers ce serveur.
- Les **ports 80 et 443** ouverts et joignables depuis Internet (80 pour la redirection
  HTTP → HTTPS, 443 pour le trafic et le challenge ACME).
- Le dépôt cloné sur le serveur (les images sont construites depuis le contexte local).

## 2. Variables d'environnement

Copier le gabarit puis renseigner les valeurs réelles :

```bash
cp .env.example .env
```

| Variable | Rôle | Exemple |
|---|---|---|
| `DB_NAME` | Nom de la base | `solage` |
| `DB_USER` | Utilisateur PostgreSQL | `solage` |
| `DB_PASSWORD` | **Mot de passe fort** (jamais commité) | `…` |
| `APP_DOMAIN` | Domaine public servi en HTTPS | `solage.mondomaine.fr` |
| `ACME_EMAIL` | E-mail Let's Encrypt (avis d'expiration des certificats) | `ops@mondomaine.fr` |

`docker compose` lit automatiquement `.env`. Les variables notées `${VAR:?…}` dans la pile
**font échouer le démarrage** si elles manquent : garde-fou volontaire contre un déploiement
mal configuré.

> En production, l'application joint Postgres par le nom de service `postgres` sur le réseau
> interne Docker (déjà fixé dans la pile) ; `DB_HOST`/`DB_PORT` du gabarit ne servent qu'au
> développement local.

## 3. Procédure de déploiement

```bash
# 1. Récupérer la version à déployer (premier déploiement : git clone)
git pull                       # ou : git checkout <tag>

# 2. Construire l'image et démarrer la pile
docker compose -f docker-compose.prod.yml up -d --build

# 3. Suivre le démarrage
docker compose -f docker-compose.prod.yml logs -f
```

L'ordonnancement est garanti par la pile : Postgres démarre, son *healthcheck* passe au vert,
le service **`migrate`** applique les migrations puis se termine (`exit 0`), et seulement
ensuite l'application démarre (`depends_on … service_completed_successfully`). Traefik obtient
le certificat à la première requête HTTPS.

> **Schéma de base — premier démarrage vs suivants.** Au premier déploiement, le volume
> `postgres-data` est vide : Postgres exécute `solage.pg.sql` (schéma initial) via
> `docker-entrypoint-initdb.d`. Le service `migrate` applique ensuite les évolutions. Aux
> déploiements suivants, le volume existe : `solage.pg.sql` **n'est pas rejoué**, seules les
> migrations idempotentes s'appliquent.

Rejouer les migrations manuellement si besoin (elles sont idempotentes) :

```bash
docker compose -f docker-compose.prod.yml run --rm migrate
```

## 4. Vérification (smoke test)

```bash
# Tous les services « up », postgres « healthy », migrate « exited (0) »
docker compose -f docker-compose.prod.yml ps

# HTTP redirige vers HTTPS  (remplacer $APP_DOMAIN par le domaine réel)
curl -I http://$APP_DOMAIN     # → 308 vers https://

# HTTPS répond et porte l'en-tête HSTS
curl -I https://$APP_DOMAIN    # → 200 + Strict-Transport-Security
```

Si le certificat n'est pas délivré, vérifier que le DNS pointe bien vers le serveur et que le
port 443 est ouvert : c'est la cause n°1 d'échec ACME.

## 5. Mise à jour

Identique au déploiement : `git pull` puis `up -d --build`. Seuls les conteneurs dont l'image
change sont recréés ; les migrations additives s'appliquent sans toucher aux données existantes.

## 6. Retour arrière (rollback)

Deux dimensions, traitées séparément.

**Code applicatif** — revenir à la version précédente et reconstruire :

```bash
git checkout <tag-ou-sha-précédent>
docker compose -f docker-compose.prod.yml up -d --build
```

> **Limite assumée.** Les images sont taguées `solage-app:prod` (tag *mutable*, construit sur
> le serveur, sans *registry*). Le retour arrière repose donc sur l'état Git du serveur. En
> contexte professionnel, je taguerais les images par SHA de commit et les pousserais sur un
> *registry* pour un rollback par simple repointage de tag — compromis acceptable ici pour un
> projet solo.

**Données** — les migrations sont **additives et idempotentes** (aucun script *down*) :
l'ancien code reste compatible avec une colonne ajoutée, donc un rollback de code seul ne casse
pas la base. Si une migration a réellement supprimé ou transformé des données, restaurer la
sauvegarde prise **avant** le déploiement (section 7).

## 7. Sauvegarde et restauration

Prendre un *dump* **avant chaque déploiement** :

```bash
docker compose -f docker-compose.prod.yml exec -T postgres \
  sh -c 'pg_dump --clean --if-exists -U "$POSTGRES_USER" "$POSTGRES_DB"' \
  > solage-$(date +%F-%H%M).sql
```

Restauration :

```bash
cat solage-AAAA-MM-JJ-HHMM.sql | \
  docker compose -f docker-compose.prod.yml exec -T postgres \
  sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
```

`--clean --if-exists` rend la restauration rejouable (les objets sont supprimés puis recréés).
Stocker les *dumps* **hors du dépôt Git** : ils contiennent des données.

## 8. Environnements

| Aspect | Développement | Pré-production (staging) | Production |
|---|---|---|---|
| Fichier compose | `docker-compose.yml` | `docker-compose.prod.yml` + CA staging | `docker-compose.prod.yml` |
| TLS | aucun (HTTP) | Let's Encrypt **staging** | Let's Encrypt **production** |
| Domaine | `localhost` | domaine jetable | domaine réel |
| Port Postgres | 5432 exposé (outils) | non exposé | non exposé |
| Code | *bind-mount* (rechargement à chaud) | image | image |
| Jeu de données | `seed.sql` chargé | non | non |
| Dashboard Traefik | `:8080` (insecure) | désactivé | désactivé |
