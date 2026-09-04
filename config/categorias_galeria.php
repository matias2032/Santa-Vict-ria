<?php
// config/categorias_galeria.php
// Liga o slug de categoria usado no nome do ficheiro (ex: "actividades--foto.jpg")
// a uma chave de tradução em lang/{idioma}.php. Adiciona uma nova linha aqui sempre
// que criares uma categoria nova; sem entrada aqui, a categoria continua a funcionar
// (aparece com o texto do nome do ficheiro, sem tradução), só não muda por idioma.

const MAPA_CATEGORIAS_GALERIA = [
    'actividades'  => 'galeria.categoria.actividades',
    'instalações'  => 'galeria.categoria.instalacoes',
    'laboratorio'  => 'galeria.categoria.laboratorio',
];

/**
 * Normaliza o slug bruto do nome do ficheiro para comparação (minúsculas, hífens/underscores viram espaço).
 */
function normalizarSlugCategoria(string $bruto): string
{
    return mb_strtolower(trim(str_replace(['-', '_'], ' ', $bruto)));
}

/**
 * Devolve o texto traduzido da categoria a partir do slug do ficheiro.
 * Se não houver mapeamento, cai para o texto capitalizado do próprio slug
 * (comportamento atual) — nunca quebra, só fica sem tradução.
 */
function traduzirCategoriaGaleria(string $slugBruto): string
{
    $normalizado = normalizarSlugCategoria($slugBruto);
    $chave = MAPA_CATEGORIAS_GALERIA[$normalizado] ?? null;

    return $chave !== null ? t($chave) : ucwords($normalizado);
}