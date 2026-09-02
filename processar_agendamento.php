<?php
//processar_agendamento.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/mailer.php';

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

    // Nome e preço do tratamento selecionado (para usar na auto-resposta), se aplicável
    $nomeTratamento = '';
    $precoTratamento = null;
    if ($idTratamento) {
        $stmtTrat = $pdo->prepare("SELECT nome, preco FROM tratamentos WHERE id_tratamento = :id");
        $stmtTrat->execute([':id' => $idTratamento]);
        $tratamentoInfo = $stmtTrat->fetch();
        if ($tratamentoInfo) {
            $nomeTratamento = (string)$tratamentoInfo['nome'];
            $precoTratamento = (float)$tratamentoInfo['preco'];
        }
    }

    // 2. Envia e-mail de confirmação ao cliente via SMTP (PHPMailer) e regista o resultado
    $assuntoCliente = 'Recebemos o seu pedido de agendamento - Centro Médico Santa Victória';
    $erroCliente = null;
    try {
        $sucessoCliente = sendAutoReply($emailCliente, $nomeCliente, $nomeTratamento, $precoTratamento, 'pt');
    } catch (\Throwable $e) {
        $sucessoCliente = false;
        $erroCliente = $e->getMessage();
        error_log('Falha ao enviar confirmação ao cliente: ' . $e->getMessage());
    }
    registarEmail($pdo, $idAgendamento, $emailCliente, 'confirmacao_cliente', $assuntoCliente, $sucessoCliente, $erroCliente);

    // 3. Notifica a clínica sobre o novo pedido, também via SMTP (PHPMailer)
    $assuntoClinica = 'Novo pedido de agendamento recebido';
    $erroClinica = null;
    try {
        $sucessoClinica = sendClinicNotification(
            $idAgendamento,
            $nomeCliente,
            $emailCliente,
            $telefone,
            $dataPref,
            $mensagem
        );
    } catch (\Throwable $e) {
        $sucessoClinica = false;
        $erroClinica = $e->getMessage();
        error_log('Falha ao notificar clínica: ' . $e->getMessage());
    }
    registarEmail($pdo, $idAgendamento, TO_EMAIL, 'notificacao_clinica', $assuntoClinica, $sucessoClinica, $erroClinica);

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
function registarEmail(
    PDO $pdo,
    int $idAgendamento,
    string $destinatario,
    string $tipo,
    string $assunto,
    bool $sucesso,
    ?string $erro = null
): void {
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
            ':erro'           => $sucesso ? null : ($erro ?: 'Falha ao enviar via SMTP - verificar configuração em config.php.'),
        ]);
    } catch (PDOException $e) {
        error_log('Erro ao registar log de e-mail: ' . $e->getMessage());
    }
}