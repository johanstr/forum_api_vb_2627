<?php
namespace App\Http;

class RequestHandler
{
   // Constanten voor de request-methodes
   public const GET = 'GET';
   public const POST = 'POST';
   public const PUT = 'PUT';
   public const PATCH = 'PATCH';
   public const DELETE = 'DELETE';
   public const OPTIONS = 'OPTIONS';

   // Properties
   private array $routes;
   private string $requestMethod = "GET";
   private string $resource = "/";
   private ?int $id = null;

   public function __construct(array $routes)
   {
      $this->routes = $routes;
      $this->requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);

      $this->parseURI();
   }

   // Haalt de resource en het id uit de URL (die .htaccess in $_GET heeft gezet)
   private function parseURI(): void
   {
      if (isset($_GET['resource'])) {
         $this->resource = strtolower($_GET['resource']);
      } else {
         $this->resource = '/';
      }

      if (isset($_GET['id'])) {
         if (!ctype_digit($_GET['id']) || (int) $_GET['id'] < 1) {
            ApiResponse::sendError(
               ApiResponse::HTTP_STATUS_BAD_REQUEST,
               'Het id moet een positief geheel getal zijn.'
            );
         }

         $this->id = (int) $_GET['id'];
      }
   }

   // Maakt de sleutel om in routes.php te zoeken: 'threads' of 'threads/{id}'
   private function getRouteKey(): string
   {
      if ($this->id === null) {
         return $this->resource;
      }

      return $this->resource . '/{id}';
   }

   // Zoekt de route bij de methode en de URL. Geeft null als die niet bestaat.
   private function findRoute(): ?array
   {
      return $this->routes[$this->requestMethod][$this->getRouteKey()] ?? null;
   }

   // Geeft de methodes die WEL bestaan voor deze URL (nodig voor een 405)
   private function getAllowedMethods(): array
   {
      $allowed = [];

      foreach ($this->routes as $method => $routesForMethod) {
         if (isset($routesForMethod[$this->getRouteKey()])) {
            $allowed[] = $method;
         }
      }

      return $allowed;
   }

   // Leest de data die de client in de body van het request heeft gestuurd
   private function getRequestData(): array
   {
      $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
      $body = file_get_contents('php://input');

      // 1. JSON (de standaard voor API's)
      if (str_contains($contentType, 'application/json')) {
         if (trim($body) === '') {
            return [];
         }

         $data = json_decode($body, true);

         if (!is_array($data)) {
            ApiResponse::sendError(
               ApiResponse::HTTP_STATUS_BAD_REQUEST,
               'De body bevat geen geldige JSON.'
            );
         }

         return $data;
      }

      // 2. Formulierdata bij POST: die zet PHP zelf in $_POST
      if ($this->requestMethod === self::POST) {
         return $_POST;
      }

      // 3. Formulierdata bij PUT en PATCH: die lezen we zelf uit
      parse_str($body, $data);

      return $data;
   }

   public function handleRequest(): void
   {
      // 1. Een OPTIONS-request is een CORS-preflight: meteen beantwoorden
      if ($this->requestMethod === self::OPTIONS) {
         ApiResponse::sendCORSHeaders();
      }

      // 2. Bestaat de route?
      $route = $this->findRoute();

      if ($route === null) {
         $allowedMethods = $this->getAllowedMethods();

         // De URL bestaat wel, maar niet met deze methode
         if (count($allowedMethods) > 0) {
            header('Allow: ' . implode(', ', $allowedMethods));
            ApiResponse::sendError(
               ApiResponse::HTTP_STATUS_METHOD_NOT_ALLOWED,
               "De methode {$this->requestMethod} is niet toegestaan voor deze URL."
            );
         }

         // De URL bestaat helemaal niet
         ApiResponse::sendError(
            ApiResponse::HTTP_STATUS_NOT_FOUND,
            'Deze route bestaat niet.'
         );
      }

      // 3. Controller en methode uit de route halen
      [$classname, $methodName] = $route;
      $controller = new $classname();

      // 4. De argumenten voor de controller-methode verzamelen
      $arguments = [];

      if ($this->id !== null) {
         $arguments[] = $this->id;
      }

      if (in_array($this->requestMethod, [self::POST, self::PUT, self::PATCH])) {
         $arguments[] = $this->getRequestData();
      }

      // 5. De controller-methode aanroepen, bijvoorbeeld $controller->show(5)
      $controller->$methodName(...$arguments);
   }
}
