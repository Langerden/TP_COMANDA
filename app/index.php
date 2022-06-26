<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/AutenticadorJWT.php';
require_once './middlewares/MWPermisos.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/SurveyController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Eloquent
//$container=$app->getContainer();

//$capsule = new Capsule;
// $capsule->addConnection([
//     'driver'    => 'mysql',
//     'host'      => $_ENV['MYSQL_HOST'],
//     'database'  => $_ENV['MYSQL_DB'],
//     'username'  => $_ENV['MYSQL_USER'],
//     'password'  => $_ENV['MYSQL_PASS'],
//     'charset'   => 'utf8',
//     'collation' => 'utf8_unicode_ci',
//     'prefix'    => '',
// ]);

// $capsule->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'localhost',
//     'database'  => 'comanda',
//     'username'  => 'root',
//     'password'  => "",
//     'charset'   => 'utf8',
//     'collation' => 'utf8_unicode_ci',
//     'prefix'    => '',
// ]);

$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("TP Comanda 3 - Langer");
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

// Routes
$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id}', \UsuarioController::class .  ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    $group->put('[/{id}]', \UsuarioController::class . ':ModificarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
    $group->delete('/{id}', \UsuarioController::class . ':BorrarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
    $group->post('/login', \UsuarioController::class . ':Login');
  });

  $app->group('/products', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{id}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
    $group->put('[/{id}]', \ProductoController::class . ':ModificarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
    $group->delete('/{id}', \ProductoController::class . ':BorrarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
  });

  $app->group('/tables', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{id}', \MesaController::class . ':TraerUno');
    $group->post('[/]', \MesaController::class . ':CargarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
    $group->put('[/{id}]', \MesaController::class . ':ModificarUno')->add(\MWPermisos::class . ':VerifyIsWaitress');
    $group->delete('/{id}', \MesaController::class . ':BorrarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
  });

  $app->group('/orders', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/{id}', \PedidoController::class . ':TraerUno');
    $group->get('/productos/{orderNumber}', \PedidoController::class . ':TraerProductosDeUnPedido');
    $group->get('/{ordernumber}/mesa/{mesanumber}', \PedidoController::class . ':ConsultarTiempoRestante');
    //$group->get('/status', \PedidoController::class . ':TraerTodosSegunEstado')->add(\MWPermisos::class . ':VerifyIsSocio');
    $group->post('[/]', \PedidoController::class . ':CargarUno')->add(\MWPermisos::class . ':VerifyIsWaitress');
    $group->put('[/{id}]', \PedidoController::class . ':ModificarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
    $group->delete('/{id}', \PedidoController::class . ':BorrarUno')->add(\MWPermisos::class . ':VerifyIsSocio');
  });

  //post para atender una orden
  $app->group('/order', function (RouteCollectorProxy $group) {
    $group->get('/{orderId}/product/{productId}', \PedidoController::class . ':AddProductInTheOrder')->add(\MWPermisos::class . ':VerifyIsWaitress');
    $group->post('/status/{orderNumber}', \PedidoController::class . ':ModificarPedidoFromChef')->add(\MWPermisos::class . ':VerifyIsChef');
    $group->post('/complete/{orderNumber}', \PedidoController::class . ':ModificarPedidoFromWaitress')->add(\MWPermisos::class . ':VerifyIsWaitress');
    //$group->get('/status', \PedidoController::class . ':TraerTodosSegunEstado')->add(\MWPermisos::class . ':VerifyIsSocio');
  });
  
  $app->group('/querys', function (RouteCollectorProxy $group) {
    $group->get('/employees/{consulta}', \UsuarioController::class . ':ConsultaUsuarios');
    $group->get('/orders/{consulta}', \PedidoController::class . ':ConsultaPedidos');
    $group->get('/tables/{consulta}', \MesaController::class . ':ConsultaMesas');
    $group->get('/tables/{fechaInicio}/{fechaFin}', \MesaController::class . ':ConsultaMesasFecha');
  });

  $app->group('/surveys', function (RouteCollectorProxy $group) {
    $group->post('[/]', \SurveyController::class . ':CreateSurvery');
    $group->get('/writecsv', \SurveyController::class . ':EndpointWriteCSV');
    $group->get('/readcsv', \SurveyController::class . ':EndpointReadCSV');
    $group->get('/createpdf',\SurveyController::class . ':EndpointCreatePDF');
  });


  //TODO chequear status del pedido y de las mesas


$app->run();

?>