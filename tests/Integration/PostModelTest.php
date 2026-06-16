<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Test;

require_once __DIR__ . '/DatabaseTestCase.php';

/**
 * Tests d'intégration de PostModel — la couche d'accès aux données du parcours
 * « Publier un message » : création, lecture, suppression contre une vraie
 * Postgres. Chaque test tourne dans une transaction annulée au teardown.
 */
final class PostModelTest extends DatabaseTestCase
{
    /** Insère un auteur dans la transaction (FK posts.user_id) et renvoie son id. */
    private function insertUser(): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password) VALUES (:n, :e, :p) RETURNING id'
        );
        $stmt->execute([
            ':n' => 'Auteur',
            ':e' => 'post-author@test.io',
            ':p' => password_hash('secret', PASSWORD_BCRYPT),
        ]);
        return (int) $stmt->fetchColumn();
    }

    private function newPost(int $userId, string $content): PostModel
    {
        return new PostModel(null, $userId, $content, '2026-01-01 12:00:00', null, null, null, null);
    }

    #[Test]
    public function createPost_insere_et_renvoie_un_id(): void
    {
        $userId = $this->insertUser();

        $id = $this->newPost($userId, 'Bonjour le monde')->createPost();

        $this->assertNotFalse($id);
        $this->assertGreaterThan(0, (int) $id);
    }

    #[Test]
    public function getPostById_lit_le_post_cree(): void
    {
        $userId = $this->insertUser();
        $id = (int) $this->newPost($userId, 'Contenu de test')->createPost();

        $post = PostModel::getPostById($id);

        $this->assertNotNull($post);
        $this->assertSame('Contenu de test', $post->getContent());
        $this->assertSame($userId, $post->getUserId());
    }

    #[Test]
    public function getPostById_retourne_null_si_inconnu(): void
    {
        $this->assertNull(PostModel::getPostById(999999999));
    }

    #[Test]
    public function delete_supprime_le_post(): void
    {
        $userId = $this->insertUser();
        $id = (int) $this->newPost($userId, 'À supprimer')->createPost();

        $this->assertTrue(PostModel::delete($id));
        $this->assertNull(PostModel::getPostById($id));
    }

    #[Test]
    public function delete_retourne_false_si_post_inexistant(): void
    {
        $this->assertFalse(PostModel::delete(999999999));
    }

    #[Test]
    public function getAllPostsByUserId_ne_retourne_que_les_posts_de_l_auteur(): void
    {
        $userId = $this->insertUser();
        $this->newPost($userId, 'Post 1')->createPost();
        $this->newPost($userId, 'Post 2')->createPost();

        $this->assertCount(2, PostModel::getAllPostsByUserId($userId));
    }
}
