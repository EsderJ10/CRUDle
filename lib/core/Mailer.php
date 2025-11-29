<?php
/*
 * Clase Mailer simple para enviar correos vía SMTP sin dependencias externas.
 * Utiliza fsockopen para conectar al servidor SMTP.
 * Autor: José Antonio Cortés Ferre
 */

require_once __DIR__ . '/../../config/paths.php';
require_once getPath('config/config.php');

class Mailer {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $timeout = 30;
    private $socket;
    private $debug = false;

    public function __construct() {
        $this->host = SMTP_HOST;
        $this->port = SMTP_PORT;
        $this->user = SMTP_USER;
        $this->pass = SMTP_PASS;
    }

    public function send($to, $subject, $body) {
        try {
            $this->connect();
            $this->auth();
            
            $this->sendCommand("MAIL FROM: <" . SMTP_FROM . ">");
            $this->sendCommand("RCPT TO: <$to>");
            $this->sendCommand("DATA");
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
            $headers .= "To: <$to>\r\n";
            $headers .= "Subject: $subject\r\n";
            
            $this->sendCommand($headers . "\r\n" . $body . "\r\n.");
            $this->sendCommand("QUIT");
            
            fclose($this->socket);
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            if ($this->socket) fclose($this->socket);
            return false;
        }
    }

    private function connect() {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            throw new Exception("Could not connect to SMTP host: $errstr ($errno)");
        }
        $this->getResponse();
        $this->sendCommand("EHLO " . gethostname());
    }

    private function auth() {
        if (!empty($this->user) && !empty($this->pass)) {
            $this->sendCommand("AUTH LOGIN");
            $this->sendCommand(base64_encode($this->user));
            $this->sendCommand(base64_encode($this->pass));
        }
    }

    private function sendCommand($command) {
        fputs($this->socket, $command . "\r\n");
        return $this->getResponse();
    }

    private function getResponse() {
        $response = "";
        while ($str = fgets($this->socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        if ($this->debug) {
            error_log("SMTP: " . $response);
        }
        return $response;
    }
}
?>
