<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../lib/vendor/autoload.php';
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/HttpHelper.php';

class EmailHelper
{
  private $assunto;
  private $mensagem;
  private $mailer;

  /**
   * EmailHelper constructor.
   * @param string $assunto
   * @param string $mensagem
   */
  public function __construct($assunto, $mensagem)
  {
    $this->assunto        = $assunto;
    $this->mensagem       = $mensagem;
    $this->mailer         = new PHPMailer(true);
  }

  public function addDestinatario($endereco, $nome = '')
  {
    try {
      $this->mailer->addAddress($endereco, $nome);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }
  public function addCC($endereco, $nome = '')
  {
    try {
      $this->mailer->addCC($endereco, $nome);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }
  public function addCCO($endereco, $nome = '')
  {
    try {
      $this->mailer->addBCC($endereco, $nome);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  public function addAnexo($path, $nome = '')
  {
    try {
      $this->mailer->addAttachment($path, $nome);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  public function enviar() {
    $mail = $this->mailer;
    try {
      //Server settings
      $mail->SMTPDebug = Config::EMAIL_DEBUG ? SMTP::DEBUG_CONNECTION : SMTP::DEBUG_OFF;
      $mail->isSMTP();
      $mail->Host       = Config::EMAIL_HOST;
      $mail->SMTPAuth   = true;
      $mail->Username   = Config::EMAIL_LOGIN;
      $mail->Password   = Config::EMAIL_SENHA;
      $mail->SMTPSecure = Config::EMAIL_TLS ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port       = Config::EMAIL_PORTA;

      //Recipients
      $mail->setFrom(Config::EMAIL_LOGIN, Config::EMAIL_NOME);
      $mail->addReplyTo(Config::EMAIL_RESPONDEDOR_ENDERECO, Config::EMAIL_RESPONDEDOR_NOME);

      // Content
      $mail->isHTML(true);
      $mail->Subject = $this->assunto;
      $mail->Body    = $this->mensagem;
      $mail->CharSet = PHPMailer::CHARSET_UTF8;

      $mail->send();
      return true;
    } catch (Exception $e) {
      HttpHelper::erroJson(500, "Não foi possível enviar email", 0, array($mail->ErrorInfo, $e));
      return false;
    }
  }
}