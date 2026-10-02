<?php
namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Models\ThreadModel;
use App\Models\TopicModel;

class TopicController extends Controller
{
   // Regels voor de velden van een topic
   private const RULES = [
      'title'     => 'required|string|max:100',
      'content'   => 'required|string',
      'thread_id' => 'required|int|exists:' . ThreadModel::class,
      'user_id'   => 'required|int',
   ];

   // GET /topics
   public function index(): void
   {
      ApiResponse::sendResponse(TopicModel::all());
   }

   // GET /topics/{id}
   public function show(int $id): void
   {
      $topic = TopicModel::find($id);

      if ($topic === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Topic {$id} bestaat niet.");
      }

      ApiResponse::sendResponse($topic);
   }

   // POST /topics
   public function create(array $request): void
   {
      $errors = $this->validate($request, self::RULES);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      $topic = TopicModel::create($request);

      header('Location: ' . ApiResponse::url('topics/' . $topic['id']));
      ApiResponse::sendResponse($topic, ApiResponse::HTTP_STATUS_CREATED);
   }

   // PUT /topics/{id} : alle velden zijn verplicht
   public function update(int $id, array $request): void
   {
      if (TopicModel::find($id) === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Topic {$id} bestaat niet.");
      }

      $errors = $this->validate($request, self::RULES);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      ApiResponse::sendResponse(TopicModel::update($id, $request));
   }

   // PATCH /topics/{id} : alleen de meegestuurde velden worden aangepast
   public function patch(int $id, array $request): void
   {
      if (TopicModel::find($id) === null) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Topic {$id} bestaat niet.");
      }

      $errors = $this->validate($request, self::RULES, true);

      if (!empty($errors)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_UNPROCESSABLE, 'De invoer is niet geldig.', $errors);
      }

      ApiResponse::sendResponse(TopicModel::update($id, $request));
   }

   // DELETE /topics/{id}
   public function destroy(int $id): void
   {
      if (!TopicModel::delete($id)) {
         ApiResponse::sendError(ApiResponse::HTTP_STATUS_NOT_FOUND, "Topic {$id} bestaat niet.");
      }

      ApiResponse::sendResponse(null, ApiResponse::HTTP_STATUS_NO_CONTENT);
   }
}
