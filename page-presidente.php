<?php
/**
 * Template Name: Presidente
 * Template da página "Presidente".
 *
 * @package AGERT
 */

if (!defined('ABSPATH')) {
    exit;
}

// Adiciona classe específica ao body
add_filter('body_class', function ($classes) {
    $classes[] = 'page-presidente';
    return $classes;
});

get_header();
?>
<div class="container-lg py-5">
    <div class="d-flex align-items-center mb-4">
        <?php agert_back_button(); ?>
        <?php agert_breadcrumb(); ?>
    </div>
    <h1 class="text-center mb-5"><?php _e('Presidente', 'agert'); ?></h1>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-soft p-4 h-100 text-center">
                <div class="mb-3">
                    <div class="photo d-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" fill="#6b7280" class="bi bi-person" viewBox="0 0 16 16" role="img" aria-label="<?php esc_attr_e('Sem foto', 'agert'); ?>">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                            <path fill-rule="evenodd" d="M14 14s-1-4-6-4-6 4-6 4 1 0 6 0 6 0 6 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mb-1 fw-bold">Itamar Antônio de Oliveira Júnior</p>
                <p class="mb-3">Presidente da AGERT</p>
                <p><span class="label-muted"><?php _e('Mandato:', 'agert'); ?></span> 2025 - Atual</p>
                <p><span class="label-muted"><?php _e('Formação:', 'agert'); ?></span> Graduação em Engenharia Ambiental</p>
                <p><span class="label-muted"><?php _e('Especialização:', 'agert'); ?></span> Saúde e Saneamento Ambiental; Perícia, Auditoria e Gestão Ambiental</p>
                <p><span class="label-muted"><?php _e('Mestrado:', 'agert'); ?></span> Desenvolvimento e Planejamento Territorial</p>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card-soft p-4 mb-4">
                <h2 class="card-title-sm mb-3"><i class="bi bi-person-circle me-2" aria-hidden="true"></i>Biografia</h2>
                <p>Engenheiro Ambiental (11 anos), Mestre e MBA. Atuação público/privada em obras, licenciamento, saneamento, resíduos e monitoramento. Perfil de campo, colaborativo e solucionador; domínio de legislação e políticas públicas.</p>
            </div>
            <div class="card-soft p-4 mb-4">
                <h2 class="card-title-sm mb-3"><i class="bi bi-briefcase me-2" aria-hidden="true"></i>Experiência Profissional</h2>
                <div class="item-row">
                    <div class="item-head">
                        <span class="item-title">Presidente da AGERT</span>
                        <span class="label-muted">2020 - Presente</span>
                    </div>
                    <div class="item-sub">Agência Reguladora de Timon</div>
                </div>
                <div class="item-row">
                    <div class="item-head">
                        <span class="item-title">Diretor de Regulação</span>
                        <span class="label-muted">2015 - 2020</span>
                    </div>
                    <div class="item-sub">Secretaria Municipal de Serviços Públicos</div>
                </div>
                <div class="item-row">
                    <div class="item-head">
                        <span class="item-title">Coordenador Técnico</span>
                        <span class="label-muted">2010 - 2015</span>
                    </div>
                    <div class="item-sub">Departamento de Concessões</div>
                </div>
                <div class="item-row">
                    <div class="item-head">
                        <span class="item-title">Analista de Regulação</span>
                        <span class="label-muted">2005 - 2010</span>
                    </div>
                    <div class="item-sub">Consultoria em Serviços Públicos</div>
                </div>
            </div>
            <div class="card-soft p-4">
                <h2 class="card-title-sm mb-3"><i class="bi bi-mortarboard me-2" aria-hidden="true"></i>Formação Acadêmica</h2>
                <div class="item-row">
                    <div class="item-head">
                        <span class="item-title">Graduação em Engenharia Ambiental</span>
                    </div>
                </div>
                <div class="item-row">
                    <div class="item-head">
                        <span class="item-title">Especialização em Saúde e Saneamento Ambiental</span>
                    </div>
                </div>
                <div class="item-row">
                    <div class="item-head">
                        <span class="item-title">Especialização em Perícia, Auditoria e Gestão Ambiental</span>
                    </div>
                </div>
                <div class="item-row">
                    <div class="item-head">
                        <span class="item-title">Mestrado em Desenvolvimento e Planejamento Territorial</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
?>
