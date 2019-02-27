<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get All customers
$app->get('/api/productos', function(Request $request, Response $response){
  // // $params = $app->request()->getBody();
  // if($request->getHeaders()['HTTP_AUTHORIZATION']){
  // $access_token = $request->getHeaders()['HTTP_AUTHORIZATION'][0];
  // $access_token = explode(" ", $access_token)[1];
  // Find the access token, if a user is returned, find the productos
  // if(!empty($access_token)){
  // $user_found = verifyToken($access_token);
  // if(!empty($user_found)){
  $sql = "SELECT * FROM productos";
  try{
    // Get db object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    // Add the products array inside an object
    $productosResponse = array('productos'=>$productos);
    $newResponse = $response->withJson($productosResponse);
    return $newResponse;

  }catch(PDOException $e){
    echo '{"error":{"text": '.$e->getMessage().'}}';
  }

  // }else{
  //   $data = array("status" => "error", "message" => "El usuario no tiene permisos para realizar la operación");
  //   $newResponse = $response->withJson($data, 401);
  //   return $newResponse;
  // }
  // }
  // }else{
  //   $data = array("status" => "error", "message" => "El usuario no tiene permisos para realizar la operación");
  //   $newResponse = $response->withJson($data, 401);
  //   return $newResponse;
  // }


});

// Get single producto
$app->get('/api/productos/{id}', function(Request $request, Response $response){
  $id = $request->getAttribute('id');
  $sql = "SELECT * FROM productos WHERE id = $id";

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
$app->post('/api/productos', function(Request $request, Response $response){
  $name = $request->getParam('name');
  $price = $request->getParam('price');
  $stock = $request->getParam('stock');
  $description = $request->getParam('description');
  $picture = $request->getParam('picture');

  $imgData = str_replace(' ','+',$picture);
  $imgData =  substr($imgData,strpos($imgData,",")+1);
  $imgData = base64_decode($imgData);
  $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
  $filename = sprintf('%s.%0.8s', $basename, 'jpg');
  $filePath = __DIR__. '/../../pictures/'.$filename;
  // Write $imgData into the image file
  $file = fopen($filePath, 'w');
  fwrite($file, $imgData);
  fclose($file);

  $sql = "INSERT INTO productos (name,price,stock,description,picture) VALUES (:name,:price,:stock,:description,:picture)";
  try{
    // Get db object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':picture', $filename);

    $stmt->execute();

    $newResponse = $response->withStatus(200);
    $body = $response->getBody();
    $body->write('{"status": "success","message": "Producto agregado", "producto": "'.$name.'"}');
    $newResponse = $newResponse->withBody($body);
    return $newResponse;


  }catch(PDOException $e){
    echo '{"error":{"text": '.$e->getMessage().'}}';

  }
});


// Update product
$app->put('/api/productos/{id}', function(Request $request, Response $response){
  $id = $request->getAttribute('id');

  $name = $request->getParam('name');
  $price = $request->getParam('price');
  $stock = $request->getParam('stock');
  $description = $request->getParam('description');
  $picture = $request->getParam('picture');


  $sql = "UPDATE productos SET
  name = :name,
  price = :price,
  stock = :stock,
  description = :description,
  picture = :picture
  WHERE id = $id";

  try{
    // Get db object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':picture', $picture);


    $stmt->execute();

    echo('{"notice":{"text":"product updated"}}');

  }catch(PDOException $e){
    echo '{"error":{"text": '.$e->getMessage().'}}';
  }
});

//Delete producto
$app->delete('/api/productos/{id}', function(Request $request, Response $response){
  $id = $request->getAttribute('id');
  $sql = "DELETE FROM productos WHERE id = $id";

  try{
    // Get db object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);
    $stmt->execute();

    echo('{"notice":{"text":"producto deleted"}}');

  }catch(PDOException $e){
    echo '{"error":{"text": '.$e->getMessage().'}}';
  }

});
