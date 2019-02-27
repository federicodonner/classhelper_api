<?php

use \Psr\Http\Message\ResponseInterface as Response;

// Return the login record from the token, or an empty array if none exists
  function verifyToken(String $access_token)
  {
      if (!empty($access_token)) {
          $sql = "SELECT * FROM login WHERE token = '$access_token'";
          try {
              // Get db object
              $db = new db();
              // Connect
              $db = $db->connect();
              $stmt = $db->query($sql);
              $users = $stmt->fetchAll(PDO::FETCH_OBJ);
              return $users;
          } catch (PDOException $e) {
              echo '{"error":{"text": '.$e->getMessage().'}}';
          }
      } else {
          return [];
      }
  };

// Return a response with a 401 not allowed error.
 function respondWithError(Response $response, String $errorText, Int $status)
 {
     $newResponse = $response->withStatus($status);
     $body = $response->getBody();
     $body->write('{"status": "error","message": "'.$errorText.'"}');
     $newResponse = $newResponse->withBody($body);
     return $newResponse;
 };




 function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
 {
     $pieces = [];
     $max = mb_strlen($keyspace, '8bit') - 1;
     for ($i = 0; $i < $length; ++$i) {
         $pieces []= $keyspace[random_int(0, $max)];
     }
     return implode('', $pieces);
 };
