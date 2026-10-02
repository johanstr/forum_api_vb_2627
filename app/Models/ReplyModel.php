<?php
namespace App\Models;

class ReplyModel extends Model
{
   protected static string $table = 'replies';
   protected static array $fillable = [ 'content', 'topic_id', 'user_id' ];
}
