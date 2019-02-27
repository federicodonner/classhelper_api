<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get All customers
$app->get('/api/almuerzos', function(Request $request, Response $response){
  // // $params = $app->request()->getBody();
  // if($request->getHeaders()['HTTP_AUTHORIZATION']){
  // $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
  // $access_token = explode(" ", $access_token)[1];
  // Find the access token, if a user is returned, find the productos
  // if(!empty($access_token)){
  // $user_found = verifyToken($access_token);
  // if(!empty($user_found)){
  $sql = "SELECT * FROM almuerzos";
  try{
    // Get db object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $almuerzos = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    // Add the products array inside an object
    $almuerzosResponse = array('almuerzos'=>$almuerzos);
    $newResponse = $response->withJson($almuerzosResponse);
    return $newResponse;

  }catch(PDOException $e){
    echo '{"error":{"text": '.$e->getMessage().'}}';
  }

});

// Get single producto
$app->get('/api/almuerzos/{id}', function(Request $request, Response $response){
  $id = $request->getAttribute('id');
  $sql = "SELECT * FROM almuerzos WHERE id = $id";

  try{
    // Get db object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $producto = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    echo json_encode($producto);

  }catch(PDOException $e){
    echo '{"error":{"text": '.$e->getMessage().'}}';
  }
});



// Add product
$app->post('/api/almuerzos', function(Request $request, Response $response){

  $params = $request->getBody();
  if($request->getHeaders()['HTTP_AUTHORIZATION']){
    $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
    $access_token = explode(" ", $access_token)[1];
    // Find the access token, if a user is returned, post the products
    if(!empty($access_token)){
      $user_found = verifyToken($access_token);
      if(!empty($user_found)){

        $price_s = $request->getParam('price_s');
        $price_l = $request->getParam('price_l');
        $monday = $request->getParam('monday');
        $menuMonday = $request->getParam('menuMonday');
        $menuTuesday = $request->getParam('menuTuesday');
        $menuWednesday = $request->getParam('menuWednesday');
        $menuThursday = $request->getParam('menuThursday');
        $menuFriday = $request->getParam('menuFriday');
        $menuSaturday = $request->getParam('menuSaturday');
        $menuSunday =  $request->getParam('menuSunday');

        $sql = "INSERT INTO almuerzos (price_s,price_l,monday,menuMonday,menuTuesday,menuWednesday,menuThursday,menuFriday,menuSaturday,menuSunday) VALUES (:price_s,:price_l,:monday,:menuMonday,:menuTuesday,:menuWednesday,:menuThursday,:menuFriday,:menuSaturday,:menuSunday)";
        try{
          // Get db object
          $db = new db();
          // Connect
          $db = $db->connect();

          $stmt = $db->prepare($sql);

          $stmt->bindParam(':price_s', $price_s);
          $stmt->bindParam(':price_l', $price_l);
          $stmt->bindParam(':monday', $monday);
          $stmt->bindParam(':menuMonday', $menuMonday);
          $stmt->bindParam(':menuTuesday', $menuTuesday);
          $stmt->bindParam(':menuWednesday', $menuWednesday);
          $stmt->bindParam(':menuThursday', $menuThursday);
          $stmt->bindParam(':menuFriday', $menuFriday);
          $stmt->bindParam(':menuSaturday', $menuSaturday);
          $stmt->bindParam(':menuSunday', $menuSunday);

          $stmt->execute();

          $newResponse = $response->withStatus(200);
          $body = $response->getBody();
          $body->write('{"status": "success","message": "Almuerzo agregado", "almuerzo": "'.$menu.'"}');
          $newResponse = $newResponse->withBody($body);
          return $newResponse;


        }catch(PDOException $e){
          echo '{"error":{"text": '.$e->getMessage().'}}';

        }
      }else{
        return loginError($response, 'Error de login, usuario no encontrado');
      }
    }else{
      return loginError($response, 'Error de login, falta access token');
    }
  }else{
    return loginError($response, 'Error de encabezado HTTP');
  }
});


// Update product
$app->put('/api/almuerzos/{id}', function(Request $request, Response $response){

  $params = $request->getBody();
  if($request->getHeaders()['HTTP_AUTHORIZATION']){
    $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
    $access_token = explode(" ", $access_token)[1];
    // Find the access token, if a user is returned, post the products
    if(!empty($access_token)){
      $user_found = verifyToken($access_token);
      if(!empty($user_found)){

        $id = $request->getAttribute('id');

        $price_s = $request->getParam('price_s');
        $price_l = $request->getParam('price_l');
        $menuMonday = $request->getParam('menuMonday');
        $menuTuesday = $request->getParam('menuTuesday');
        $menuWednesday = $request->getParam('menuWednesday');
        $menuThursday = $request->getParam('menuThursday');
        $menuFriday = $request->getParam('menuFriday');
        $menuSaturday = $request->getParam('menuSaturday');
        $menuSunday =  $request->getParam('menuSunday');

        $sql = "UPDATE almuerzos SET
        price_s = :price_s,
        price_l = :price_l,
        menuMonday = :menuMonday,
        menuTuesday = :menuTuesday,
        menuWednesday = :menuWednesday,
        menuThursday = :menuThursday,
        menuFriday = :menuFriday,
        menuSaturday = :menuSaturday,
        menuSunday = :menuSunday
        WHERE id = $id";

        try{
          // Get db object
          $db = new db();
          // Connect
          $db = $db->connect();

          $stmt = $db->prepare($sql);

          $stmt->bindParam(':price_s', $price_s);
          $stmt->bindParam(':price_l', $price_l);
          $stmt->bindParam(':menuMonday', $menuMonday);
          $stmt->bindParam(':menuTuesday', $menuTuesday);
          $stmt->bindParam(':menuWednesday', $menuWednesday);
          $stmt->bindParam(':menuThursday', $menuThursday);
          $stmt->bindParam(':menuFriday', $menuFriday);
          $stmt->bindParam(':menuSaturday', $menuSaturday);
          $stmt->bindParam(':menuSunday', $menuSunday);

          $stmt->execute();

          echo('{"notice":{"text":"product updated"}}');

        }catch(PDOException $e){
          echo '{"error":{"text": '.$e->getMessage().'}}';
        }
      }else{
        return loginError($response, 'Error de login, usuario no encontrado');
      }
    }else{
      return loginError($response, 'Error de login, falta access token');
    }
  }else{
    return loginError($response, 'Error de encabezado HTTP');
  }
});
