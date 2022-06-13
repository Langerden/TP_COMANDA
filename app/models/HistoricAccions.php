<?php

//namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class HistoricAccions
{
    public $id;
    public $accion;
    public $createdAt;

    public static function CreateRegistry($userId, $accion) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO historic_accions (user_id, accion, createdAt) VALUES (:user_id, :accion, :createdAt)");
            $consulta->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $consulta->bindValue(':accion', $accion, PDO::PARAM_STR);
            $consulta->bindValue(':createdAt', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetTimeLogin() {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM historic_accions WHERE accion = 'Login exitoso'");
            $consulta->execute();
            $historicAccions = $consulta->fetchAll(PDO::FETCH_CLASS, "HistoricAccions");
            if (is_null($historicAccions)) {
                throw new Exception("No existen historic accions");
            }
            return $historicAccions;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    // public $primaryKey = 'id';
    // public $table = 'hisotry_actions';

    // public $incrementing = true;
    // public $timestamps = true;

    // const UPDATED_AT = null;
    // const CREATED_AT = 'fecha_creacion';

    // public $fillable = [
    //     'userId', 'accion', 'createdAt'
    // ];

    // public static function CreateRegistry($userId, $accion)
    // {
    //     $registro_acciones = new HistoricAccions();
    //     $registro_acciones->userId = $userId;
    //     $registro_acciones->accion = $accion;
    //     $registro_acciones->createdAt = date('Y-m-d H:i:s');
    //     $registro_acciones->save();        
    // }
}
?>