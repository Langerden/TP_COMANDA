<?php

// namespace App\Models;

// use Exception;
// use Illuminate\Database\Eloquent\Model;

class Product 
{
    public $id;
    public $productName;
    public $product_type;
    public $price;
    public $description;

    public static function CreateProduct($productName, $product_type, $price, $description) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO products (productName, product_type, price, description)
        VALUES (:productName, :product_type, :price, :description)");
        $consulta->bindValue(':productName', $productName, PDO::PARAM_STR);
        $consulta->bindValue(':product_type', $product_type, PDO::PARAM_STR);
        $consulta->bindValue(':price', $price, PDO::PARAM_INT);
        $consulta->bindValue(':description', $description, PDO::PARAM_STR);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function GetAllProducts() {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM products WHERE description != 'DELETED'");
            $consulta->execute();
            $products = $consulta->fetchAll(PDO::FETCH_CLASS, "Product");
            if (is_null($products)) {
                throw new Exception("No existen productos");
            }
            return $products;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetProductById($id) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM products WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $product = $consulta->fetchObject("Product");
            if (is_null($product)) {
                throw new Exception("No existe el producto con el id " . $id);
            }
            return $product;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function UpdateProduct($id, $price, $description) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE products SET price = :price, description = :description WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->bindValue(':price', $price, PDO::PARAM_INT);
            $consulta->bindValue(':description', $description, PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function DeleteProduct($id) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE products set description = 'DELETED' WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    // protected $primaryKey = 'id';
    // protected $table = 'products';

    // public $incrementing = true;
    // public $timestamps = false;

    // protected $fillable = [
    //     'productName', 'product_type', 'price', 'description'
    // ];

    // public function ValidProductType($product_type) {
    //     if($product_type != "bebida" && $product_type != "plato" && $product_type != "postre") {
    //         throw new Exception("El tipo de producto no es valido");
    //     } else {
    //         return true;
    //     }
    // }

    // public static function CreateProduct($productName, $product_type, $price, $description)
    // {
    //     try{
    //         $producto = new Product();
    //         $producto->productName = $productName;
    //         if($producto->ValidProductType($product_type)) {
    //             $producto->product_type = $product_type;
    //         }
    //         $producto->price = $price;
    //         $producto->description = $description;
    
    //         $producto->save();
            
    //         return $producto->id;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function GetAllProducts() {
    //     try {
    //         $list = Product::all();
    //         if(count($list) < 1) { throw new Exception("No hay productos"); }
    //         return $list;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function GetProductById($id) {
    //     try{
    //         $producto = Product::find($id);
    //         if(is_null($producto)) { throw new Exception("No existe el producto con el id ". $id); }
    //         return $producto;
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function UpdateProduct($id, $price, $description) {
    //     try {
    //         $producto = Product::find($id);
    //         if (is_null($producto)) { 
    //             throw new Exception("No existe el producto con el id ". $id); 
    //         } else {            
    //         $producto->price = $price;
    //         $producto->description = $description;            
    //         $producto->save();
    //         return $producto->id;
    //         }
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

    // public static function DeleteProduct($id) {
    //     try {
    //         $producto = Product::find($id);
    //         if (is_null($producto)) { 
    //             throw new Exception("No existe el producto con el id ". $id); 
    //         } else {
    //             $producto->description = "YA NO HAY STOCK";
    //             $producto->save();
    //             return $producto->id;
    //         }
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }
    // }

}

?>