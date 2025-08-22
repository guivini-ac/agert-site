<?php
/**
 * Card de vídeo seguindo o padrão do card de reunião.
 *
 * @package AGERT
 */

if (!defined('ABSPATH')) {
    exit;
}

$meeting_id = $meeting ? $meeting->ID : 0;
$video_id   = $video ? $video->ID : 0;

$video_url  = $video_id ? get_post_meta($video_id, 'video_url', true) : '';
$duration   = $video_id ? (int) get_post_meta($video_id, 'duracao_segundos', true) : 0;
$thumb_url  = '';

if ($video_url) {
    $platform = agert_detectar_plataforma_video($video_url);
    if ($platform === 'youtube') {
        $yt_id = agert_extrair_youtube_id($video_url);
        if ($yt_id) {
            $thumb_url = agert_thumbnail_youtube($yt_id);
        }
    } elseif ($platform === 'vimeo') {
        $thumb_url = agert_thumbnail_vimeo($video_url);
    } else {
        $custom_thumb_id = get_post_meta($video_id, 'thumbnail_personalizada', true);
        if ($custom_thumb_id) {
            $thumb_url = wp_get_attachment_url($custom_thumb_id);
        }
    }
}

if (!$thumb_url && $meeting_id) {
    $thumb_url = get_the_post_thumbnail_url($meeting_id, 'large');
}

$tipo       = '';
if ($meeting_id) {
    $terms = get_the_terms($meeting_id, 'tipo_reuniao');
    if ($terms && !is_wp_error($terms)) {
        $tipo = $terms[0]->name;
    } else {
        $tipo = agert_meta($meeting_id, 'tipo_reuniao');
    }
}

$doc_qtd    = $meeting_id ? agert_count_documentos($meeting_id) : 0;
$data_hora  = $meeting_id ? agert_meta($meeting_id, 'data_hora') : '';
$titulo     = $meeting_id ? get_the_title($meeting_id) : get_the_title($video_id);
$descricao  = $meeting_id ? get_the_excerpt($meeting_id) : wp_trim_words(get_post_field('post_content', $video_id), 20);
?>
<div class="card card-soft h-100">
    <div class="p-3 pb-0">
        <div class="position-relative">
            <?php if ($thumb_url) : ?>
                <img src="<?php echo esc_url($thumb_url); ?>" class="thumb-16x9 w-100" alt="<?php echo esc_attr($titulo); ?>">
            <?php else : ?>
                <div class="thumb-16x9 w-100 d-flex align-items-center justify-content-center bg-light text-muted">
                    <?php _e('Não há imagens/vídeo', 'agert'); ?>
                </div>
            <?php endif; ?>
            <?php if ($duration) : ?>
                <span class="duration-badge"><?php echo esc_html(agert_seconds_to_mmss((int) $duration)); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($data_hora) : ?>
            <p class="small text-muted mb-1"><i class="bi bi-calendar3 me-1"></i><?php echo esc_html(date_i18n('d/m/Y', strtotime($data_hora))); ?></p>
        <?php endif; ?>
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="card-title mb-0 me-2"><?php echo esc_html($titulo); ?></h5>
            <?php if ($tipo) : ?>
                <span class="badge-chip"><?php echo esc_html($tipo); ?></span>
            <?php endif; ?>
        </div>
        <p class="muted mb-3"><?php echo esc_html($descricao); ?></p>
    </div>
    <div class="card-footer bg-white border-0 pt-0">
        <div class="d-flex justify-content-between small text-muted mb-2">
            <span><?php echo esc_html($doc_qtd); ?> <?php _e('documentos', 'agert'); ?></span>
            <span><?php echo $video_url ? __('Vídeo disponível', 'agert') : __('Vídeo indisponível', 'agert'); ?></span>
        </div>
        <?php if ($video_url) : ?>
            <a href="<?php echo esc_url($video_url); ?>" target="_blank" class="btn btn-brand w-100"><?php _e('Assistir ao vídeo', 'agert'); ?></a>
        <?php endif; ?>
    </div>
</div>
