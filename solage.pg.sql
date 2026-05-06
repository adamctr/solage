-- PostgreSQL schema for Solage
-- Ported from MariaDB dump (solage.sql).
-- Notes:
--   - `user` is reserved in PostgreSQL → renamed to `user_id` in FK columns.
--   - `datetime` → `TIMESTAMP`.
--   - `int(11) AUTO_INCREMENT` → `SERIAL` / `BIGSERIAL`.
--   - Engine/charset/collate clauses dropped (utf8 is the cluster default).
--   - Migrations.php applies the additive columns reply_to_parent / image / etc.

BEGIN;

-- -----------------------------------------------------------------------------
-- roles
-- -----------------------------------------------------------------------------
CREATE TABLE roles (
    id   SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

INSERT INTO roles (id, name) VALUES
    (1, 'Admin'),
    (2, 'Utilisateur'),
    (3, 'Modérateur');
SELECT setval(pg_get_serial_sequence('roles', 'id'), (SELECT COALESCE(MAX(id), 1) FROM roles));

-- -----------------------------------------------------------------------------
-- users
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    id        SERIAL PRIMARY KEY,
    name      VARCHAR(255) NOT NULL,
    firstname VARCHAR(255) NULL,
    email     VARCHAR(255) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,
    role      INT NULL REFERENCES roles(id) ON DELETE SET NULL,
    image     VARCHAR(255) NULL
);
CREATE INDEX users_role_idx ON users(role);

INSERT INTO users (id, name, firstname, email, password, role, image) VALUES
    (1, 'Dupont', 'Jean',   'jean.dupont@example.com',   '$2y$10$E8bX3s/Y0uY1Bd.NFUyF4Oe9iG6FECZsTHBNEEdpt88Kk2flFz.R6', 1, 'path/to/image1.jpg'),
    (2, 'Martin', 'Sophie', 'sophie.martin@example.com', '$2y$10$V1kPdA0q5wv1.5W5IFx5neL0h2of3gdWsH.tR6H1z2BaU7y9E5B/y', 2, 'path/to/image2.jpg'),
    (3, 'Durand', 'Pierre', 'pierre.durand@example.com', '$2y$10$G9Yf0vX/mg5HRC2RGT5xUeB2z33r7CML0P8/2W5vA1QmM1drV7p/a', 3, 'path/to/image3.jpg');
SELECT setval(pg_get_serial_sequence('users', 'id'), (SELECT COALESCE(MAX(id), 1) FROM users));

-- -----------------------------------------------------------------------------
-- posts
-- -----------------------------------------------------------------------------
CREATE TABLE posts (
    id              SERIAL PRIMARY KEY,
    user_id         INT NULL REFERENCES users(id) ON DELETE CASCADE,
    date            TIMESTAMP NOT NULL,
    likes           INT DEFAULT 0,
    content         TEXT NULL,
    reply_to        INT NULL,
    reply_to_parent INT NULL,
    image           TEXT NULL
);
CREATE INDEX posts_user_idx ON posts(user_id);

INSERT INTO posts (id, user_id, date, likes, content) VALUES
    (1, 1, '2024-09-25 11:36:45', 10, 'Ceci est un post intéressant !'),
    (2, 2, '2024-09-25 11:36:45', 5,  'Voici un autre post.'),
    (3, 1, '2024-09-25 11:36:45', 0,  'Un post sans likes.');
SELECT setval(pg_get_serial_sequence('posts', 'id'), (SELECT COALESCE(MAX(id), 1) FROM posts));

-- -----------------------------------------------------------------------------
-- responses
-- -----------------------------------------------------------------------------
CREATE TABLE responses (
    id       SERIAL PRIMARY KEY,
    post     INT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id  INT NULL REFERENCES users(id) ON DELETE CASCADE,
    date     TIMESTAMP NOT NULL,
    likes    INT DEFAULT 0,
    content  TEXT NULL,
    reply_to INT NULL REFERENCES responses(id) ON DELETE SET NULL
);
CREATE INDEX responses_post_idx     ON responses(post);
CREATE INDEX responses_user_idx     ON responses(user_id);
CREATE INDEX responses_reply_to_idx ON responses(reply_to);

INSERT INTO responses (id, post, user_id, date, likes, content, reply_to) VALUES
    (1, 1, 2, '2024-09-25 11:36:45', 2, 'Ceci est une réponse au post 1.', NULL),
    (2, 1, 3, '2024-09-25 11:36:45', 3, 'Je suis d''accord avec le post 1.', NULL),
    (3, 2, 1, '2024-09-25 11:36:45', 1, 'Merci pour ce post !', NULL);
SELECT setval(pg_get_serial_sequence('responses', 'id'), (SELECT COALESCE(MAX(id), 1) FROM responses));

-- -----------------------------------------------------------------------------
-- likes
-- -----------------------------------------------------------------------------
CREATE TABLE likes (
    id         SERIAL PRIMARY KEY,
    user_id    INT NULL REFERENCES users(id) ON DELETE CASCADE,
    post       INT NULL REFERENCES posts(id) ON DELETE CASCADE,
    response   INT NULL REFERENCES responses(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL
);
CREATE INDEX likes_user_idx     ON likes(user_id);
CREATE INDEX likes_post_idx     ON likes(post);
CREATE INDEX likes_response_idx ON likes(response);

INSERT INTO likes (id, user_id, post, response, created_at) VALUES
    (1, 1, 1, NULL, '2024-09-25 11:36:45'),
    (2, 2, 1, NULL, '2024-09-25 11:36:45'),
    (3, 3, 2, NULL, '2024-09-25 11:36:45'),
    (4, 2, 1, 1,    '2024-09-25 11:36:45'),
    (5, 1, 3, NULL, '2024-09-25 11:36:45');
SELECT setval(pg_get_serial_sequence('likes', 'id'), (SELECT COALESCE(MAX(id), 1) FROM likes));

-- -----------------------------------------------------------------------------
-- users_favorites_posts (join table)
-- -----------------------------------------------------------------------------
CREATE TABLE users_favorites_posts (
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    post    INT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, post)
);
CREATE INDEX users_favorites_posts_post_idx ON users_favorites_posts(post);

INSERT INTO users_favorites_posts (user_id, post) VALUES
    (1, 1),
    (2, 2),
    (3, 3);

COMMIT;
