<?php
// config/disponibilidade.php
// Regras de disponibilidade por dia da semana para cada tratamento.
// Sem registos em tratamento_dias para um id_tratamento = disponível todos os dias.

/**
 * Mapeamento dia ISO-8601 (1=Segunda ... 7=Domingo) -> chave de tradução do nome completo.
 */
const DIAS_SEMANA_CHAVES = [
    1 => 'dia.segunda',
    2 => 'dia.terca',
    3 => 'dia.quarta',
    4 => 'dia.quinta',
    5 => 'dia.sexta',
    6 => 'dia.sabado',
    7 => 'dia.domingo',
];

/**
 * Mesma ideia, mas para a versão abreviada (usada no formulário, junto ao nome do serviço).
 */
const DIAS_SEMANA_ABREV_CHAVES = [
    1 => 'dia.abrev.segunda',
    2 => 'dia.abrev.terca',
    3 => 'dia.abrev.quarta',
    4 => 'dia.abrev.quinta',
    5 => 'dia.abrev.sexta',
    6 => 'dia.abrev.sabado',
    7 => 'dia.abrev.domingo',
];

/**
 * Devolve, para cada id de tratamento pedido, a lista de dias (1-7) em que está disponível.
 * Um array vazio para um tratamento significa "sem restrição" (disponível todos os dias).
 *
 * @return array<int, int[]>  [ id_tratamento => [dias ISO ordenados] ]
 */
function buscarDiasPorTratamentos(PDO $pdo, array $idTratamentos): array
{
    $idTratamentos = array_values(array_unique(array_map('intval', $idTratamentos)));
    if (empty($idTratamentos)) {
        return [];
    }

    $porTratamento = [];
    foreach ($idTratamentos as $id) {
        $porTratamento[$id] = []; // por omissão: sem restrição
    }

    $placeholders = implode(',', array_fill(0, count($idTratamentos), '?'));
    $stmt = $pdo->prepare(
        "SELECT id_tratamento, dia_semana FROM tratamento_dias WHERE id_tratamento IN ($placeholders) ORDER BY dia_semana ASC"
    );
    $stmt->execute($idTratamentos);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $porTratamento[(int)$linha['id_tratamento']][] = (int)$linha['dia_semana'];
    }

    return $porTratamento;
}

/**
 * Valida se os tratamentos escolhidos podem, todos, ser marcados na data preferida.
 * Se $dataPreferencial estiver vazia, a validação é ignorada (o campo é opcional).
 *
 * @return array{
 *     valido: bool,
 *     diaSemana: int|null,
 *     idsIndisponiveis: int[],
 *     diasComuns: int[]
 * }
 * 'diasComuns' é a interseção dos dias disponíveis entre os tratamentos restritos
 * selecionados — só faz sentido quando 'valido' é false. Se ficar vazio, não existe
 * nenhum dia em que todos os tratamentos restritos coincidam.
 */
function validarDisponibilidadeAgendamento(PDO $pdo, array $idTratamentos, ?string $dataPreferencial): array
{
    $resultado = ['valido' => true, 'diaSemana' => null, 'idsIndisponiveis' => [], 'diasComuns' => []];

    if (empty($idTratamentos) || empty($dataPreferencial)) {
        return $resultado;
    }

    $timestamp = strtotime($dataPreferencial);
    if ($timestamp === false) {
        return $resultado; // datas inválidas são tratadas noutro ponto do formulário
    }

    $diaSemana = (int)date('N', $timestamp);
    $resultado['diaSemana'] = $diaSemana;

    $diasPorTratamento = buscarDiasPorTratamentos($pdo, $idTratamentos);

    $idsIndisponiveis = [];
    $conjuntosRestritos = [];

    foreach ($idTratamentos as $id) {
        $dias = $diasPorTratamento[$id] ?? [];
        if (empty($dias)) {
            continue; // sem restrição: disponível em qualquer dia
        }
        $conjuntosRestritos[] = $dias;
        if (!in_array($diaSemana, $dias, true)) {
            $idsIndisponiveis[] = $id;
        }
    }

    if (empty($idsIndisponiveis)) {
        return $resultado; // válido
    }

    $resultado['valido'] = false;
    $resultado['idsIndisponiveis'] = $idsIndisponiveis;

    if (!empty($conjuntosRestritos)) {
        $intersecao = array_shift($conjuntosRestritos);
        foreach ($conjuntosRestritos as $conjunto) {
            $intersecao = array_intersect($intersecao, $conjunto);
        }
        sort($intersecao);
        $resultado['diasComuns'] = array_values($intersecao);
    }

    return $resultado;
}