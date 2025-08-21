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
    <h1 class="mb-4"><?php _e('Acervo', 'agert'); ?></h1>

    <nav class="tabbar mb-4" aria-label="<?php esc_attr_e('Seções do acervo', 'agert'); ?>">
        <div class="nav nav-tabs" id="acervoTabs" role="tablist">
            <button class="nav-link active" id="reunioes-tab" data-bs-toggle="tab" data-bs-target="#reunioes-pane" type="button" role="tab" aria-controls="reunioes-pane" aria-selected="true"><?php _e('Reuniões', 'agert'); ?></button>
            <button class="nav-link" id="anexos-tab" data-bs-toggle="tab" data-bs-target="#anexos-pane" type="button" role="tab" aria-controls="anexos-pane" aria-selected="false"><?php _e('Anexos', 'agert'); ?></button>
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
                'orderby'        => 'meta_value',
                'meta_key'       => 'data_hora',
                'order'          => 'DESC',
                's'              => $search,
            );

            if ($date) {
                $args['meta_query'][] = array(
                    'key'     => 'data_hora',
                    'value'   => $date,
                    'compare' => 'LIKE',
                );
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

        <div class="tab-pane fade" id="anexos-pane" role="tabpanel" aria-labelledby="anexos-tab">
            <?php
            $attachments = new WP_Query(array(
                'post_type'      => 'anexo',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));

            if ($attachments->have_posts()) :
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
            else :
                get_template_part('parts/reunioes/empty-state');
            endif;
            wp_reset_postdata();
            ?>
        </div>

        <div class="tab-pane fade" id="videos-pane" role="tabpanel" aria-labelledby="videos-tab">
            <?php
            $videos_query = new WP_Query(array(
                'post_type'      => 'reuniao_video',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ));

            if ($videos_query->have_posts()) :
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
            else :
                get_template_part('parts/reunioes/empty-state');
            endif;
            wp_reset_postdata();
            ?>
        </div>

    </div>
</div>

<?php get_footer(); ?>

