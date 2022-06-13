<?php


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;


class MWPermisos
{
    public static function VerifyEmployeeOrder(Request $request, RequestHandler $handler)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');
        $parametros = $request->getParsedBody();
        $orderId = $parametros['orderId'];

        try {
            $user = AutentificadorJWT::GetTokenData($jwtHeader);

            $order = Order::GetOrderById($orderId);

            if ($user->id != $order->userId || $order == null) {
                throw new Exception("A este usuario no le corresponde el pedido ingresado.");
            }

            $response = $handler->handle($request);

            return $response;
        } catch (Exception $e) {

            $response = new Response();

            $payload = json_encode(array('Error: ' => $e->getMessage()));

            $response->getBody()->write($payload);

            return $response
            ->withHeader('Content-Type', 'application/json');;
        }
    }

    public static function VerifyIsSocio(Request $request, RequestHandler $handler) {
        $jwtHeader = $request->getHeaderLine('Authorization');
        $response = new Response();

        try {
            $user = AutentificadorJWT::GetTokenData($jwtHeader);

            if (strtoupper($user->rol) == 'SOCIO') {                
                $response = $handler->handle($request);
                $response = $response->withStatus( 200 );
            } else {                
                throw new Exception("El usuario no es socio.");
            }
        } catch (Exception $e) {         
            $payload = json_encode(array('Error: ' => $e->getMessage()));
            $response->getBody()->write($payload);
            $response = $response->withStatus( 401 );
        }
        return $response->withHeader('Content-Type', 'application/json');;
    }

    public static function VerifyIsBartender(Request $request, RequestHandler $handler) {
        $jwtHeader = $request->getHeaderLine('Authorization');
        $response = new Response();

        try {
            $user = AutentificadorJWT::GetTokenData($jwtHeader);

            if (strtoupper($user->rol) == 'BARTENDER') {                
                $response = $handler->handle($request);
                $response = $response->withStatus( 200 );
            } else {                
                throw new Exception("El usuario no es bartender.");
            }
        } catch (Exception $e) {         
            $payload = json_encode(array('Error: ' => $e->getMessage()));
            $response->getBody()->write($payload);
            $response = $response->withStatus( 401 );
        }
        return $response->withHeader('Content-Type', 'application/json');;
    }

    public static function VerifyIsChef(Request $request, RequestHandler $handler) {
        $jwtHeader = $request->getHeaderLine('Authorization');
        $response = new Response();

        try {
            $user = AutentificadorJWT::GetTokenData($jwtHeader);

            if (strtoupper($user->rol) == 'CHEF') {                
                $response = $handler->handle($request);
                $response = $response->withStatus( 200 );
            } else {                
                throw new Exception("El usuario no es chef.");
            }
        } catch (Exception $e) {         
            $payload = json_encode(array('Error: ' => $e->getMessage()));
            $response->getBody()->write($payload);
            $response = $response->withStatus( 401 );
        }
        return $response->withHeader('Content-Type', 'application/json');;
    }

    public static function VerifyIsWaitress(Request $request, RequestHandler $handler) {
        $jwtHeader = $request->getHeaderLine('Authorization');        
        $response = new Response();

        try {
            $user = AutentificadorJWT::GetTokenData($jwtHeader);

            if (strtoupper($user->rol) == 'MOZO') {                
                $response = $handler->handle($request);
                $response = $response->withStatus( 200 );
            } else {                
                throw new Exception("El usuario no es mesero.");
            }
        } catch (Exception $e) {         
            $payload = json_encode(array('Error: ' => $e->getMessage()));
            $response->getBody()->write($payload);
            $response = $response->withStatus( 401 );
        }
        return $response->withHeader('Content-Type', 'application/json');;
    }
}
