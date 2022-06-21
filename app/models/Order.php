<?php

// namespace App\Models;
//require_once './Table.php';

// use Exception;
// use Illuminate\Database\Eloquent\Model;


class Order
{

    public $id;
    public $table_id;
    public $user_id;
    public $product_id;
    public $status;
    public $createdAt;
    public $estimatedTime;
    public $finalPrice;
    public $orderNumber;
    public $picture;
    public $finishAt;

    public static function CreateOrder($tableId, $userId, $productId, $status, $orderNumber, $picture) {        
        try {
            $pictueDB = $picture;
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            if(is_array($picture)) {
                $pictueDB = Order::SavePicture($picture, $orderNumber, $tableId);            
            }
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO orders (table_id, user_id, product_id, status, createdAt, finalPrice, orderNumber, picture) 
            VALUES (:table_id, :user_id, :product_id, :status, NOW(), 0, :orderNumber, :picture)");
            $consulta->bindValue(':table_id', $tableId, PDO::PARAM_INT);
            $consulta->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $consulta->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $consulta->bindValue(':status', $status, PDO::PARAM_STR);
            $consulta->bindValue('orderNumber',$orderNumber , PDO::PARAM_INT);
            $consulta->bindValue(':picture', $pictueDB, PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetAllOrders() {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM orders WHERE status != 'CANCELADO'");
            $consulta->execute();
            $orders = $consulta->fetchAll(PDO::FETCH_CLASS, "Order");
            if (is_null($orders)) {
                throw new Exception("No existen pedidos");
            }
            return $orders;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetLoMasYMenosVendido($orderBy) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT product_id, COUNT(product_id) AS cantidad 
            FROM orders WHERE status != 'CANCELADO' 
            GROUP BY product_id 
            ORDER BY cantidad " . $orderBy . " LIMIT 1");    
            $consulta->execute();
            $product = $consulta->fetchAll(PDO::FETCH_CLASS, "Order");
            if (is_null($product)) {
                throw new Exception("No existen pedidos");
            }
            return $product[0]->product_id;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetOrderById($id) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM orders WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $order = $consulta->fetchObject("Order");
            if (is_null($order)) {
                throw new Exception("No existe el pedido con el id " . $id);
            }
            return $order;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetOrdersByStatus($status) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM orders WHERE status = :status");
            $consulta->bindValue(':status', $status, PDO::PARAM_STR);
            $consulta->execute();
            $orders = $consulta->fetchAll(PDO::FETCH_CLASS, "Order");
            if (is_null($orders)) {
                throw new Exception("No existen pedidos con el status " . $status);
            }
            return $orders;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetOrderByTableNumber($orderNumber, $tableNumber) {
        
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM orders WHERE orderNumber = :orderNumber AND table_id = :table_id");
            $consulta->bindValue(':orderNumber', $orderNumber, PDO::PARAM_INT);
            $consulta->bindValue(':table_id', $tableNumber, PDO::PARAM_INT);
            $consulta->execute();
            $order = $consulta->fetchObject("Order");            
            if (!$order) {                
                throw new Exception("No existe el pedido con la mesa " . $tableNumber);
            } 
            return $order;
        
    }

    public static function GetOrderByOrderNumber($orderNumber) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM orders WHERE orderNumber = :orderNumber");
            $consulta->bindValue(':orderNumber', $orderNumber, PDO::PARAM_STR);
            $consulta->execute();
            $orders = $consulta->fetchAll(PDO::FETCH_CLASS, "Order");
            if (is_null($orders)) {
                throw new Exception("No existe el pedido con el numero de pedido " . $orderNumber);
            }
            return $orders;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetTableNumberMoreAndLessPrice($orderBy){
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT table_id, SUM(finalPrice) AS total 
            FROM orders WHERE status != 'CANCELADO' 
            GROUP BY table_id 
            ORDER BY total " . $orderBy . " LIMIT 1");    
            $consulta->execute();
            $table = $consulta->fetchAll(PDO::FETCH_CLASS, "Order");
            if (is_null($table)) {
                throw new Exception("No existen pedidos");
            }            
            return $table[0]->table_id;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetTableNumberMoreFinalPrice() {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT table_id FROM orders ORDER BY finalPrice DESC LIMIT 1;");
            $consulta->execute();
            $table = $consulta->fetchAll(PDO::FETCH_CLASS, "Order");
            if (is_null($table)) {
                throw new Exception("No existen pedidos");
            }            
            return $table[0]->table_id;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetTableNumberLessFinalPrice() {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT table_id
            FROM orders
            WHERE status != 'CANCELADO' AND finalPrice > 0
            ORDER BY finalPrice ASC LIMIT 1;");
            $consulta->execute();
            $table = $consulta->fetchAll(PDO::FETCH_CLASS, "Order");
            if (is_null($table)) {
                throw new Exception("No existen pedidos");
            }            
            return $table[0]->table_id;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetOrdersBetweenDates($startDate, $endDate) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM orders WHERE finishAt BETWEEN :startDate AND :endDate");
            $consulta->bindValue(':startDate', $startDate, PDO::PARAM_STR);
            $consulta->bindValue(':endDate', $endDate, PDO::PARAM_STR);
            $consulta->execute();
            $tables = $consulta->fetchAll(PDO::FETCH_CLASS, "Order");
            if (is_null($tables)) {
                throw new Exception("No existen pedidos");
            }
            return $tables;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function UpdateUserAndTable($id, $tableId, $userId, $newFileName) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE orders SET table_id = :table_id, user_id = :user_id, picture = :picture WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->bindValue(':table_id', $tableId, PDO::PARAM_INT);
            $consulta->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $consulta->bindValue(':picture', $newFileName, PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function UpdateOrderChef($orderNumber, $status, $estimatedTime) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE orders SET status = :status, estimatedTime = :estimatedTime WHERE orderNumber = :orderNumber");            
            $consulta->bindValue(':orderNumber', $orderNumber, PDO::PARAM_INT);
            $consulta->bindValue(':status', $status, PDO::PARAM_STR);
            $consulta->bindValue(':estimatedTime', $estimatedTime, PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function UpdateOrderWaitress($orderNumber, $status) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE orders SET status = :status,
            finishAt = :finishAt WHERE orderNumber = :orderNumber");
            $consulta->bindValue(':orderNumber', $orderNumber, PDO::PARAM_INT);
            $consulta->bindValue(':status', $status, PDO::PARAM_STR);
            $consulta->bindValue(':finishAt', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $consulta->execute();
    } catch (Exception $e) {
            return $e->getMessage();
        }
    }
 
    public static function SetPrice($orderNumber, $finalPrice) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE orders SET finalPrice = :finalPrice WHERE orderNumber = :orderNumber");
            $consulta->bindValue(':orderNumber', $orderNumber, PDO::PARAM_INT);
            $consulta->bindValue(':finalPrice', $finalPrice, PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function DeleteOrder($id) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE orders SET status = 'CANCELADO' WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetTableWithMoreAndLessOrders($orderBy) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT table_id, COUNT(*) AS orders 
            FROM orders GROUP BY table_id ORDER BY orders ". $orderBy. " LIMIT 1");
            $consulta->execute();
            $table = $consulta->fetchObject("Table");
            if (is_null($table)) {
                throw new Exception("No existen mesas con pedidos");
            }
            
            return $table;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    // protected $primaryKey = 'id';
    // protected $table = 'orders';

    // public $incrementing = true;
    // public $timestamps = true;

    // const UPDATED_AT = null;
    // const CREATED_AT = 'fechaCreacion';

    // protected $fillable = [
    //     'tableId', 'userId', 'productId', 'status', 'estimatedTime', 'createdAt', 'finalPrice', 'orderNumber', "picture"
    // ];


    // // public function products() {
    // //     return $this->belongsToMany('App\Models\Product', 'order_product', 'orderId', 'productId');
    // // }

    // public function ValidStatus($status) {
    //     if($status != "pendiente" && $status != "en preparacion" && $status != "listo para servir"
    //      && $status != "servido" && $status != "cancelado") {
    //         throw new Exception("El status no es valido");
    //     } else {
    //         return true;
    //     }
    // }

    // public static function CreatePedido($tableId, $userId, $productId, $status, $picture) {
    //     try {
    //         $pedido = new Order();
    //         $pedido->tableId = $tableId;
    //         $pedido->userId = $userId;
    //         $pedido->productId = $productId;
    //         if($pedido->ValidStatus($status)) {
    //             $pedido->status = $status;
    //         }
    //         $pedido->estimatedTime = null;     
    //         $pedido->createdAt = date("Y-m-d H:i:s");
    //         $pedido->finalPrice = 0;       
    //         $pedido->orderNumber = rand(10000, 99999);
    //         $pedido->picture = Order::SavePicture($picture, $pedido->orderNumber, $pedido->tableId);
    //         $pedido->save();
            
    //         Table::UpdateTable($tableId, "con cliente esperando pedido");

    //         return $pedido->id;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function GetAllOrders() {
    //     try {
    //         $list = Order::all();
    //         if(count($list) < 1) { throw new Exception("No hay ningun pedido registrado"); }
    //         return $list;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function GetOrderById($id) {
    //     try{
    //         $pedido = Order::find($id);
    //         if(is_null($pedido)) { throw new Exception("No existe el pedido con el id ". $id); }
    //         return $pedido;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function GetOrderByOrderNumber($orderNumber) {
    //     try{
    //         $pedido = Order::where('orderNumber', $orderNumber)->first();
    //         if(is_null($pedido)) { throw new Exception("No existe el pedido con el numero de pedido ". $orderNumber); }
    //         return $pedido;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }
    
    // public static function UpdateUserAndTable($id, $tableId, $userId) {
    //     try {
    //         $pedido = Order::find($id);
    //         if (is_null($pedido)) { throw new Exception("No existe el pedido con el id ". $id); }            
    //         $pedido->tableId = $tableId;
    //         $pedido->userId = $userId;
    //         $pedido->save();
    //         return $pedido->id;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function UpdateOrderChef($id, $status, $estimatedTime) {
    //     try {
    //         $pedido = Order::find($id);
    //         if (is_null($pedido)) { throw new Exception("No existe el pedido con el id ". $id); }
    //         if($pedido->ValidStatus($status)) {
    //             $pedido->status = $status;
    //         }
    //         $pedido->estimatedTime = $estimatedTime;
    //         $pedido->save();
    //         return $pedido->id;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function DeleteOrder($id) {
    //     try {
    //         $pedido = Order::find($id);
    //         if (is_null($pedido)) { throw new Exception("No existe el pedido con el id ". $id); }
    //         $pedido->status = "cancelado";
    //         $pedido->save();
    //         return $pedido->id;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }
    
    public static function SavePicture($file, $orderNumber, $tableId) {
        try {
            $filename = $orderNumber . "_" . $tableId;
            $path = "./images/".$filename.".jpg";
            //$type = explode(".", $file["name"]);
            //$path .= ".".$type[1];

            //SI el directorio no existe, que lo cree
            if (!dir("images/")) {
                mkdir("images/", 0777, true);
            }

            move_uploaded_file($file["tmp_name"], $path);
            return $path;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function FindAndChangePictureName($actualDir, $orderNumber, $tableId) {
        try {
            $newFileName = $orderNumber . "_" . $tableId;
            $newDir = "./images/".$newFileName.".jpg";
            rename($actualDir, $newDir);            
            return $newDir;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

?>