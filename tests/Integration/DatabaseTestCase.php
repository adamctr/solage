<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Classe de base des tests d'intégration : chaque test tourne dans une
 * transaction annulée au teardown.
 *
 * Database est un singleton → le PDO de la transaction est le même que celui
 * qu'utilisent les modèles. Donc ce qu'un modèle insère pendant le test est
 * annulé au rollBack : base jetable, état identique avant/après, tests
 * rejouables à l'infini sans pollution.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected PDO $db;

    protected function setUp(): void
    {
        $this->db = Database::getConnection();
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
}
