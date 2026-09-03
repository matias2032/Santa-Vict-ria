<?php
// config.php
// Configurações de envio de e-mail via SMTP para o PHPMailer (usado em mailer.php)
//
// IMPORTANTE:
// - Nunca coloque este ficheiro num repositório público com as credenciais reais.
// - Idealmente, carregue estes valores de variáveis de ambiente (getenv()) em produção.

define('APP_ENV', 'production');

define('SMTP_HOST', 'mail.stecheng.co.mz');
define('SMTP_PORT', 465);
define('SMTP_AUTH', true);
define('SMTP_SECURE', 'ssl');

define('SMTP_USERNAME', 'matias@stecheng.co.mz');
define('SMTP_PASSWORD', 'Hexo.200632'); // <-- coloque a senha real aqui

define('FROM_EMAIL', 'matias@stecheng.co.mz');
define('FROM_NAME', 'Centro Médico Santa Victória');

define('TO_EMAIL','matias@stecheng.co.mz,info@stecheng.co.mz,compras@stecheng.co.mz');

define('MAIL_DEBUG', 0);
define('MAIL_TIMEOUT', 30);