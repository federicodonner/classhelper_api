<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../src/config/db.php';

require '../src/config/externalFunctions.php';
require '../src/config/pusherConfig.php';

$app = new \Slim\App;

// Customer routes
require '../src/routes/class.php';
require '../src/routes/message.php';
require '../src/routes/cors.php';


$app->run();
