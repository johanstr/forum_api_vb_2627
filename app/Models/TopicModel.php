<?php
namespace App\Models;

class TopicModel extends Model
{
   protected static string $table = 'topics';
   protected static array $fillable = [ 'title', 'content', 'thread_id', 'user_id' ];
}
