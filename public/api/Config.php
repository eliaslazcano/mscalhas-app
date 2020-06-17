<?php


class Config
{
  const APP_NOME = "MS Calhas";
  const EMPRESA_NOME = "MS Calhas";
  const HOSPEDAGEM_URL = "https://mscalhas.com.br";

  //Banco de dados
  const DATABASE_HOST     = "186.219.220.111";
  const DATABASE_NAME     = "mscalhas";
  const DATABASE_USER     = "mscalhas";
  const DATABASE_PASSWD   = "r1h4ppy2";
  const DATABASE_TIMEZONE = "-04:00";
  const DATABASE_CHARSET  = "utf8";

  //Global CORS config
  const ALLOW_ORIGIN      = "*";                           //Hosts separados por virgula. * = Qualquer.
  const ALLOW_HEADERS     = "Authorization, Content-Type"; //Headers separados por virgula. * = Qualquer

  //Envio de Email
  const EMAIL_HOST  = "smtp.umbler.com";
  const EMAIL_PORTA = 587;
  const EMAIL_TLS   = true; //false = SSL, true = TLS
  const EMAIL_LOGIN = "sistema@mscalhas.com.br";
  const EMAIL_SENHA = "30251154@mate";
  const EMAIL_NOME  = "MS Calhas: Sistema de Gestão";
  const EMAIL_RESPONDEDOR_ENDERECO  = "mscalhas7125@hotmail.com";
  const EMAIL_RESPONDEDOR_NOME      = "MS Calhas";
  const EMAIL_DEBUG = false;
}