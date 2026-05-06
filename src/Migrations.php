<?php

class Migrations {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    protected function columnExists(string $table, string $column): bool {
        $sql = "SELECT 1 FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = :table
                  AND column_name = :column
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':table' => $table, ':column' => $column]);
        return $stmt->fetchColumn() !== false;
    }

    protected function addColumnIfNotExists(string $table, string $column, string $type, string $options = ''): void {
        if (!$this->columnExists($table, $column)) {
            $sql = "ALTER TABLE \"$table\" ADD COLUMN \"$column\" $type $options";
            $this->db->exec($sql);
            Logger::get()->info('migration.column.added', ['table' => $table, 'column' => $column]);
        } else {
            Logger::get()->debug('migration.column.skipped', ['table' => $table, 'column' => $column, 'reason' => 'exists']);
        }
    }

    protected function removeColumnIfExists(string $table, string $column): void {
        if ($this->columnExists($table, $column)) {
            $sql = "ALTER TABLE \"$table\" DROP COLUMN \"$column\"";
            $this->db->exec($sql);
            Logger::get()->info('migration.column.removed', ['table' => $table, 'column' => $column]);
        } else {
            Logger::get()->debug('migration.column.skipped', ['table' => $table, 'column' => $column, 'reason' => 'absent']);
        }
    }

    public function migrate(): void {
        $this->addColumnIfNotExists('posts', 'reply_to', 'INT', 'NULL');
        $this->addColumnIfNotExists('posts', 'reply_to_parent', 'INT', 'NULL');
        $this->addColumnIfNotExists('posts', 'image', 'TEXT', 'NULL');
        $this->removeColumnIfExists('users', 'username');
    }
}
