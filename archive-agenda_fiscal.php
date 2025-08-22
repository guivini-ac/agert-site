<?php
/**
 * Archive template for Agenda Fiscal.
 *
 * @package AGERT
 */

get_header();

$prestador  = isset($_GET['prestador']) ? sanitize_text_field($_GET['prestador']) : '';
$modalidade = isset($_GET['modalidade']) ? sanitize_text_field($_GET['modalidade']) : '';
$inicio     = isset($_GET['inicio']) ? sanitize_text_field($_GET['inicio']) : '';

$meta_query = array();

if ($prestador) {
    $meta_query[] = array(
        'key'     => '_prestador',
        'value'   => $prestador,
        'compare' => 'LIKE',
    );
}

if ($modalidade) {
    $meta_query[] = array(
        'key'     => '_modalidade',
        'value'   => $modalidade,
        'compare' => 'LIKE',
    );
}

if ($inicio) {
    $meta_query[] = array(
        'key'   => '_inicio',
        'value' => $inicio,
    );
}

$args = array(
    'post_type'      => 'agenda_fiscal',
    'posts_per_page' => 20,
    'paged'          => get_query_var('paged', 1),
    'meta_key'       => '_inicio',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
);

if (!empty($meta_query)) {
    $args['meta_query'] = $meta_query;
}

$query = new WP_Query($args);
?>
<section class="py-5">
    <div class="container">
        <h1 class="mb-4"><?php _e('Planejamento Fiscal', 'agert'); ?></h1>
        <form method="get" class="agenda-filters mb-4">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="prestador" class="form-control" placeholder="<?php _e('Prestador', 'agert'); ?>" value="<?php echo esc_attr($prestador); ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="modalidade" class="form-control" placeholder="<?php _e('Modalidade', 'agert'); ?>" value="<?php echo esc_attr($modalidade); ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="inicio" class="form-control" value="<?php echo esc_attr($inicio); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><?php _e('Filtrar', 'agert'); ?></button>
                </div>
            </div>
        </form>
        <?php if ($query->have_posts()) : ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php _e('Início', 'agert'); ?></th>
                            <th><?php _e('Fim', 'agert'); ?></th>
                            <th><?php _e('Prestador de Serviço', 'agert'); ?></th>
                            <th><?php _e('Atividade/Local', 'agert'); ?></th>
                            <th><?php _e('Modalidade', 'agert'); ?></th>
                            <th><?php _e('Responsável', 'agert'); ?></th>
                            <th><?php _e('Objetivo', 'agert'); ?></th>
                            <th><?php _e('Anexo', 'agert'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                            <tr>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), '_inicio', true)); ?></td>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), '_fim', true)); ?></td>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), '_prestador', true)); ?></td>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), '_atividade', true)); ?></td>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), '_modalidade', true)); ?></td>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), '_responsavel', true)); ?></td>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), '_objetivo', true)); ?></td>
                                <td>
                                    <?php if ($aid = get_post_meta(get_the_ID(), '_arquivo_id', true)) : ?>
                                        <a href="<?php echo esc_url(wp_get_attachment_url($aid)); ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                            <?php _e('Baixar anexo', 'agert'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <p><?php _e('Nenhuma programação encontrada.', 'agert'); ?></p>
        <?php endif; wp_reset_postdata(); ?>
        <?php
        $pagination = paginate_links(array(
            'total'   => $query->max_num_pages,
            'current' => max(1, get_query_var('paged', 1)),
            'add_args' => array_filter(compact('prestador', 'modalidade', 'inicio')),
        ));
        if ($pagination) : ?>
            <nav class="agenda-pagination mt-4">
                <?php echo $pagination; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php
get_footer();
