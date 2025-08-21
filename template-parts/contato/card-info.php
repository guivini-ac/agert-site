<?php
/**
 * Card com informação de contato reutilizável.
 *
 * @package AGERT
 */

if (!defined('ABSPATH')) {
    exit;
}

$defaults = array(
    'icon'         => '',
    'label'        => '',
    'text'         => '',
    'wrap_classes' => 'col',
);
$args = wp_parse_args($args, $defaults);
?>
<div class="<?php echo esc_attr($args['wrap_classes']); ?>">
    <div class="card-soft p-4 h-100">
        <div class="d-flex align-items-start">
            <?php if ($args['icon']) : ?>
                <i class="bi <?php echo esc_attr($args['icon']); ?> fs-3 me-3" aria-hidden="true"></i>
            <?php endif; ?>
            <div>
                <?php if ($args['label']) : ?>
                    <p class="mb-1 fw-semibold"><?php echo esc_html($args['label']); ?></p>
                <?php endif; ?>
                <?php if ($args['text']) : ?>
                    <p class="mb-0 text-break"><?php echo esc_html($args['text']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
