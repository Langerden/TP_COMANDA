<?php


class Usuario {

    public $id;
    public $username;
    public $user_password;
    public $rol;
    public $area;
    public $user_status;
    public $createdAt;
    public $deletedAt;

    public static function ValidRole($rol) {
        if($rol != "SOCIO" && $rol != "MOZO" && $rol != "CHEF" && $rol != "BARTENDER" ) {
            throw new Exception("El rol no es valido");
        } else {
            return true;
        }
    }

    public static function ValidArea($area) {
        if($area != "BARRA" && $area != "COCINA" && $area != "CANDYBAR") {
            throw new Exception("El area no es valido");
        } else {
            return true;
        }
    }

    public static function CreateUser($username, $password, $rol, $area) {
        
        if($rol != "SOCIO" && Usuario::ValidArea($area)) {
            $area = $area;
        }        else { $area = "SOCIO"; }
        
        if($rol != "SOCIO" && Usuario::ValidArea($area)) {
            $area = $area;
        } else { $area = "SOCIO"; }

        try {            
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (username, user_password, rol, area, user_status, createdAt) 
            VALUES (:username, :user_password, :rol, :area, :user_status, :createdAt)");
            $claveHash = password_hash($password, PASSWORD_DEFAULT);
            $consulta->bindValue(':username', $username, PDO::PARAM_STR);
            $consulta->bindValue(':user_password', $claveHash, PDO::PARAM_STR);
            $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
            $consulta->bindValue(':area', $area, PDO::PARAM_STR);
            $consulta->bindValue(':user_status', 'ACTIVE', PDO::PARAM_STR);
            $consulta->bindValue(':createdAt', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetAllUsers() {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE user_status = 'ACTIVE'");
            $consulta->execute();
            $list = $consulta->fetchAll(PDO::FETCH_CLASS, "Usuario");
            if (count($list) < 1) {
                throw new Exception("No hay ningun usuario registrado");
            }
            return $list;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetUserById($id) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $usuario = $consulta->fetchObject("Usuario");
            if (is_null($usuario)) {
                throw new Exception("No existe el usuario con el id " . $id);
            }
            return $usuario;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetUserByUsername($username) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE username = :username");
            $consulta->bindValue(':username', $username, PDO::PARAM_STR);
            $consulta->execute();
            $usuario = $consulta->fetchObject("Usuario");
            if (is_null($usuario)) {
                throw new Exception("No existe el usuario con el username " . $username);
            }
            return $usuario;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function UpdateUser($id, $area) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET area = :area WHERE id = :id");
            $consulta->bindValue(':area', $area, PDO::PARAM_STR);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function LogicalDelete($id) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET deletedAt = NOW(), user_status = :user_status WHERE id = :id");            
            $consulta->bindValue(':user_status', 'INACTIVE', PDO::PARAM_STR);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);            
            $consulta->execute();
            return $objAccesoDatos->obtenerUltimoId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

}


// namespace App\Models;

// use Exception;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

// class Usuario extends Model
// {
//     use SoftDeletes;

//     public $primaryKey = 'id';
//     public $table = 'users';

//     public $incrementing = true;
//     public $timestamps = false;

//     const DELETED_AT = 'fechaBaja';

//     public $fillable = [
//         'username', 'password', 'rol', 'status', 'dateInit', 'dateEnd', 'area'
//     ];

//     private function ValidRole($rol) {
//         if($rol != "SOCIO" && $rol != "MOZO" && $rol != "CHEF" && $rol != "BARTENDER" ) {
//             throw new Exception("El rol no es valido");
//         } else {
//             return true;
//         }
//     }

//     private function ValidArea($area) {
//         if($area != "BARRA" && $area != "COCINA" && $area != "CANDYBAR") {
//             throw new Exception("El area no es valido");
//         } else {
//             return true;
//         }
//     }

//     public static function CreateUser ($username, $password, $rol, $area) {
//         try {
//             var_dump($username);
//             $user = new Usuario();
//             $user->username = $username;
//             $user->password = $password;
//             if($user->ValidRole($rol)) {
//                 $user->rol = $rol;
//             }            
//             $user->status = "ACTIVE";
//             $user->dateInit = date("Y-m-d H:i:s");             
//             $user->dateEnd = null;
//             if($rol != "SOCIO" && $user->ValidArea($area)) {
//                 $user->area = $area;
//             } else { $user->area = "SOCIO"; }
//             $user->save();
//             return $user->id;
//         } catch (Exception $e) {
//             return $e->getMessage();
//         }
//     }

//     public static function GetAllUsers() {
//         try {
//             $list = Usuario::all();
//             if(count($list) < 1) { throw new Exception("No hay ningun usuario registrado"); }
//             return $list;
//         } catch (Exception $e) {
//             return $e->getMessage();
//         }
//     }

//     public static function GetUserById($id) {
//         try{
//             $user = Usuario::find($id);
//             if(is_null($user)) { throw new Exception("No existe el user con el id ". $id); }
//             return $user;
//         } catch (Exception $e) {
//             return $e->getMessage();
//         }
//     }

//     public static function GetUserByUsername($username) {
//         try{
//             $user = Usuario::where('username', $username)->first();
//             if(is_null($user)) { throw new Exception("No existe el user con el username ". $username); }
//             return $user;
//         } catch (Exception $e) {
//             return $e->getMessage();
//         }
//     }

//     public static function UpdateUser($id, $area) {
//         try {
//             $user = Usuario::find($id);
//             if (is_null($user) || $user->rol == "SOCIO") {
//                 throw new Exception("El Usuario es un SOCIO o no existe el user con el id ". $id);
//             } else {
//                 if($user->ValidArea($area)) {
//                     $user->area = $area;
//                 }
//                 $user->save();

//                 return $user->id;
//             }
//         } catch (Exception $e) {
//             return $e->getMessage();
//         }
//     }

//     public static function LogicalDelete($id) {
//         try {
//             $user = Usuario::find($id);
//             if(is_null($user)) { throw new Exception("No existe el user con el id ". $id); 
//             } else {
//                 $user->status = "FIRED";
//                 $user->dateEnd = date("Y-m-d H:i:s");
//                 $user->save();
//                 return $user->id;
//             }
//         } catch (Exception $e) {
//             return $e->getMessage();
//         }
//     }
// }

?>