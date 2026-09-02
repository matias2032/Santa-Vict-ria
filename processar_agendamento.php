<?php
require_once __DIR__ . '/config/db.php';

// Só aceita pedidos vindos do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contacto.php');
    exit;
}

$nomeCliente   = trim($_POST['nome_cliente'] ?? '');
$emailCliente  = trim($_POST['email_cliente'] ?? '');
$telefone      = trim($_POST['telefone_cliente'] ?? '');
$idTratamento  = !empty($_POST['id_tratamento']) ? (int)$_POST['id_tratamento'] : null;
$dataPref      = !empty($_POST['data_preferencial']) ? $_POST['data_preferencial'] : null;
$mensagem      = trim($_POST['mensagem'] ?? '');

// Validação simples dos campos obrigatórios
if ($nomeCliente === '' || !filter_var($emailCliente, FILTER_VALIDATE_EMAIL) || !$pdo) {
    header('Location: contacto.php?estado=erro');
    exit;
}

const EMAIL_CLINICA = 'geral@santavictoria.co.mz';

try {
    $pdo->beginTransaction();

    // 1. Grava o agendamento
    $stmt = $pdo->prepare(
        "INSERT INTO agendamentos
            (id_tratamento, nome_cliente, email_cliente, telefone_cliente, data_preferencial, mensagem, status_agendamento)
         VALUES (:id_tratamento, :nome_cliente, :email_cliente, :telefone_cliente, :data_preferencial, :mensagem, 'pendente')"
    );
    $stmt->execute([
        ':id_tratamento'     => $idTratamento,
        ':nome_cliente'      => $nomeCliente,
        ':email_cliente'     => $emailCliente,
        ':telefone_cliente'  => $telefone ?: null,
        ':data_preferencial' => $dataPref,
        ':mensagem'          => $mensagem ?: null,
    ]);

    $idAgendamento = (int)$pdo->lastInsertId();

    $pdo->commit();

    // 2. Envia e-mail de confirmação ao cliente e regista o resultado
    $assuntoCliente = 'Recebemos o seu pedido de agendamento - Centro Médico Santa Victória';
    $corpoCliente = "Olá {$nomeCliente},\n\n"
        . "Recebemos o seu pedido de agendamento e a nossa equipa entrará em contacto brevemente para confirmar.\n\n"
        . "Centro Médico Santa Victória";
    $cabecalhos = "From: Centro Médico Santa Victória <" . EMAIL_CLINICA . ">\r\n";

    $sucessoCliente = @mail($emailCliente, $assuntoCliente, $corpoCliente, $cabecalhos);
    registarEmail($pdo, $idAgendamento, $emailCliente, 'confirmacao_cliente', $assuntoCliente, $sucessoCliente);

    // 3. Notifica a clínica sobre o novo pedido
    $assuntoClinica = 'Novo pedido de agendamento recebido';
    $corpoClinica = "Novo agendamento (#{$idAgendamento}):\n\n"
        . "Nome: {$nomeCliente}\nE-mail: {$emailCliente}\nTelefone: {$telefone}\n"
        . "Data preferencial: " . ($dataPref ?: 'não indicada') . "\nMensagem: " . ($mensagem ?: '-');

    $sucessoClinica = @mail(EMAIL_CLINICA, $assuntoClinica, $corpoClinica, $cabecalhos);
    registarEmail($pdo, $idAgendamento, EMAIL_CLINICA, 'notificacao_clinica', $assuntoClinica, $sucessoClinica);

    header('Location: contacto.php?estado=sucesso');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao gravar agendamento: ' . $e->getMessage());
    header('Location: contacto.php?estado=erro');
    exit;
}

/**
 * Regista, na tabela email_logs, o resultado do envio de um e-mail.
 */
function registarEmail(PDO $pdo, int $idAgendamento, string $destinatario, string $tipo, string $assunto, bool $sucesso): void
{
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO email_logs (id_agendamento, destinatario, tipo_email, assunto, enviado_com_sucesso, mensagem_erro)
             VALUES (:id_agendamento, :destinatario, :tipo_email, :assunto, :sucesso, :erro)"
        );
        $stmt->execute([
            ':id_agendamento' => $idAgendamento,
            ':destinatario'   => $destinatario,
            ':tipo_email'     => $tipo,
            ':assunto'        => $assunto,
            ':sucesso'        => $sucesso ? 1 : 0,
            ':erro'           => $sucesso ? null : 'Falha ao enviar via mail() - verificar configuração SMTP do servidor.',
        ]);
    } catch (PDOException $e) {
        error_log('Erro ao registar log de e-mail: ' . $e->getMessage());
    }
}
