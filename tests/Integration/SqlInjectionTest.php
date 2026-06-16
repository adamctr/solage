<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Test;

require_once __DIR__ . '/DatabaseTestCase.php';

/**
 * Test d'intégration sécurité : prouve que les requêtes préparées rendent une
 * charge d'injection SQL inerte.
 *
 * SearchModel::searchPosts entoure le terme de %...% et l'envoie en paramètre
 * lié (WHERE p.content LIKE ?). L'entrée est donc traitée comme une donnée,
 * jamais comme du SQL : une charge d'injection ne renvoie rien et ne lève rien,
 * alors qu'un terme normal fonctionne.
 */
final class SqlInjectionTest extends DatabaseTestCase
{
    private const MARQUEUR = 'SqliProbe_4242_contenu_unique';

    protected function setUp(): void
    {
        parent::setUp();

        // Un utilisateur + un post au contenu unique, dans la transaction.
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password) VALUES (:n, :e, :p) RETURNING id'
        );
        $stmt->execute([
            ':n' => 'Test',
            ':e' => 'sqli@test.io',
            ':p' => password_hash('secret', PASSWORD_BCRYPT),
        ]);
        $userId = (int) $stmt->fetchColumn();

        $this->db->prepare(
            'INSERT INTO posts (user_id, date, content) VALUES (:u, :d, :c)'
        )->execute([
            ':u' => $userId,
            ':d' => '2026-01-01 12:00:00',
            ':c' => self::MARQUEUR,
        ]);
    }

    #[Test]
    public function une_charge_d_injection_reste_inerte(): void
    {
        // %' OR '1'='1% est cherché littéralement : aucun contenu ne correspond,
        // et aucune exception n'est levée. Si l'injection marchait, on aurait
        // toutes les lignes.
        $resultats = (new SearchModel())->searchPosts("' OR '1'='1");

        $this->assertCount(0, $resultats);
    }

    #[Test]
    public function une_recherche_normale_fonctionne(): void
    {
        $resultats = (new SearchModel())->searchPosts(self::MARQUEUR);

        $this->assertCount(1, $resultats);
    }
}
