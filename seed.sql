-- Solage — demo seed for oral defense.
-- Mounted in dev as /docker-entrypoint-initdb.d/02-seed.sql, so it runs once
-- after 01-schema.sql on the first `docker compose up`. To re-seed:
--   docker compose down -v && docker compose up
--
-- All accounts share the password "demo1234".
-- Bcrypt cost 10, same defaults as UserModel::createUser.

BEGIN;

-- -----------------------------------------------------------------------------
-- users  (role 1=Admin, 2=Utilisateur, 3=Modérateur — cf. solage.pg.sql)
-- -----------------------------------------------------------------------------
INSERT INTO users (id, name, firstname, email, password, role, image) VALUES
    (1,  'Lemoine',   'Alice',     'admin@solage.demo',      '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 1, '👑'),
    (2,  'Garnier',   'Bob',       'mod@solage.demo',        '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 3, '🛡️'),
    (3,  'Mendes',    'Clara',     'clara@solage.demo',      '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 3, '🧠'),
    (4,  'Petit',     'David',     'user@solage.demo',       '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 2, '🚀'),
    (5,  'Rossi',     'Emma',      'emma@solage.demo',       '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 2, '🍕'),
    (6,  'Tran',      'Félix',     'felix@solage.demo',      '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 2, '🐳'),
    (7,  'Noir',      'Gabrielle', 'gabrielle@solage.demo',  '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 2, '🌹'),
    (8,  'Bernard',   'Hugo',      'hugo@solage.demo',       '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 2, '🏃'),
    (9,  'Diallo',    'Inès',      'ines@solage.demo',       '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 2, '🎬'),
    (10, 'Caron',     'Julien',    'julien@solage.demo',     '$2y$10$S70YWZGb3AiqlmkNvnA7t.QyPbHEwip1AiVkRs5FsmWO9bu6QcLyK', 2, '🎲');
SELECT setval(pg_get_serial_sequence('users', 'id'), (SELECT MAX(id) FROM users));

-- -----------------------------------------------------------------------------
-- posts  — top-level (reply_to IS NULL) then threaded replies.
-- Dates spread over the two weeks before 2026-05-12 to look alive.
-- -----------------------------------------------------------------------------
INSERT INTO posts (id, user_id, date, likes, content, reply_to, reply_to_parent) VALUES
    -- top-level
    (1,  1,  '2026-05-10 09:12:00', 0, 'Première version de Solage en prod aujourd''hui. Trois ans de boulot. On respire 🚀', NULL, NULL),
    (2,  4,  '2026-05-10 14:30:00', 0, 'Quand tu refactor un controller à 200 lignes en 40 lignes propres : satisfaction maximum.', NULL, NULL),
    (3,  3,  '2026-05-09 18:45:00', 0, 'Petit thread sur MVC vs MVVM. Je sais que c''est éculé, mais il y a un détail qui change tout.', NULL, NULL),
    (4,  5,  '2026-05-09 12:00:00', 0, 'La pizza à la truffe c''est de l''arnaque ou pas ? Débat ouvert.', NULL, NULL),
    (5,  6,  '2026-05-08 22:15:00', 0, 'Docker Compose v3 → v3.8, deux jours perdus pour un changement de syntaxe de volume. Adorable.', NULL, NULL),
    (6,  8,  '2026-05-08 16:42:00', 0, 'Marathon de Paris dimanche. 3h47, premier sub-4h, je suis content.', NULL, NULL),
    (7,  9,  '2026-05-07 11:30:00', 0, 'Question sérieuse : c''est qui le clown qui a inventé Europe/Paris vs UTC+1 vs UTC+2 ?', NULL, NULL),
    (8,  7,  '2026-05-07 09:20:00', 0, 'PostgreSQL > MySQL. Je prendrai pas les questions.', NULL, NULL),
    (9,  10, '2026-05-06 19:00:00', 0, 'Soirée jeux de société hier, 4h de Brass Birmingham. J''ai perdu mais je m''en remettrai.', NULL, NULL),
    (10, 1,  '2026-05-06 14:15:00', 0, 'Petit rappel : si vous écrivez du SQL dans un controller, je vous renvoie le code en review.', NULL, NULL),
    (11, 2,  '2026-05-05 20:30:00', 0, 'Le café froid de 16h, c''est devenu une dépendance ou une personnalité ?', NULL, NULL),
    (12, 4,  '2026-05-05 11:00:00', 0, 'Premier post sur Solage 🎉 Hâte de tester les features.', NULL, NULL),
    (13, 5,  '2026-05-04 21:10:00', 0, 'Recette de la semaine : risotto aux champignons. Le secret c''est de ne pas remuer en continu, contre-intuitif.', NULL, NULL),
    (14, 3,  '2026-05-04 08:45:00', 0, 'XSS, IDOR, CSRF — checklist du jour avant de pusher. Faites pareil.', NULL, NULL),
    (15, 6,  '2026-05-03 17:30:00', 0, 'FrankenPHP vs PHP-FPM, retour d''expérience : worker mode change la vie pour les apps stateful.', NULL, NULL),
    (16, 8,  '2026-05-03 09:00:00', 0, 'Plus jamais de café Starbucks. Voilà, c''est dit.', NULL, NULL),
    (17, 9,  '2026-05-02 22:00:00', 0, 'Sortie ciné : Dune 3. Mitigée. Visuel ouf, scénario en carton.', NULL, NULL),
    (18, 7,  '2026-05-02 15:20:00', 0, 'Apprenez. Le. SQL. Les ORM c''est pratique mais sans fondations c''est l''enfer.', NULL, NULL),
    (19, 10, '2026-05-01 19:30:00', 0, 'Week-end à Lyon : bouchons, soleil, Fourvière. Si vous n''y êtes pas allés, foncez.', NULL, NULL),
    (20, 1,  '2026-05-01 10:00:00', 0, 'Code review du mois : 73 PRs mergées, 0 rollback. Équipe au top.', NULL, NULL),
    (21, 2,  '2026-04-30 14:00:00', 0, 'Question piège : un index sur une colonne avec 90% de NULL, ça sert à quoi à votre avis ?', NULL, NULL),
    (22, 5,  '2026-04-29 18:00:00', 0, 'Vu un concert hier : Christine and the Queens. Bouleversant.', NULL, NULL),

    -- replies on p3 (thread MVC vs MVVM)
    (23, 6,  '2026-05-09 19:00:00', 0, 'Tu parles du data binding bidirectionnel ?', 3, 3),
    (24, 3,  '2026-05-09 19:15:00', 0, 'Exactement. En MVVM le ViewModel observe le Model, en MVC le controller orchestre. Côté testabilité ce n''est pas la même.', 23, 3),
    (25, 8,  '2026-05-09 20:30:00', 0, 'Sur un projet PHP classique je ne vois pas l''intérêt de MVVM honnêtement.', 3, 3),

    -- replies on p4 (pizza truffe)
    (26, 7,  '2026-05-09 12:30:00', 0, 'Arnaque totale. C''est de l''huile parfumée 9 fois sur 10.', 4, 4),
    (27, 5,  '2026-05-09 13:00:00', 0, 'Merci, j''ai un argument pour mon mari maintenant.', 26, 4),

    -- replies on p7 (timezone)
    (28, 6,  '2026-05-07 12:00:00', 0, 'DST. C''est juste DST. Le clown c''est Benjamin Franklin.', 7, 7),
    (29, 9,  '2026-05-07 12:15:00', 0, 'Je vais lui en vouloir personnellement.', 28, 7),

    -- replies on p10 (no SQL in controllers)
    (30, 4,  '2026-05-06 14:45:00', 0, 'Noté chef 😅', 10, 10),
    (31, 3,  '2026-05-06 15:00:00', 0, 'Strict separation of concerns. C''est la base.', 10, 10),

    -- replies on p14 (security checklist)
    (32, 2,  '2026-05-04 09:30:00', 0, 'Tu rajoutes le rate limit dans la liste ?', 14, 14),
    (33, 3,  '2026-05-04 09:45:00', 0, 'Oui, et la validation côté serveur — jamais côté client only.', 32, 14),

    -- replies on p15 (FrankenPHP)
    (34, 1,  '2026-05-03 18:00:00', 0, 'Worker mode c''est cool mais attention aux fuites mémoire si tu stockes des objets en variables globales.', 15, 15),
    (35, 6,  '2026-05-03 18:30:00', 0, '+1, j''ai mangé 6h de debug là-dessus.', 34, 15),

    -- replies on p18 (apprenez le SQL)
    (36, 10, '2026-05-02 16:00:00', 0, 'Le drame des juniors qui ne savent pas écrire un JOIN proprement.', 18, 18),
    (37, 4,  '2026-05-02 16:30:00', 0, 'Recommandation de bouquin ?', 18, 18),
    (38, 7,  '2026-05-02 16:45:00', 0, 'SQL Performance Explained de Markus Winand. Court, percutant, change ta carrière.', 37, 18),

    -- replies on p21 (partial index)
    (39, 6,  '2026-04-30 14:30:00', 0, 'Partial index sur WHERE col IS NOT NULL ? Tu cibles que les 10% utiles.', 21, 21),
    (40, 2,  '2026-04-30 14:45:00', 0, 'Exactement 🎯 Beaucoup l''oublient.', 39, 21);
SELECT setval(pg_get_serial_sequence('posts', 'id'), (SELECT MAX(id) FROM posts));

-- -----------------------------------------------------------------------------
-- likes  — populate so popular posts feel popular.
-- The displayed count is COUNT(*) on this table (PostModel::getPosts), so
-- one row = one heart on the timeline.
-- -----------------------------------------------------------------------------
INSERT INTO likes (user_id, post, created_at) VALUES
    -- p1 Alice's prod launch — everyone celebrates (9 likes)
    (2, 1, '2026-05-10 09:20:00'), (3, 1, '2026-05-10 09:25:00'),
    (4, 1, '2026-05-10 09:31:00'), (5, 1, '2026-05-10 10:02:00'),
    (6, 1, '2026-05-10 10:15:00'), (7, 1, '2026-05-10 11:00:00'),
    (8, 1, '2026-05-10 12:30:00'), (9, 1, '2026-05-10 14:00:00'),
    (10,1, '2026-05-10 16:45:00'),

    -- p2 refactor
    (1, 2, '2026-05-10 15:00:00'), (3, 2, '2026-05-10 15:10:00'),
    (5, 2, '2026-05-10 16:20:00'), (7, 2, '2026-05-10 18:00:00'),

    -- p3 MVC thread
    (1, 3, '2026-05-09 19:00:00'), (6, 3, '2026-05-09 19:05:00'),
    (8, 3, '2026-05-09 21:00:00'),

    -- p4 pizza
    (1, 4, '2026-05-09 12:30:00'), (7, 4, '2026-05-09 12:40:00'),
    (10,4, '2026-05-09 14:00:00'),

    -- p5 Docker rage (devs solidaires)
    (1, 5, '2026-05-08 22:30:00'), (3, 5, '2026-05-09 08:00:00'),
    (4, 5, '2026-05-09 09:15:00'),

    -- p6 marathon — 7 likes
    (1, 6, '2026-05-08 17:00:00'), (2, 6, '2026-05-08 17:30:00'),
    (3, 6, '2026-05-08 18:00:00'), (4, 6, '2026-05-08 19:00:00'),
    (5, 6, '2026-05-08 20:00:00'), (7, 6, '2026-05-08 21:00:00'),
    (9, 6, '2026-05-09 08:00:00'),

    -- p8 PostgreSQL
    (1, 8, '2026-05-07 10:00:00'), (3, 8, '2026-05-07 10:30:00'),
    (6, 8, '2026-05-07 11:00:00'),

    -- p9 Brass Birmingham
    (1, 9, '2026-05-06 19:30:00'), (5, 9, '2026-05-06 20:00:00'),

    -- p10 Alice no-SQL — devs hochent la tête
    (2, 10, '2026-05-06 14:30:00'), (3, 10, '2026-05-06 14:35:00'),
    (4, 10, '2026-05-06 14:50:00'), (6, 10, '2026-05-06 15:30:00'),
    (9, 10, '2026-05-06 18:00:00'),

    -- p11 café froid
    (2, 11, '2026-05-05 20:45:00'), (4, 11, '2026-05-05 21:00:00'),

    -- p13 risotto
    (1, 13, '2026-05-04 21:30:00'), (4, 13, '2026-05-04 22:00:00'),
    (7, 13, '2026-05-05 08:00:00'), (9, 13, '2026-05-05 09:00:00'),

    -- p14 security checklist
    (1, 14, '2026-05-04 09:00:00'), (2, 14, '2026-05-04 09:15:00'),
    (4, 14, '2026-05-04 10:00:00'), (6, 14, '2026-05-04 11:00:00'),
    (10,14, '2026-05-04 14:00:00'),

    -- p15 FrankenPHP
    (1, 15, '2026-05-03 18:00:00'), (2, 15, '2026-05-03 18:30:00'),
    (3, 15, '2026-05-03 19:00:00'), (8, 15, '2026-05-03 20:00:00'),

    -- p17 Dune 3
    (5, 17, '2026-05-02 22:30:00'), (10,17, '2026-05-02 23:00:00'),

    -- p18 SQL prophet — 6 likes
    (1, 18, '2026-05-02 16:00:00'), (2, 18, '2026-05-02 16:15:00'),
    (3, 18, '2026-05-02 16:30:00'), (5, 18, '2026-05-02 17:00:00'),
    (6, 18, '2026-05-02 17:30:00'), (9, 18, '2026-05-02 18:00:00'),

    -- p19 Lyon
    (4, 19, '2026-05-01 20:00:00'), (5, 19, '2026-05-01 21:00:00'),
    (8, 19, '2026-05-01 22:00:00'),

    -- p20 code review du mois
    (3, 20, '2026-05-01 10:30:00'), (5, 20, '2026-05-01 11:00:00'),
    (6, 20, '2026-05-01 12:00:00'), (8, 20, '2026-05-01 13:00:00'),
    (10,20, '2026-05-01 14:00:00'),

    -- p21 partial index
    (1, 21, '2026-04-30 14:30:00'), (3, 21, '2026-04-30 14:45:00'),
    (6, 21, '2026-04-30 15:00:00'),

    -- p22 concert
    (4, 22, '2026-04-29 18:30:00'), (8, 22, '2026-04-29 19:00:00'),
    (9, 22, '2026-04-29 20:00:00');

COMMIT;
