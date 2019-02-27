<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get All configs
$app->get('/api/configuracion', function(Request $request, Response $response){
  $sql = "SELECT * FROM configuracion";
  try{
    // Get db object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $configuracion = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;

    $response = new stdClass;
    $response->configuracion = $configuracion;

    echo json_encode($response);

  }catch(PDOException $e){
    echo '{"error":{"text": '.$e->getMessage().'}}';
  }
});

// Update configuration
$app->put('/api/configuracion/{id}', function(Request $request, Response $response){
  $id = $request->getAttribute('id');
  $home_title = $request->getParam('home_title');
  $home_text = $request->getParam('home_text');

  $sql = "UPDATE configuracion SET
  home_title = :home_title,
  home_title = :home_title
  WHERE id = $id";

  try{
    // Get db object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':home_title', $home_title);
    $stmt->bindParam(':home_text', $home_text);
    $stmt->execute();

    echo('{"notice":{"text":"configuration updated"}}');

  }catch(PDOException $e){
    echo '{"error":{"text": '.$e->getMessage().'}}';
  }
});
