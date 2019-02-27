<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get all the recepees
$app->get('/api/receta', function (Request $request, Response $response) {
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
                $sql = "SELECT * FROM receta";
                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->query($sql);
                    $recetas = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $db = null;


                    // Add the recepees array inside an object
                    $recetaResponse = array('recetas'=>$recetas);
                    $newResponse = $response->withJson($recetaResponse);
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




// Get single recepee
$app->get('/api/receta/{id}', function (Request $request, Response $response) {
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
                $sql = "SELECT * FROM receta WHERE id = $id";

                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->query($sql);
                    $receta = $stmt->fetchAll(PDO::FETCH_OBJ);

                    // Verify that the receta exists
                    if (!empty($receta)) {
                        // If it does, find the steps for it
                        $sql = "SELECT * FROM paso WHERE receta = $id order by numero_paso";
                        $stmt = $db->query($sql);
                        $pasos = $stmt->fetchAll(PDO::FETCH_OBJ);

                        // Find the ingredient and display the details
                        foreach ($pasos as $paso) {
                            $id_ingrediente = $paso->ingrediente;
                            $sql = "SELECT * FROM ingrediente where id=$id_ingrediente";
                            $stmt = $db->query($sql);
                            $ingredientes = $stmt->fetchAll(PDO::FETCH_OBJ);
                            $paso->detalles_ingrediente = $ingredientes[0];
                        }

                        // Store the steps as a property of the recepee
                        $receta[0]->pasos=$pasos;

                        $db = null;

                        $recetaResponse = $receta[0];
                        $newResponse = $response->withJson($recetaResponse);
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



// Add recepee
$app->post('/api/receta', function (Request $request, Response $response) {

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
                // Get the recetas details from the request body
                $name = $request->getParam('name');
                $servings = $request->getParam('servings');
                $instructions = $request->getParam('instructions');
                $resulting_product = $request->getParam('resulting_product');

                // Store the steps, will be used later
                $steps = $request->getParam('steps');

                // If there is no resulting product, assign 0
                $resulting_product = $resulting_product ? $resulting_product : "0";

                // Verify that the information is present
                if ($name && $servings && $instructions) {
                    try {
                        // Store the recepee
                        $sql = "INSERT INTO receta (nombre, porciones, instrucciones, producto_final) VALUES (:name,:servings,:instructions,:resulting_product)";
                        // Get db object
                        $db = new db();
                        // Connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);

                        $stmt->bindparam(':name', $name);
                        $stmt->bindparam(':servings', $servings);
                        $stmt->bindparam(':instructions', $instructions);
                        $stmt->bindparam(':resulting_product', $resulting_product);

                        $stmt->execute();

                        // Retrieve the id of the inserted recepee
                        // Needed to store the steps
                        $sql = "SELECT LAST_INSERT_ID() as id_recepee";
                        $stmt = $db->query($sql);
                        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

                        $id_recepee = $result[0]->id_recepee;

                        // Go through the steps and store them related to the recepee
                        foreach ($steps as $step) {
                            $sql = "INSERT INTO paso (receta, numero_paso, ingrediente, cantidad, instrucciones) VALUES (:id_recepee,:step_number,:ingredient,:ammount,:instructions)";

                            $stmt = $db->prepare($sql);

                            $stmt->bindparam(':id_recepee', $id_recepee);
                            $stmt->bindparam(':step_number', $step[step_number]);
                            $stmt->bindparam(':ingredient', $step[ingredient]);
                            $stmt->bindparam(':ammount', $step[ammount]);
                            $stmt->bindparam(':instructions', $step[instructions]);

                            $stmt->execute();
                        }

                        $newResponse = $response->withStatus(200);
                        $body = $response->getBody();
                        $body->write('{"status": "success","message": "Receta agreagda", "receta": "'.$name.'"}');
                        $newResponse = $newResponse->withBody($body);
                        return $newResponse;
                    } catch (PDOException $e) {
                        echo '{"error":{"text": '.$e->getMessage().'}}';
                    }
                } else { // if ($name && $username && $password && $email) {
                    return respondWithError($response, 'Campos incorrectos', 401);
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



// Update recepee
$app->put('/api/receta/{id}', function (Request $request, Response $response) {

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

                // Get the recepee id from the URL
                $id = $request->getAttribute('id');

                // Get the variables from the request body

                // Get the recetas details from the request body
                $name = $request->getParam('name');
                $servings = $request->getParam('servings');
                $instructions = $request->getParam('instructions');
                $resulting_product = $request->getParam('resulting_product');

                // Store the steps, will be used later
                $steps = $request->getParam('steps');

                // If there is no resulting product, assign 0
                $resulting_product = $resulting_product ? $resulting_product : "0";

                // Verify that the information is present
                if ($name && $servings && $instructions) {
                    $sql = "UPDATE receta SET
                nombre = :name,
                porciones = :servings,
                instrucciones = :instructions,
                producto_final = :resulting_product
                WHERE id = $id";

                    try {
                        // Get db object
                        $db = new db();
                        // Connect
                        $db = $db->connect();

                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':servings', $servings);
                        $stmt->bindParam(':instructions', $instructions);
                        $stmt->bindParam(':resulting_product', $resulting_product);

                        $stmt->execute();


                        // After the recepee is updated, I delete all the steps previously associated with it
                        $sql = "DELETE FROM paso WHERE receta = $id";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();

                        // Then store the new, edited steps


                        // Go through the steps and store them related to the recepee
                        foreach ($steps as $step) {
                            $sql = "INSERT INTO paso (receta, numero_paso, ingrediente, cantidad, instrucciones) VALUES (:id_recepee,:step_number,:ingredient,:ammount,:instructions)";

                            $stmt = $db->prepare($sql);

                            $stmt->bindparam(':id_recepee', $id);
                            $stmt->bindparam(':step_number', $step[step_number]);
                            $stmt->bindparam(':ingredient', $step[ingredient]);
                            $stmt->bindparam(':ammount', $step[ammount]);
                            $stmt->bindparam(':instructions', $step[instructions]);

                            $stmt->execute();
                        }

                        $newResponse = $response->withStatus(200);
                        $body = $response->getBody();
                        $body->write('{"status": "success","message": "Receta actualizada", "receta": "'.$name.'"}');
                        $newResponse = $newResponse->withBody($body);
                        return $newResponse;
                    } catch (PDOException $e) {
                        echo '{"error":{"text": '.$e->getMessage().'}}';
                    }
                } else {
                    return respondWithError($response, 'Campos incorrectos', 401);
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



// Delete recepee
$app->delete('/api/receta/{id}', function (Request $request, Response $response) {

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

                // Get the recepee id from the URL
                $id = $request->getAttribute('id');

                // Delete the selected recpee
                $sql = "DELETE FROM receta WHERE id = $id";


                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->prepare($sql);
                    $stmt->execute();

                    // After the recpee is deleted, delete all the steps related to it
                    $sql = "DELETE FROM paso WHERE receta = $id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();

                    $newResponse = $response->withStatus(200);
                    $body = $response->getBody();
                    $body->write('{"status": "success","message": "Receta eliminada"}');
                    $newResponse = $newResponse->withBody($body);
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
