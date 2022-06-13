<?php
// use App\Models\HistoricAccions;
// use App\Models\Table;
// use App\Models\Product;

// use App\Models\Order;

require_once './interfaces/IApiUsable.php';
require_once './models/Table.php';
require_once './models/Product.php';
require_once './models/Order.php';
require_once './models/Usuario.php';
require_once './models/HistoricAccions.php';

class PedidoController implements IApiUsable
{
    
    public function CargarUno($request, $response, $args)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');    
        $parametros = $request->getParsedBody();

        $tableId = $parametros['tablenumber'];
        $userId = $parametros['userId'];
        $productId = $parametros['productId'];
        $status = 'pendiente';

        $table = Table::GetTableById($tableId);
        $user = Usuario::GetUserById($userId);
        $product = Product::GetProductById($productId);

        if(!is_null($table) && !is_null($user) && !is_null($product)){
            $order = Order::CreateOrder($tableId, $userId, $productId, $status, rand(10000, 99999), $_FILES['picture']);
            $table = Table::UpdateTable($tableId, 'con cliente esperando pedido');

            HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, 
            "Creando el pedido con id: " . $order->id);

            $payload = json_encode(array("mensaje" => "Pedido ". $order." creado con exito"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(201);
        } else{
            $payload = json_encode(array("mensaje" => "Id Usuario || Id Producto || Id Mesa, INEXISTENTES"));
        }
      
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        $id = $args['id'];
        $pedido = Order::GetOrderById($id);

        //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Obteniendo el pedido con id: " . $id);

        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }

    public function TraerTodos($request, $response, $args)
    {      
        $jwtHeader = $request->getHeaderLine('Authorization');

        $lista = Order::GetAllOrders();

        //cAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Listando todos los pedidos");

        $payload = json_encode(array("orders" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }

    public function ModificarUno($request, $response, $args) {
        $jwtHeader = $request->getHeaderLine('Authorization');

        $parametros = $request->getParsedBody();
        $id = $args['id'];
        $tableId = $parametros['tableId'];
        $userId = $parametros['userId'];

        $table = Table::GetTableById($tableId);        
        $user = Usuario::GetUserById($userId);

        if(!is_null($table) && !is_null($user)){

            $pedido = Order::GetOrderById($id);

            $newFilename = Order::FindAndChangePictureName($pedido->picture, $pedido->orderNumber, $table->id);

            $order = Order::UpdateUserAndTable($id, $tableId, $userId, $newFilename);

            //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "El pedido con id: " . $order . " cambio de mesa o de mozo");

            $payload = json_encode(array("mensaje" => "Pedido modificado con TableId: " . $tableId . " y UserId: " . $userId));

            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(200);
        } else{
            $payload = json_encode(array("mensaje" => "Id Usuario || Id Mesa, INEXISTENTES"));
        }
      
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
    
    public static function ModificarPedidoFromChef($request, $response, $args)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        $parametros = $request->getParsedBody();

        $orderNumber = $args['orderNumber'];
        $order_status = $parametros['orderStatus'];
        $estimatedTime = $parametros['estimatedTime'];

        if($order_status != "en preparacion" && $order_status != "listo para servir") {
            throw new Exception("El status no es valido");
        }

        $pedidos = Order::GetOrderByOrderNumber($orderNumber);        
        
        if(!is_null($pedidos)) {
          Order::UpdateOrderChef($orderNumber, $order_status, $estimatedTime);
          //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "CHEF. Modificando el status del pedido " . $orderNumber . " a " . $order_status);
          $payload = json_encode(array("mensaje" => "Pedido " .$orderNumber. " modificado con exito"));
        } else {
          $payload = json_encode(array("mensaje" => "El pedido con numero de pedido: " .$orderNumber. " no existe"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }
    
    public static function ModificarPedidoFromWaitress($request, $response, $args)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        $parametros = $request->getParsedBody();

        $orderNumber = $args['orderNumber'];
        $order_status = $parametros['orderStatus'];

        if($order_status != "servido") {
            throw new Exception("El status no es valido");
        }
        
        $pedido = Order::GetOrderByOrderNumber($orderNumber);
        $totalPrice = 0;
        //var_dump($pedido);

       for ($i=0; $i < count($pedido) ; $i++) { 
         $producto = Product::GetProductById($pedido[$i]->product_id);         
         $totalPrice += $producto->price;
       }
        
        if(!is_null($pedido)) {
          Order::UpdateOrderWaitress($orderNumber, $order_status, $totalPrice);
          Order::SetPrice($orderNumber, $totalPrice);

          //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "MOZO. Modificando el status del pedido " . $orderNumber . " a " . $order_status);
          $payload = json_encode(array("mensaje" => "Pedido " .$orderNumber. " modificado con exito"));
        } else {
          $payload = json_encode(array("mensaje" => "El pedido con id: " .$orderNumber. " no existe"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }

    public function BorrarUno($request, $response, $args)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        $id = $args['id'];
        Order::DeleteOrder($id);
          
        //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Borrando el pedido con id: " . $id);
          
        $payload = json_encode(array("mensaje" => "Pedido " .$id. " borrado con exito"));       

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }

    public function AddProductInTheOrder($request, $response, $args) {
        $jwtHeader = $request->getHeaderLine('Authorization');

        $parametros = $request->getParsedBody();
        $orderId = $parametros['orderId'];
        $productId = $parametros['productId'];

        $order = Order::GetOrderById($orderId);
        $product = Product::GetProductById($productId);

        if(!is_null($order) && !is_null($product)) {
            $order = Order::CreateOrder($order->table_id, $order->user_id, $product->id, $order->status, $order->orderNumber, $order->picture);
            
            //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Agregando el producto " . $product->productName . " al pedido " . $order->orderNumber);
            $payload = json_encode(array("mensaje" => "Producto agregado al pedido con exito"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(201);
        } else{
            $payload = json_encode(array("mensaje" => "Ocurrio un error al agregar el producto al pedido"));
        }
    }

    public function TraerProductosDeUnPedido($request, $response, $args) {
        $jwtHeader = $request->getHeaderLine('Authorization');

        $orderNumber = $args['orderNumber'];
        $lista = Order::GetOrderByOrderNumber($orderNumber);

        if(!is_null($lista)) {
          //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Consultando los productos del pedido " . $orderNumber);
          $payload = json_encode(array("products" => $lista));
        } else {
            $payload = json_encode(array("mensaje" => "El pedido con numero de orden: " .$orderNumber. " no existe"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }

    public function TraerTodosSegunEstado($request, $response, $args) {
        $jwtHeader = $request->getHeaderLine('Authorization');

        $status = $args['status'];
        $lista = Order::GetOrdersByStatus($status);

        if(!is_null($lista)) {
          //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Consultando los pedidos con status " . $status);
          $payload = json_encode(array("orders" => $lista));
        } else {
            $payload = json_encode(array("mensaje" => "El pedido con status: " .$status. " no existe"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);        
  }

  public function ConsultarTiempoRestante($request, $response, $args) {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $ordernumber = $args['ordernumber'];
    $tablenumber = $args['mesanumber'];

    $order = Order::GetOrderByTableNumber($ordernumber, $tablenumber);
    //var_dump($order);

    if(is_null($order->estimatedTime)) {
      throw new Exception("El pedido no esta en preparacion");	
    } else {
      //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Consultando el tiempo restante del pedido " . $ordernumber);
      $payload = json_encode(array("Tiempo estimado" => $order->estimatedTime));
    } 

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }

  public function ConsultaPedidos ($request, $response, $args) {

    $consulta = $args['consulta'];
    $payload = "";

    switch ($consulta) {
      case 'LoMasPedido':
        $idProductoMasVendido = Order::GetLoMasYMenosVendido("DESC");
        $product = Product::GetProductById($idProductoMasVendido);
        $payload = json_encode(array("producto" => $product->productName));
        break;
      case 'LoMenosPedido':
        $idProductoMenosVendido = Order::GetLoMasYMenosVendido("ASC");
        $product = Product::GetProductById($idProductoMenosVendido);
        $payload = json_encode(array("producto" => $product->productName));        
        break;
      case 'PedidosFueraDeTiempo':
        $pedidos = Order::GetOrdersByStatus('servido');
        $pedidosFueraDeTiempo = PedidoController::CalculateEstimatedTime($pedidos);
        $payload = json_encode(array("pedidos" => $pedidosFueraDeTiempo));
        break;
      case 'PedidosCancelados':
        $pedidos = Order::GetOrdersByStatus('CANCELADO');
        $payload = json_encode(array("pedidos" => $pedidos));
        break;
      }   

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }

  private static function CalculateEstimatedTime($orders) {
    $list = array();
    foreach ($orders as $order) {
      $estimatedTime = intval($order->estimatedTime);

      //calculo la diferencia en minutos entre createdAt y finishedAT
      $createdAt = new DateTime($order->createdAt);
      $finishedAt = new DateTime($order->finishAt);
      $diff = $createdAt->diff($finishedAt);
      //paso la diferencia de horas a minutos
      $minutes = $diff->h * 60 + $diff->i;
      if($minutes > $estimatedTime) {
        array_push($list, $order);
      }
    }
    return $list;
  }

}
?>