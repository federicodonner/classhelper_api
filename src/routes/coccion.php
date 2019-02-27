<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get all the cook plan
$app->get('/api/coccion', function (Request $request, Response $response) {
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
                $sql = "SELECT * FROM coccion";
                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->query($sql);
                    $cocciones = $stmt->fetchAll(PDO::FETCH_OBJ);

                    // Fetch the details of each recepee
                    foreach ($cocciones as $coccion) {
                        $id_coccion = $coccion->id;
                        $sql = "SELECT * FROM coccion_x_receta WHERE id_coccion=$id_coccion";
                        $stmt = $db->query($sql);
                        $recetas = $stmt->fetchAll(PDO::FETCH_OBJ);

                        foreach ($recetas as $receta) {
                            $id_receta = $receta->id_receta;
                            $sql = "SELECT * FROM receta WHERE id=$id_receta";
                            $stmt = $db->query($sql);
                            $detalles = $stmt->fetchAll(PDO::FETCH_OBJ);
                            $receta->detalles = $detalles[0];
                        }
                        $coccion->recetas = $recetas;
                    }

                    // Add the cook plans array inside an object
                    $coccionesResponse = array('cocciones'=>$cocciones);
                    $newResponse = $response->withJson($coccionesResponse);
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




// Get single cook plan
$app->get('/api/coccion/{id}', function (Request $request, Response $response) {
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
                $sql = "SELECT * FROM coccion WHERE id = $id";

                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->query($sql);
                    $coccion = $stmt->fetchAll(PDO::FETCH_OBJ);

                    // Find the cook plan

                    // Verify that the cook plan exists
                    if (!empty($coccion)) {
                        $id_coccion = $coccion[0]->id;
                        $sql = "SELECT * FROM coccion_x_receta WHERE id_coccion=$id_coccion";
                        $stmt = $db->query($sql);
                        $recetas = $stmt->fetchAll(PDO::FETCH_OBJ);

                        foreach ($recetas as $receta) {
                            $id_receta = $receta->id_receta;
                            $sql = "SELECT * FROM receta WHERE id=$id_receta";
                            $stmt = $db->query($sql);
                            $detalles = $stmt->fetchAll(PDO::FETCH_OBJ);

                            $sql = "SELECT * FROM paso WHERE receta = $id_receta order by numero_paso";
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
                            $detalles[0]->pasos=$pasos;

                            // Store the recpee details as a property of the cook plan
                            $receta->detalles = $detalles[0];
                        }

                        $coccion[0]->recetas = $recetas;

                        $db = null;

                        $coccionResponse = $coccion[0];
                        $newResponse = $response->withJson($coccionResponse);
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



// Add cook plan
$app->post('/api/coccion', function (Request $request, Response $response) {

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
                $comments = $request->getParam('comments');
                $plan_date = $request->getParam('plan_date');


                // Store the recpees, will be used later
                $recepees = $request->getParam('recepees');

                // Verify that the date is planned and that the recpees has something
                if ($plan_date && !empty($recepees)) {
                    try {
                        // Store the cook plan
                        $sql = "INSERT INTO coccion (comentarios, fecha_planificado) VALUES (:comments,:plan_date)";
                        // Get db object
                        $db = new db();
                        // Connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);

                        $stmt->bindparam(':comments', $comments);
                        $stmt->bindparam(':plan_date', $plan_date);

                        $stmt->execute();

                        // Retrieve the id of the inserted cook plan
                        // Needed to store the recpees
                        $sql = "SELECT LAST_INSERT_ID() as id_cook_plan";
                        $stmt = $db->query($sql);
                        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

                        $id_cook_plan = $result[0]->id_cook_plan;

                        // Go through the recepees and store them related to the cook plan
                        foreach ($recepees as $recepee) {
                            $sql = "INSERT INTO coccion_x_receta (id_coccion,id_receta,cantidad,comentarios,cocinado) VALUES(:id_cook_plan,:id_recepee,:ammount,:comments,:cooked)";

                            $stmt = $db->prepare($sql);

                            $zero = 0;
                            $stmt->bindparam(':id_cook_plan', $id_cook_plan);
                            $stmt->bindparam(':id_recepee', $recepee[id_recepee]);
                            $stmt->bindparam(':ammount', $recepee[ammount]);
                            $stmt->bindparam(':comments', $recepee[comments]);
                            $stmt->bindparam(':cooked', $zero);
                            $stmt->execute();
                        }

                        $newResponse = $response->withStatus(200);
                        $body = $response->getBody();
                        $body->write('{"status": "success","message": "Plan de cocción agreagdo"}');
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



// Update cook plan
$app->put('/api/coccion/{id}', function (Request $request, Response $response) {

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

                // Get the cook plan id from the URL
                $id = $request->getAttribute('id');

                // Verify if the PUT is used only to mark a cooked recepee
                $id_cooked = $request->getQueryParam('cocinado', $default = null);


                // If it is, update the entry in the database
                if ($id_cooked) {
                    // Get the variables from the request body
                    $cook_comments = $request->getParam('cook_comments');
                    $sql = "UPDATE coccion_x_receta SET cocinado = :cooked, comentarios_cocina = :cook_comments, fecha_cocinado = :cooked_date WHERE id = $id_cooked";

                    try {
                        // Get db object
                        $db = new db();
                        // Connect
                        $db = $db->connect();

                        $stmt = $db->prepare($sql);

                        $cooked = 1;
                        $now = time();

                        $stmt->bindParam(':cooked', $cooked);
                        $stmt->bindParam(':cook_comments', $cook_comments);
                        $stmt->bindParam(':cooked_date', $now);

                        $stmt->execute();

                        $newResponse = $response->withStatus(200);
                        $body = $response->getBody();
                        $body->write('{"status": "success","message": "Plan de cocción actualizado"}');
                        $newResponse = $newResponse->withBody($body);
                        return $newResponse;
                    } catch (PDOException $e) {
                        echo '{"error":{"text": '.$e->getMessage().'}}';
                    }





                    // If it isnt, update the whole cook plan object
                } else {

                    // Get the cook plan details from the request body
                    $comments = $request->getParam('comments');
                    $plan_date = $request->getParam('plan_date');

                    // Store the recpees, will be used later
                    $recepees = $request->getParam('recepees');

                    // Verify that the information is present
                    if ($plan_date && !empty($recepees)) {
                        $sql = "UPDATE coccion SET
                comentarios = :comments,
                fecha_planificado = :plan_date
                WHERE id = $id";

                        try {
                            // Get db object
                            $db = new db();
                            // Connect
                            $db = $db->connect();

                            $stmt = $db->prepare($sql);

                            $stmt->bindParam(':comments', $comments);
                            $stmt->bindParam(':plan_date', $plan_date);

                            $stmt->execute();


                            // After the cook plan is updated, I delete all the recpee plans previously associated with it
                            $sql = "DELETE FROM coccion_x_receta WHERE id_coccion = $id";
                            $stmt = $db->prepare($sql);
                            $stmt->execute();

                            // Then store the new, edited recpee plans
                            // Go through the recepees and store them related to the cook plan
                            foreach ($recepees as $recepee) {
                                $sql = "INSERT INTO coccion_x_receta (id_coccion,id_receta,cantidad,comentarios,cocinado,fecha_cocinado,comentarios_cocina) VALUES(:id_cook_plan,:id_recepee,:ammount,:comments,:cooked,:cooked_date,:cooked_comments)";

                                $stmt = $db->prepare($sql);

                                $stmt->bindparam(':id_cook_plan', $id);
                                $stmt->bindparam(':id_recepee', $recepee[id_recepee]);
                                $stmt->bindparam(':ammount', $recepee[ammount]);
                                $stmt->bindparam(':comments', $recepee[comments]);
                                $stmt->bindparam(':cooked', $recepee[cooked]);
                                $stmt->bindparam(':cooked_date', $recepee[cooked_date]);
                                $stmt->bindparam(':cooked_comments', $recepee[cooked_comments]);
                                $stmt->execute();
                            }

                            $newResponse = $response->withStatus(200);
                            $body = $response->getBody();
                            $body->write('{"status": "success","message": "Plan de cocción actualizado"}');
                            $newResponse = $newResponse->withBody($body);
                            return $newResponse;
                        } catch (PDOException $e) {
                            echo '{"error":{"text": '.$e->getMessage().'}}';
                        }
                    } else {
                        return respondWithError($response, 'Campos incorrectos', 401);
                    }
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



// Delete cook plan
$app->delete('/api/coccion/{id}', function (Request $request, Response $response) {

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
                $sql = "DELETE FROM coccion WHERE id = $id";


                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->prepare($sql);
                    $stmt->execute();

                    // After the cook plan is deleted, delete all the recpee plans related to it
                    $sql = "DELETE FROM coccion_x_receta WHERE id_coccion = $id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();

                    $newResponse = $response->withStatus(200);
                    $body = $response->getBody();
                    $body->write('{"status": "success","message": "Plan de cocción eliminado"}');
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
