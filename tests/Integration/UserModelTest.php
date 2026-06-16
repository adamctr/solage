<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Test;

require_once __DIR__ . '/DatabaseTestCase.php';

/**
 * Tests d'intégration de UserModel + UserValidator contre une vraie Postgres.
 *
 * Les données sont arrangées par INSERT direct dans la transaction (pas via
 * createUser, couplé au docroot — cf. createUser_stocke_un_mot_de_passe_hashe),
 * puis on vérifie la chaîne validateur → modèle → SQL → password_verify.
 */
final class UserModelTest extends DatabaseTestCase
{
    /**
     * Insère un utilisateur dans la transaction et renvoie son id.
     * name/email/password sont NOT NULL ; firstname/role/image sont nullables.
     */
    private function insertUser(string $email, string $plainPassword): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, role)
             VALUES (:name, :email, :password, :role) RETURNING id'
        );
        $stmt->execute([
            ':name'     => 'Test',
            ':email'    => $email,
            ':password' => password_hash($plainPassword, PASSWORD_BCRYPT),
            ':role'     => 2,
        ]);

        return (int) $stmt->fetchColumn();
    }

    #[Test]
    public function getUserByEmail_retourne_l_utilisateur_existant(): void
    {
        $this->insertUser('t@test.io', 'secret');

        $user = (new UserModel())->getUserByEmail('t@test.io');

        $this->assertNotNull($user);
        $this->assertSame('t@test.io', $user->getEmail());
    }

    #[Test]
    public function getUserByEmail_retourne_null_si_inconnu(): void
    {
        $this->assertNull((new UserModel())->getUserByEmail('nope@test.io'));
    }

    #[Test]
    public function le_mot_de_passe_est_stocke_hashe(): void
    {
        $this->insertUser('t@test.io', 'secret');

        $hash = (new UserModel())->getUserByEmail('t@test.io')->getPassword();

        $this->assertNotSame('secret', $hash);
        $this->assertTrue(password_verify('secret', $hash));
    }

    #[Test]
    public function createUser_stocke_un_mot_de_passe_hashe(): void
    {
        // createUser fait include 'assets/emojiList.php' : chemin relatif au CWD
        // (couplage au docroot). En prod le CWD est public/ ; on s'y place le
        // temps de l'appel puis on restaure. Finding documenté dans le guide.
        $cwd = getcwd();
        chdir(__DIR__ . '/../../public');
        try {
            $ok = (new UserModel())->createUser('Nouveau', 'create@test.io', 'secret');
        } finally {
            chdir($cwd);
        }
        $this->assertTrue($ok);

        $user = (new UserModel())->getUserByEmail('create@test.io');
        $this->assertNotNull($user);
        $this->assertNotSame('secret', $user->getPassword());
        $this->assertTrue(password_verify('secret', $user->getPassword()));
    }

    #[Test]
    public function login_valide_avec_les_bons_identifiants(): void
    {
        $this->insertUser('t@test.io', 'secret');

        $result = UserValidator::login('t@test.io', 'secret');

        $this->assertTrue($result['ok']);
    }

    #[Test]
    public function login_refuse_un_mauvais_mot_de_passe(): void
    {
        $this->insertUser('t@test.io', 'secret');

        $result = UserValidator::login('t@test.io', 'faux');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('mot de passe', $result['message']);
    }

    #[Test]
    public function login_refuse_un_email_inexistant(): void
    {
        $result = UserValidator::login('nope@test.io', 'secret');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString("n'existe pas", $result['message']);
    }

    #[Test]
    public function login_refuse_des_champs_vides(): void
    {
        $result = UserValidator::login('', '');

        $this->assertFalse($result['ok']);
    }

    #[Test]
    public function register_refuse_un_email_deja_utilise(): void
    {
        $this->insertUser('t@test.io', 'secret');

        $result = UserValidator::register('t@test.io', 'secret');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('déjà utilisé', $result['message']);
    }

    #[Test]
    public function register_accepte_un_email_libre(): void
    {
        $result = UserValidator::register('libre@test.io', 'secret');

        $this->assertTrue($result['ok']);
    }
}
