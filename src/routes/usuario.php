<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get all the users
$app->get('/api/usuario', function (Request $request, Response $response) {
    // Verify if the auth header is available
    if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
        // If the header is available, get the token
        $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
        $access_token = explode(" ", $access_token)[1];
        // Find the access token, if a user is returned, post the products
        if (!empty($access_token)) {
            $user_found = verifyToken($access_token);
            // Verify that there is a user logged in
            if (!empty($user_found)) {
                $sql = "SELECT * FROM usuario";
                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->query($sql);
                    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $db = null;

                    // Delete the password hash index
                    foreach ($users as $user) {
                        unset($user->password_hash);
                    }

                    // Add the products array inside an object
                    $usersResponse = array('users'=>$users);
                    $newResponse = $response->withJson($usersResponse);
                    return $newResponse;
                } catch (PDOException $e) {
                    echo '{"error":{"text": '.$e->getMessage().'}}';
                }
            } else {  // if (!empty($user_found)) {
                return respondWithError($response, 'Error de login, usuario no encontrado', 401);
            }
        } else { // if (!empty($access_token)) {
            return respondWithError($response, 'Error de login, falta access token', 401);
        }
    } else { // if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
        return respondWithError($response, 'Error de encabezado HTTP', 401);
    }
});




// Get single user
$app->get('/api/usuario/{id}', function (Request $request, Response $response) {
    // Verify if the auth header is available
    if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
        // If the header is available, get the token
        $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
        $access_token = explode(" ", $access_token)[1];
        // Find the access token, if a user is returned, post the products
        if (!empty($access_token)) {
            $user_found = verifyToken($access_token);
            // Verify that there is a user logged in
            if (!empty($user_found)) {
                $id = $request->getAttribute('id');
                $sql = "SELECT * FROM usuario WHERE id = $id";

                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->query($sql);
                    $user = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $db = null;

                    // Add the users array inside an object
                    if (!empty($user)) {
                        // Delete the password hash for the response
                        unset($user[0]->password_hash);

                        $usersResponse = $user[0];
                        $newResponse = $response->withJson($usersResponse);
                        return $newResponse;
                    } else {
                        return respondWithError($response, 'Id incorrecto', 401);
                    }
                } catch (PDOException $e) {
                    echo '{"error":{"text": '.$e->getMessage().'}}';
                }
            } else {  // if (!empty($user_found)) {
                return respondWithError($response, 'Error de login, usuario no encontrado', 401);
            }
        } else { // if (!empty($access_token)) {
            return respondWithError($response, 'Error de login, falta access token', 401);
        }
    } else { // if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
        return respondWithError($response, 'Error de encabezado HTTP', 401);
    }
});



// Add user
$app->post('/api/usuario', function (Request $request, Response $response) {

    // Get the user's details from the request body
    $name = $request->getParam('name');
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    $email = $request->getParam('email');

    // Verify that the information is present
    if ($name && $username && $password && $email) {
        // Verify that the email has an email format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Check that there is no other users's with the same username
            $sql = "SELECT username FROM usuario where username = '$username'";

            try {
                // Get db object
                $db = new db();
                // Connect
                $db = $db->connect();

                $stmt = $db->query($sql);
                $user = $stmt->fetchAll(PDO::FETCH_OBJ);

                if (empty($user)) {

                // If it is, create the hash for storage
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);

                    // Store the information in the database
                    $sql = "INSERT INTO usuario (nombre, username, email, password_hash) VALUES (:name,:username,:email,:password_hash)";

                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->prepare($sql);


                    $stmt->bindparam(':name', $name);
                    $stmt->bindparam(':username', $username);
                    $stmt->bindparam(':email', $email);
                    $stmt->bindparam(':password_hash', $password_hash);

                    $stmt->execute();

                    $newResponse = $response->withStatus(200);
                    $body = $response->getBody();
                    $body->write('{"status": "success","message": "Usuario agreagdo", "usuario": "'.$username.'"}');
                    $newResponse = $newResponse->withBody($body);
                    return $newResponse;
                } else { // if (empty($user)) {
                    return respondWithError($response, 'El usuario ya existe', 401);
                }
            } catch (PDOException $e) {
                echo '{"error":{"text": '.$e->getMessage().'}}';
            }
        } else { // if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return respondWithError($response, 'Formato de email incorrecto', 401);
        }
    } else { // if ($name && $username && $password && $email) {
        return respondWithError($response, 'Campos incorrectos', 401);
    }
});


// // Update product
// $app->put('/api/usuario/{id}', function (Request $request, Response $response) {
//     $params = $request->getBody();
//     if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
//         $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
//         $access_token = explode(" ", $access_token)[1];
//         // Find the access token, if a user is returned, post the products
//         if (!empty($access_token)) {
//             $user_found = verifyToken($access_token);
//             if (!empty($user_found)) {
//                 $id = $request->getAttribute('id');
//
//                 $price_s = $request->getParam('price_s');
//                 $price_l = $request->getParam('price_l');
//                 $menuMonday = $request->getParam('menuMonday');
//                 $menuTuesday = $request->getParam('menuTuesday');
//                 $menuWednesday = $request->getParam('menuWednesday');
//                 $menuThursday = $request->getParam('menuThursday');
//                 $menuFriday = $request->getParam('menuFriday');
//                 $menuSaturday = $request->getParam('menuSaturday');
//                 $menuSunday =  $request->getParam('menuSunday');
//
//                 $sql = "UPDATE almuerzos SET
//         price_s = :price_s,
//         price_l = :price_l,
//         menuMonday = :menuMonday,
//         menuTuesday = :menuTuesday,
//         menuWednesday = :menuWednesday,
//         menuThursday = :menuThursday,
//         menuFriday = :menuFriday,
//         menuSaturday = :menuSaturday,
//         menuSunday = :menuSunday
//         WHERE id = $id";
//
//                 try {
//                     // Get db object
//                     $db = new db();
//                     // Connect
//                     $db = $db->connect();
//
//                     $stmt = $db->prepare($sql);
//
//                     $stmt->bindParam(':price_s', $price_s);
//                     $stmt->bindParam(':price_l', $price_l);
//                     $stmt->bindParam(':menuMonday', $menuMonday);
//                     $stmt->bindParam(':menuTuesday', $menuTuesday);
//                     $stmt->bindParam(':menuWednesday', $menuWednesday);
//                     $stmt->bindParam(':menuThursday', $menuThursday);
//                     $stmt->bindParam(':menuFriday', $menuFriday);
//                     $stmt->bindParam(':menuSaturday', $menuSaturday);
//                     $stmt->bindParam(':menuSunday', $menuSunday);
//
//                     $stmt->execute();
//
//                     echo('{"notice":{"text":"product updated"}}');
//                 } catch (PDOException $e) {
//                     echo '{"error":{"text": '.$e->getMessage().'}}';
//                 }
//             } else {
//                 return respondWithError($response, 'Error de login, usuario no encontrado');
//             }
//         } else {
//             return respondWithError($response, 'Error de login, falta access token');
//         }
//     } else {
//         return respondWithError($response, 'Error de encabezado HTTP');
//     }
// });
