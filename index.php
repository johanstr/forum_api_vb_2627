<?php
require __DIR__ . '/vendor/autoload.php';

use App\Http\ApiResponse;
use App\Http\RequestHandler;

try {
   $routes = require __DIR__ . '/app/Routes/routes.php';

   $requestHandler = new RequestHandler($routes);
   $requestHandler->handleRequest();
} catch (Throwable $e) {
   // De echte fout schrijven we in het logbestand van de server,
   // de client krijgt alleen een algemene melding.
   error_log($e->getMessage());

   ApiResponse::sendError(
      ApiResponse::HTTP_STATUS_SERVER_ERROR,
      'Er ging iets mis op de server. Probeer het later opnieuw.'
   );
}
