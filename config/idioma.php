<?php
// config/idioma.php
// Deteta, guarda em cookie e disponibiliza o idioma ativo do visitante (pt/en/...),
// e fornece a função central para carregar tratamentos já traduzidos, com fallback para PT.

$idiomasSuportados = ['pt', 'en']; // acrescenta aqui um novo código (ex: 'fr') quando quiseres estender

if (isset($_GET['lang']) && in_array($_GET['lang'], $idiomasSuportados, true)) {
    setcookie('idioma_site', $_GET['lang'], time() + 60 * 60 * 24 * 365, '/');
    $_COOKIE['idioma_site'] = $_GET['lang']; // já disponível neste próprio pedido
}

$idioma = $_COOKIE['idioma_site'] ?? 'pt';
if (!in_array($idioma, $idiomasSuportados, true)) {
    $idioma = 'pt'; // protege contra cookie adulterado/inválido
}

/**
 * Devolve os tratamentos ativos, traduzidos para o idioma pedido.
 * Quando não existe tradução para esse idioma, cai automaticamente para PT.
 *
 * @param PDO|null $pdo
 * @param string   $idioma        código do idioma ativo (ex: 'pt', 'en')
 * @param array    $colunasExtra  colunas de `tratamentos` (não traduzíveis) a incluir, ex: ['preco']
 * @return array
 */
function buscarTratamentosTraduzidos(
    ?PDO $pdo,
    string $idioma,
    array $colunasExtra = ['preco'],
    string $ordenarPor = 'nome',
    string $direcao = 'ASC',
    ?int $limite = null
): array
{
    if (!$pdo) {
        return [];
    }

    $colunasPermitidas = ['preco', 'ativo', 'criado_em'];
    $colunasSql = '';
    foreach ($colunasExtra as $coluna) {
        if (in_array($coluna, $colunasPermitidas, true)) {
            $colunasSql .= 't.' . $coluna . ', ';
        }
    }

    // Whitelist de ordenação: nunca insere $ordenarPor/$direcao directamente no SQL
    $ordenacaoPermitida = ['nome' => 'nome', 'preco' => 't.preco', 'criado_em' => 't.criado_em'];
    $colunaOrdenacao = $ordenacaoPermitida[$ordenarPor] ?? 'nome';
    $direcao = strtoupper($direcao) === 'DESC' ? 'DESC' : 'ASC';

    $sql = "SELECT
                t.id_tratamento,
                {$colunasSql}
                COALESCE(tr_idioma.nome, tr_pt.nome) AS nome,
                COALESCE(tr_idioma.descricao, tr_pt.descricao) AS descricao
            FROM tratamentos t
            LEFT JOIN tratamentos_traducoes tr_idioma
                ON tr_idioma.id_tratamento = t.id_tratamento AND tr_idioma.idioma = :idioma
            LEFT JOIN tratamentos_traducoes tr_pt
                ON tr_pt.id_tratamento = t.id_tratamento AND tr_pt.idioma = 'pt'
            WHERE t.ativo = 1
            ORDER BY {$colunaOrdenacao} {$direcao}";

    if ($limite !== null) {
        $sql .= ' LIMIT ' . (int)$limite;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['idioma' => $idioma]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao carregar tratamentos traduzidos: ' . $e->getMessage());
        return [];
    }
}

/**
 * Devolve nome/preço traduzidos de um conjunto específico de tratamentos (por ID),
 * independentemente do estado 'ativo' — usado para reconstruir o resumo de um
 * agendamento já submetido (ex: nos e-mails de confirmação/notificação).
 * Mesmo fallback para PT que buscarTratamentosTraduzidos().
 *
 * @param int[] $idsTratamentos
 */
function buscarTratamentosPorIds(?PDO $pdo, string $idioma, array $idsTratamentos): array
{
    if (!$pdo || empty($idsTratamentos)) {
        return [];
    }

    $idsTratamentos = array_map('intval', $idsTratamentos);
    $marcadores = implode(',', array_fill(0, count($idsTratamentos), '?'));

    $sql = "SELECT
                t.id_tratamento,
                t.preco,
                COALESCE(tr_idioma.nome, tr_pt.nome) AS nome,
                COALESCE(tr_idioma.descricao, tr_pt.descricao) AS descricao
            FROM tratamentos t
            LEFT JOIN tratamentos_traducoes tr_idioma
                ON tr_idioma.id_tratamento = t.id_tratamento AND tr_idioma.idioma = ?
            LEFT JOIN tratamentos_traducoes tr_pt
                ON tr_pt.id_tratamento = t.id_tratamento AND tr_pt.idioma = 'pt'
            WHERE t.id_tratamento IN ($marcadores)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge([$idioma], $idsTratamentos));
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao carregar tratamentos por ID: ' . $e->getMessage());
        return [];
    }
}

/**
 * Textos fixos da UI (i18n).
 * Carrega o ficheiro do idioma ativo; se não existir, cai para PT.
 * Novo idioma = criar /lang/xx.php com as mesmas chaves, nada mais muda aqui.
 */
$caminhoTraducao = __DIR__ . '/../lang/' . $idioma . '.php';
$traducoes = file_exists($caminhoTraducao) ? require $caminhoTraducao : require __DIR__ . '/../lang/pt.php';

/**
 * Devolve o texto traduzido para a chave dada, no idioma ativo.
 * Se a chave não existir na tradução, devolve a própria chave (facilita detectar o que falta traduzir).
 */
function t(string $chave): string
{
    global $traducoes;
    return $traducoes[$chave] ?? $chave;
}