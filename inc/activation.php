<?php
/**
 * Funções executadas na ativação do tema.
 *
 * @package AGERT
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cria páginas padrão se não existirem.
 */
function agert_create_pages() {
    $pages = array(
        'acervo' => array(
            'title'    => 'Acervo',
            'content'  => '<p>Consulte reuniões, anexos e vídeos.</p>',
            'template' => 'page-acervo.php',
        ),
        'sobre-a-agert' => array(
            'title'    => 'Sobre a AGERT',
            'content'  => '<p>Página institucional.</p>',
            'template' => 'page-sobre-agert.php',
        ),
        'presidente' => array(
            'title'    => 'Presidente',
            'content'  => '<p>Conheça o presidente da AGERT.</p>',
            'template' => 'page-presidente.php',
        ),
        'contato' => array(
            'title'    => 'Contato',
            'content'  => '<p>Fale conosco.</p>',
            'template' => 'page-contato.php',
        ),
    );

    foreach ($pages as $slug => $page_data) {
        $page = get_page_by_path($slug);
        if (!$page) {
            $page_id = wp_insert_post(array(
                'post_title'   => $page_data['title'],
                'post_content' => $page_data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_name'    => $slug,
            ));
            if (!is_wp_error($page_id) && isset($page_data['template'])) {
                update_post_meta($page_id, '_wp_page_template', $page_data['template']);
            }
        } else {
            if (isset($page_data['template'])) {
                update_post_meta($page->ID, '_wp_page_template', $page_data['template']);
            }
        }
    }
}

/**
 * Cria menu principal e associa páginas.
 */
function agert_create_menu() {
    $menu_name = 'Menu Principal';
    $menu = wp_get_nav_menu_object($menu_name);

    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
        $order = 1;

        // Home link
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title'   => 'Início',
            'menu-item-url'     => home_url('/'),
            'menu-item-type'    => 'custom',
            'menu-item-status'  => 'publish',
            'menu-item-position'=> $order++,
        ));

        // Pages
        foreach (array('sobre-a-agert', 'presidente', 'acervo', 'contato') as $slug) {
            $page = get_page_by_path($slug);
            if ($page) {
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title'   => $page->post_title,
                    'menu-item-object'  => 'page',
                    'menu-item-object-id' => $page->ID,
                    'menu-item-type'    => 'post_type',
                    'menu-item-status'  => 'publish',
                    'menu-item-position'=> $order++,
                ));
            }
        }

        $locations = get_theme_mod('nav_menu_locations');
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}

/**
 * Hook de ativação do tema.
 */
function agert_seed_demo_data() {
    if (get_posts(array('post_type' => 'reuniao', 'posts_per_page' => 1))) {
        return;
    }

    $meeting_id = wp_insert_post(array(
        'post_title'   => 'Reunião de Exemplo',
        'post_content' => 'Conteúdo de exemplo para testes.',
        'post_type'    => 'reuniao',
        'post_status'  => 'publish',
    ));

    if ($meeting_id) {
        update_post_meta($meeting_id, 'data_hora', gmdate('Y-m-d H:i:s'));
        update_post_meta($meeting_id, 'duracao', 60);
        update_post_meta($meeting_id, 'local', 'Sala de Reuniões');
        update_post_meta($meeting_id, 'resumo', 'Reunião criada automaticamente para testes.');

        $vid = wp_insert_post(array(
            'post_type'   => 'reuniao_video',
            'post_status' => 'publish',
            'post_title'  => 'Vídeo de Exemplo',
        ));
        if ($vid) {
            update_post_meta($vid, 'reuniao_relacionada', $meeting_id);
            update_post_meta($vid, 'video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
            update_post_meta($vid, 'duracao_segundos', 60);
        }

    }
}

function agert_theme_activation() {
    agert_create_pages();
    agert_create_menu();
    agert_seed_demo_data();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'agert_theme_activation');
