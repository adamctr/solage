<?php

class Migrations {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Vérifie si une colonne existe dans une table
    protected function columnExists($table, $column) {
        $result = $this->db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result->fetch() !== false;
    }

    // Ajoute une colonne à une table si elle n'existe pas
    protected function addColumnIfNotExists($table, $column, $type, $options = '') {
        if (!$this->columnExists($table, $column)) {
            $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $type $options";
            $this->db->exec($sql);
            error_log("Migration appliquée : Ajout de la colonne '$column' à la table '$table'.\n");
        } else {
            error_log( "La colonne '$column' existe déjà dans la table '$table'.\n");
        }
    }

    // Méthode pour appliquer les migrations
    public function migrate() {
        $this->addColumnIfNotExists('posts', 'reply_to', 'INT', 'NULL');
        //$this->addColumnIfNotExists('users', 'username', 'STRING', 'NULL');
    }
}
