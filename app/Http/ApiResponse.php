<?php
namespace App\Http;

class ApiResponse
{
   // Algemene informatie over de API
   public const API_VERSION = '1.0';
   public const API_NAME = 'forum_api';

   // SUCCESS CODES (2xx)
   public const HTTP_STATUS_OK = 200;
   public const HTTP_STATUS_CREATED = 201;
   public const HTTP_STATUS_NO_CONTENT = 204;

   // CLIENT ERROR CODES (4xx)
   public const HTTP_STATUS_BAD_REQUEST = 400;
   public const HTTP_STATUS_UNAUTHORIZED = 401;
   public const HTTP_STATUS_FORBIDDEN = 403;
   public const HTTP_STATUS_NOT_FOUND = 404;
   public const HTTP_STATUS_METHOD_NOT_ALLOWED = 405;
   public const HTTP_STATUS_UNPROCESSABLE = 422;

   // SERVER ERROR CODES (5xx)
   public const HTTP_STATUS_SERVER_ERROR = 500;
   public const HTTP_STATUS_NOT_IMPLEMENTED = 501;
   public const HTTP_STATUS_SERVICE_NOT_AVAIL = 503;

   // De officiële tekst bij elke statuscode
   private const STATUS_MESSAGES = [
      200 => 'OK',
      201 => 'Created',
      204 => 'No Content',
      400 => 'Bad Request',
      401 => 'Unauthorized',
      403 => 'Forbidden',
      404 => 'Not Found',
      405 => 'Method Not Allowed',
      422 => 'Unprocessable Content',
      500 => 'Internal Server Error',
      501 => 'Not Implemented',
      503 => 'Service Unavailable',
   ];

   // Antwoord op een CORS-preflight (OPTIONS-request)
   public static function sendCORSHeaders(): void
   {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
      header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
      header('Access-Control-Max-Age: 86400');

      self::sendStatusCode(self::HTTP_STATUS_NO_CONTENT);
      exit;
   }

   // Headers die bij elke gewone response horen
   public static function sendDefaultHeaders(): void
   {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Expose-Headers: Location');
      header('Content-Type: application/json; charset=utf-8');
   }

   public static function sendStatusCode(int $code): void
   {
      http_response_code($code);
   }

   public static function getStatusMessage(int $code): string
   {
      return self::STATUS_MESSAGES[$code] ?? '';
   }

   // Maakt een volledige URL naar een resource, bijv. voor de Location-header
   public static function url(string $path): string
   {
      $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
      $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

      return "{$scheme}://{$_SERVER['HTTP_HOST']}{$basePath}/{$path}";
   }

   // Het meta-deel dat in elke response zit
   private static function buildMeta(int $code, mixed $data = null): array
   {
      $meta = [
         'api_version'    => self::API_VERSION,
         'api_name'       => self::API_NAME,
         'status'         => $code,
         'status_message' => self::getStatusMessage($code),
      ];

      // Alleen bij een lijst (een array met sleutels 0, 1, 2, ...) tellen we de items
      if (is_array($data) && array_is_list($data)) {
         $meta['count'] = count($data);
      }

      return $meta;
   }

   private static function toJson(array $response): string
   {
      return json_encode(
         $response,
         JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
      );
   }

   // Stuurt een succesvolle response en stopt het script
   public static function sendResponse(mixed $data, int $code = self::HTTP_STATUS_OK): void
   {
      self::sendDefaultHeaders();
      self::sendStatusCode($code);

      // Bij 204 No Content mag er geen body worden meegestuurd
      if ($code !== self::HTTP_STATUS_NO_CONTENT) {
         echo self::toJson([
            'meta' => self::buildMeta($code, $data),
            'data' => $data,
         ]);
      }

      exit;
   }

   // Stuurt een foutmelding en stopt het script
   public static function sendError(int $code, string $message, array $details = []): void
   {
      self::sendDefaultHeaders();
      self::sendStatusCode($code);

      $error = [ 'message' => $message ];

      if (!empty($details)) {
         $error['details'] = $details;
      }

      echo self::toJson([
         'meta'  => self::buildMeta($code),
         'error' => $error,
      ]);

      exit;
   }
}
