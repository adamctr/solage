# Trucs à retenir pour dire au diplôme

Notes pour la soutenance. Anecdotes, déclics, et points à glisser dans le discours ou en réponse à une question du jury.

---

## 1. Le déclic IDOR — faille sur l'URSSAF

**Source veille** : article *« Fuites de données dans le secteur de la santé : comprendre pour mieux réagir »*, qui revient notamment sur la faille IDOR de l'URSSAF (accès aux données personnelles d'autres assurés en changeant un identifiant dans l'URL).

**Le déclic** : en lisant l'analyse de l'incident, j'ai réalisé que **Solage avait exactement la même classe de faille**. Mes routes `/edituser/{id}`, `/api/users/delete` et `/api/posts/delete` vérifiaient que l'utilisateur était *loggé* (`AuthMiddleware`), mais **pas** qu'il était propriétaire de la ressource. Un utilisateur authentifié pouvait éditer ou supprimer le profil de n'importe qui en changeant l'ID dans l'URL ou le JSON.

**Correctif appliqué** : check `current_user_id === target_owner_id || isAdmin()` au plus tôt dans chaque contrôleur, sinon → `403` + `Logger::warning(...)` pour audit. Documenté dans `Probleme-Solution.md`.

**Ce que ça illustre pour le jury** :
- **T3 — Apprendre en continu** : la veille technologique a un impact direct, mesurable, sur la qualité du projet. Ce n'est pas une case à cocher.
- **Authentification ≠ Autorisation** : être loggé ne suffit jamais — il faut toujours re-vérifier les droits sur **la ressource demandée**, pas seulement sur l'identité.
- **IDOR = OWASP A01 (Broken Access Control)**, n°1 du Top 10 OWASP 2021. La faille la plus répandue sur le web, et celle qui a touché un organisme aussi gros que l'URSSAF.
- **Cycle vertueux** : incident réel public → veille → audit du code maison → fix → documentation → réponse jury prête.

---
