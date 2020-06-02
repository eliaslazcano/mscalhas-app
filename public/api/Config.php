<?php


class Config
{
  const APP_NOME = "MS Calhas";
  const EMPRESA_NOME = "MS Calhas";
  const HOSPEDAGEM_URL = "http://mscalhas.com.br";

  //Banco de dados
  const DATABASE_HOST     = "localhost";
  const DATABASE_NAME     = "mscalhas";
  const DATABASE_USER     = "root";
  const DATABASE_PASSWD   = "";
  const DATABASE_TIMEZONE = "-04:00";
  const DATABASE_CHARSET  = "utf8";

  //Global CORS config
  const ALLOW_ORIGIN      = "*";                           //Hosts separados por virgula. * = Qualquer.
  const ALLOW_HEADERS     = "Authorization, Content-Type"; //Headers separados por virgula. * = Qualquer

  //Envio de Email
  const EMAIL_HOST  = "smtp.gmail.com";
  const EMAIL_PORTA = 587;
  const EMAIL_TLS   = true; //false = SSL, true = TLS
  const EMAIL_LOGIN = "sistemamscalhas@gmail.com";
  const EMAIL_SENHA = "djhwgvweohrublpd";
  const EMAIL_NOME  = "MS Calhas: Sistema de Gestão";
  const EMAIL_RESPONDEDOR_ENDERECO  = "mscalhas7125@hotmail.com";
  const EMAIL_RESPONDEDOR_NOME      = "MS Calhas";
  const EMAIL_DEBUG = false;
}