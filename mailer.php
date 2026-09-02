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
 * @param array $servicos Lista de serviços [ ['nome' => ..., 'preco' => ...], ... ]
 */
function sendAutoReply(
    string $toEmail,
    string $toName,
    array $servicos = [],
    string $lang = 'pt'
): bool {
    try {
        $mail = getMailer();
        $mail->addAddress($toEmail, $toName);

        $mail->Subject = 'Recebemos o seu pedido de agendamento - Centro Médico Santa Victória';

        $linhasServico = [];
        if (!empty($servicos)) {
            $linhasServico[] = 'Recebemos o seu pedido de agendamento para os seguintes serviços:';
            $total = 0;
            foreach ($servicos as $s) {
                $preco = (float)$s['preco'];
                $total += $preco;
                $linhasServico[] = ' - ' . $s['nome'] . ': ' . formatarPrecoMT($preco);
            }
            $linhasServico[] = '';
            $linhasServico[] = 'Valor total estimado: ' . formatarPrecoMT($total);
        } else {
            $linhasServico[] = 'Recebemos o seu pedido de agendamento e vamos analisá-lo com atenção.';
        }

        $body_lines = array_merge(
            [
                'Olá ' . $toName . ',',
                '',
            ],
            $linhasServico,
            [
                '',
                'A nossa equipe vai confirmar a disponibilidade e entrará em contacto consigo brevemente,',
                'por telefone ou e-mail, para combinar a data e hora exatas da sua consulta.',
                '',
                'Se precisar de algo urgente ou quiser falar connosco, use os contactos abaixo:',
                'Telefone: +258 87 000 0345 ou +258 85 282 4765',
                'Obrigado pela confiança em escolher o Centro Médico Santa Victória.',
                'Cuidamos de si.',
                '',
                'Com os melhores cumprimentos,',
                'Equipa do Centro Médico Santa Victória',
            ]
        );

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
    array $servicos,
    string $nomeCliente,
    string $emailCliente,
    string $telefone,
    ?string $dataPreferencial,
    ?string $mensagem
): bool {
    try {
        $mail = getMailer();
        addRecipientsFromEnv($mail);

        $mail->Subject = 'Nova marcação de consulta recebida';

$textoServicos = 'Nenhum indicado';
        $textoTotal = null;

        if (!empty($servicos)) {
            $listaStr = [];
            $total = 0;
            foreach ($servicos as $s) {
                $preco = (float)$s['preco'];
                $total += $preco;
                $listaStr[] = $s['nome'] . ' (' . formatarPrecoMT($preco) . ')';
            }
            $textoServicos = implode(', ', $listaStr);
            $textoTotal = "Total estimado: " . formatarPrecoMT($total);
        }

        $body_lines = [
            "Resumo do Agendamento:",
            '',
            "Nome: {$nomeCliente}",
            "E-mail: {$emailCliente}",
            "Telefone: " . ($telefone !== '' ? $telefone : 'não indicado'),
            "Data preferencial: " . ($dataPreferencial ?: 'não indicada'),
            "Mensagem: " . ($mensagem ?: '-'),
            "Serviços solicitados: {$textoServicos}",
        ];

        // Se houver total calculado, adiciona na linha seguinte
        if ($textoTotal !== null) {
            $body_lines[] = $textoTotal;
        }

        $mail->Body = implode("\r\n", $body_lines);
        $mail->send();

        return true;

    } catch (\Throwable $exception) {
        error_log('Falha ao notificar a clínica: ' . $exception->getMessage());
        return false;
    }
}