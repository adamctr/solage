<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests unitaires de SessionManager au mock.
 *
 * SessionManager reçoit son UserModel par injection de dépendance : on lui
 * passe un mock, donc on teste login() et isAdmin() SANS base de données.
 * createMock() n'appelle pas le constructeur réel de UserModel (qui ouvrirait
 * une connexion via Database::getConnection()) — c'est précisément ce que la DI
 * rend possible.
 */
final class SessionManagerTest extends TestCase
{
    protected function setUp(): void
    {
        // On active la session AVANT de poser des valeurs dans $_SESSION : ainsi
        // le constructeur de SessionManager ne relance pas session_start() (qui
        // réinitialiserait $_SESSION). Cookies désactivés : en test on ne valide
        // que l'état serveur ($_SESSION), pas l'envoi d'en-têtes.
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_cookies', '0');
            session_start();
        }
        $_SESSION = [];
    }

    #[Test]
    public function login_peuple_la_session_depuis_le_modele(): void
    {
        $user = $this->createMock(UserModel::class);
        $user->method('getName')->willReturn('Garnier');
        $user->method('getImage')->willReturn('🛡️');
        $user->method('getRole')->willReturn('3');

        $model = $this->createMock(UserModel::class);
        $model->method('getUserById')->with(2)->willReturn($user);

        $session = new SessionManager($model);
        $session->login(2);

        $this->assertSame(2, $_SESSION['user_id']);
        $this->assertSame('Garnier', $_SESSION['name']);
        $this->assertSame('🛡️', $_SESSION['image']);
        $this->assertSame('3', $_SESSION['role']);
        $this->assertTrue($session->isLoggedIn());
        $this->assertSame($user, $session->getUser());
    }

    #[Test]
    public function isAdmin_vrai_pour_le_role_admin(): void
    {
        $admin = $this->createMock(UserModel::class);
        $admin->method('getRoleName')->willReturn('Admin');

        $model = $this->createMock(UserModel::class);
        $model->method('getUserById')->with(1)->willReturn($admin);

        // Le constructeur charge l'utilisateur depuis la session.
        $_SESSION['user_id'] = 1;
        $session = new SessionManager($model);

        $this->assertTrue($session->isAdmin());
    }

    #[Test]
    public function isAdmin_faux_pour_un_role_non_admin(): void
    {
        $moderateur = $this->createMock(UserModel::class);
        $moderateur->method('getRoleName')->willReturn('Modérateur');

        $model = $this->createMock(UserModel::class);
        $model->method('getUserById')->with(2)->willReturn($moderateur);

        $_SESSION['user_id'] = 2;
        $session = new SessionManager($model);

        $this->assertFalse($session->isAdmin());
    }
}
