<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Administração customizada para Reuniões e Vídeos.
 *
 * @package AGERT
 */

/**
 * Remove o editor padrão e cria páginas próprias de cadastro.
 */
function agert_reuniao_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=reuniao',
        __('Nova Reunião', 'agert'),
        __('Nova Reunião', 'agert'),
        'edit_posts',
        'agert-nova-reuniao',
        'agert_nova_reuniao_page'
    );

    add_submenu_page(
        null,
        __('Adicionar Vídeo de Reunião', 'agert'),
        __('Adicionar Vídeo de Reunião', 'agert'),
        'edit_posts',
        'agert-add-video',
        'agert_add_video_page'
    );

    remove_submenu_page('edit.php?post_type=reuniao', 'post-new.php?post_type=reuniao');
    remove_submenu_page('edit.php?post_type=reuniao', 'post-new.php?post_type=reuniao_video');
}
add_action('admin_menu', 'agert_reuniao_admin_menu', 9);

/**
 * Formulário para criar Reunião.
 */
function agert_nova_reuniao_page() {
    if (!current_user_can('edit_posts')) {
        wp_die(__('Você não tem permissão.', 'agert'));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('agert_criar_reuniao', 'agert_criar_reuniao_nonce')) {
        $title = sanitize_text_field($_POST['titulo'] ?? '');
        if ($title) {
            $post_id = wp_insert_post(array(
                'post_type'   => 'reuniao',
                'post_status' => 'publish',
                'post_title'  => $title,
            ));
            if ($post_id && !is_wp_error($post_id)) {
                agert_save_reuniao_meta_from_request($post_id);
                wp_redirect(admin_url('edit.php?post_type=reuniao'));
                exit;
            }
        }
    }

    $participantes = get_posts(array(
        'post_type'      => 'participante',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));

    $anexos_posts = get_posts(array(
        'post_type'      => 'anexo',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));
    ?>
    <div class="wrap">
        <h1><?php _e('Nova Reunião', 'agert'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('agert_criar_reuniao', 'agert_criar_reuniao_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="titulo"><?php _e('Título *', 'agert'); ?></label></th>
                    <td><input type="text" id="titulo" name="titulo" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th><label for="descricao"><?php _e('Descrição', 'agert'); ?></label></th>
                    <td><textarea id="descricao" name="descricao" rows="4" class="large-text"></textarea></td>
                </tr>
                <tr>
                    <th><label for="pautas"><?php _e('Pautas', 'agert'); ?></label></th>
                    <td><textarea id="pautas" name="pautas" rows="4" class="large-text" placeholder="Uma por linha"></textarea></td>
                </tr>
                <tr>
                    <th><label for="decisoes"><?php _e('Decisões', 'agert'); ?></label></th>
                    <td><textarea id="decisoes" name="decisoes" rows="4" class="large-text" placeholder="Uma por linha"></textarea></td>
                </tr>
                <tr>
                    <th><label for="data_hora"><?php _e('Data e Horário', 'agert'); ?></label></th>
                    <td><input type="datetime-local" id="data_hora" name="data_hora" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="duracao"><?php _e('Duração (min)', 'agert'); ?></label></th>
                    <td><input type="number" id="duracao" name="duracao" class="small-text" min="0" /></td>
                </tr>
                <tr>
                    <th><label for="local"><?php _e('Local', 'agert'); ?></label></th>
                    <td><input type="text" id="local" name="local" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="prestador"><?php _e('Prestador de Serviço', 'agert'); ?></label></th>
                    <td><input type="text" id="prestador" name="prestador" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="participantes"><?php _e('Participantes', 'agert'); ?></label></th>
                    <td>
                        <select id="participantes" name="participantes[]" multiple style="width:300px;height:100px;">
                            <?php foreach ($participantes as $p) : ?>
                                <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Anexos', 'agert'); ?></th>
                    <td>
                        <select id="anexos" name="anexos[]" multiple style="width:300px;height:100px;">
                            <?php foreach ($anexos_posts as $a) : ?>
                                <option value="<?php echo $a->ID; ?>"><?php echo esc_html($a->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Criar Reunião', 'agert')); ?>
        </form>
    </div>
    <?php
}

/**
 * Persiste os metadados da reunião a partir de $_POST.
 */
function agert_save_reuniao_meta_from_request($post_id) {
    $map = array(
        'descricao'    => 'sanitize_textarea_field',
        'data_hora'    => 'sanitize_text_field',
        'duracao'      => 'intval',
        'local'        => 'sanitize_text_field',
        'prestador'    => 'sanitize_text_field',
    );
    foreach ($map as $field => $cb) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $value = call_user_func($cb, $_POST[$field]);
            update_post_meta($post_id, $field, $value);
        } else {
            delete_post_meta($post_id, $field);
        }
    }

    $participantes = isset($_POST['participantes']) ? array_map('intval', (array)$_POST['participantes']) : array();
    update_post_meta($post_id, 'participantes', $participantes);

    $pautas = isset($_POST['pautas']) ? array_filter(array_map('sanitize_text_field', preg_split('/\r\n|\r|\n/', $_POST['pautas']))) : array();
    update_post_meta($post_id, 'pautas', $pautas);

    $decisoes = isset($_POST['decisoes']) ? array_filter(array_map('sanitize_text_field', preg_split('/\r\n|\r|\n/', $_POST['decisoes']))) : array();
    update_post_meta($post_id, 'decisoes', $decisoes);

    $anexos = isset($_POST['anexos']) ? array_map('intval', (array) $_POST['anexos']) : array();
    update_post_meta($post_id, 'anexos', $anexos);
}

/**
 * Meta box para editar reuniões.
 */
function agert_reuniao_meta_boxes() {
    add_meta_box(
        'agert_reuniao_detalhes',
        __('Detalhes da Reunião', 'agert'),
        'agert_reuniao_meta_box',
        'reuniao',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes_reuniao', 'agert_reuniao_meta_boxes');

function agert_reuniao_meta_box($post) {
    wp_nonce_field('agert_reuniao_meta', 'agert_reuniao_meta_nonce');
    $descricao  = get_post_meta($post->ID, 'descricao', true);
    $pautas     = (array) get_post_meta($post->ID, 'pautas', true);
    $decisoes   = (array) get_post_meta($post->ID, 'decisoes', true);
    $data_hora  = get_post_meta($post->ID, 'data_hora', true);
    $duracao    = get_post_meta($post->ID, 'duracao', true);
    $local      = get_post_meta($post->ID, 'local', true);
    $prestador  = get_post_meta($post->ID, 'prestador', true);
    $particip   = (array) get_post_meta($post->ID, 'participantes', true);
    $anexos     = (array) get_post_meta($post->ID, 'anexos', true);

    $participantes = get_posts(array(
        'post_type' => 'participante',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    $anexos_posts = get_posts(array(
        'post_type'      => 'anexo',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ));

    $videos = get_posts(array(
        'post_type' => 'reuniao_video',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => 'reuniao_relacionada',
        'meta_value' => $post->ID,
    ));
    ?>
    <table class="form-table">
        <tr>
            <th><label for="descricao"><?php _e('Descrição', 'agert'); ?></label></th>
            <td><textarea id="descricao" name="descricao" rows="3" class="large-text"><?php echo esc_textarea($descricao); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="pautas"><?php _e('Pautas', 'agert'); ?></label></th>
            <td><textarea id="pautas" name="pautas" rows="3" class="large-text" placeholder="Uma por linha"><?php echo esc_textarea(implode("\n", $pautas)); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="decisoes"><?php _e('Decisões', 'agert'); ?></label></th>
            <td><textarea id="decisoes" name="decisoes" rows="3" class="large-text" placeholder="Uma por linha"><?php echo esc_textarea(implode("\n", $decisoes)); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="data_hora"><?php _e('Data e Horário', 'agert'); ?></label></th>
            <td><input type="datetime-local" id="data_hora" name="data_hora" value="<?php echo esc_attr($data_hora); ?>" /></td>
        </tr>
        <tr>
            <th><label for="duracao"><?php _e('Duração (min)', 'agert'); ?></label></th>
            <td><input type="number" id="duracao" name="duracao" value="<?php echo esc_attr($duracao); ?>" class="small-text" min="0" /></td>
        </tr>
        <tr>
            <th><label for="local"><?php _e('Local', 'agert'); ?></label></th>
            <td><input type="text" id="local" name="local" value="<?php echo esc_attr($local); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="prestador"><?php _e('Prestador', 'agert'); ?></label></th>
            <td><input type="text" id="prestador" name="prestador" value="<?php echo esc_attr($prestador); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="participantes"><?php _e('Participantes', 'agert'); ?></label></th>
            <td>
                <select id="participantes" name="participantes[]" multiple style="width:300px;height:100px;">
                    <?php foreach ($participantes as $p) : ?>
                        <option value="<?php echo $p->ID; ?>" <?php selected(in_array($p->ID, $particip, true)); ?>><?php echo esc_html($p->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php _e('Anexos', 'agert'); ?></th>
            <td>
                <select id="anexos" name="anexos[]" multiple style="width:300px;height:100px;">
                    <?php foreach ($anexos_posts as $a) : ?>
                        <option value="<?php echo $a->ID; ?>" <?php selected(in_array($a->ID, $anexos, true)); ?>><?php echo esc_html($a->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php if ($videos) : ?>
        <tr>
            <th><?php _e('Vídeos', 'agert'); ?></th>
            <td>
                <ul>
                    <?php foreach ($videos as $v) : ?>
                        <li><a href="<?php echo get_edit_post_link($v->ID); ?>"><?php echo esc_html($v->post_title); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <a class="button" href="<?php echo admin_url('admin.php?page=agert-add-video&reuniao_id=' . $post->ID); ?>"><?php _e('Adicionar Vídeo de Reunião', 'agert'); ?></a>
            </td>
        </tr>
        <?php else : ?>
        <tr>
            <th><?php _e('Vídeos', 'agert'); ?></th>
            <td><a class="button" href="<?php echo admin_url('admin.php?page=agert-add-video&reuniao_id=' . $post->ID); ?>"><?php _e('Adicionar Vídeo de Reunião', 'agert'); ?></a></td>
        </tr>
        <?php endif; ?>
    </table>
    <?php
}

function agert_save_reuniao_meta($post_id) {
    if (!isset($_POST['agert_reuniao_meta_nonce']) || !wp_verify_nonce($_POST['agert_reuniao_meta_nonce'], 'agert_reuniao_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    agert_save_reuniao_meta_from_request($post_id);
}
add_action('save_post_reuniao', 'agert_save_reuniao_meta');

/**
 * Form para adicionar vídeo de reunião.
 */
function agert_add_video_page() {
    if (!current_user_can('edit_posts')) {
        wp_die(__('Você não tem permissão.', 'agert'));
    }
    $reuniao_id = isset($_GET['reuniao_id']) ? intval($_GET['reuniao_id']) : 0;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('agert_criar_video', 'agert_criar_video_nonce')) {
        $title = sanitize_text_field($_POST['titulo'] ?? '');
        $reuniao_id = intval($_POST['reuniao_relacionada'] ?? 0);
        if ($title && $reuniao_id) {
            $vid = wp_insert_post(array(
                'post_type'   => 'reuniao_video',
                'post_status' => 'publish',
                'post_title'  => $title,
            ));
            if ($vid && !is_wp_error($vid)) {
                update_post_meta($vid, 'reuniao_relacionada', $reuniao_id);
                update_post_meta($vid, 'video_url', esc_url_raw($_POST['video_url'] ?? ''));
                if (!empty($_POST['duracao_segundos'])) {
                    update_post_meta($vid, 'duracao_segundos', intval($_POST['duracao_segundos']));
                }
                wp_redirect(get_edit_post_link($reuniao_id, '')); // volta para reunião
                exit;
            }
        }
    }
    $reunioes = get_posts(array(
        'post_type' => 'reuniao',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    ?>
    <div class="wrap">
        <h1><?php _e('Adicionar Vídeo de Reunião', 'agert'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('agert_criar_video', 'agert_criar_video_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="titulo"><?php _e('Título *', 'agert'); ?></label></th>
                    <td><input type="text" id="titulo" name="titulo" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th><label for="video_url"><?php _e('URL do Vídeo *', 'agert'); ?></label></th>
                    <td><input type="url" id="video_url" name="video_url" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th><label for="duracao_segundos"><?php _e('Duração (seg)', 'agert'); ?></label></th>
                    <td><input type="number" id="duracao_segundos" name="duracao_segundos" class="small-text" min="0" /></td>
                </tr>
                <tr>
                    <th><label for="reuniao_relacionada"><?php _e('Reunião', 'agert'); ?></label></th>
                    <td>
                        <select id="reuniao_relacionada" name="reuniao_relacionada" required>
                            <option value=""><?php _e('Selecione', 'agert'); ?></option>
                            <?php foreach ($reunioes as $r) : ?>
                                <option value="<?php echo $r->ID; ?>" <?php selected($reuniao_id, $r->ID); ?>><?php echo esc_html($r->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Salvar Vídeo', 'agert')); ?>
        </form>
    </div>
    <?php
}

/**
 * Colunas e filtros na listagem de Reuniões.
 */
function agert_reuniao_columns($columns) {
    $columns['tipo_reuniao'] = __('Tipo', 'agert');
    $columns['modalidade_reuniao'] = __('Modalidade', 'agert');
    $columns['data_hora'] = __('Data/Hora', 'agert');
    $columns['duracao'] = __('Duração', 'agert');
    $columns['local'] = __('Local', 'agert');
    $columns['prestador'] = __('Prestador', 'agert');
    $columns['anexos'] = __('Anexos', 'agert');
    $columns['videos'] = __('Vídeos', 'agert');
    $columns['participantes'] = __('Participantes', 'agert');
    return $columns;
}
add_filter('manage_reuniao_posts_columns', 'agert_reuniao_columns');

function agert_reuniao_columns_content($column, $post_id) {

    switch ($column) {
        case 'data_hora':
            echo esc_html(get_post_meta($post_id, 'data_hora', true));
            break;
        case 'duracao':
            $d = intval(get_post_meta($post_id, 'duracao', true));
            echo $d ? $d . 'min' : '-';
            break;
        case 'local':
            echo esc_html(get_post_meta($post_id, 'local', true));
            break;
        case 'prestador':
            echo esc_html(get_post_meta($post_id, 'prestador', true));
            break;
        case 'anexos':
            $an = get_post_meta($post_id, 'anexos', true);
            echo is_array($an) ? count($an) : 0;
            break;
        case 'videos':
            $v = get_posts(array('post_type' => 'reuniao_video', 'meta_key' => 'reuniao_relacionada', 'meta_value' => $post_id, 'fields' => 'ids'));
            echo is_array($v) ? count($v) : 0;
            break;
        case 'participantes':
            $p = get_post_meta($post_id, 'participantes', true);
            echo is_array($p) ? count($p) : 0;
            break;
    }
}
add_action('manage_reuniao_posts_custom_column', 'agert_reuniao_columns_content', 10, 2);

function agert_reuniao_sortable($columns) {
    $columns['data_hora'] = 'data_hora';
    return $columns;
}
add_filter('manage_edit-reuniao_sortable_columns', 'agert_reuniao_sortable');

function agert_reuniao_orderby($query) {
    if (!is_admin()) return;
    $orderby = $query->get('orderby');
    if ('data_hora' === $orderby) {
        $query->set('meta_key', 'data_hora');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'agert_reuniao_orderby');

function agert_reuniao_filters() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'reuniao') {
        return;
    }
    wp_dropdown_categories(array(
        'show_option_all' => __('Todos os tipos', 'agert'),
        'taxonomy' => 'tipo_reuniao',
        'name' => 'tipo_reuniao',
        'orderby' => 'name',
        'selected' => $_GET['tipo_reuniao'] ?? '',
        'hierarchical' => true,
        'hide_empty' => false,
    ));
    wp_dropdown_categories(array(
        'show_option_all' => __('Todas as modalidades', 'agert'),
        'taxonomy' => 'modalidade_reuniao',
        'name' => 'modalidade_reuniao',
        'orderby' => 'name',
        'selected' => $_GET['modalidade_reuniao'] ?? '',
        'hierarchical' => false,
        'hide_empty' => false,
    ));
    echo '<input type="month" name="mes" value="' . esc_attr($_GET['mes'] ?? '') . '" />';
}
add_action('restrict_manage_posts', 'agert_reuniao_filters');

function agert_reuniao_filter_query($query) {
    if (!is_admin() || $query->get('post_type') !== 'reuniao') {
        return;
    }
    if (!empty($_GET['tipo_reuniao'])) {
        $query->set('tax_query', array(array(
            'taxonomy' => 'tipo_reuniao',
            'field'    => 'term_id',
            'terms'    => intval($_GET['tipo_reuniao'])
        )));
    }
    if (!empty($_GET['modalidade_reuniao'])) {
        $tax = $query->get('tax_query', array());
        $tax[] = array(
            'taxonomy' => 'modalidade_reuniao',
            'field' => 'term_id',
            'terms' => intval($_GET['modalidade_reuniao'])
        );
        $query->set('tax_query', $tax);
    }
    if (!empty($_GET['mes'])) {
        $query->set('meta_query', array(array(
            'key' => 'data_hora',
            'value' => array($_GET['mes'] . '-01', $_GET['mes'] . '-31'),
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        )));
    }
}
add_action('pre_get_posts', 'agert_reuniao_filter_query');

/**
 * Colunas para vídeos de reunião.
 */
function agert_video_columns($cols) {
    $cols['reuniao'] = __('Reunião', 'agert');
    $cols['video_url'] = __('URL', 'agert');
    $cols['duracao'] = __('Duração', 'agert');
    return $cols;
}
add_filter('manage_reuniao_video_posts_columns', 'agert_video_columns');

function agert_video_columns_content($col, $post_id) {
    if ($col === 'reuniao') {
        $rid = get_post_meta($post_id, 'reuniao_relacionada', true);
        if ($rid) {
            $title = get_the_title($rid);
            echo '<a href="' . get_edit_post_link($rid) . '">' . esc_html($title) . '</a>';
        } else {
            echo '-';
        }
    } elseif ($col === 'video_url') {
        $url = get_post_meta($post_id, 'video_url', true);
        echo $url ? '<a href="' . esc_url($url) . '" target="_blank">' . esc_html(parse_url($url, PHP_URL_HOST)) . '</a>' : '-';
    } elseif ($col === 'duracao') {
        $d = intval(get_post_meta($post_id, 'duracao_segundos', true));
        if ($d) {
            $m = floor($d/60); $s = $d%60;
            echo sprintf('%02d:%02d', $m, $s);
        } else {
            echo '-';
        }
    }
}
add_action('manage_reuniao_video_posts_custom_column', 'agert_video_columns_content', 10, 2);

/**
 * Remove vídeos associados ao excluir uma reunião.
 *
 * @param int $post_id ID da reunião.
 */
function agert_delete_reuniao_videos(int $post_id) {
    if (get_post_type($post_id) !== 'reuniao') {
        return;
    }

    $videos = get_posts(array(
        'post_type'      => 'reuniao_video',
        'post_status'    => 'any',
        'meta_key'       => 'reuniao_relacionada',
        'meta_value'     => $post_id,
        'fields'         => 'ids',
        'posts_per_page' => -1,
    ));

    foreach ($videos as $vid) {
        wp_delete_post($vid, true);
    }
}
add_action('before_delete_post', 'agert_delete_reuniao_videos');
add_action('wp_trash_post', 'agert_delete_reuniao_videos');

?>

