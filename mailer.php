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
 * Mantido igual em qualquer idioma: a moeda (Metical/MT) nunca é traduzida.
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
 * Carrega o array de traduções diretamente do ficheiro /lang/{$lang}.php,
 * sem depender do estado global $idioma/t() de config/idioma.php.
 * Isto permite que mailer.php seja chamado de forma independente
 * (ex: futuros scripts de reenvio ou cron jobs) sempre com o idioma correto.
 */
function carregarTraducoesEmail(string $lang): array
{
    $lang = in_array($lang, ['pt', 'en'], true) ? $lang : 'pt';
    $arquivo = __DIR__ . "/lang/{$lang}.php";

    if (!file_exists($arquivo)) {
        $arquivo = __DIR__ . '/lang/pt.php';
    }

    return require $arquivo;
}

/**
 * Envia o e-mail de auto-resposta ao cliente após um pedido de agendamento,
 * no idioma escolhido pelo próprio cliente ($lang).
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
        $textos = carregarTraducoesEmail($lang);
        $tt = fn(string $chave) => $textos[$chave] ?? $chave;

        $mail = getMailer();
        $mail->addAddress($toEmail, $toName);

        $mail->Subject = $tt('email.cliente.assunto');

        $linhasServico = [];
        if (!empty($servicos)) {
            $linhasServico[] = $tt('email.cliente.intro_com_servicos');
            $total = 0;
            foreach ($servicos as $s) {
                $preco = (float)$s['preco'];
                $total += $preco;
                $linhasServico[] = ' - ' . $s['nome'] . ': ' . formatarPrecoMT($preco);
            }
            $linhasServico[] = '';
            $linhasServico[] = sprintf($tt('email.cliente.total_estimado'), formatarPrecoMT($total));
        } else {
            $linhasServico[] = $tt('email.cliente.intro_sem_servicos');
        }

        $body_lines = array_merge(
            [
                sprintf($tt('email.cliente.saudacao'), $toName),
                '',
            ],
            $linhasServico,
            [
                '',
                $tt('email.cliente.confirmacao_1'),
                $tt('email.cliente.confirmacao_2'),
                '',
                $tt('email.cliente.texto_urgente'),
                $tt('email.cliente.texto_telefone'),
                $tt('email.cliente.texto_obrigado'),
                $tt('email.cliente.texto_slogan'),
                '',
                $tt('email.cliente.despedida'),
                $tt('email.cliente.assinatura'),
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
 * Sempre em português — a equipa da clínica só lê PT, independentemente
 * do idioma em que o cliente navegou o site ou fez a marcação.
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