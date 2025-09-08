<?php
/**
 * Template Name: Sobre a AGERT
 * Página institucional com seções de histórico, missão, visão e valores.
 *
 * @package AGERT
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        $missao  = get_post_meta(get_the_ID(), 'missao', true);
        $visao   = get_post_meta(get_the_ID(), 'visao', true);
        $valores = get_post_meta(get_the_ID(), 'valores', true);
        if (!is_array($valores)) {
            $valores = array_filter(array_map('trim', explode("\n", (string) $valores)));
        }

        // Dados estáticos de exemplo
        $default_content = 'A Agência Reguladora de Serviços Públicos Delegados do Município de Timon é responsável por regular, fiscalizar e controlar os serviços públicos delegados.';
        $default_missao  = 'Regular e fiscalizar os serviços públicos delegados com transparência, eficiência e responsabilidade.';
        $default_visao   = 'Ser referência na regulação municipal, garantindo qualidade nos serviços prestados.';
        $default_valores = array('Transparência', 'Eficiência', 'Responsabilidade');

        $content = get_the_content();
        if (!$content) {
            $content = $default_content;
        }
        if (!$missao) {
            $missao = $default_missao;
        }
        if (!$visao) {
            $visao = $default_visao;
        }
        if (!$valores) {
            $valores = $default_valores;
        }
?>
<div class="container py-5 page-sobre-agert">
    <div class="d-flex align-items-center mb-4">
        <?php agert_back_button(); ?>
        <?php agert_breadcrumb(); ?>
    </div>
    <h1 class="mb-4 text-center"><?php the_title(); ?></h1>

    <?php if ($content) : ?>
        <section class="mb-5">
            <h2 class="h4 mb-3"><?php _e('Histórico', 'agert'); ?></h2>
            <div class="page-intro"><?php echo apply_filters('the_content', $content); ?></div>
        </section>
    <?php endif; ?>

    <?php if ($missao) : ?>
        <section class="mb-5">
            <h2 class="h4 mb-3"><?php _e('Missão', 'agert'); ?></h2>
            <p><?php echo esc_html($missao); ?></p>
        </section>
    <?php endif; ?>

    <?php if ($visao) : ?>
        <section class="mb-5">
            <h2 class="h4 mb-3"><?php _e('Visão', 'agert'); ?></h2>
            <p><?php echo esc_html($visao); ?></p>
        </section>
    <?php endif; ?>

    <?php if ($valores) : ?>
        <section class="mb-4 valores">
            <h2 class="h4 mb-3"><?php _e('Valores', 'agert'); ?></h2>
            <ul class="list-unstyled row row-cols-1 row-cols-md-2 g-3">
                <?php foreach ($valores as $valor) : ?>
                    <li class="col"><?php echo esc_html($valor); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</div>
<?php
    endwhile;
endif;

get_footer();
?>
