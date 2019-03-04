<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/message', function (Request $request, Response $response) {

  // // Verify if the auth header is available
    //   if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
    //       // If the header is available, get the token
    //       $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
    //       $access_token = explode(" ", $access_token)[1];
    //       // Find the access token, if a user is returned, post the products
    //       if (!empty($access_token)) {
    //           $user_found = verifyToken($access_token);
    //           // Verify that there is a user logged in
    //           if (!empty($user_found)) {
    //               // Get the ingredient's details from the request body



    $channel = $request->getParam('channel');
    $event = $request->getParam('event');
    $message = $request->getParam('message');
  
    // Verify that there is a channel and an event for the message
    if ($channel && $event) {
        $pusher = connectPusher();
        $data['message'] = $message;

        $pusher->trigger($channel, $event, $data);

        $newResponse = $response->withStatus(200);
        $body = $response->getBody();
        $body->write('{"status": "success","message": "Mensaje enviado"}');
        $newResponse = $newResponse->withBody($body);
        return $newResponse;
    } else { // if ($channel && $event) {
        return respondWithError($response, 'Campos incorrectos', 401);
    }
    //         } else {  // if (!empty($user_found)) {
    //             return respondWithError($response, 'Error de login, usuario no encontrado', 401);
    //         }
    //     } else { // if (!empty($access_token)) {
    //         return respondWithError($response, 'Error de login, falta access token', 401);
    //     }
    // } else { // if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
    //     return respondWithError($response, 'Error de encabezado HTTP', 401);
    // }
});
