<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get all the products
$app->get('/api/producto', function (Request $request, Response $response) {
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
                $sql = "SELECT * FROM producto";
                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->query($sql);
                    $products = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $db = null;

                    // Add the products array inside an object
                    $productsResponse = array('productos'=>$products);
                    $newResponse = $response->withJson($productsResponse);
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




// Get single product
$app->get('/api/producto/{id}', function (Request $request, Response $response) {
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
                $sql = "SELECT * FROM producto WHERE id = $id";

                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->query($sql);
                    $product = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $db = null;

                    // Verify that the ingredient exists
                    if (!empty($product)) {
                        $productResponse = $product[0];
                        $newResponse = $response->withJson($productResponse);
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



// Add product
$app->post('/api/producto', function (Request $request, Response $response) {

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

                // Get the ingredient's details from the request body
                $name = $request->getParam('name');
                $price = $request->getParam('price');
                $description = $request->getParam('description');
                $picture = $request->getParam('picture');

                // Verify that the information is present
                if ($name && $price && $description) {

                  // Check that there is no other ingredient with the same name
                    $sql = "SELECT * FROM producto where nombre = '$name'";

                    try {
                        // Get db object
                        $db = new db();
                        // Connect
                        $db = $db->connect();

                        $stmt = $db->query($sql);
                        $existing_product = $stmt->fetchAll(PDO::FETCH_OBJ);

                        // If there is no other product with the same name, store it
                        if (empty($existing_product)) {
                            echo('ok');
                            // Store the information in the database
                            $sql = "INSERT INTO producto (nombre, imagen, precio, descripcion, stock) VALUES (:name,:picture,:price,:description,:stock)";

                            $stmt = $db->prepare($sql);

                            $stock = 0;
                            $stmt->bindparam(':name', $name);
                            $stmt->bindparam(':picture', $picture);
                            $stmt->bindparam(':price', $price);
                            $stmt->bindparam(':description', $description);
                            $stmt->bindparam(':stock', $stock);
                            //
                            $stmt->execute();

                            $newResponse = $response->withStatus(200);
                            $body = $response->getBody();
                            $body->write('{"status": "success","message": "Producto agreagdo", "producto": "'.$name.'"}');
                            $newResponse = $newResponse->withBody($body);
                            return $newResponse;
                        } else { // if (empty($user)) {
                            return respondWithError($response, 'El producto ya está ingresado', 401);
                        }
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



// Update ingredient
$app->put('/api/producto/{id}', function (Request $request, Response $response) {

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

                // Get the product id from the URL
                $id = $request->getAttribute('id');

                // Get the product's details from the request body
                $name = $request->getParam('name');
                $price = $request->getParam('price');
                $description = $request->getParam('description');
                $picture = $request->getParam('picture');
                $stock = $request->getParam('stock');

                if ($name && $price && $description) {
                    $sql = "UPDATE producto SET
                nombre = :name,
                precio = :price,
                descripcion = :description,
                imagen = :picture,
                stock = :stock
                WHERE id = $id";

                    try {
                        // Get db object
                        $db = new db();
                        // Connect
                        $db = $db->connect();

                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':price', $price);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':picture', $picture);
                        $stmt->bindParam(':stock', $stock);

                        $stmt->execute();

                        $newResponse = $response->withStatus(200);
                        $body = $response->getBody();
                        $body->write('{"status": "success","message": "Producto actualizado", "ingrediente": "'.$name.'"}');
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

// Update ingredient
$app->delete('/api/producto/{id}', function (Request $request, Response $response) {

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

                // Get the product id from the URL
                $id = $request->getAttribute('id');

                $sql = "DELETE FROM producto WHERE id = $id";

                try {
                    // Get db object
                    $db = new db();
                    // Connect
                    $db = $db->connect();

                    $stmt = $db->prepare($sql);
                    $stmt->execute();

                    $newResponse = $response->withStatus(200);
                    $body = $response->getBody();
                    $body->write('{"status": "success","message": "Producto eliminado"');
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
