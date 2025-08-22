<?php
/**
 * Card de reunião para listagens.
 *
 * @package AGERT
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id     = get_the_ID();
$video_data  = agert_get_reuniao_video_data($post_id);
$thumb_url   = $video_data['thumb'] ?? '';
$video_dur   = $video_data['duration'] ?? 0;
$tipo        = '';
$terms       = get_the_terms($post_id, 'tipo_reuniao');
if ($terms && !is_wp_error($terms)) {
    $tipo = $terms[0]->name;
} else {
    $tipo = agert_meta($post_id, 'tipo_reuniao');
}
$video     = !empty($video_data);
$doc_qtd   = agert_count_documentos($post_id);
$data_hora = agert_meta($post_id, 'data_hora');
?>
<div class="card card-soft h-100">

    <div class="p-3 pb-0">
        <div class="position-relative">
            <?php if ($thumb_url) : ?>
                <img src="<?php echo esc_url($thumb_url); ?>" class="thumb-16x9 w-100" alt="<?php echo esc_attr(get_the_title()); ?>">
            <?php else : ?>
                <div class="thumb-16x9 w-100 d-flex align-items-center justify-content-center bg-light text-muted">
                    <?php _e('Não há imagens/vídeo', 'agert'); ?>
                </div>
            <?php endif; ?>
            <?php if ($video_dur) : ?>
                <span class="duration-badge"><?php echo esc_html(agert_seconds_to_mmss((int) $video_dur)); ?></span>
            <?php endif; ?>
        </div>

    </div>
    <div class="card-body">
        <?php if ($data_hora) : ?>
            <p class="small text-muted mb-1"><i class="bi bi-calendar3 me-1"></i><?php echo esc_html(date_i18n('d/m/Y', strtotime($data_hora))); ?></p>
        <?php endif; ?>
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="card-title mb-0 me-2"><?php the_title(); ?></h5>
            <?php if ($tipo) : ?>
                <span class="badge-chip"><?php echo esc_html($tipo); ?></span>
            <?php endif; ?>
        </div>
        <p class="muted mb-3"><?php echo esc_html(get_the_excerpt()); ?></p>
    </div>
    <div class="card-footer bg-white border-0 pt-0">
        <div class="d-flex justify-content-between small text-muted mb-2">
            <span><?php echo esc_html($doc_qtd); ?> <?php _e('documentos', 'agert'); ?></span>
            <span><?php echo $video ? __('Vídeo disponível', 'agert') : __('Vídeo indisponível', 'agert'); ?></span>
        </div>
        <a href="<?php the_permalink(); ?>" class="btn btn-brand w-100">Ver Detalhes Completos</a>
    </div>
</div>
