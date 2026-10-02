<?php
namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\ReplyModel;
use App\Models\TopicModel;

class ReplyController extends Controller
{
   // Regels voor de velden van een reply
   private const RULES = [
      'content'  => 'required|string',
      'topic_id' => 'required|int|exists:' . TopicModel::class,
      'user_id'  => 'required|int',
   ];

   // GET /replies
   public function index(): void
   {
      ApiResponse::sendResponse(ReplyModel::all());
   }

   // GET /replies/{id}
   public function show(int $id): void
   {
      $reply = ReplyModel::find($id);

      if ($reply === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Reply {$id} bestaat niet.");
      }

      ApiResponse::sendResponse($reply);
   }

   // POST /replies
   public function create(array $request): void
   {
      $errors = $this->validate($request, self::RULES);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      $reply = ReplyModel::create($request);

      header('Location: ' . ApiResponse::url('replies/' . $reply['id']));
      ApiResponse::sendResponse($reply, ApiResponse::HTTP_STATUS_CREATED);
   }

   // PUT /replies/{id} : alle velden zijn verplicht
   public function update(int $id, array $request): void
   {
      if (ReplyModel::find($id) === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Reply {$id} bestaat niet.");
      }

      $errors = $this->validate($request, self::RULES);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      ApiResponse::sendResponse(ReplyModel::update($id, $request));
   }

   // PATCH /replies/{id} : alleen de meegestuurde velden worden aangepast
   public function patch(int $id, array $request): void
   {
      if (ReplyModel::find($id) === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Reply {$id} bestaat niet.");
      }

      $errors = $this->validate($request, self::RULES, true);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      ApiResponse::sendResponse(ReplyModel::update($id, $request));
   }

   // DELETE /replies/{id}
   public function destroy(int $id): void
   {
      if (!ReplyModel::delete($id)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Reply {$id} bestaat niet.");
      }

      ApiResponse::sendResponse(null, ApiResponse::HTTP_STATUS_NO_CONTENT);
   }
}
