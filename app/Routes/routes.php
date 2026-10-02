<?php

use App\Http\Controllers\ThreadController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\ReplyController;

return [
   // Read
   'GET' => [
      // route => [Controller, 'methode']
      '/'            => [ ThreadController::class, 'index' ],
      'threads'      => [ ThreadController::class, 'index' ],
      'threads/{id}' => [ ThreadController::class, 'show' ],
      'topics'       => [ TopicController::class, 'index' ],
      'topics/{id}'  => [ TopicController::class, 'show' ],
      'replies'      => [ ReplyController::class, 'index' ],
      'replies/{id}' => [ ReplyController::class, 'show' ],
   ],
   // Create
   'POST' => [
      'threads'      => [ ThreadController::class, 'create' ],
      'topics'       => [ TopicController::class, 'create' ],
      'replies'      => [ ReplyController::class, 'create' ],
   ],
   // Update (volledig: alle velden verplicht)
   'PUT' => [
      'threads/{id}' => [ ThreadController::class, 'update' ],
      'topics/{id}'  => [ TopicController::class, 'update' ],
      'replies/{id}' => [ ReplyController::class, 'update' ],
   ],
   // Update (gedeeltelijk: alleen meegestuurde velden)
   'PATCH' => [
      'threads/{id}' => [ ThreadController::class, 'patch' ],
      'topics/{id}'  => [ TopicController::class, 'patch' ],
      'replies/{id}' => [ ReplyController::class, 'patch' ],
   ],
   // Delete
   'DELETE' => [
      'threads/{id}' => [ ThreadController::class, 'destroy' ],
      'topics/{id}'  => [ TopicController::class, 'destroy' ],
      'replies/{id}' => [ ReplyController::class, 'destroy' ],
   ],
];
