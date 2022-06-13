<?php

require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;

class AutentificadorJWT
{
    private static $secretKey = 'clave-secreta';
    private static $encryptionType = ['HS256'];
    
    public static function CreateToken($data)
    {
        $createdTime = time();
        $payload = array(
            'iat' => $createdTime,
            'exp' => $createdTime + (60*60),            
            'aud' => self::Aud(),
            'data' => $data,
            'app' => "Comanda"
        );

        return JWT::encode($payload, self::$secretKey);
    }
    
    public static function VerifyToken($token)
    {
        if(empty($token))
        {
            throw new Exception("El token esta vacio.");
        }       
      try {
            $decoded = JWT::decode(
            $token,
            self::$secretKey,
            self::$encryptionType
        );
        } catch (Exception $e) {
            throw $e;
        } 
        if($decoded->aud !== self::Aud())
        {
           throw new Exception("No es el usuario valido");
        }
    }
    
   
     public static function GetPayLoad($token)
    {
        if (empty($token)) {
            throw new Exception("The token is empty.");
        }
        return JWT::decode(
            $token,
            self::$secretKey,
            self::$encryptionType
        );
    }

     public static function GetTokenData($token)
    {
        return JWT::decode(
            $token,
            self::$secretKey,
            self::$encryptionType
        )->data;
    }

    private static function Aud()
    {
        $aud = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }
        
        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();
        
        return sha1($aud);
    }
}
