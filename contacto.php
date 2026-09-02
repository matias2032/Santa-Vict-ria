<!-- contacto.php -->
<?php
require_once __DIR__ . '/config/db.php';
$tituloPagina = 'Contacto';

// Lista de tratamentos para o formulário (inclui preço)
$tratamentos = [];
if ($pdo) {
    try {
        $tratamentos = $pdo->query("SELECT id_tratamento, nome, preco FROM tratamentos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao carregar tratamentos: ' . $e->getMessage());
    }
}

// Estado vindo do processar_agendamento.php (via redirect com querystring)
$estado = $_GET['estado'] ?? null; // 'sucesso' ou 'erro'
$tratamentosPreSelecionados = [];
if (isset($_GET['tratamento'])) {
    $tratamentosPreSelecionados = array_map('intval', (array)$_GET['tratamento']);
}

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
                <div class="mensagem-estado mensagem-sucesso" id="mensagemEstado">
                    O seu pedido de agendamento foi recebido. Entraremos em contacto para confirmar.
                </div>
            <?php elseif ($estado === 'erro'): ?>
                <div class="mensagem-estado mensagem-erro" id="mensagemEstado">
                    Não foi possível enviar o seu pedido. Por favor tente novamente ou contacte-nos por telefone.
                </div>
            <?php endif; ?>

            <?php if ($estado === 'sucesso' || $estado === 'erro'): ?>
            <script>
                (function () {
                    var msg = document.getElementById('mensagemEstado');
                    if (!msg) return;

                    // Ao fim de 3 segundos, inicia um fade suave e depois remove o elemento
                    setTimeout(function () {
                        msg.style.transition = 'opacity 0.4s ease';
                        msg.style.opacity = '0';

                        setTimeout(function () {
                            msg.remove();
                        }, 400);
                    }, 3000);
                })();
            </script>
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

                <div class="campo">
                    <label for="data_preferencial">Data preferencial</label>
                    <input type="date" id="data_preferencial" name="data_preferencial">
                </div>

                <div class="campo">
                    <label for="buscar_servico">Serviços pretendidos (pode escolher mais de um)</label>
                    
                    <!-- Campo de Pesquisa em Tempo Real -->
                    <input type="text" id="buscar_servico" placeholder="Pesquisar serviço..." style="margin-bottom: 10px;">

                    <!-- Contêiner com Rolagem Vertical e Contador -->
                    <div class="caixa-servicos-scroll">
                        <div class="lista-servicos-pesquisa" id="listaServicos">
                            <?php foreach ($tratamentos as $tratamento): ?>
                                <?php $idTrat = (int)$tratamento['id_tratamento']; ?>
                                <label class="item-servico-checkbox" data-nome="<?= mb_strtolower(htmlspecialchars($tratamento['nome'])) ?>">
                                    <input type="checkbox" name="id_tratamentos[]" value="<?= $idTrat ?>"
                                        <?= in_array($idTrat, $tratamentosPreSelecionados, true) ? 'checked' : '' ?>>
                                    <span class="nome-servico"><?= htmlspecialchars($tratamento['nome']) ?></span>
                                    <span class="preco-servico"><?= number_format((float)$tratamento['preco'], 2, ',', '.') ?> MT</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <span class="resumo-selecao" id="resumoSelecao">0 serviço(s) selecionado(s)</span>
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


<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBusca = document.getElementById('buscar_servico');
    const itens = document.querySelectorAll('.item-servico-checkbox');
    const resumo = document.getElementById('resumoSelecao');
    const checkboxes = document.querySelectorAll('.item-servico-checkbox input[type="checkbox"]');

    // 1. Filtro de pesquisa instantâneo
    inputBusca.addEventListener('input', function () {
        const termo = this.value.toLowerCase().trim();
        itens.forEach(item => {
            const nome = item.getAttribute('data-nome');
            if (nome.includes(termo)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // 2. Atualizar contador de selecionados
    function atualizarContador() {
        const selecionados = document.querySelectorAll('.item-servico-checkbox input[type="checkbox"]:checked').length;
        resumo.textContent = selecionados + ' serviço(s) selecionado(s)';
    }

// Reordena itens marcados para o topo ao carregar a página
    const lista = document.getElementById('listaServicos');
    itens.forEach(item => {
        const cb = item.querySelector('input[type="checkbox"]');
        if (cb && cb.checked) {
            lista.prepend(item);
        }
    });

    checkboxes.forEach(cb => cb.addEventListener('change', atualizarContador));
    atualizarContador(); // Inicializa
});
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>