<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get all the classes
$app->get('/api/class', function (Request $request, Response $response) {
    $sql = "SELECT * FROM class";
    try {
        // Get db object
        $db = new db();
        // Connect
        $db = $db->connect();

        // Get a list of all the classes
        $stmt = $db->query($sql);
        $classes = $stmt->fetchAll(PDO::FETCH_OBJ);

        // For each class, find the subject information and store it in the object
        foreach ($classes as $class) {
            $sql = "SELECT * FROM subject WHERE id = $class->subject";

            $stmt = $db->query($sql);
            $subjects = $stmt->fetchAll(PDO::FETCH_OBJ);

            $class->subject = $subjects[0];
        }

        $db = null;

        // Make the response objecti
        $classesResponse = array('classes'=>$classes);
        $newResponse = $response->withJson($classesResponse);
        $newResponse = $newResponse->withStatus(201);
        return $newResponse;
    } catch (PDOException $e) {
        echo '{"error":{"text": '.$e->getMessage().'}}';
    }
});




// Get single class
$app->get('/api/class/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "SELECT * FROM class WHERE id = $id";

    try {
        // Get db object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->query($sql);
        $class = $stmt->fetchAll(PDO::FETCH_OBJ);


        // Verify that the class exists
        if (!empty($class)) {
            // If it exists, find all the slides from the class
            $sql = "SELECT * FROM slide WHERE class = $id";

            $stmt = $db->query($sql);
            $slides = $stmt->fetchAll(PDO::FETCH_OBJ);



            foreach ($slides as $slide) {
                $activity = $slide->activity;

                if ($activity != null) {
                    $activityId = $activity;
                    $sql = "SELECT * FROM activity WHERE id = $activityId";

                    $slide->c=$sql;

                    $stmt = $db->query($sql);
                    $activities = $stmt->fetchAll(PDO::FETCH_OBJ);

                    $slide->activity = $activities[0];
                }
            }


            $db = null;
            // Store them in the class object
            $class[0]->slides = $slides;

            $classResponse = $class[0];
            $newResponse = $response->withJson($classResponse);
            $newResponse = $newResponse->withStatus(201);
            return $newResponse;
        } else {
            return respondWithError($response, 'Id incorrecto', 401);
        }
    } catch (PDOException $e) {
        echo '{"error":{"text": '.$e->getMessage().'}}';
    }
});


//
// // Add ingredient
// $app->post('/api/ingrediente', function (Request $request, Response $response) {
//
//   // Verify if the auth header is available
//     if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
//         // If the header is available, get the token
//         $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
//         $access_token = explode(" ", $access_token)[1];
//         // Find the access token, if a user is returned, post the products
//         if (!empty($access_token)) {
//             $user_found = verifyToken($access_token);
//             // Verify that there is a user logged in
//             if (!empty($user_found)) {
//                 // Get the ingredient's details from the request body
//                 $name = $request->getParam('name');
//                 $units = $request->getParam('units');
//
//                 // Verify that the information is present
//                 if ($name && $units) {
//
//         // Check that there is no other ingredient with the same name
//                     $sql = "SELECT nombre FROM ingrediente where nombre = '$name'";
//
//                     try {
//                         // Get db object
//                         $db = new db();
//                         // Connect
//                         $db = $db->connect();
//
//                         $stmt = $db->query($sql);
//                         $ingredient = $stmt->fetchAll(PDO::FETCH_OBJ);
//
//                         if (empty($ingredient)) {
//                             $now = time();
//                             // Store the information in the database
//                             $sql = "INSERT INTO ingrediente (nombre, unidades, cantidad, fecha_arqueo) VALUES (:name,:units,:count,:count_date)";
//
//                             $stmt = $db->prepare($sql);
//
//                             $count = 0;
//                             $stmt->bindparam(':name', $name);
//                             $stmt->bindparam(':units', $units);
//                             $stmt->bindparam(':count', $count);
//                             $stmt->bindparam(':count_date', $now);
//                             //
//                             $stmt->execute();
//
//                             $newResponse = $response->withStatus(200);
//                             $body = $response->getBody();
//                             $body->write('{"status": "success","message": "Ingrediente agreagdo", "ingrediente": "'.$name.'"}');
//                             $newResponse = $newResponse->withBody($body);
//                             return $newResponse;
//                         } else { // if (empty($user)) {
//                             return respondWithError($response, 'El ingrediente ya está ingresado', 401);
//                         }
//                     } catch (PDOException $e) {
//                         echo '{"error":{"text": '.$e->getMessage().'}}';
//                     }
//                 } else { // if ($name && $username && $password && $email) {
//                     return respondWithError($response, 'Campos incorrectos', 401);
//                 }
//             } else {  // if (!empty($user_found)) {
//                 return respondWithError($response, 'Error de login, usuario no encontrado', 401);
//             }
//         } else { // if (!empty($access_token)) {
//             return respondWithError($response, 'Error de login, falta access token', 401);
//         }
//     } else { // if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
//         return respondWithError($response, 'Error de encabezado HTTP', 401);
//     }
// });
//
//
//
// // Update ingredient
// $app->put('/api/ingrediente/{id}', function (Request $request, Response $response) {
//
//     // Verify if the auth header is available
//     if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
//         // If the header is available, get the token
//         $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
//         $access_token = explode(" ", $access_token)[1];
//         // Find the access token, if a user is returned, post the products
//         if (!empty($access_token)) {
//             $user_found = verifyToken($access_token);
//             // Verify that there is a user logged in
//             if (!empty($user_found)) {
//
//                 // Get the ingredient id from the URL
//                 $id = $request->getAttribute('id');
//
//                 // Get the variables from the request body
//                 $name = $request->getParam('name');
//                 $units = $request->getParam('units');
//                 $count = $request->getParam('count');
//
//                 if ($name && $units && $count) {
//                     $now = time();
//
//                     $sql = "UPDATE ingrediente SET
//                 nombre = :name,
//                 unidades = :units,
//                 cantidad = :count,
//                 fecha_arqueo = :now
//                 WHERE id = $id";
//
//                     try {
//                         // Get db object
//                         $db = new db();
//                         // Connect
//                         $db = $db->connect();
//
//                         $stmt = $db->prepare($sql);
//
//                         $stmt->bindParam(':name', $name);
//                         $stmt->bindParam(':units', $units);
//                         $stmt->bindParam(':count', $count);
//                         $stmt->bindParam(':now', $now);
//
//                         $stmt->execute();
//
//                         $newResponse = $response->withStatus(200);
//                         $body = $response->getBody();
//                         $body->write('{"status": "success","message": "Ingrediente actualizado", "ingrediente": "'.$name.'"}');
//                         $newResponse = $newResponse->withBody($body);
//                         return $newResponse;
//                     } catch (PDOException $e) {
//                         echo '{"error":{"text": '.$e->getMessage().'}}';
//                     }
//                 } else {
//                     // return respondWithError($response, 'Campos incorrectos', 401);
//                     echo($name.$units.$count);
//                 }
//             } else {  // if (!empty($user_found)) {
//                 return respondWithError($response, 'Error de login, usuario no encontrado', 401);
//             }
//         } else { // if (!empty($access_token)) {
//             return respondWithError($response, 'Error de login, falta access token', 401);
//         }
//     } else { // if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
//         return respondWithError($response, 'Error de encabezado HTTP', 401);
//     }
// });
//
// //Delete ingredient
// $app->delete('/api/ingrediente/{id}', function (Request $request, Response $response) {
//
//     // Verify if the auth header is available
//     if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
//         // If the header is available, get the token
//         $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
//         $access_token = explode(" ", $access_token)[1];
//         // Find the access token, if a user is returned, post the products
//         if (!empty($access_token)) {
//             $user_found = verifyToken($access_token);
//             // Verify that there is a user logged in
//             if (!empty($user_found)) {
//
//                 // Get the product id from the URL
//                 $id = $request->getAttribute('id');
//
//                 // Try to determine if the ingredient is being used in a recepee
//                 $sql = "SELECT * FROM paso WHERE ingrediente = $id";
//                 try {
//                   // Get db object
//                   $db = new db();
//                   // Connect
//                   $db = $db->connect();
//
//                   $stmt = $db->query($sql);
//                   $steps = $stmt->fetchAll(PDO::FETCH_OBJ);
//
//                   // if $steps is empty, then the ingredient is not being used
//                   if(empty($steps)){
//
//                     $sql = "DELETE FROM ingrediente WHERE id = $id";
//
//                         $stmt = $db->prepare($sql);
//                         $stmt->execute();
//
//                         $newResponse = $response->withStatus(200);
//                         $body = $response->getBody();
//                         $body->write('{"status": "success","message": "Ingrediente eliminado"');
//                         $newResponse = $newResponse->withBody($body);
//                         return $newResponse;
//                   }else{
//                     // Find the name of the recepee that uses the ingredient and tell the user
//                     $recepee_id = $steps[0]->receta;
//                     $sql = "SELECT nombre FROM receta where id = $recepee_id";
//                     $stmt = $db->query($sql);
//                     $recepee = $stmt->fetchAll(PDO::FETCH_OBJ);
//
//                     return respondWithError($response, 'No se puede eliminar el ingrediente, es utilizado en la receta '.$recepee[0]->nombre, 406);
//                   }
//
//                     } catch (PDOException $e) {
//                         echo '{"error":{"text": '.$e->getMessage().'}}';
//                     }
//
//
//
//
//             } else {  // if (!empty($user_found)) {
//                 return respondWithError($response, 'Error de login, usuario no encontrado', 401);
//             }
//         } else { // if (!empty($access_token)) {
//             return respondWithError($response, 'Error de login, falta access token', 401);
//         }
//     } else { // if ($request->getHeaders()['HTTP_AUTHORIZATION']) {
//         return respondWithError($response, 'Error de encabezado HTTP', 401);
//     }
// });
