<?php

require_once './models/Survery.php';

class SurveyController {

    public function EndpointWriteCSV($request, $response, $args) {
        
        $surveriesDB = Survery::GetAllSurvies();

        if( (count($surveriesDB) > 0) && SurveyController::WriteCSV($surveriesDB)) {
            $payload = json_encode(array("surveys" => $surveriesDB));
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } else {
            $payload = json_encode(array("ERROR" => "No se pudo guardar el archivo"));
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }

    private static function WriteCSV($fileContent, $fileName = "./surveys.csv") {
        $directory = dirname($fileName, 1);
        $success = false;

        try {
            if(!file_exists($directory)){
                mkdir($directory, 0777, true);
            }
            $file = fopen($fileName, "w");
            if ($file) {
                foreach ($fileContent as $entity) {
                    $line = $entity->id . "," . $entity->id_table . "," . $entity->score_table . "," . $entity->score_restarnt . "," . $entity->score_waiter . "," . $entity->score_chef . "," . $entity->comments . PHP_EOL;
                    fwrite($file, $line);
                    $success = true;
                }
            }
        } catch (\Throwable $th) {
            echo "Error saving the file";
        }finally{
            fclose($file);
        }

        return $success;
    }

    public static function EndpointReadCSV($request, $response, $args){
        $arrayFile = SurveyController::ReadCSV();

        $payload = json_encode(array("surveys" => $arrayFile));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    private static function ReadCSV($filename="./surveys.csv"){
        $file = fopen($filename, "r");
        $datos = array();
       
        if (file_exists($filename) && filesize($filename) > 0) {

            $file = fopen($filename, "r");

            while(!feof($file)) {
                $linea = fgets($file);

                if(!empty($linea)){
                 
                    $linea = str_replace(PHP_EOL, "", $linea);
                    $csvData = explode(",", $linea);
                    $auxSurv = Survery::ConstructSurvery( intval($csvData[0]), intval($csvData[1]), intval($csvData[2]), intval($csvData[3]), 
                    intval($csvData[4]) , intval($csvData[5]) , $csvData[6]);
                    array_push($datos, $auxSurv);
                }
            }

            fclose($file);
        }
        return $datos;
    }

    public static function CreateSurvery($request, $response, $args) {

        $parametros = $request->getParsedBody();
        $orderNumber = $parametros["orderNumber"];

        $order = Order::GetOrderByOrderNumber($orderNumber);

        if($order == null){
            $payload = json_encode(array("ERROR" => "No se encontro la orden"));
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        } else {
            $table = Table::GetTableByTableNumber($order[0]->table_id);
            if($table->table_status == "con cliente pagando") {
                $tableScore = $parametros["tableScore"];
                $restaurantScore = $parametros["restaurantScore"];
                $waiterScore = $parametros["waiterScore"];
                $chefScore = $parametros["chefScore"];
                $comments = $parametros["comments"];

                if(self::ValidateScores($tableScore) && self::ValidateScores($restaurantScore)
                && self::ValidateScores($waiterScore) && self::ValidateScores($chefScore)
                && strlen($comments) <= 66) {
                    $survery = Survery::CreateSurvery($order[0]->table_id, $tableScore, $restaurantScore, $waiterScore, $chefScore, $comments);
                    $payload = json_encode(array("mensaje" => "Encuesta ". $survery." creada con exito! Gracias"));
                } else {
                $payload = json_encode(array("ERROR" => "Los puntajes deben ser entre 1 y 10. El comentario tiene un maximo de 66 caracteres"));
                }
                
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        
            } else {
                $payload = json_encode(array("ERROR" => "El pedido fue cancelado o todavia no fue abonado"));
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(500);
            }          
        }        
    }

    private static function ValidateScores($score) {
        return $score > 0 && $score < 11;
    }

   

}
