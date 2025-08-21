<?php
/**
 * Archive template for Agenda Fiscal.
 *
 * @package AGERT
 */

get_header();

$args = array(
    'post_type'      => 'agenda_fiscal',
    'posts_per_page' => -1,
    'meta_key'       => '_inicio',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
);
$query = new WP_Query($args);
?>
<section class="py-5">
    <div class="container">
        <h1 class="mb-4"><?php _e('Planejamento Fiscal', 'agert'); ?></h1>
        <?php if ($query->have_posts()) : ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="col">
                        <div class="card agenda-card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge text-white"><?php echo esc_html(get_post_meta(get_the_ID(), '_modalidade', true)); ?></span>
                                    <small class="text-muted">
                                        <?php echo esc_html(get_post_meta(get_the_ID(), '_inicio', true)); ?> - <?php echo esc_html(get_post_meta(get_the_ID(), '_fim', true)); ?>
                                    </small>
                                </div>
                                <h5 class="card-title mb-2"><?php echo esc_html(get_post_meta(get_the_ID(), '_atividade', true)); ?></h5>
                                <p class="card-text mb-1"><strong><?php _e('Prestador:', 'agert'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_prestador', true)); ?></p>
                                <p class="card-text mb-1"><strong><?php _e('Responsável:', 'agert'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_responsavel', true)); ?></p>
                                <p class="card-text"><strong><?php _e('Objetivo:', 'agert'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_objetivo', true)); ?></p>
                                <?php if ($aid = get_post_meta(get_the_ID(), '_arquivo_id', true)) : ?>
                                    <a href="<?php echo esc_url(wp_get_attachment_url($aid)); ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                        <?php _e('Baixar anexo', 'agert'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p><?php _e('Nenhuma programação encontrada.', 'agert'); ?></p>
        <?php endif; wp_reset_postdata(); ?>
    </div>
</section>
<?php
get_footer();
