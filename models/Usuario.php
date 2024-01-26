<?php

namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = "usuarios";
    protected static $columnasDB = ["id", "nombre", "email", "password", "token", "confirmado"];

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $password2;
    public $token;
    public $confirmado;
    public $password_actual;
    public $password_nuevo;

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->nombre = $args["nombre"] ?? "";
        $this->email = $args["email"] ?? "";
        $this->password = $args["password"] ?? "";
        $this->password2 = $args["password2"] ?? "";
        $this->password_actual = $args["password_actual"] ?? "";
        $this->password_nuevo = $args["password_nuevo"] ?? "";
        $this->token = $args["token"] ?? "";
        $this->confirmado = $args["confirmado"] ?? 0;
    }

    public function validarLogin() {
        if(!$this->email) {
            self::$alertas["error"][] = "El email es obligatorio";
        }

        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas["error"][] = "Email no válido";
        }

        if(!$this->password) {
            self::$alertas["error"][] = "El password del usuario es obligatorio";
        } else {
            if(strlen($this->password) < 6) {
                self::$alertas["error"][] = "El password debe contener al menos 6 carácteres";
            }
        }

        return self::$alertas;
    }

    //Validación para cuentas nuevas
    public function validarNuevaCuenta() {
        if(!$this->nombre) {
            self::$alertas["error"][] = "El nombre del usuario es obligatorio";
        }

        if(!$this->email) {
            self::$alertas["error"][] = "El email del usuario es obligatorio";
        }

        if(!$this->password) {
            self::$alertas["error"][] = "El password del usuario es obligatorio";
        } else {
            if(strlen($this->password) < 6) {
                self::$alertas["error"][] = "El password debe contener al menos 6 carácteres";
            }
        }

        if($this->password !== $this->password2) {
            self::$alertas["error"][] = "Los passwords son diferentes";
        }

        return self::$alertas;
    }

    public function validarEmail() {
        if(!$this->email) {
            self::$alertas["error"][] = "El email es obligatorio";
        }

        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas["error"][] = "Email no válido";
        }

        return self::$alertas;
    }

    public function validarPassword() {
        if(!$this->password) {
            self::$alertas["error"][] = "El password del usuario es obligatorio";
        } else {
            if(strlen($this->password) < 6) {
                self::$alertas["error"][] = "El password debe contener al menos 6 carácteres";
            }
        }

        return self::$alertas;
    }

    public function validarPerfil() {
        if(!$this->nombre) {
            self::$alertas["error"][] = "El nombre del usuario es obligatorio";
        }

        if(!$this->email) {
            self::$alertas["error"][] = "El email es obligatorio";
        }

        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas["error"][] = "Email no válido";
        }

        return self::$alertas;
    }

    public function nuevo_password() {
        if(!$this->password_actual) {
            self::$alertas["error"][] = "El password no puede ir vacío";
        }

        if(!$this->password_nuevo) {
            self::$alertas["error"][] = "El password nuevo no puede ir vacío";
        } else {
            if(strlen($this->password_nuevo) < 6) {
                self::$alertas["error"][] = "El password nuevo debe contener al menos 6 carácteres";
            }
        }

        return self::$alertas;
    }

    public function comprobar_password() {
        return password_verify($this->password_actual, $this->password);
    }

    //Hashea el password
    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    //Generar un token
    public function crearToken() {
        $this->token = uniqid();
    }
}