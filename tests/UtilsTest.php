<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests unitaires de Utils : échappement anti-XSS (e), détection AJAX (isAjax)
 * et transport JSON (sendResponse). Logique pure, aucune base de données.
 */
final class UtilsTest extends TestCase
{
    protected function tearDown(): void
    {
        // isAjax() lit $_SERVER : on remet l'état propre entre les tests.
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    #[Test]
    public function e_neutralise_une_balise_script(): void
    {
        $out = Utils::e('<script>alert(1)</script>');

        $this->assertStringNotContainsString('<script>', $out);
        $this->assertStringContainsString('&lt;script&gt;', $out);
    }

    #[Test]
    public function e_encode_les_chevrons(): void
    {
        $out = Utils::e('<b>x</b>');

        $this->assertStringNotContainsString('<b>', $out);
        $this->assertStringContainsString('&lt;b&gt;', $out);
    }

    #[Test]
    public function e_encode_les_guillemets_doubles(): void
    {
        $this->assertStringContainsString('&quot;', Utils::e('il a dit "oui"'));
    }

    #[Test]
    public function e_encode_l_apostrophe_en_apos(): void
    {
        // ENT_HTML5 : l'apostrophe devient &apos; (et non &#039;).
        $out = Utils::e("O'Brien");

        $this->assertStringContainsString('&apos;', $out);
        $this->assertStringNotContainsString("'", $out);
    }

    #[Test]
    public function e_encode_l_esperluette(): void
    {
        $this->assertStringContainsString('&amp;', Utils::e('a & b'));
    }

    #[Test]
    public function e_transforme_null_en_chaine_vide(): void
    {
        $this->assertSame('', Utils::e(null));
    }

    #[Test]
    public function isAjax_vrai_avec_l_entete_xmlhttprequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->assertTrue(Utils::isAjax());
    }

    #[Test]
    public function isAjax_faux_sans_l_entete(): void
    {
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);

        $this->assertFalse(Utils::isAjax());
    }

    #[Test]
    public function sendResponse_serialise_success_et_message(): void
    {
        ob_start();
        Utils::sendResponse(true, 'ok');
        $json = ob_get_clean();

        $this->assertSame(
            ['success' => true, 'message' => 'ok'],
            json_decode($json, true)
        );
    }

    #[Test]
    public function sendResponse_inclut_data_quand_non_vide(): void
    {
        ob_start();
        Utils::sendResponse(true, 'ok', ['id' => 7]);
        $json = ob_get_clean();

        $this->assertSame(['id' => 7], json_decode($json, true)['data']);
    }

    #[Test]
    public function sendResponse_omet_data_quand_falsy(): void
    {
        // if ($data) est faux pour [] : la clé data est absente du JSON.
        ob_start();
        Utils::sendResponse(true, 'ok', []);
        $json = ob_get_clean();

        $this->assertArrayNotHasKey('data', json_decode($json, true));
    }
}
