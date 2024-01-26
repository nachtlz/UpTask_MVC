<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {

        $alertas = [];

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if(empty($alertas)) {

                $usuario = Usuario::where("email", $auth->email);

                if($usuario && $usuario->confirmado) {

                    if(password_verify($auth->password, $usuario->password)) {

                        session_start();

                        $_SESSION["id"] = $usuario->id;
                        $_SESSION["nombre"] = $usuario->nombre;
                        $_SESSION["email"] = $usuario->email;
                        $_SESSION["login"] = true;
                        
                        header("Location: /dashboard");
                    } else {
                        Usuario::setAlerta("error", "Password incorrecto");
                    }

                } else {
                    Usuario::setAlerta("error", "El usuario no existe o no está confirmado");
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render("auth/login", [
            "titulo" => "Iniciar Sesión",
            "alertas" => $alertas
        ]);
    }

    public static function logout(Router $router) {
        
        session_start();
        $_SESSION = [];
        header("Location: /");
    }

    public static function crear(Router $router) {

        $usuario = new Usuario;
        $alertas = [];

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();
            
            if(empty($alertas)) {

                $existeUsuario = Usuario::where("email", $usuario->email);
                if($existeUsuario) {
                    Usuario::setAlerta("error", "El usuario ya está registrado");
                } else {

                    //Hashear el password
                    $usuario->hashPassword();

                    //Eliminar password2
                    unset($usuario->password2);

                    //Generar token
                    $usuario->crearToken();

                    //Guardar usuario
                    $resultado = $usuario->guardar();

                    //Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    if($resultado) {
                        header("Location: /mensaje");
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render("auth/crear", [
            "titulo" => "Crea tu cuenta",
            "usuario" => $usuario,
            "alertas" => $alertas
        ]);
    }

    public static function olvide(Router $router) {

        $alertas = [];

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {

                $usuario = Usuario::where("email", $usuario->email);

                if($usuario && $usuario->confirmado) {

                    //Generar nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    //Actualizar al usuario
                    $resultado = $usuario->guardar();

                    //Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    //Imprimir alerta
                    Usuario::setAlerta("exito", "Hemos enviado las instrucciones a tu email");

                } else {
                    Usuario::setAlerta("error", "El usuario no existe o no esta confirmado");
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render("auth/olvide", [
            "titulo" => "Olvide mi password",
            "alertas" => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {

        $token = s($_GET["token"]);
        $mostrar = true;

        if(!$token) header("Location: /");

        $usuario = Usuario::where("token", $token);

        if(empty($usuario)) {
            Usuario::setAlerta("error", "Token no válido");
            $mostrar = false;
        }

        if($_SERVER["REQUEST_METHOD"] === "POST") {

            $usuario->sincronizar($_POST);

            //Validar password
            $alertas = $usuario->validarPassword();

            if(empty($alertas)) {

                //Hashear
                $usuario->hashPassword();
                unset($usuario->password2);
                $usuario->token = null;

                //Guardar
                $resultado = $usuario->guardar();

                if($resultado) {
                    header("Location: /");
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render("auth/reestablecer", [
            "titulo" => "Reestablecer password",
            "alertas" => $alertas,
            "mostrar" => $mostrar
        ]);
    }

    public static function mensaje(Router $router) {
        
        $router->render("auth/mensaje", [
            "titulo" => "Cuenta Creada Exitosamente"
        ]);
    }

    public static function confirmar(Router $router) {
        
        $token = s($_GET["token"]);
        if(!$token) header("Location: /");

        //Encontrar al usuario con este token
        $usuario = Usuario::where("token", $token);

        if(empty($usuario)) {
            Usuario::setAlerta("error", "Token no válido");
        } else {
            $usuario->confirmado = 1;
            unset($usuario->password2);
            $usuario->token = null;

            $usuario->guardar();

            Usuario::setAlerta("exito", "Cuenta Comprobada Correctamente");
        }

        $alertas = Usuario::getAlertas();
        $router->render("auth/confirmar", [
            "titulo" => "Confirma tu cuenta UpTask",
            "alertas" => $alertas
        ]);
    }
}