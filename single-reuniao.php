<?php
/**
 * Template para exibição de uma reunião individual.
 *
 * @package AGERT
 */

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        $id = get_the_ID();

        $resumo        = agert_meta($id, 'resumo');
        $videos        = get_posts(array(
            'post_type'      => 'reuniao_video',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => 'reuniao_relacionada',
            'meta_value'     => $id,
        ));
        $data_hora     = agert_meta($id, 'data_hora');
        $duracao       = agert_meta($id, 'duracao');
        $local         = agert_meta($id, 'local');
        $pauta         = agert_meta($id, 'pautas', array());
        $decisoes      = agert_meta($id, 'decisoes', array());
        $participantes = agert_meta($id, 'participantes', array());
        $documentos    = agert_meta($id, 'anexos', array());

        $documentos    = get_posts(array(
            'post_type'      => 'anexo',
            'post_status'    => 'publish',
            'meta_key'       => '_reuniao_id',
            'meta_value'     => $id,
            'posts_per_page' => -1,
        ));

        $transmitido   = agert_meta($id, 'transmitido_em', $data_hora);
        if (!$resumo) {
            $resumo = get_the_excerpt();
        }

        $tipo_reuniao = '';
        $terms = get_the_terms($id, 'tipo_reuniao');
        if ($terms && !is_wp_error($terms)) {
            $tipo_reuniao = $terms[0]->name;
        } else {
            $tipo_reuniao = agert_meta($id, 'tipo_reuniao');
        }
        ?>

        <div class="container py-4">
            <div class="d-flex align-items-center mb-3">
                <a href="<?php echo esc_url(agert_get_page_link('acervo')); ?>" class="btn btn-outline-brand btn-sm me-3" aria-label="Voltar para Acervo" title="Voltar para Acervo">
                    <i class="bi bi-arrow-left"></i> Voltar para Acervo
                </a>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo esc_url(agert_get_page_link('acervo')); ?>">Acervo</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
                    </ol>
                </nav>
            </div>

            <header class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="mb-2"><?php the_title(); ?></h1>
                    <ul class="list-inline text-muted small mb-0">
                        <?php if ($data_hora) : ?>
                            <li class="list-inline-item me-3"><i class="bi bi-calendar-event me-1"></i><?php echo esc_html(date_i18n('d/m/Y \à\s H:i', strtotime($data_hora))); ?></li>
                        <?php endif; ?>
                        <?php if ($duracao) : ?>
                            <li class="list-inline-item me-3"><i class="bi bi-clock me-1"></i><?php echo esc_html(agert_minutes_to_human((int) $duracao)); ?></li>
                        <?php endif; ?>
                        <?php if ($local) : ?>
                            <li class="list-inline-item"><i class="bi bi-geo-alt me-1"></i><?php echo esc_html($local); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php if ($tipo_reuniao) : ?>
                    <span class="badge-chip"><?php echo esc_html($tipo_reuniao); ?></span>
                <?php endif; ?>
            </header>

            <div class="row g-4">
                <div class="col-lg-8">
                    <?php if ($resumo) : ?>
                        <div class="card card-soft mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">Sobre a Reunião</h6>
                                <p class="mb-0"><?php echo wp_kses_post($resumo); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card card-soft mb-4" id="transmissao">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">Transmissão/Vídeos</h6>
                            <?php if (!empty($videos)) : ?>
                                <?php foreach ($videos as $vid) :
                                    $url = get_post_meta($vid->ID, 'video_url', true);
                                    if (!$url) { continue; }
                                    $embed = wp_oembed_get($url);
                                    if (!$embed) { continue; }
                                    $embed = preg_replace('/<iframe /', '<iframe class="rounded" ', $embed);
                                    ?>
                                    <div class="ratio ratio-16x9 mb-3">
                                        <?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </div>
                                    <?php
                                    $dur = get_post_meta($vid->ID, 'duracao_segundos', true);
                                    $desc = get_post_field('post_content', $vid->ID);
                                    if ($dur || $desc) : ?>
                                        <p class="text-muted small mb-3">
                                            <?php if ($dur) : ?>
                                                <?php echo esc_html(agert_seconds_to_mmss((int) $dur)); ?>
                                            <?php endif; ?>
                                            <?php if ($desc) : ?>
                                                <?php echo esc_html($desc); ?>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="card-soft video-empty">
                                    <i class="bi bi-camera-video-off" aria-hidden="true"></i>
                                    <p>Nenhum vídeo disponível</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($pauta) && is_array($pauta)) : ?>
                        <div class="card card-soft mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">Pauta da Reunião</h6>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($pauta as $item) : ?>
                                        <li class="list-dot mb-1"><?php echo esc_html($item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($decisoes) && is_array($decisoes)) : ?>
                        <div class="card card-soft mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">Principais Decisões</h6>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($decisoes as $item) : ?>
                                        <li class="d-flex align-items-start mb-2"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i><span><?php echo esc_html($item); ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="col-lg-4">
                    <?php if (!empty($participantes) && is_array($participantes)) : ?>
                        <div class="card card-soft mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">Participantes</h6>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($participantes as $p) :
                                        $nome  = $p['nome'] ?? '';
                                        $cargo = $p['cargo'] ?? '';
                                        if (!$nome) {
                                            continue;
                                        }
                                        ?>
                                        <li class="py-2 border-bottom">
                                            <strong><?php echo esc_html($nome); ?></strong>
                                            <?php if ($cargo) : ?> – <span class="text-muted"><?php echo esc_html($cargo); ?></span><?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($documentos)) : ?>
                        <div class="card card-soft mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3">Documentos</h6>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($documentos as $doc_post) :
                                        $aid = (int) get_post_meta($doc_post->ID, '_arquivo_id', true);
                                        if (!$aid) {
                                            continue;
                                        }
                                        $file_url = wp_get_attachment_url($aid);
                                        if (!$file_url) {
                                            continue;
                                        }
                                        $file_name = get_the_title($aid);
                                        $size_h = '';
                                        $file_path = get_attached_file($aid);
                                        if ($file_path && file_exists($file_path)) {
                                            $size_h = agert_bytes_to_human(filesize($file_path));
                                        }
                                        $same_domain = strpos($file_url, home_url()) === 0;
                                        ?>
                                        <div class="doc-row">
                                            <span><?php echo esc_html(get_the_title($doc_post)); ?></span>
                                            <?php if ($size_h) : ?><span class="doc-size"><?php echo esc_html($size_h); ?></span><?php endif; ?>
                                            <a class="btn btn-brand btn-sm ms-2" href="<?php echo esc_url($file_url); ?>" <?php echo $same_domain ? 'download' : 'target="_blank" rel="noopener noreferrer"'; ?> aria-label="Download <?php echo esc_attr($file_name); ?>" title="Download <?php echo esc_attr($file_name); ?>">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card card-soft">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">Informações</h6>
                            <ul class="list-unstyled small mb-0">
                                <?php if ($data_hora) : ?>
                                    <li class="mb-2"><span class="text-muted d-block">Data e hora</span><span class="fw-semibold"><?php echo esc_html(date_i18n('d/m/Y \à\s H:i', strtotime($data_hora))); ?></span></li>
                                <?php endif; ?>
                                <?php if ($local) : ?>
                                    <li class="mb-2"><span class="text-muted d-block">Local</span><span class="fw-semibold"><?php echo esc_html($local); ?></span></li>
                                <?php endif; ?>
                                <?php if ($duracao) : ?>
                                    <li class="mb-2"><span class="text-muted d-block">Duração</span><span class="fw-semibold"><?php echo esc_html(agert_minutes_to_human((int) $duracao)); ?></span></li>
                                <?php endif; ?>
                                <?php if ($tipo_reuniao) : ?>
                                    <li class="mb-0"><span class="text-muted d-block">Tipo</span><span class="fw-semibold"><?php echo esc_html($tipo_reuniao); ?></span></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
        <?php
    endwhile;
endif;

get_footer();
