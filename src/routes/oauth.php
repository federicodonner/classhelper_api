<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Add product
$app->post('/api/oauth', function (Request $request, Response $response) {
    $grant_type = $request->getParam('grant_type');

    if ($grant_type == 'password') {
        $username = $request->getParam('username');
        $password = $request->getParam('password');

        if ($username && $password) {
            $sql = "SELECT * FROM usuario WHERE username = '$username'";

            try {
                // Get db object
                $db = new db();
                // Connect
                $db = $db->connect();

                $stmt = $db->query($sql);
                $user = $stmt->fetchAll(PDO::FETCH_OBJ);

                // Check that there is at least one user with that name
                if ($user == null) {
                    // If there isn't, reply with failed status
                    return respondWithError($response, "Nombre de usuario o password incorrecto", 409);
                } else {
                    // If there is one, send the token
                    // Verify the password vs the hash
                    if (password_verify($password, $user[0]->password_hash)) {

                    // Store the user token in the database
                        // Prepare viarables
                        $access_token = random_str(32);
                        $now = time();
                        $usuario = $user[0]->id;

                        // SQL statement
                        $sql = "INSERT INTO login (usuario,token,fecha) VALUES (:usuario,:token,:now)";

                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(':usuario', $usuario);
                        $stmt->bindParam(':token', $access_token);
                        $stmt->bindParam(':now', $now);

                        $stmt->execute();

                        // Respond with the token
                        $newResponse = $response->withStatus(201);
                        $body = $response->getBody();
                        $body->write('{"status":"201", "token":"'.$access_token.'"}');
                        $newResponse = $newResponse->withBody($body);
                        return $newResponse;
                    }
                }
            } catch (PDOException $e) {
                echo '{"error":{"text": '.$e->getMessage().'}}';
            }
        } else {
            return respondWithError($response, 'Falta nombre de usuario o contraseña', 401);
        }
    } elseif ($grant_type == 'token') { // if ($grant_type == 'password') {
    } else {
        return respondWithError($response, 'Grant_type incorrecto', 406);
    }
});
