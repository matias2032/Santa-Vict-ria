<!-- contacto.php -->
<?php
require_once __DIR__ . '/config/db.php';
$tituloPagina = 'Contacto';

// Lista de tratamentos para o select do formulário
$tratamentos = [];
if ($pdo) {
    try {
        $tratamentos = $pdo->query("SELECT id_tratamento, nome FROM tratamentos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao carregar tratamentos: ' . $e->getMessage());
    }
}

// Estado vindo do processar_agendamento.php (via redirect com querystring)
$estado = $_GET['estado'] ?? null; // 'sucesso' ou 'erro'
$tratamentoPreSelecionado = isset($_GET['tratamento']) ? (int)$_GET['tratamento'] : null;

require_once __DIR__ . '/includes/header.php';
?>

<section class="secao secao-pagina-topo">
    <div class="container">
        <p class="eyebrow">Contacto</p>
        <h1>Fale connosco</h1>
        <p class="texto-lead" style="max-width:640px">
            Marque a sua consulta ou tire dúvidas. A nossa equipa responde o mais rápido possível.
        </p>
    </div>
</section>

<section class="secao" id="agendamento">
    <div class="container grelha-contacto">
        <div>
            <h2>Informações de contacto</h2>

            <div class="info-contacto-item">
                <h3>Morada</h3>
                <p>Cidade de Tete, Moçambique</p>
            </div>
            <div class="info-contacto-item">
                <h3>Telefone</h3>
                <a href="+258870000345" target="_blank">+258 87 000 0345</a> ou <a href="+258852824765" target="_blank">+258 85 282 4765</a>
            </div>
            <div class="info-contacto-item">
                <h3>E-mail</h3>

                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@stecheng.co.mz&su=Contato%20via%20site" target="_blank">info@cmsv.co.mz</a>
            </div>
            <div class="info-contacto-item">
                <h3>Horário</h3>
                <p>Segunda a Domingo: 07h00 ás 22:00</p>
            </div>
        </div>

        <div>
            <?php if ($estado === 'sucesso'): ?>
                <div class="mensagem-estado mensagem-sucesso">
                    O seu pedido de agendamento foi recebido. Entraremos em contacto para confirmar.
                </div>
            <?php elseif ($estado === 'erro'): ?>
                <div class="mensagem-estado mensagem-erro">
                    Não foi possível enviar o seu pedido. Por favor tente novamente ou contacte-nos por telefone.
                </div>
            <?php endif; ?>

            <form class="formulario" action="processar_agendamento.php" method="POST">
                <div class="campo-linha">
                    <div class="campo">
                        <label for="nome_cliente">Nome completo</label>
                        <input type="text" id="nome_cliente" name="nome_cliente" required>
                    </div>
                    <div class="campo">
                        <label for="telefone_cliente">Telefone</label>
                        <input type="tel" id="telefone_cliente" name="telefone_cliente">
                    </div>
                </div>

                <div class="campo">
                    <label for="email_cliente">E-mail</label>
                    <input type="email" id="email_cliente" name="email_cliente" required>
                </div>

                <div class="campo-linha">
                    <div class="campo">
                        <label for="id_tratamento">Serviço pretendido</label>
                        <select id="id_tratamento" name="id_tratamento">
                            <option value="">Selecione (opcional)</option>
                            <?php foreach ($tratamentos as $tratamento): ?>
                                <option value="<?= (int)$tratamento['id_tratamento'] ?>"
                                    <?= $tratamentoPreSelecionado === (int)$tratamento['id_tratamento'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tratamento['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="data_preferencial">Data preferencial</label>
                        <input type="date" id="data_preferencial" name="data_preferencial">
                    </div>
                </div>

                <div class="campo">
                    <label for="mensagem">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" placeholder="Conte-nos um pouco sobre o que precisa..."></textarea>
                </div>

                <button type="submit" class="botao botao-primario">Enviar pedido de agendamento</button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
