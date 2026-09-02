<?php
//mailer.php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;

function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;

    $mail->SMTPAuth = SMTP_AUTH;

    if (SMTP_AUTH) {
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
    }

    $mail->SMTPSecure = SMTP_SECURE !== '' ? SMTP_SECURE : false;

    if ($mail->SMTPSecure === false) {
        $mail->SMTPAutoTLS = false;
    }

    $mail->CharSet = 'UTF-8';
    $mail->Timeout = MAIL_TIMEOUT;
    $mail->SMTPDebug = MAIL_DEBUG;

    $mail->Debugoutput = function (string $str, int $level): void {
        error_log("PHPMailer [$level]: $str");
    };

    $mail->setFrom(FROM_EMAIL, FROM_NAME);

    $mail->isHTML(false);

    return $mail;
}

/**
 * Formata um valor numérico como preço em Metical (MT), no padrão pt-MZ.
 * Ex: 2500.5 -> "2.500,50 MT"
 */
function formatarPrecoMT(float $valor): string
{
    return number_format($valor, 2, ',', '.') . ' MT';
}

function addRecipientsFromEnv(PHPMailer $mail, string $default = TO_EMAIL): void
{
    $emails = array_filter(array_map('trim', explode(',', $default)));

    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($email);
        }
    }
}

/**
 * Envia o e-mail de auto-resposta ao cliente após um pedido de agendamento.
 *
 * NOTA (i18n futuro):
 * Por enquanto os textos estão fixos em português, diretamente no código.
 * Quando evoluirmos para suporte bilíngue (pt/en), estes textos devem voltar
 * a ser carregados via includes/i18n.php + loadTranslations($lang), tal como
 * estava inicialmente previsto. O parâmetro $lang já fica aqui pronto para
 * essa altura, mas não é usado por agora.
 *
 * @param float|null $servicePrice Preço do tratamento (coluna `preco` da tabela `tratamentos`).
 *                                 Passe null quando não houver tratamento selecionado.
 */
function sendAutoReply(
    string $toEmail,
    string $toName,
    string $serviceType = '',
    ?float $servicePrice = null,
    string $lang = 'pt'
): bool {
    try {
        $mail = getMailer();
        $mail->addAddress($toEmail, $toName);

        $mail->Subject = 'Recebemos o seu pedido de agendamento - Centro Médico Santa Victória';

        $linhaServico = 'Recebemos o seu pedido de agendamento e vamos analisá-lo com atenção.';
        if ($serviceType !== '') {
            $linhaServico = 'Recebemos o seu pedido de agendamento para o serviço "' . $serviceType . '"';
            $linhaServico .= $servicePrice !== null
                ? ' (' . formatarPrecoMT($servicePrice) . ').'
                : '.';
        }

        $body_lines = [
            'Olá ' . $toName . ',',
            '',
            $linhaServico,
            '',
            'A nossa equipa vai confirmar a disponibilidade e entrará em contacto consigo brevemente,',
            'por telefone ou e-mail, para combinar a data e hora exatas da sua consulta.',
            '',
             'Se precisar de algo urgente ou quiser falar connosco, use os contactos abaixo ',
            'Telefone: +258 87 000 0345 ou +258 85 282 4765',
            'Obrigado pela confiança em escolher o Centro Médico Santa Victória.',
            'Cuidamos de si.',
            '',
            'Com os melhores cumprimentos,',
            'Equipa do Centro Médico Santa Victória',
        ];

        $mail->Body = implode("\r\n", $body_lines);
        $mail->send();

        return true;

    } catch (\Throwable $exception) {
        error_log('Falha ao enviar auto-resposta: ' . $exception->getMessage());
        return false;
    }
}

/**
 * Envia a notificação interna à clínica sobre um novo pedido de agendamento.
 * Usa TO_EMAIL (config.php) como lista de destinatários — pode conter vários
 * e-mails separados por vírgula, graças a addRecipientsFromEnv().
 */
function sendClinicNotification(
    int $idAgendamento,
    string $nomeCliente,
    string $emailCliente,
    string $telefone,
    ?string $dataPreferencial,
    ?string $mensagem
): bool {
    try {
        $mail = getMailer();
        addRecipientsFromEnv($mail);

        $mail->Subject = 'Novo pedido de agendamento recebido';

        $body_lines = [
            "Novo agendamento (#{$idAgendamento}):",
            '',
            "Nome: {$nomeCliente}",
            "E-mail: {$emailCliente}",
            "Telefone: " . ($telefone !== '' ? $telefone : 'não indicado'),
            "Data preferencial: " . ($dataPreferencial ?: 'não indicada'),
            "Mensagem: " . ($mensagem ?: '-'),
        ];

        $mail->Body = implode("\r\n", $body_lines);
        $mail->send();

        return true;

    } catch (\Throwable $exception) {
        error_log('Falha ao notificar a clínica: ' . $exception->getMessage());
        return false;
    }
}