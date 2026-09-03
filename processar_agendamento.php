<?php
//processar_agendamento.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/idioma.php';
require_once __DIR__ . '/config/disponibilidade.php';
require_once __DIR__ . '/mailer.php';

// Só aceita pedidos vindos do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contacto.php');
    exit;
}

$nomeCliente    = trim($_POST['nome_cliente'] ?? '');
$emailCliente   = trim($_POST['email_cliente'] ?? '');
$telefone       = trim($_POST['telefone_cliente'] ?? '');
$idTratamentos  = isset($_POST['id_tratamentos']) && is_array($_POST['id_tratamentos']) 
                  ? array_map('intval', $_POST['id_tratamentos']) 
                  : [];
$dataPref       = !empty($_POST['data_preferencial']) ? $_POST['data_preferencial'] : null;
$mensagem       = trim($_POST['mensagem'] ?? '');

// Validação simples dos campos obrigatórios
if ($nomeCliente === '' || !filter_var($emailCliente, FILTER_VALIDATE_EMAIL) || !$pdo) {
    header('Location: contacto.php?estado=erro');
    exit;
}

// Validação de disponibilidade: os tratamentos escolhidos têm de coincidir com o dia
// da semana da data preferencial (tratamentos sem restrição estão sempre disponíveis).
$resultadoDisponibilidade = validarDisponibilidadeAgendamento($pdo, $idTratamentos, $dataPref);

if (!$resultadoDisponibilidade['valido']) {
    $mensagemIndisponibilidade = construirMensagemIndisponibilidade($pdo, $idioma, $resultadoDisponibilidade);

    $paramsRedirect = [
        'estado' => 'indisponivel',
        'msg'    => $mensagemIndisponibilidade,
    ];
    if ($dataPref) {
        $paramsRedirect['data'] = $dataPref;
    }
    foreach ($idTratamentos as $id) {
        $paramsRedirect['tratamento'][] = $id;
    }

    header('Location: contacto.php?' . http_build_query($paramsRedirect) . '#agendamento');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Grava o agendamento (sem campo id_tratamento único)
    $stmt = $pdo->prepare(
        "INSERT INTO agendamentos
            (nome_cliente, email_cliente, telefone_cliente, data_preferencial, mensagem, status_agendamento)
         VALUES (:nome_cliente, :email_cliente, :telefone_cliente, :data_preferencial, :mensagem, 'pendente')"
    );
    $stmt->execute([
        ':nome_cliente'      => $nomeCliente,
        ':email_cliente'     => $emailCliente,
        ':telefone_cliente'  => $telefone ?: null,
        ':data_preferencial' => $dataPref,
        ':mensagem'          => $mensagem ?: null,
    ]);

    $idAgendamento = (int)$pdo->lastInsertId();

    // 2. Grava a relação com múltiplos tratamentos na tabela intermédia
    if (!empty($idTratamentos)) {
        $stmtRel = $pdo->prepare(
            "INSERT INTO agendamento_tratamentos (id_agendamento, id_tratamento) VALUES (:id_agendamento, :id_tratamento)"
        );
        foreach ($idTratamentos as $idTrat) {
            $stmtRel->execute([
                ':id_agendamento' => $idAgendamento,
                ':id_tratamento'  => $idTrat,
            ]);
        }
    }

    $pdo->commit();

    // Busca dados detalhados (nome + preço) dos serviços selecionados.
    // O e-mail ao cliente sai no idioma que ele escolheu no site ($idioma,
    // detetado em config/idioma.php). A notificação interna da clínica
    // é sempre em português, por isso busca-se uma segunda vez fixa em 'pt'.
    $servicosCliente = buscarTratamentosPorIds($pdo, $idioma, $idTratamentos);
    $servicosClinica = buscarTratamentosPorIds($pdo, 'pt', $idTratamentos);

// 2. Envia e-mail de confirmação ao cliente via SMTP (PHPMailer) e regista o resultado
    $assuntoCliente = t('email.cliente.assunto');
    $erroCliente = null;
    try {
        $sucessoCliente = sendAutoReply($emailCliente, $nomeCliente, $servicosCliente, $idioma);
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
            $servicosClinica,
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

/**
 * Constrói a mensagem (já traduzida, no idioma do visitante) explicando quais
 * serviços não estão disponíveis no dia escolhido, e sugerindo como proceder:
 * um dia comum entre os serviços restritos, se existir, ou marcações separadas.
 */
function construirMensagemIndisponibilidade(PDO $pdo, string $idioma, array $resultado): string
{
    $servicosIndisponiveis = buscarTratamentosPorIds($pdo, $idioma, $resultado['idsIndisponiveis']);
    $nomes = implode(', ', array_column($servicosIndisponiveis, 'nome'));

    $nomeDia = t(DIAS_SEMANA_CHAVES[$resultado['diaSemana']]);

    $partes = [
        sprintf(t('contacto.disponibilidade.indisponivel'), $nomeDia, $nomes),
    ];

    if (!empty($resultado['diasComuns'])) {
        $nomesDias = implode(', ', array_map(
            fn($d) => t(DIAS_SEMANA_CHAVES[$d]),
            $resultado['diasComuns']
        ));
        $partes[] = sprintf(t('contacto.disponibilidade.sugestao_dia_comum'), $nomesDias);
    } else {
        $partes[] = t('contacto.disponibilidade.sugestao_dias_separados');
    }

    return implode(' ', $partes);
}