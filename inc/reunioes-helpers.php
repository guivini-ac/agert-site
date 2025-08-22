<?php
/**
 * Helpers específicos das páginas de reuniões.
 *
 * @package AGERT
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Converte minutos em formato "3h 15min".
 */
function agert_minutes_to_human(int $min): string {
    $hours   = intdiv($min, 60);
    $minutes = $min % 60;
    $parts   = array();

    if ($hours > 0) {
        $parts[] = $hours . 'h';
    }
    if ($minutes > 0) {
        $parts[] = $minutes . 'min';
    }

    return implode(' ', $parts);
}

/**
 * Converte segundos para mm:ss ou hh:mm:ss quando necessário.
 */
function agert_seconds_to_mmss(int $sec): string {
    $hours   = floor($sec / 3600);
    $minutes = floor(($sec % 3600) / 60);
    $seconds = $sec % 60;

    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    }

    return sprintf('%02d:%02d', $minutes, $seconds);
}

/**
 * Converte bytes em tamanho legível.
 */
function agert_bytes_to_human(int $bytes): string {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return sprintf('%s %s', round($bytes, $i ? 1 : 0), $units[$i]);
}

/**
 * Detecta a plataforma de vídeo a partir da URL.
 */
function agert_detectar_plataforma_video(string $url): string {
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        return 'youtube';
    }
    if (strpos($url, 'vimeo.com') !== false) {
        return 'vimeo';
    }
    return 'outro';
}

/**
 * Extrai o ID de um vídeo do YouTube.
 */
function agert_extrair_youtube_id(string $url): string {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}

/**
 * Retorna a URL da thumbnail do YouTube.
 */
function agert_thumbnail_youtube(string $id): string {
    return "https://img.youtube.com/vi/{$id}/maxresdefault.jpg";
}

/**
 * Obtém a thumbnail do Vimeo via oEmbed.
 */
function agert_thumbnail_vimeo(string $url): string {
    $response = wp_remote_get('https://vimeo.com/api/oembed.json?url=' . rawurlencode($url));
    if (!is_wp_error($response)) {
        $data = json_decode(wp_remote_retrieve_body($response));
        if (!empty($data->thumbnail_url)) {
            return $data->thumbnail_url;
        }
    }
    return '';
}

/**
 * Retorna o ano de uma string datetime.
 */
function agert_get_year_from_datetime(string $dt): int {
    $time = strtotime($dt);
    return (int) gmdate('Y', $time);
}

/**
 * Conta documentos relacionados à reunião.
 */
function agert_count_documentos(int $post_id): int {
    $docs = get_posts(array(
        'post_type'      => 'anexo',
        'post_status'    => 'publish',
        'meta_key'       => '_reuniao_id',
        'meta_value'     => $post_id,
        'fields'         => 'ids',
        'posts_per_page' => -1,
    ));

    return is_array($docs) ? count($docs) : 0;
}

/**
 * Verifica se reunião possui vídeo (CPT reuniao_video relacionado).
 */
function agert_reuniao_has_video(int $post_id): bool {
    $videos = get_posts(array(
        'post_type'      => 'reuniao_video',
        'post_status'    => 'publish',
        'meta_key'       => 'reuniao_relacionada',
        'meta_value'     => $post_id,
        'fields'         => 'ids',
        'posts_per_page' => 1,
    ));
    return !empty($videos);
}

/**
 * Obtém dados do primeiro vídeo associado à reunião.
 *
 * @param int $post_id ID da reunião.
 * @return array{url:string, duration:int, thumb:string}|array
 */
if (!function_exists('agert_get_reuniao_video_data')) {
function agert_get_reuniao_video_data(int $post_id): array {
    $videos = get_posts(array(
        'post_type'      => 'reuniao_video',
        'post_status'    => 'publish',
        'meta_key'       => 'reuniao_relacionada',
        'meta_value'     => $post_id,
        'posts_per_page' => 1,
    ));

    if (empty($videos)) {
        return array();
    }

    $video    = $videos[0];
    $url      = get_post_meta($video->ID, 'video_url', true);
    $duration = (int) get_post_meta($video->ID, 'duracao_segundos', true);
    $thumb    = '';

    $platform = agert_detectar_plataforma_video($url);
    if ($platform === 'youtube') {
        $yt = agert_extrair_youtube_id($url);
        if ($yt) {
            $thumb = agert_thumbnail_youtube($yt);
        }
    } elseif ($platform === 'vimeo') {
        $thumb = agert_thumbnail_vimeo($url);
    } else {
        $custom_thumb_id = get_post_meta($video->ID, 'thumbnail_personalizada', true);
        if ($custom_thumb_id) {
            $thumb = wp_get_attachment_url($custom_thumb_id);
        }
    }

    if (!$thumb) {
        $thumb = get_the_post_thumbnail_url($video->ID, 'large');
    }

    return array(
        'url'      => $url,
        'duration' => $duration,
        'thumb'    => $thumb,
    );
}
}

/**
 * Verifica se reunião possui documentos.
 */
function agert_reuniao_has_docs(int $post_id): bool {
    return agert_count_documentos($post_id) > 0;
}

/**
 * Lista anos disponíveis de reuniões.
 *
 * @return int[]
 */
function agert_available_years(): array {
    $years = array();
    $q = new WP_Query(array(
        'post_type'      => 'reuniao',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids'
    ));

    foreach ($q->posts as $pid) {
        $dt = agert_meta($pid, 'data_hora', '');
        if ($dt) {
            $y = (int) date_i18n('Y', strtotime($dt));
        } else {
            $y = (int) get_the_date('Y', $pid);
        }
        $years[$y] = true;
    }

    $keys = array_keys($years);
    rsort($keys);

    if (empty($keys)) {
        $keys[] = (int) date('Y');
    }

    return $keys;
}

/**
 * Retorna o ano ativo considerando querystring.
 *
 * @return int
 */
function agert_active_year(): int {
    $y = isset($_GET['ano']) ? (int) $_GET['ano'] : 0;
    $list = agert_available_years();
    if ($y && in_array($y, $list, true)) {
        return $y;
    }
    return $list[0];
}

/**
 * Monta WP_Query para reuniões filtradas.
 *
 * @param array $p Parâmetros de filtro.
 *
 * @return WP_Query
 */
function agert_query_reunioes_filtradas(array $p): WP_Query {
    $ano = (int) ($p['ano'] ?? agert_active_year());
    $de  = $p['de']  ?? sprintf('%d-01-01 00:00:00', $ano);
    $ate = $p['ate'] ?? sprintf('%d-12-31 23:59:59', $ano);

    $meta_query = array('relation' => 'AND');

    // data_hora OU post_date (fallback)
    $meta_query[] = array(
        'relation' => 'OR',
        array(
            'key'     => 'data_hora',
            'value'   => array($de, $ate),
            'type'    => 'DATETIME',
            'compare' => 'BETWEEN'
        ),
        array(
            'key'     => 'data_hora',
            'compare' => 'NOT EXISTS'
        )
    );

    if (!empty($p['status'])) {
        if ($p['status'] === 'video') {
            $meta_query[] = array('key' => 'videos', 'compare' => 'EXISTS');
        }
    }

    if (!empty($p['local'])) {
        $meta_query[] = array(
            'key'     => 'local',
            'value'   => $p['local'],
            'compare' => 'LIKE'
        );
    }

    $args = array(
        'post_type'      => 'reuniao',
        'post_status'    => 'publish',
        'paged'          => max(1, (int) ($p['paged'] ?? 1)),
        'posts_per_page' => (int) ($p['posts_per_page'] ?? 9),
        's'              => $p['q'] ?? '',
        'meta_query'     => $meta_query,
    );

    // tipo: tax ou meta
    if (!empty($p['tipo'])) {
        if (taxonomy_exists('tipo_reuniao')) {
            $args['tax_query'] = array(array(
                'taxonomy' => 'tipo_reuniao',
                'field'    => 'slug',
                'terms'    => $p['tipo']
            ));
        } else {
            $meta_query[] = array(
                'key'     => 'tipo_reuniao',
                'value'   => $p['tipo'],
                'compare' => 'LIKE'
            );
            $args['meta_query'] = $meta_query;
        }
    }

    // ordenação
    $ordem = $p['ordem'] ?? 'data_desc';
    if ($ordem === 'data_asc' || $ordem === 'data_desc') {
        $args['meta_key']  = 'data_hora';
        $args['orderby']   = 'meta_value';
        $args['meta_type'] = 'DATETIME';
        $args['order']     = ($ordem === 'data_asc') ? 'ASC' : 'DESC';
    } elseif ($ordem === 'titulo_za') {
        $args['orderby'] = 'title';
        $args['order']   = 'DESC';
    } else {
        $args['orderby'] = 'title';
        $args['order']   = 'ASC';
    }

    if (!empty($p['fields'])) {
        $args['fields'] = $p['fields'];
    }

    return new WP_Query($args);
}

/**
 * Coleta documentos agregados das reuniões filtradas.
 *
 * @param array $p Filtros para agert_query_reunioes_filtradas.
 * @return array Lista de documentos
 */
function agert_coletar_documentos(array $p): array {
    $q = agert_query_reunioes_filtradas(array_merge($p, array('posts_per_page' => -1)));
    $items = array();

    while ($q->have_posts()) {
        $q->the_post();
        $pid = get_the_ID();

        // coletar posts do CPT anexo relacionados
        $docs = get_posts(array(
            'post_type'      => 'anexo',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_reuniao_id',
            'meta_value'     => $pid,
        ));

        foreach ($docs as $d_post) {
            $aid       = (int) get_post_meta($d_post->ID, '_arquivo_id', true);
            $file_path = $aid ? get_attached_file($aid) : '';
            $file_size = ($file_path && file_exists($file_path)) ? filesize($file_path) : 0;
            $items[] = array(
                'doc' => array(
                    'rotulo'         => get_the_title($d_post),
                    'arquivo_id'     => $aid,
                    'arquivo_url'    => $aid ? wp_get_attachment_url($aid) : '',
                    'tamanho_bytes'  => $file_size,
                    'resumo'         => get_the_excerpt($d_post)
                ),
                'meeting' => get_post($pid)
            );
        }
    }
    wp_reset_postdata();

    return array(
        'results' => $items,
        'total'   => count($items),
        'pages'   => 1
    );
}

/**
 * Coleta vídeos agregados das reuniões filtradas.
 *
 * @param array $p Filtros.
 * @return array {results,total,pages}
 */

/**
 * Cria dados de exemplo se não existirem reuniões.
 */
function agert_seed_demo_if_empty() {
    $existing = get_posts(array(
        'post_type'      => 'reuniao',
        'posts_per_page' => 1,
        'post_status'    => 'any',
    ));
    if ($existing) {
        return;
    }

    $post_id = wp_insert_post(array(
        'post_type'    => 'reuniao',
        'post_status'  => 'publish',
        'post_title'   => 'Reunião de Demonstração',
        'post_content' => 'Conteúdo de exemplo da reunião.',
    ));
    if (!$post_id) {
        return;
    }

    $now = current_time('mysql');
    update_post_meta($post_id, 'data_hora', $now);
    update_post_meta($post_id, 'duracao', 90);
    update_post_meta($post_id, 'local', 'Porto Alegre');
    update_post_meta($post_id, 'resumo', 'Reunião inicial de demonstração.');
    update_post_meta($post_id, 'pautas', array('Apresentação do projeto', 'Planejamento das ações'));
    update_post_meta($post_id, 'decisoes', array('Projeto aprovado', 'Próxima reunião agendada'));
    update_post_meta($post_id, 'participantes', array());

    // Create sample attachments
    $docs = array();
    for ($i = 1; $i <= 2; $i++) {
        $bits = wp_upload_bits("documento-demo-$i.txt", null, "Documento de exemplo $i");
        if (empty($bits['error'])) {
            $filetype  = wp_check_filetype($bits['file']);
            $attach_id = wp_insert_attachment(array(
                'post_mime_type' => $filetype['type'],
                'post_title'     => "Documento $i",
                'post_status'    => 'inherit',
            ), $bits['file']);
            if (!is_wp_error($attach_id)) {
                $docs[] = array(
                    'rotulo'        => "Documento $i",
                    'arquivo_id'    => $attach_id,
                    'tamanho_bytes' => file_exists($bits['file']) ? filesize($bits['file']) : 0,
                );
            }
        }
    }

    // Sample video
    $vid = wp_insert_post(array(
        'post_type'   => 'reuniao_video',
        'post_status' => 'publish',
        'post_title'  => 'Vídeo de Demonstração',
    ));
    if ($vid) {
        update_post_meta($vid, 'reuniao_relacionada', $post_id);
        update_post_meta($vid, 'video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        update_post_meta($vid, 'duracao_segundos', 120);
    }
}
