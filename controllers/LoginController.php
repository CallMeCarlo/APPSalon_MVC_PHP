<?php 

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {
        $alertas = [];
        $auth = new Usuario;

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                //Comprobar que existe el usuario
                $usuario = Usuario::where("email", $auth->email);
                
                if($usuario) {
                    //Verificar el password
                    if($usuario->comprobarPasswordAndVerificado($auth->password)) {
                        //Autenticamos al usuario
                        session_start();

                        $_SESSION["id"] = $usuario->id;
                        $_SESSION["nombre"] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION["email"] = $usuario->email;
                        $_SESSION["login"] = true;

                        //Redireccionamiento
                        if($usuario->admin === "1") {
                            $_SESSION["admin"] = $usuario->admin ?? null;

                            header("Location: /admin");
                        } else {
                            header("Location: /cita");
                        }
                        
                    }

                } else {
                    Usuario::setAlerta("error", "El usuario que intenta ingresar no existe");
                }

            }

        }     

        $alertas = Usuario::getAlertas();

        $router->render("/auth/login", [
            "alertas" => $alertas,
            "auth" => $auth
        ]);
    }

    public static function logout() {
        $_SESSION = [];
        header("Location: /");
    }

    public static function olvide(Router $router) {

        $alertas = [];

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                //Comprobar que existe el usuario
                $usuario = Usuario::where("email", $auth->email);
                
                if($usuario and $usuario->confirmado === "1") {

                    //Generar token de un solo uso
                    $usuario->crearToken();
                    $usuario->guardar();

                    //Enviar el email
                    Usuario::setAlerta("exito", "Revisa tu email");
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                } else {
                    Usuario::setAlerta("error", "El usuario que intenta ingresar no existe");
                }

            }

        }

        $alertas = Usuario::getAlertas();

        $router->render("/auth/olvide-password", [
            "alertas" => $alertas
        ]);
    }

    public static function recuperar(Router $router) {

        $alertas = [];
        $error = false;

        $token = s($_GET["token"]); //Obtener el token de la URL
        //debuguear($token);

        //Buscar usuario por su token
        $usuario = Usuario::where("token", $token);

        if(empty($usuario)) {
            //Mostrar mensaje de error
            Usuario::setAlerta("error", "Token No Válido");
            $error = true;

        } 

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            //Leer el nuevo password y guardarlo
            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();

            if(empty($alertas)) {
                $usuario->password = null;
                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();
                if($resultado) {
                    header("Location: /");
                }
                
            }

        }
 
         //Obtener alertas
         $alertas = Usuario::getAlertas();
        

        $router->render("/auth/recuperar-password", [
            "alertas" => $alertas,
            "error" => $error
        ]);
    }

        
    

    public static function crear(Router $router) {

        $usuario = new Usuario();

        //Alertas vacías
        $alertas = [];
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            //Revisar que alertas este vacío
            if(empty($alertas)) {
                //Verificar que el usuario no este registrado
                $resultado = $usuario->existeUsuario();

                if($resultado->num_rows) {
                    $alertas = Usuario::getAlertas();
                } else {
                    //Hashear el password
                    $usuario->hashPassword();

                    //Generar un token unico
                    $usuario->crearToken();

                    //Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);

                    $email->enviarConfirmacion();

                    //Crear el usuario
                    $resultado = $usuario->guardar();
                    if($resultado) {
                        header("Location: /mensaje");
                    }

                    //debuguear($usuario);


                    
                }

            }

        }

        $router->render("/auth/crear-cuenta", [
            "usuario" => $usuario,
            "alertas" => $alertas

        ]);
    }
    public static function mensaje(Router $router) {

        $router->render("auth/mensaje"); 
    }

    public static function confirmar(Router $router) {

        $alertas = [];
        $token = s($_GET["token"]); //Obtener el token de la URL

        $usuario = Usuario::where("token", $token);

        if(empty($usuario)) {
            //Mostrar mensaje de error
            Usuario::setAlerta("error", "Token No Válido");

        } else {
            //Modificar a usuario confirmado
            $usuario->confirmado = "1";
            $usuario->token = null;
            $usuario->guardar();
            Usuario::setAlerta("exito", "Confirmación exitosa");

        }

        //Obtener alertas
        $alertas = Usuario::getAlertas();

        //Renderizar la vista
        $router->render("/auth/confirmar-cuenta", [
            "alertas" => $alertas
        ]);
    }

}