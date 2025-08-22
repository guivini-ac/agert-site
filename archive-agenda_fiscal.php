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
    </div>
</section>
<?php
get_footer();
