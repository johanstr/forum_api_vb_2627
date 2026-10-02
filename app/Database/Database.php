<?php
namespace App\Database;

use PDO;
use PDOStatement;

class Database
{
   // Instellingen voor MAMP: gebruiker root met wachtwoord root
   // (bij XAMPP is het wachtwoord leeg: $dbPass = '')
   private string $dbHost = '127.0.0.1';
   private string $dbName = 'forum_api_vb';
   private string $dbUser = 'root';
   private string $dbPass = 'root';

   private PDO $dbConnection;
   private ?PDOStatement $dbStatement = null;

   public function __construct()
   {
      $this->dbConnection = new PDO(
         "mysql:host={$this->dbHost};dbname={$this->dbName};charset=utf8mb4",
         $this->dbUser,
         $this->dbPass,
         [
            // Bij een fout gooit PDO een exception, in plaats van stil te blijven
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Rijen komen terug als array met kolomnamen: ['id' => 1, 'title' => '...']
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Echte prepared statements; getallen komen terug als getal
            PDO::ATTR_EMULATE_PREPARES => false,
         ]
      );
   }

   public function query(string $sql, array $args = []): void
   {
      $this->dbStatement = $this->dbConnection->prepare($sql);
      $this->dbStatement->execute($args);
   }

   public function insert(string $sql, array $args = []): int
   {
      $this->query($sql, $args);

      return (int) $this->dbConnection->lastInsertId();
   }

   // Eén rij ophalen, of null als er niets gevonden is
   public function get(): ?array
   {
      $row = $this->dbStatement?->fetch();

      if ($row === false || $row === null) {
         return null;
      }

      return $row;
   }

   // Alle rijen ophalen
   public function getAll(): array
   {
      return $this->dbStatement?->fetchAll() ?? [];
   }

   // Hoeveel rijen zijn er veranderd door de laatste query?
   public function rowCount(): int
   {
      return $this->dbStatement?->rowCount() ?? 0;
   }
}
