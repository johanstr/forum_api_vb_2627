<?php
namespace App\Models;

use App\Database\Database;

abstract class Model
{
   // Elke model-klasse vult deze twee properties zelf in
   protected static string $table;
   protected static array $fillable;

   private static ?Database $db = null;

   // Geeft de databaseverbinding; maakt hem aan als dat nog niet gebeurd is
   protected static function db(): Database
   {
      if (self::$db === null) {
         self::$db = new Database();
      }

      return self::$db;
   }

   // Houdt alleen de velden over die in $fillable staan
   protected static function onlyFillable(array $data): array
   {
      return array_intersect_key($data, array_flip(static::$fillable));
   }

   public static function all(): array
   {
      $table = static::$table;

      self::db()->query("SELECT * FROM `{$table}`");

      return self::db()->getAll();
   }

   public static function find(int $id): ?array
   {
      $table = static::$table;

      self::db()->query("SELECT * FROM `{$table}` WHERE `id` = :id", [ 'id' => $id ]);

      return self::db()->get();
   }

   public static function create(array $data): array
   {
      $table = static::$table;

      $data = static::onlyFillable($data);
      $data['created_at'] = date('Y-m-d H:i:s');
      $data['updated_at'] = date('Y-m-d H:i:s');

      // Bijv. ['title', 'content'] wordt "`title`, `content`" en ":title, :content"
      $columns = array_keys($data);
      $columnList = '`' . implode('`, `', $columns) . '`';
      $placeholders = ':' . implode(', :', $columns);

      $id = self::db()->insert(
         "INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})",
         $data
      );

      return static::find($id);
   }

   public static function update(int $id, array $data): ?array
   {
      $table = static::$table;

      $data = static::onlyFillable($data);
      $data['updated_at'] = date('Y-m-d H:i:s');

      // Bijv. "`title` = :title, `updated_at` = :updated_at"
      $sets = [];
      foreach (array_keys($data) as $column) {
         $sets[] = "`{$column}` = :{$column}";
      }

      $data['id'] = $id;

      self::db()->query(
         "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE `id` = :id",
         $data
      );

      return static::find($id);
   }

   // Geeft true als er echt een rij is verwijderd
   public static function delete(int $id): bool
   {
      $table = static::$table;

      self::db()->query("DELETE FROM `{$table}` WHERE `id` = :id", [ 'id' => $id ]);

      return self::db()->rowCount() > 0;
   }
}
