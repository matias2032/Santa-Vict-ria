<?php
require_once __DIR__ . '/config/db.php';
$tituloPagina = 'Serviços';

$tratamentos = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_tratamento, nome, descricao, preco 
                              FROM tratamentos 
                              WHERE ativo = 1 
                              ORDER BY nome ASC");
        $tratamentos = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao carregar tratamentos: ' . $e->getMessage());
    }
}

// Serviço em destaque: usa o primeiro da lista para dar visibilidade extra a um tratamento.
// Podes trocar por outra lógica (ex: um id fixo) assim que tiveres um serviço "estrela".
$servicoDestaque = !empty($tratamentos) ? $tratamentos[0] : null;

require_once __DIR__ . '/includes/header.php';
?>

<section class="secao secao-pagina-topo">
    <div class="container">
        <p class="eyebrow">O que oferecemos</p>
        <h1>Os nossos serviços</h1>
        <p class="texto-lead" style="max-width:640px">
            Especialidades e serviços clínicos pensados para acompanhar a sua saúde em todas as etapas da vida.
        </p>
    </div>
</section>

<?php if ($servicoDestaque): ?>
<section class="secao secao-destaque-servico">
    <div class="container cartao-destaque-servico">
        <div class="cartao-destaque-servico-icone">
            <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2 3 7v6c0 5 3.8 8.7 9 9 5.2-.3 9-4 9-9V7l-9-5z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
        </div>
        <div class="cartao-destaque-servico-texto">
            <p class="cartao-destaque-servico-eyebrow">Serviço em destaque</p>
            <h2><?= htmlspecialchars($servicoDestaque['nome']) ?></h2>
            <p><?= htmlspecialchars($servicoDestaque['descricao'] ?? '') ?></p>
        </div>
        <a href="contacto.php?tratamento=<?= (int)$servicoDestaque['id_tratamento'] ?>#agendamento" class="botao botao-branco">Marcar este serviço</a>
    </div>
</section>
<?php endif; ?>

<section class="secao secao-servicos">
    <div class="container">
        <?php if (empty($tratamentos)): ?>
            <p>Ainda não existem serviços cadastrados. Volte brevemente.</p>
        <?php else: ?>
            <div class="barra-pesquisa-servicos">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text" id="pesquisaServicos" placeholder="Pesquisar por nome ou especialidade..." aria-label="Pesquisar serviços">
            </div>

            <div class="grelha-servicos" id="grelhaServicos">
                <?php foreach ($tratamentos as $tratamento): ?>
                    <article class="cartao-servico"
                             data-nome="<?= htmlspecialchars(mb_strtolower($tratamento['nome'])) ?>"
                             data-descricao="<?= htmlspecialchars(mb_strtolower($tratamento['descricao'] ?? '')) ?>">
                        <h3><?= htmlspecialchars($tratamento['nome']) ?></h3>
                        <p><?= htmlspecialchars($tratamento['descricao'] ?? '') ?></p>
                        <?php if (!empty($tratamento['preco'])): ?>
                            <p class="cartao-servico-preco">Desde <?= number_format((float)$tratamento['preco'], 2, ',', '.') ?> MT</p>
                        <?php endif; ?>
                        <a href="contacto.php?tratamento=<?= (int)$tratamento['id_tratamento'] ?>#agendamento" class="link-seta">Marcar consulta</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <p class="mensagem-sem-resultados" id="mensagemSemResultados" hidden>
                Nenhum serviço encontrado para essa pesquisa.
            </p>
        <?php endif; ?>
    </div>
</section>

<section class="secao secao-faq">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">Dúvidas frequentes</p>
            <h2>Perguntas sobre os nossos serviços</h2>
            <p class="texto-lead">Não encontrou a resposta que procurava? Contacte-nos diretamente.</p>
        </div>

        <div class="lista-faq">
            <details class="item-faq" open>
                <summary>Preciso de marcação prévia para ser atendido?</summary>
                <p>Recomendamos sempre marcar consulta com antecedência, para garantir o horário que melhor se adequa a si. Em casos urgentes, faça-nos contacto direto por telefone.</p>
            </details>
            <details class="item-faq">
                <summary>Os preços apresentados incluem tudo?</summary>
                <p>Os valores indicados referem-se à consulta ou serviço em si. Exames complementares, se necessários, são orçamentados à parte pela nossa equipa.</p>
            </details>
            <details class="item-faq">
                <summary>Posso alterar ou cancelar uma marcação?</summary>
                <p>Sim. Entre em contacto connosco com a maior antecedência possível para reagendarmos sem transtornos.</p>
            </details>
            <details class="item-faq">
                <summary>Atendem crianças e idosos?</summary>
                <p>Sim, temos serviços dedicados a diferentes faixas etárias, incluindo pediatria e acompanhamento geriátrico.</p>
            </details>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
