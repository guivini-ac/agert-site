<?php
/**
 * Helpers específicos da página do Presidente.
 *
 * @package AGERT
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retorna a tag <img> de um attachment ID com atributos escapados.
 */
if (!function_exists('agert_img')) {
    function agert_img($id, $size = 'large', $attrs = array()) {
        if (!$id) {
            return '';
        }
        $clean = array();
        foreach ($attrs as $attr => $val) {
            $clean[$attr] = esc_attr($val);
        }
        return wp_get_attachment_image($id, $size, false, $clean);
    }
}

