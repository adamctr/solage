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

-- Seed data lives in seed.sql (mounted as 02-seed.sql in dev).

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

-- -----------------------------------------------------------------------------
-- users_favorites_posts (join table)
-- -----------------------------------------------------------------------------
CREATE TABLE users_favorites_posts (
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    post    INT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, post)
);
CREATE INDEX users_favorites_posts_post_idx ON users_favorites_posts(post);

COMMIT;
