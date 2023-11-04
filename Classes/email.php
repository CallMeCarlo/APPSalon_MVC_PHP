<?php 

namespace Classes;
use PHPMailer\PHPMailer\PHPMailer;


class Email {

    public $email;
    public $nombre;
    public $token;

    public function __construct($email, $nombre, $token) {
        
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion() {

        //Crear el objeto de email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV["EMAIL_HOST"];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV["EMAIL_PORT"];
        $mail->Username = $_ENV["EMAIL_USER"];
        $mail->Password = $_ENV["EMAIL_PASS"];

        $mail->setFrom("cuentas@appsalon.com"); //Quien lo envia
        $mail->addAddress("cuentas@appsalon.com", "AppSalon.com"); //A donde se envía
        $mail->Subject = "Confirma tu cuenta";

        //SetHTML
        $mail->isHTML(TRUE);
        $mail->CharSet = "UTF-8";

        $contenido = "<html>";
        $contenido .= "<p><strong> Hola " . $this->nombre . " </strong> Has creado tu cuenta en AppSalon solo debes de confirmarla presionando el siguiente enlace</p>";
        $contenido .= "<p> Presiona Aqui: <a href='" . $_ENV["APP_URL"] . "/confirmar-cuenta?token=" . $this->token . "'>Confirmar Cuenta</a> </p>";
        $contenido .= "<p> Si tu no solicitaste esta cuenta, ignora este correo </p>";
        $contenido .= "</html>";
        $mail->Body = $contenido;

        //Enviar el email
        $mail->send();

    }

    public function enviarInstrucciones() {

        //Crear el objeto de email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV["EMAIL_HOST"];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV["EMAIL_PORT"];
        $mail->Username = $_ENV["EMAIL_USER"];
        $mail->Password = $_ENV["EMAIL_PASS"];

        $mail->setFrom("cuentas@appsalon.com"); //Quien lo envia
        $mail->addAddress("cuentas@appsalon.com", "AppSalon.com"); //A donde se envía
        $mail->Subject = "Reestablece tu contraseña";

        //SetHTML
        $mail->isHTML(TRUE);
        $mail->CharSet = "UTF-8";

        $contenido = "<html>";
        $contenido .= "<p><strong> Hola " . $this->nombre . " </strong> Has solicitado un cambio de contraseña para tu cuenta en AppSalon solo debes de presionar el siguiente enlace</p>";
        $contenido .= "<p> Presiona Aqui: <a href='" . $_ENV["APP_URL"] . "/recuperar?token=" . $this->token . "'>Reestablece tu Contraseña</a> </p>";
        $contenido .= "<p> Si tu no solicitaste reestablecer tu contraseña, ignora este correo </p>";
        $contenido .= "</html>";
        $mail->Body = $contenido;

        //Enviar el email
        $mail->send();
    }

}

