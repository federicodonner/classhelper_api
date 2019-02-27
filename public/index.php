<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../src/config/db.php';

require '../src/config/externalFunctions.php';

$app = new \Slim\App;

// Customer routes
// require '../src/routes/productos.php';
require '../src/routes/producto.php';
require '../src/routes/coccion.php';
require '../src/routes/ingrediente.php';
require '../src/routes/receta.php';
require '../src/routes/oauth.php';
require '../src/routes/usuario.php';
require '../src/routes/cors.php';


$app->run();

// echo('hla');
