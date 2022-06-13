<?php

// use App\Models\HistoricAccions;
// use App\Models\Table;

require_once './interfaces/IApiUsable.php';
require_once './models/Table.php';
require_once './models/HistoricAccions.php';
class MesaController extends Table implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $parametros = $request->getParsedBody();
    $tableNumber = $parametros['tableNumber'];

    $tableId = Table::CreateTable($tableNumber, "vacia");

    HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Creando la mesa con id: " . $tableId);

    $payload = json_encode(array("mensaje" => "Mesa " . $tableId . " creada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(201);
  }

  public function TraerUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $id = $args['id'];
    $mesa = Table::GetTableById($id);

    //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Obteniendo la mesa con id: " . $id);

    $payload = json_encode($mesa);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }


  public function TraerTodos($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');

    $lista = Table::GetAllTables();

    //HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Listando todas las mesas");

    $payload = json_encode(array("tables" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }

  public function ModificarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $parametros = $request->getParsedBody();

    $id = $args['id'];
    $table_status = $parametros['table_status'];

    $table = Table::GetTableById($id);
    if ($table->ValidStatus($table_status)) {
      Table::UpdateTable($id, $table_status);
      HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Modificando la mesa con id: " . $id);
    }

    $payload = json_encode(array("mensaje" =>  "Mesa " . $id . " modificada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }

  public function BorrarUno($request, $response, $args)
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    $id = $args['id'];

    $tableId = Table::DeleteTable($id);

    HistoricAccions::CreateRegistry(AutentificadorJWT::GetTokenData($jwtHeader)->id, "Borrando la mesa con id: " . $id);

    $payload = json_encode(array("mensaje" => "Mesa " . $id . " borrada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }

  public function ConsultaMesas($request, $response, $args)
  {
    $consulta = $args['consulta'];
    $payload = "";

    switch ($consulta) {
      case 'MesaMasUsada':
        $idMesaMasUsada = Order::GetTableWithMoreAndLessOrders("DESC");
        $mesa = Table::GetTableByTableNumber($idMesaMasUsada->table_id);
        $payload = json_encode(array("mesa" => $mesa));
        break;
      case 'MesaMenosUsada':
        $idMesaMasUsada = Order::GetTableWithMoreAndLessOrders("ASC");
        $mesa = Table::GetTableByTableNumber($idMesaMasUsada->table_id);
        $payload = json_encode(array("mesa" => $mesa));
        break;
      case 'MesaMejoresComentarios':
        // TODO
        break;
      case 'MesaPeoresComentarios':
        // TODO
        break;
      case 'MasFacturo':
        $mesaMasFacturo = Order::GetTableNumberMoreAndLessPrice("DESC");
        $mesa = Table::GetTableByTableNumber($mesaMasFacturo);
        $payload = json_encode(array("mesa" => $mesa));
        break;
      case 'MenosFacturo':
        $mesaMenosFacturo = Order::GetTableNumberMoreAndLessPrice("ASC");
        $mesa = Table::GetTableByTableNumber($mesaMenosFacturo);
        $payload = json_encode(array("mesa" => $mesa));
        break;
      case 'MayorImporte':
        $mesaMaxImporte = Order::GetTableNumberMoreFinalPrice();
        $mesa = Table::GetTableByTableNumber($mesaMaxImporte);
        $payload = json_encode(array("mesa" => $mesa));
        break;
      case 'MenorImporte':
        $mesaMinImporte = Order::GetTableNumberLessFinalPrice();
        $mesa = Table::GetTableByTableNumber($mesaMinImporte);
        $payload = json_encode(array("mesa" => $mesa));
        break;
      default:
        $lista = "Error, ingresar valor valido";
        break;
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }


  public function ConsultaMesasFecha($request, $response, $args)
  {
    $tables = array();

    $fechaInicio = date($args['fechaInicio']);
    $fechaFin = date($args['fechaFin']);
    $mesaImporteEntreDosFechas = Order::GetTableNumbersBetweenDates($fechaInicio, $fechaFin);    


    for ($i=0; $i < count($mesaImporteEntreDosFechas) ; $i++) { 
      $mesa = Table::GetTableByTableNumber($mesaImporteEntreDosFechas[$i]->table_id);
      array_push($tables, $mesa);
    }

    $payload = json_encode(array("tables" => $tables));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }
}
