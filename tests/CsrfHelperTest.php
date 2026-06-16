<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests unitaires de CsrfHelper. La protection CSRF ne s'appuie que sur
 * $_SESSION (un simple tableau qu'on pilote depuis le test) : aucune base,
 * aucun session_start() nécessaire.
 */
final class CsrfHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    #[Test]
    public function getToken_genere_64_caracteres_hexadecimaux(): void
    {
        // 32 octets de random_bytes -> 64 caractères hexadécimaux.
        $token = CsrfHelper::getToken();

        $this->assertSame(64, strlen($token));
        $this->assertTrue(ctype_xdigit($token));
    }

    #[Test]
    public function getToken_est_stable_dans_une_meme_session(): void
    {
        $this->assertSame(CsrfHelper::getToken(), CsrfHelper::getToken());
    }

    #[Test]
    public function verifyToken_accepte_le_bon_token(): void
    {
        $_SESSION['csrf_token'] = 'jeton-de-reference';

        $this->assertTrue(CsrfHelper::verifyToken('jeton-de-reference'));
    }

    #[Test]
    public function verifyToken_rejette_un_mauvais_token(): void
    {
        $_SESSION['csrf_token'] = 'jeton-de-reference';

        $this->assertFalse(CsrfHelper::verifyToken('jeton-faux'));
    }

    #[Test]
    public function verifyToken_rejette_une_chaine_vide(): void
    {
        $_SESSION['csrf_token'] = 'jeton-de-reference';

        $this->assertFalse(CsrfHelper::verifyToken(''));
    }

    #[Test]
    public function verifyToken_rejette_null(): void
    {
        $_SESSION['csrf_token'] = 'jeton-de-reference';

        $this->assertFalse(CsrfHelper::verifyToken(null));
    }

    #[Test]
    public function verifyToken_rejette_quand_aucun_token_en_session(): void
    {
        // Sans token en session, le !empty() de verifyToken court-circuite.
        $this->assertFalse(CsrfHelper::verifyToken('jeton-de-reference'));
    }

    #[Test]
    public function field_rend_un_input_cache_avec_le_token(): void
    {
        $html = CsrfHelper::field();

        $this->assertStringContainsString('name="csrf_token"', $html);
        $this->assertStringContainsString(CsrfHelper::getToken(), $html);
    }
}
