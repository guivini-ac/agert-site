<?php
/**
 * Helpers para página Sobre da ABERT.
 *
 * @package AGERT
 */

// Prevenir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recupera metadados da página "Sobre" com suporte a aliases.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Chave principal.
 * @param mixed  $default Valor padrão.
 *
 * @return mixed
 */
function abert_sobre_meta($post_id, $key, $default = '') {
    $aliases = array(
        'sobre_texto' => array('sobre_descricao'),
        'valores'     => array('nossos_valores'),
        'orgaos'      => array('estrutura_org'),
    );

    $value = agert_meta($post_id, $key, null);
    if ($value !== null && $value !== '' && $value !== array()) {
        return $value;
    }

    if (isset($aliases[$key])) {
        foreach ($aliases[$key] as $alias) {
            $value = agert_meta($post_id, $alias, null);
            if ($value !== null && $value !== '' && $value !== array()) {
                return $value;
            }
        }
    }

    return $default;
}

/**
 * Imprime ícone do Bootstrap Icons.
 *
 * @param string $name  Nome do ícone.
 * @param string $class Classes adicionais.
 * @return void
 */
function abert_icon($name, $class = '') {
    if (empty($name)) {
        return;
    }
    $classes = trim('bi bi-' . sanitize_html_class($name) . ' ' . $class);
    echo '<i class="' . esc_attr($classes) . '" aria-hidden="true"></i>';
}

/**
 * Preenche a página "Sobre" com dados de exemplo se estiver vazia.
 *
 * @param int $page_id ID da página.
 *
 * @return void
 */
function abert_sobre_seed_if_empty(int $page_id): void {
    $has_intro = (bool) agert_meta($page_id, 'sobre_texto', '');
    $has_vals  = agert_meta($page_id, 'valores', array());
    $has_org   = agert_meta($page_id, 'orgaos', array());

    if ($has_intro && !empty($has_vals) && !empty($has_org)) {
        return;
    }

    update_post_meta($page_id, 'sobre_texto',
        'A Associação Brasileira de Emissoras de Rádio e Televisão (ABERT) representa as emissoras de rádio e TV no Brasil, defendendo seus interesses e promovendo o desenvolvimento do setor.');

    update_post_meta($page_id, 'valores', array(
        array('titulo' => 'Liberdade de Expressão', 'descricao' => 'Defesa da liberdade de imprensa.',       'icone' => 'broadcast'),
        array('titulo' => 'Inovação',              'descricao' => 'Promoção de novas tecnologias.',        'icone' => 'cpu'),
        array('titulo' => 'Transparência',         'descricao' => 'Atuação clara e responsável.',          'icone' => 'eye'),
        array('titulo' => 'Representatividade',    'descricao' => 'Valorização das emissoras associadas.', 'icone' => 'people'),
    ));

    update_post_meta($page_id, 'orgaos', array(
        array('titulo' => 'Diretoria',           'descricao' => 'Gestão estratégica da entidade.',            'icone' => 'people'),
        array('titulo' => 'Conselho Consultivo', 'descricao' => 'Apoio e orientação institucional.',          'icone' => 'chat-square-text'),
        array('titulo' => 'Comitês Técnicos',    'descricao' => 'Debate de temas específicos do setor.',     'icone' => 'gear'),
    ));
}

