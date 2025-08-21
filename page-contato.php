<?php
/**
 * Template da página "Contato".
 *
 * @package AGERT
 */

get_header();

$success_message = '';
$error_message   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_nonce'])) {
    if (wp_verify_nonce($_POST['contact_nonce'], 'agert_contact')) {
        $name    = sanitize_text_field($_POST['name'] ?? '');
        $email   = sanitize_email($_POST['email'] ?? '');
        $phone   = sanitize_text_field($_POST['phone'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if ($name && $email && $message) {
            $to      = get_option('admin_email');
            $subject = sprintf(__('Contato de %s', 'agert'), $name);
            $body    = "Nome: $name\nEmail: $email\nTelefone: $phone\n\nMensagem:\n$message";

            if (wp_mail($to, $subject, $body)) {
                $success_message = __('Mensagem enviada com sucesso!', 'agert');
            } else {
                $error_message = __('Ocorreu um erro ao enviar a mensagem.', 'agert');
            }
        } else {
            $error_message = __('Por favor, preencha os campos obrigatórios.', 'agert');
        }
    } else {
        $error_message = __('Falha na validação do formulário.', 'agert');
    }
}

$contact_address = get_option(
    'agert_contact_address',
    'Av. Jaime Rios, 537 - Parque Piaui, Timon, Maranhao 65631-210'
);
$contact_phone   = get_option('agert_contact_phone', '(86) 3212-1222');
$contact_email   = get_option('agert_contact_email', 'agert@timon.ma.gov.br');
$contact_map_url = get_option(
    'agert_contact_map_url',
    'https://www.google.com/maps?q=Av.+Jaime+Rios,+537+-+Parque+Piaui,+Timon,+Maranhao+65631-210&output=embed'
);
?>

<div class="container py-5">
    <div class="d-flex align-items-center mb-4">
        <?php agert_back_button(); ?>
        <?php agert_breadcrumb(); ?>
    </div>
    <h1 class="mb-4"><?php the_title(); ?></h1>

    <?php if ($success_message) : ?>
        <div class="alert alert-success" role="alert"><?php echo esc_html($success_message); ?></div>
    <?php elseif ($error_message) : ?>
        <div class="alert alert-danger" role="alert"><?php echo esc_html($error_message); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <form method="post" class="mb-4">
                <?php wp_nonce_field('agert_contact', 'contact_nonce'); ?>
                <div class="mb-3">
                    <label for="name" class="form-label"><?php _e('Nome', 'agert'); ?>*</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label"><?php _e('E-mail', 'agert'); ?>*</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label"><?php _e('Telefone', 'agert'); ?></label>
                    <input type="text" id="phone" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label"><?php _e('Mensagem', 'agert'); ?>*</label>
                    <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><?php _e('Enviar', 'agert'); ?></button>
            </form>
        </div>

        <div class="col-lg-6">
            <h2 class="h5 mb-3"><?php _e('Informações de Contato', 'agert'); ?></h2>
            <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
                <?php get_template_part('template-parts/contato/card-info', null, array(
                    'icon'  => 'bi-telephone',
                    'label' => __('Telefone', 'agert'),
                    'text'  => $contact_phone,
                )); ?>
                <?php get_template_part('template-parts/contato/card-info', null, array(
                    'icon'  => 'bi-envelope',
                    'label' => __('E-mail', 'agert'),
                    'text'  => $contact_email,
                )); ?>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                        <iframe src="<?php echo esc_url($contact_map_url); ?>" class="w-100 h-100 border-0" allowfullscreen loading="lazy"></iframe>
                    </div>
                </div>
                <?php get_template_part('template-parts/contato/card-info', null, array(
                    'icon'         => 'bi-geo-alt',
                    'label'        => __('Endereço', 'agert'),
                    'text'         => $contact_address,
                    'wrap_classes' => 'col-12'
                )); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
