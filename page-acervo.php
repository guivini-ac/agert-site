<?php
/**
 * Template da página de Acervo
 *
 * @package AGERT
 */

get_header();
?>

<div class="container py-5 page-acervo">
    <?php agert_show_status_message(); ?>
    <div class="d-flex align-items-center mb-4">
        <?php agert_back_button(); ?>
        <?php agert_breadcrumb(); ?>
    </div>
    <h1 class="mb-2"><?php _e('Acervo', 'agert'); ?></h1>
    <p class="text-center text-muted mb-4">
        <?php _e('Acompanhe as atas, resoluções, relatórios e vídeos das reuniões da AGERT. Todos os documentos estão disponíveis para download em formato PDF.', 'agert'); ?>
    </p>
    <?php
    // Filtro por ano
    global $wpdb;
    $selected_year = isset($_GET['ano']) ? (int) $_GET['ano'] : 0;
    $years = $wpdb->get_col("SELECT DISTINCT YEAR(meta_value) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE pm.meta_key = 'data_hora' AND p.post_type = 'reuniao' AND p.post_status = 'publish' ORDER BY meta_value DESC");

    if (!$selected_year && !empty($years)) {
        $selected_year = (int) $years[0];
    }

    $meeting_ids_year = array();
    if ($selected_year) {
        $meeting_ids_year = get_posts(array(
            'post_type'      => 'reuniao',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => 'data_hora',
                    'value'   => array($selected_year . '-01-01', $selected_year . '-12-31 23:59:59'),
                    'compare' => 'BETWEEN',
                    'type'    => 'DATETIME',
                ),
            ),
        ));
    }

    if ($years) :
    ?>
        <div class="d-flex justify-content-center gap-2 mb-4" aria-label="<?php esc_attr_e('Filtrar por ano', 'agert'); ?>">
            <?php foreach ($years as $year) :
                $link = add_query_arg('ano', $year);
                $classes = 'btn btn-sm ' . ($selected_year === (int) $year ? 'btn-dark text-white' : 'btn-outline-dark');
            ?>
                <a href="<?php echo esc_url($link); ?>" class="<?php echo esc_attr($classes); ?>"><?php echo esc_html($year); ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <nav class="tabbar mb-4" aria-label="<?php esc_attr_e('Seções do acervo', 'agert'); ?>">
        <div class="nav nav-tabs" id="acervoTabs" role="tablist">
            <button class="nav-link active" id="reunioes-tab" data-bs-toggle="tab" data-bs-target="#reunioes-pane" type="button" role="tab" aria-controls="reunioes-pane" aria-selected="true"><?php _e('Reuniões', 'agert'); ?></button>
            <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos-pane" type="button" role="tab" aria-controls="documentos-pane" aria-selected="false"><?php _e('Documentos', 'agert'); ?></button>
            <button class="nav-link" id="videos-tab" data-bs-toggle="tab" data-bs-target="#videos-pane" type="button" role="tab" aria-controls="videos-pane" aria-selected="false"><?php _e('Vídeos', 'agert'); ?></button>
        </div>
    </nav>

    <div class="tab-content" id="acervoTabsContent">

        <div class="tab-pane fade show active" id="reunioes-pane" role="tabpanel" aria-labelledby="reunioes-tab">
            <?php

            $paged  = get_query_var('paged') ? get_query_var('paged') : 1;
            $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
            $date   = isset($_GET['data']) ? sanitize_text_field($_GET['data']) : '';
            $tipo   = isset($_GET['tipo']) ? sanitize_key($_GET['tipo']) : '';

            $args = array(
                'post_type'      => 'reuniao',
                'posts_per_page' => 9,
                'paged'          => $paged,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
                's'              => $search,
            );

            $meta_query = array();
            if ($selected_year) {
                $meta_query[] = array(
                    'key'     => 'data_hora',
                    'value'   => array($selected_year . '-01-01', $selected_year . '-12-31 23:59:59'),
                    'compare' => 'BETWEEN',
                    'type'    => 'DATETIME',
                );
            }

            if ($date) {
                $meta_query[] = array(
                    'key'     => 'data_hora',
                    'value'   => $date,
                    'compare' => 'LIKE',
                );
            }
            if ($meta_query) {
                $args['meta_query'] = $meta_query;
            }

            if ($tipo) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'tipo_reuniao',
                    'field'    => 'slug',
                    'terms'    => $tipo,
                );
            }

            $meetings = new WP_Query($args);
            ?>

            <div class="filter-bar mb-4">
                <form method="get" class="row g-3 align-items-end">
                    <?php if ($selected_year) : ?>
                        <input type="hidden" name="ano" value="<?php echo esc_attr($selected_year); ?>">
                    <?php endif; ?>
                    <div class="col-md-4">
                        <label for="f-q" class="form-label"><?php _e('Pesquisa por nome', 'agert'); ?></label>
                        <input id="f-q" type="search" name="q" class="form-control" value="<?php echo esc_attr($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="f-date" class="form-label"><?php _e('Data', 'agert'); ?></label>
                        <input id="f-date" type="date" name="data" class="form-control" value="<?php echo esc_attr($date); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="f-tipo" class="form-label"><?php _e('Tipo de reunião', 'agert'); ?></label>
                        <select id="f-tipo" name="tipo" class="form-select">
                            <option value=""><?php _e('Todos', 'agert'); ?></option>
                            <option value="extraordinaria" <?php selected($tipo, 'extraordinaria'); ?>><?php _e('Extraordinária', 'agert'); ?></option>
                            <option value="ordinaria" <?php selected($tipo, 'ordinaria'); ?>><?php _e('Ordinária', 'agert'); ?></option>
                            <option value="convocacao" <?php selected($tipo, 'convocacao'); ?>><?php _e('Convocação', 'agert'); ?></option>
                            <option value="audiencia" <?php selected($tipo, 'audiencia'); ?>><?php _e('Audiência', 'agert'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-brand w-100"><?php _e('Filtrar', 'agert'); ?></button>
                    </div>
                </form>
            </div>

            <?php if ($meetings->have_posts()) : ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php while ($meetings->have_posts()) : $meetings->the_post(); ?>
                        <div class="col">
                            <?php get_template_part('parts/reunioes/card-reuniao'); ?>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($meetings->max_num_pages > 1) : ?>
                    <div class="mt-4">
                        <?php
                        echo paginate_links(array(
                            'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                            'format'    => '?paged=%#%',
                            'current'   => max(1, $paged),
                            'total'     => $meetings->max_num_pages,
                            'type'      => 'list',
                            'prev_text' => '<i class="bi bi-chevron-left"></i> ' . __('Anterior', 'agert'),
                            'next_text' => __('Próxima', 'agert') . ' <i class="bi bi-chevron-right"></i>',
                        ));
                        ?>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <?php get_template_part('parts/reunioes/empty-state'); ?>
            <?php endif; wp_reset_postdata(); ?>
        </div>

        <div class="tab-pane fade" id="documentos-pane" role="tabpanel" aria-labelledby="documentos-tab">
            <?php
            $docs_paged = isset($_GET['docs_page']) ? max(1, (int) $_GET['docs_page']) : 1;
            $doc_search = isset($_GET['doc_q']) ? sanitize_text_field($_GET['doc_q']) : '';
            $doc_args   = array(
                'post_type'      => 'anexo',
                'posts_per_page' => 9,
                'paged'          => $docs_paged,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
                's'              => $doc_search,
            );

            if (!empty($meeting_ids_year)) {
                $doc_args['meta_query'][] = array(
                    'key'     => '_reuniao_id',
                    'value'   => $meeting_ids_year,
                    'compare' => 'IN',
                );
            }

            $attachments = new WP_Query($doc_args);

            ?>
            <div class="filter-bar mb-4">
                <form method="get" class="row g-3 align-items-end">
                    <?php if ($selected_year) : ?>
                        <input type="hidden" name="ano" value="<?php echo esc_attr($selected_year); ?>">
                    <?php endif; ?>
                    <div class="col-md-10">
                        <label for="doc-q" class="form-label"><?php _e('Pesquisa por nome', 'agert'); ?></label>
                        <input id="doc-q" type="search" name="doc_q" class="form-control" value="<?php echo esc_attr($doc_search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-brand w-100"><?php _e('Filtrar', 'agert'); ?></button>
                    </div>
                </form>
            </div>
            <?php

            if ($attachments->have_posts()) {
                echo '<div class="document-list">';
                while ($attachments->have_posts()) : $attachments->the_post();
                    $reuniao_id = get_post_meta(get_the_ID(), '_reuniao_id', true);
                    $meeting    = $reuniao_id ? get_post($reuniao_id) : null;
                    $arquivo_id = (int) get_post_meta(get_the_ID(), '_arquivo_id', true);
                    $doc        = array(
                        'rotulo'      => get_the_title(),
                        'resumo'      => get_the_excerpt(),
                        'arquivo_url' => $arquivo_id ? wp_get_attachment_url($arquivo_id) : '',
                    );
                    get_template_part('parts/reunioes/row-documento', null, array('doc' => $doc, 'meeting' => $meeting));
                endwhile;
                echo '</div>';

                if ($attachments->max_num_pages > 1) {
                    $base_args = array(
                        'docs_page' => '%#%',
                    );
                    if ($doc_search) {
                        $base_args['doc_q'] = $doc_search;
                    }
                    if ($selected_year) {
                        $base_args['ano'] = $selected_year;
                    }
                    echo paginate_links(array(
                        'base'      => esc_url(add_query_arg($base_args)),
                        'format'    => '',
                        'current'   => $docs_paged,
                        'total'     => $attachments->max_num_pages,
                        'type'      => 'list',
                        'prev_text' => '<i class="bi bi-chevron-left"></i> ' . __('Anterior', 'agert'),
                        'next_text' => __('Próxima', 'agert') . ' <i class="bi bi-chevron-right"></i>',
                    ));
                }
            } else {
                get_template_part('parts/reunioes/empty-state');
            }
            wp_reset_postdata();
            ?>
        </div>

        <div class="tab-pane fade" id="videos-pane" role="tabpanel" aria-labelledby="videos-tab">
            <?php
            $videos_page  = isset($_GET['videos_page']) ? max(1, (int) $_GET['videos_page']) : 1;
            $video_search = isset($_GET['video_q']) ? sanitize_text_field($_GET['video_q']) : '';
            $video_args   = array(
                'post_type'      => 'reuniao_video',
                'posts_per_page' => 9,
                'paged'          => $videos_page,
                'post_status'    => 'publish',
                's'              => $video_search,
                'orderby'        => 'date',
                'order'          => 'DESC',
            );

            if (!empty($meeting_ids_year)) {
                $video_args['meta_query'][] = array(
                    'key'     => 'reuniao_relacionada',
                    'value'   => $meeting_ids_year,
                    'compare' => 'IN',
                );
            }

            $videos_query = new WP_Query($video_args);

            ?>
            <div class="filter-bar mb-4">
                <form method="get" class="row g-3 align-items-end">
                    <?php if ($selected_year) : ?>
                        <input type="hidden" name="ano" value="<?php echo esc_attr($selected_year); ?>">
                    <?php endif; ?>
                    <div class="col-md-10">
                        <label for="video-q" class="form-label"><?php _e('Pesquisa por nome', 'agert'); ?></label>
                        <input id="video-q" type="search" name="video_q" class="form-control" value="<?php echo esc_attr($video_search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-brand w-100"><?php _e('Filtrar', 'agert'); ?></button>
                    </div>
                </form>
            </div>
            <?php

            if ($videos_query->have_posts()) {
                echo '<div class="row row-cols-1 row-cols-md-3 g-4">';
                while ($videos_query->have_posts()) : $videos_query->the_post();
                    $meeting_id = get_post_meta(get_the_ID(), 'reuniao_relacionada', true);
                    $meeting    = $meeting_id ? get_post($meeting_id) : null;
                    echo '<div class="col">';
                    get_template_part('parts/reunioes/card-video', null, array(
                        'meeting' => $meeting,
                        'video'   => get_post(),
                    ));
                    echo '</div>';
                    $videos = get_post_meta(get_the_ID(), 'videos', true);
                    if (is_array($videos)) {
                        foreach ($videos as $video) {
                            echo '<div class="col">';
                            get_template_part('parts/reunioes/card-video', null, array(
                                'meeting' => get_post(),
                                'video'   => $video,
                            ));
                            echo '</div>';
                        }
                    }

                endwhile;
                echo '</div>';

                if ($videos_query->max_num_pages > 1) {
                    $base_args = array(
                        'videos_page' => '%#%',
                    );
                    if ($video_search) {
                        $base_args['video_q'] = $video_search;
                    }
                    if ($selected_year) {
                        $base_args['ano'] = $selected_year;
                    }
                    echo paginate_links(array(
                        'base'      => esc_url(add_query_arg($base_args)),
                        'format'    => '',
                        'current'   => $videos_page,
                        'total'     => $videos_query->max_num_pages,
                        'type'      => 'list',
                        'prev_text' => '<i class="bi bi-chevron-left"></i> ' . __('Anterior', 'agert'),
                        'next_text' => __('Próxima', 'agert') . ' <i class="bi bi-chevron-right"></i>',
                    ));
                }
            } else {
                get_template_part('parts/reunioes/empty-state');
            }
            wp_reset_postdata();
            ?>
        </div>

    </div>
</div>

<div class="container py-5">
    <div class="row g-4 info-bottom">
        <div class="col-md-6">
            <h5><?php _e('Informações sobre as Reuniões', 'agert'); ?></h5>
            <h6 class="mt-3"><?php _e('Reuniões Ordinárias', 'agert'); ?></h6>
            <p class="mb-1"><?php _e('As reuniões ordinárias da AGERT acontecem mensalmente, sempre na segunda terça-feira do mês, às 14h00.', 'agert'); ?></p>
            <p class="mb-1"><?php _e('Sede da AGERT - Sala de Reuniões', 'agert'); ?></p>
            <p class="mb-0"><?php _e('Rua Principal, 123 - Centro', 'agert'); ?></p>
        </div>
        <div class="col-md-6">
            <h5><?php _e('Participação Pública', 'agert'); ?></h5>
            <p class="mb-1"><?php _e('As reuniões são abertas ao público e transmitidas ao vivo pelo canal oficial da AGERT no YouTube.', 'agert'); ?></p>
            <p class="mb-0"><?php _e('Para participar presencialmente, entre em contato conosco através dos canais oficiais com antecedência mínima de 48h.', 'agert'); ?></p>
        </div>
    </div>
</div>

<?php get_footer(); ?>

