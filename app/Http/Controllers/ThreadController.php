<?php
namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\ThreadModel;

class ThreadController extends Controller
{
   // Regels voor de velden van een thread
   private const RULES = [
      'title'   => 'required|string|max:100',
      'content' => 'required|string',
      'user_id' => 'required|int',
   ];

   // GET /threads
   public function index(): void
   {
      ApiResponse::sendResponse(ThreadModel::all());
   }

   // GET /threads/{id}
   public function show(int $id): void
   {
      $thread = ThreadModel::find($id);

      if ($thread === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Thread {$id} bestaat niet.");
      }

      ApiResponse::sendResponse($thread);
   }

   // POST /threads
   public function create(array $request): void
   {
      $errors = $this->validate($request, self::RULES);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      $thread = ThreadModel::create($request);

      header('Location: ' . ApiResponse::url('threads/' . $thread['id']));
      ApiResponse::sendResponse($thread, ApiResponse::HTTP_STATUS_CREATED);
   }

   // PUT /threads/{id} : alle velden zijn verplicht
   public function update(int $id, array $request): void
   {
      if (ThreadModel::find($id) === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Thread {$id} bestaat niet.");
      }

      $errors = $this->validate($request, self::RULES);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      ApiResponse::sendResponse(ThreadModel::update($id, $request));
   }

   // PATCH /threads/{id} : alleen de meegestuurde velden worden aangepast
   public function patch(int $id, array $request): void
   {
      if (ThreadModel::find($id) === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Thread {$id} bestaat niet.");
      }

      $errors = $this->validate($request, self::RULES, true);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      ApiResponse::sendResponse(ThreadModel::update($id, $request));
   }

   // DELETE /threads/{id}
   public function destroy(int $id): void
   {
      if (!ThreadModel::delete($id)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Thread {$id} bestaat niet.");
      }

      ApiResponse::sendResponse(null, ApiResponse::HTTP_STATUS_NO_CONTENT);
   }
}
