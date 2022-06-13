<?php

// namespace App\Models;

// use Exception;
// use Illuminate\Database\Eloquent\Model;

class Table  {

    public $id;
    public $table_number;
    public $table_status;

    public function ValidStatus($status) {
    if($status != "vacia" && $status != "con cliente esperando pedido" && $status != "con cliente comiendo" && $status != "con cliente pagando"
        && $status != "cerrada") {
        throw new Exception("El status no es valido");
    } else {
        return true;
    }
}

    public static function CreateTable($tableNumber, $tableStatus) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO tables (table_number, table_status) VALUES (:table_number, :table_status)");
            $consulta->bindValue(':table_number', $tableNumber, PDO::PARAM_INT);
            $consulta->bindValue(':table_status', $tableStatus, PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetAllTables() {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tables WHERE table_status != 'DELETED'");
            $consulta->execute();
            $tables = $consulta->fetchAll(PDO::FETCH_CLASS, "Table");
            if (is_null($tables)) {
                throw new Exception("No existen mesas");
            }
            return $tables;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetTableById($id) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tables WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $table = $consulta->fetchObject("Table");
            if (is_null($table)) {
                throw new Exception("No existe la mesa con el id " . $id);
            }
            return $table;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetTableByTableNumber($tableNumber) {
        try {            
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tables WHERE table_number = :table_number");
            $consulta->bindValue(':table_number', $tableNumber, PDO::PARAM_INT);
            $consulta->execute();
            $table = $consulta->fetchObject("Table");
            if (is_null($table)) {
                throw new Exception("No existe la mesa con el numero " . $tableNumber);
            }
            return $table;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function UpdateTable($table_number, $table_status) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE tables SET table_status = :table_status WHERE table_number = :table_number");
            $consulta->bindValue(':table_status', $table_status, PDO::PARAM_STR);
            $consulta->bindValue(':table_number', $table_number, PDO::PARAM_INT);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function DeleteTable($id) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE tables set table_status = 'DELETED' WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    // protected $primaryKey = 'id';
    // protected $table = 'tables';

    // public $incrementing = true;
    // public $timestamps = false;

    // protected $fillable = [
    //     'table_status', 'tableNumber'
    // ];

    // public function ValidStatus($status) {
    //     if($status != "vacia" && $status != "con cliente esperando pedido" && $status != "con cliente comiendo" && $status != "con cliente pagando"
    //         && $status != "cerrada") {
    //         throw new Exception("El status no es valido");
    //     } else {
    //         return true;
    //     }
    // }

    // public static function CreateTable($tableNumber, $table_status) {
    //     try {
    //         $table = new Table();
    //         $table->tableNumber = $tableNumber;
    //         if($table->ValidStatus($table_status)) {
    //             $table->table_status = $table_status;
    //         }
    //         $table->save();
    //         return $table->id;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function GetAllTables() {
    //     try {
    //         $list = Table::all();
    //         if(count($list) < 1) { throw new Exception("No hay mesas"); }
    //         return $list;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function GetTableById($id) {
    //     try{
    //         $mesa = Table::find($id);
    //         if(is_null($mesa)) { throw new Exception("No existe la mesa con el id ". $id); }
    //         return $mesa;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function UpdateTable($id, $table_status) {
    //     try {
    //         $mesa = Table::find($id);
    //         if (is_null($mesa)) {
    //             throw new Exception("No existe la mesa con el id ". $id);
    //         } else {
                
    //             $mesa->table_status = $table_status;
    //             $mesa->save();

    //             return $mesa->id;
    //         }
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function DeleteTable($id) {
    //     try {
    //         $mesa = Table::find($id);
    //         if (is_null($mesa)) {
    //             throw new Exception("No existe la mesa con el id ". $id);
    //         } else {
    //             $mesa->table_status = "DELETED";
    //             $mesa->save();
    //             return $mesa->id;
    //         }
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }
}

?>