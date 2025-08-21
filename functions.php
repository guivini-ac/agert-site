<?php
/**
 * AGERT WordPress Theme Functions
 * Tema WordPress puro com Bootstrap 5
 * 
 * @package AGERT
 * @version 1.0.0
 */

// Prevenir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function agert_setup() {
    // Suporte a recursos do tema
    load_theme_textdomain('agert', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('custom-logo', array(
        'height'      => 40,
        'width'       => 40,
        'flex-width'  => true,
        'flex-height' => true,
    ));
    add_theme_support('menus');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');
    
    // Registrar menus
    register_nav_menus(array(
        'primary' => __('Menu Principal', 'agert'),
    ));
    
    // Tamanhos de imagem
    add_image_size('meeting-thumb', 400, 250, true);
    add_image_size('participant-thumb', 150, 150, true);
}
add_action('after_setup_theme', 'agert_setup');

/**
 * Registra sidebar padrão.
 */
function agert_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'agert'),
        'id'            => 'sidebar-1',
        'before_widget' => '<div id="%1$s" class="widget %2$s mb-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="widget-title mb-3">',
        'after_title'   => '</h5>',
    ));
}
add_action('widgets_init', 'agert_widgets_init');

/**
 * Garante que páginas essenciais existam.
 */
function agert_register_core_pages() {
    $pages = array(
        'contato'       => array(
            'title'    => __('Contato', 'agert'),
            'template' => 'page-contato.php',
        ),
        'sobre-a-agert' => array(
            'title'    => __('Sobre a AGERT', 'agert'),
            'template' => 'page-sobre-agert.php',
        ),
        'acervo'        => array(
            'title'    => __('Acervo', 'agert'),
            'template' => 'page-acervo.php',
        ),
        'presidente'    => array(
            'title'    => __('Presidente', 'agert'),
            'template' => 'page-presidente.php',
        ),
    );

    foreach ($pages as $slug => $data) {
        if (null === get_page_by_path($slug)) {
            $page_id = wp_insert_post(array(
                'post_title'   => $data['title'],
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ));

            if (!is_wp_error($page_id) && !empty($data['template'])) {
                update_post_meta($page_id, '_wp_page_template', $data['template']);
            }
        }
    }
}
add_action('init', 'agert_register_core_pages');

/**
 * Enqueue scripts and styles
 */
function agert_scripts() {
    $vendor_dir = get_template_directory() . '/assets/vendor';
    $vendor_uri = get_template_directory_uri() . '/assets/vendor';

    $bootstrap_version = '5.3.0';
    $icons_version     = '1.11.0';

    // Bootstrap CSS
    $bootstrap_css = $vendor_dir . '/bootstrap/bootstrap.min.css';
    if (file_exists($bootstrap_css)) {
        wp_enqueue_style('bootstrap', $vendor_uri . '/bootstrap/bootstrap.min.css', array(), $bootstrap_version);
    } else {
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@' . $bootstrap_version . '/dist/css/bootstrap.min.css', array(), $bootstrap_version);
    }

    // Bootstrap Icons
    $icons_css = $vendor_dir . '/bootstrap-icons/bootstrap-icons.css';
    if (file_exists($icons_css)) {
        wp_enqueue_style('bootstrap-icons', $vendor_uri . '/bootstrap-icons/bootstrap-icons.css', array(), $icons_version);
    } else {
        wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@' . $icons_version . '/font/bootstrap-icons.css', array(), $icons_version);
    }

    // Poppins fonts
    $poppins_css = $vendor_dir . '/poppins/poppins.css';
    if (file_exists($poppins_css)) {
        wp_enqueue_style('agert-fonts', $vendor_uri . '/poppins/poppins.css', array(), null);
    } else {
        wp_enqueue_style('agert-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap', array(), null);
    }

    // Design tokens
    wp_enqueue_style(
        'agert-ui-tokens',
        get_template_directory_uri() . '/assets/css/ui-tokens.css',
        array(),
        wp_get_theme()->get('Version')
    );

    // Theme stylesheet
    wp_enqueue_style(
        'agert-style',
        get_stylesheet_uri(),
        array('bootstrap', 'agert-fonts', 'agert-ui-tokens'),
        wp_get_theme()->get('Version')
    );

    // Header stylesheet
    wp_enqueue_style(
        'theme-header',
        get_template_directory_uri() . '/assets/css/header.css',
        array(),
        '1.0'
    );

    // Acervo stylesheet
    $acervo_css = get_template_directory() . '/assets/css/acervo.css';
    if (file_exists($acervo_css)) {
        wp_enqueue_style(
            'agert-acervo',
            get_template_directory_uri() . '/assets/css/acervo.css',
            array('agert-style'),
            wp_get_theme()->get('Version')
        );
    }

    // Agenda Fiscal stylesheet (apenas no arquivo de planejamento)
    if (is_post_type_archive('agenda_fiscal')) {
        wp_enqueue_style(
            'agert-agenda-fiscal',
            get_template_directory_uri() . '/assets/css/agenda-fiscal.css',
            array('agert-style'),
            wp_get_theme()->get('Version')
        );
    }

    // Presidente stylesheet (apenas na página do Presidente)
    if (is_page_template('page-presidente.php') || is_page('presidente')) {
        wp_enqueue_style(
            'presidente-css',
            get_template_directory_uri() . '/assets/css/presidente.css',
            array(),
            '1.0'
        );
    }

    // Sobre stylesheet (apenas nas páginas "Sobre")
    if (
        is_page_template('page-sobre.php') ||
        is_page_template('page-sobre-abert.php') ||
        is_page(array('sobre-a-agert', 'sobre', 'sobre-a-abert'))
    ) {
        wp_enqueue_style(
            'agert-sobre',
            get_template_directory_uri() . '/assets/css/sobre.css',
            array('agert-style'),
            '1.0'
        );
    }
    
    // Bootstrap JS (sem jQuery)
    $bootstrap_js = $vendor_dir . '/bootstrap/bootstrap.bundle.min.js';
    if (file_exists($bootstrap_js)) {
        wp_enqueue_script('bootstrap', $vendor_uri . '/bootstrap/bootstrap.bundle.min.js', array(), $bootstrap_version, true);
    } else {
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@' . $bootstrap_version . '/dist/js/bootstrap.bundle.min.js', array(), $bootstrap_version, true);
    }
    
    // Theme JS (apenas se necessário)
    $theme_js_path = get_template_directory() . '/assets/js/theme.js';
    if (file_exists($theme_js_path)) {
        wp_enqueue_script(
            'agert-theme',
            get_template_directory_uri() . '/assets/js/theme.js',
            array(),
            wp_get_theme()->get('Version'),
            true
        );

        // Localizar para AJAX
        wp_localize_script('agert-theme', 'agert_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('agert_nonce'),
        ));
    }


    // JS específico das páginas de reuniões
    if (is_post_type_archive('reuniao')) {
        $reunioes_js_path = get_template_directory() . '/assets/js/reunioes.js';
        if (file_exists($reunioes_js_path)) {
            wp_enqueue_script(
                'agert-acervo-js',
                get_template_directory_uri() . '/assets/js/reunioes.js',
                array(),
                wp_get_theme()->get('Version'),
                true
            );
        }
    }

    // Calendar assets for Agenda Fiscal on the front page
    if (is_front_page()) {
        wp_enqueue_style(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css',
            array(),
            '5.11.3'
        );

        wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js',
            array(),
            '5.11.3',
            true
        );

        wp_enqueue_script(
            'fullcalendar-locales',
            'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js',
            array('fullcalendar'),
            '5.11.3',
            true
        );

        wp_enqueue_script(
            'agert-agenda-fiscal',
            get_template_directory_uri() . '/assets/js/agenda-fiscal.js',
            array('fullcalendar'),
            wp_get_theme()->get('Version'),
            true
        );

        $agenda_posts = get_posts(array(
            'post_type'      => 'agenda_fiscal',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ));

        $events = array();
        foreach ($agenda_posts as $event) {
            $events[] = array(
                'title' => $event->post_title,
                'start' => get_post_meta($event->ID, '_inicio', true),
                'end'   => get_post_meta($event->ID, '_fim', true),
                'url'   => esc_url(get_permalink($event->ID)),
                'extendedProps' => array(
                    'prestador'   => get_post_meta($event->ID, '_prestador', true),
                    'atividade'   => get_post_meta($event->ID, '_atividade', true),
                    'modalidade'  => get_post_meta($event->ID, '_modalidade', true),
                    'responsavel' => get_post_meta($event->ID, '_responsavel', true),
                    'objetivo'    => get_post_meta($event->ID, '_objetivo', true),
                ),
            );
        }

        wp_localize_script('agert-agenda-fiscal', 'agertAgendaFiscal', array(
            'events' => $events,
        ));
    }
}
add_action('wp_enqueue_scripts', 'agert_scripts');

/**
 * Incluir arquivos de funcionalidades
 */
require_once get_template_directory() . '/inc/meta-helpers.php';
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/template-functions.php';
require_once get_template_directory() . '/inc/reunioes-helpers.php';
require_once get_template_directory() . '/inc/presidente-helpers.php';
require_once get_template_directory() . '/inc/sobre-helpers.php';
require_once get_template_directory() . '/inc/sobre-abert-helpers.php';
require_once get_template_directory() . '/inc/admin-reunioes.php';
require_once get_template_directory() . '/inc/admin-presidente.php';
require_once get_template_directory() . '/components/html.php';
require_once get_template_directory() . '/inc/security.php';
require_once get_template_directory() . '/inc/activation.php';

/**
 * Registra o template da página Presidente.
 */
add_filter('theme_page_templates', function ($templates) {
    $templates['page-presidente.php'] = __('Presidente', 'agert');
    return $templates;
});

// Incluir AJAX handlers se existir
$ajax_handlers_file = get_template_directory() . '/inc/ajax-handlers.php';
if (file_exists($ajax_handlers_file)) {
    require_once $ajax_handlers_file;
}

/**
 * Força o uso do template "page-sobre.php" para slugs específicos.
 */
add_filter('page_template', function ($template) {
    if (is_page(array('sobre-a-agert', 'sobre'))) {
        $new = locate_template('page-sobre.php');
        if ($new) {
            return $new;
        }
    }
    if (is_page('sobre-a-abert')) {
        $new = locate_template('page-sobre-abert.php');
        if ($new) {
            return $new;
        }
    }
    return $template;
});

/**
 * Destaca o item "Acervo" quando visualizar reuniões.
 */
add_filter('nav_menu_css_class', function ($classes, $item) {
    if (is_page('acervo') || is_post_type_archive('reuniao') || is_singular('reuniao')) {
        $acervo = agert_get_page_link('acervo');
        if (!empty($item->url) && $acervo && trailingslashit($item->url) === trailingslashit($acervo)) {
            $classes[] = 'current-menu-item';
        }
    }
    return $classes;
}, 10, 2);

/**
 * Redireciona o arquivo de reuniões antigo para a página de acervo.
 */
function agert_redirect_reunioes_to_acervo() {
    if (is_post_type_archive('reuniao')) {
        wp_safe_redirect(agert_get_page_link('acervo'), 301);
        exit;
    }
}
add_action('template_redirect', 'agert_redirect_reunioes_to_acervo');

if (!function_exists('agert_menu_fallback')) {
    /**
     * Fallback para o menu principal com links fixos.
     */
    function agert_menu_fallback() {
        $items = [
            [
                'url'        => home_url('/'),
                'label'      => __('Início', 'agert'),
                'is_current' => is_front_page(),
            ],
            [
                'url'        => agert_get_page_link('sobre-a-agert'),
                'label'      => __('Sobre a AGERT', 'agert'),
                'is_current' => is_page(['sobre-a-agert', 'sobre']),
            ],
            [
                'url'        => agert_get_page_link('presidente'),
                'label'      => __('Presidente', 'agert'),
                'is_current' => is_page('presidente'),
            ],
            [
                'url'        => agert_get_page_link('acervo'),
                'label'      => __('Acervo', 'agert'),
                'is_current' => is_page('acervo') || is_post_type_archive('reuniao') || is_singular('reuniao'),
            ],
            [
                'url'        => agert_get_page_link('contato'),
                'label'      => __('Contato', 'agert'),
                'is_current' => is_page('contato'),
            ],
        ];

        echo '<ul class="menu" role="menubar">';
        foreach ($items as $item) {
            if (empty($item['url'])) {
                continue;
            }

            $classes = ['menu-item'];
            $aria_current = '';
            if ($item['is_current']) {
                $classes[]   = 'current-menu-item';
                $aria_current = ' aria-current="page"';
            }

            printf(
                '<li class="%1$s" role="none"><a href="%2$s"%3$s role="menuitem"><span>%4$s</span></a></li>',
                esc_attr(implode(' ', $classes)),
                esc_url($item['url']),
                $aria_current,
                esc_html($item['label'])
            );
        }
        echo '</ul>';
    }
}

/**
 * Customizar excerpts
 */
function agert_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'agert_excerpt_length');

function agert_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'agert_excerpt_more');

/**
 * Limitar uploads para arquivos seguros
 */
function agert_upload_mimes($mimes) {
    $mimes['pdf'] = 'application/pdf';
    return $mimes;
}
add_filter('upload_mimes', 'agert_upload_mimes');

/**
 * Retorna o link de uma página pelo slug ou a URL inicial como fallback.
 *
 * @param string $slug Slug da página desejada.
 * @return string URL da página ou da home.
 */
function agert_get_page_link($slug) {
    $page = get_page_by_path($slug);

    if ($page instanceof WP_Post) {
        return esc_url(get_permalink($page->ID));
    }

    return home_url('/');
}

/**
 * Exibe um botão para voltar à página anterior.
 */
function agert_back_button() {
    $url = esc_url(wp_get_referer() ?: home_url('/'));
    echo '<a href="' . $url . '" onclick="history.back(); return false;" class="btn btn-outline-brand btn-sm me-3" aria-label="' . esc_attr__('Voltar', 'agert') . '" title="' . esc_attr__('Voltar', 'agert') . '"><i class="bi bi-arrow-left"></i> ' . esc_html__('Voltar', 'agert') . '</a>';
}

/**
 * Exibe breadcrumbs simples com a página atual.
 */
function agert_breadcrumb() {
    echo '<nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">';
    echo '<li class="breadcrumb-item"><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'agert') . '</a></li>';
    if (is_page()) {
        echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html(get_the_title()) . '</li>';
    }
    echo '</ol></nav>';
}
