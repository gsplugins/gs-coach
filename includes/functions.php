<?php

namespace GSCOACH;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

function is_pro_active() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	return is_plugin_active( GSCOACH_PRO_PLUGIN );
}

function gs_coach_pro_license_status() {
    $license = get_option( 'gs-coach-pro_license', false );
    return $license->license ?? false;
}

function is_gs_coach_pro_valid() {
    $license_status  = gs_coach_pro_license_status();

    if ( $license_status && 'valid' === $license_status ) {
        return true;
    }

    return false;
}

function is_pro_active_and_valid(){
    return is_pro_active() && is_gs_coach_pro_valid();
}

/**
 * Upgrade notice if compatibility fails
 */
function pro_compatibility_notice() {

    $screen = get_current_screen();
    
    if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) return;
    if ( 'update' === $screen->base && 'update' === $screen->id ) return;

    if ( ! current_user_can( 'update_plugins' ) ) return;

    $upgrade_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . GSCOACH_PRO_PLUGIN ), 'upgrade-plugin_' . GSCOACH_PRO_PLUGIN );
    $message = '<p>' . __( 'GS Coach is not working because you need to upgrade the GS Coach Pro plugin to latest version.', 'gscoach' ) . '</p>';
    $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $upgrade_url, __( 'Upgrade GS Coach Pro Now', 'gscoach' ) ) . '</p>';

    echo '<div class="error"><p>' . $message . '</p></div>';
    
}

/**
 * Compatibility check with Pro plugin
 */
function is_pro_compatible() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( defined('GSCOACH_PRO_VERSION') && is_plugin_active( GSCOACH_PRO_PLUGIN ) ) {
        if ( version_compare( GSCOACH_PRO_VERSION, GSCOACH_MIN_PRO_VERSION, '<' ) ) {
            add_action( 'admin_notices', 'GSCOACH\pro_compatibility_notice' );
            return false;
        }
    }
    return true;
}

/**
 * Plugins action links
 */
function add_pro_link( $links ) {
    if ( ! is_pro_active() ) {
        $links[] = '<a style="color: red; font-weight: bold;" class="gs-pro-link" href="https://www.gsplugins.com/product/wordpress-coaches-plugin" target="_blank">Go Pro!</a>';
    }
    $links[] = '<a href="https://www.gsplugins.com/wordpress-plugins" target="_blank">GS Plugins</a>';
    return $links;
}

/**
 * Plugins Load Text Domain
 */
function gs_load_textdomain() {
    load_plugin_textdomain( 'gscoach', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

function is_divi_active() {
    if (!defined('ET_BUILDER_PLUGIN_ACTIVE') || !ET_BUILDER_PLUGIN_ACTIVE) return false;
    return et_core_is_builder_used_on_current_request();
}

function is_divi_editor() {
    // Divi 4 computed property (legacy layouts during migration).
    if (!empty($_POST['action']) && $_POST['action'] == 'et_pb_process_computed_property' && !empty($_POST['module_type']) && $_POST['module_type'] == 'gs_coaches') {
        return true;
    }

    // Divi 5 Visual Builder / Theme Builder.
    if (function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled() && function_exists('et_builder_d5_enabled') && et_builder_d5_enabled()) {
        return true;
    }

    // Divi 5 REST preview for GS Coach module.
    if (defined('REST_REQUEST') && REST_REQUEST) {
        $rest_route = isset($GLOBALS['wp']->query_vars['rest_route']) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';
        if ($rest_route && false !== strpos($rest_route, '/gs-coach/v1/divi')) {
            return true;
        }
    }

    return false;
}

function gs_wp_kses($content) {

    $allowed_tags = wp_kses_allowed_html('post');

    $input_common_atts = ['class' => true, 'id' => true, 'style' => true, 'novalidate' => true, 'name' => true, 'width' => true, 'height' => true, 'data' => true, 'title' => true, 'placeholder' => true, 'value' => true];

    $allowed_tags = array_merge_recursive($allowed_tags, [
        'select' => $input_common_atts,
        'input' => array_merge($input_common_atts, ['type' => true, 'checked' => true]),
        'option' => ['class' => true, 'id' => true, 'selected' => true, 'data' => true, 'value' => true]
    ]);

    return wp_kses(stripslashes_deep($content), $allowed_tags);
}

function get_shortcode_params($settings) {

    $params = [];

    foreach ($settings as $key => $val) {
        $params[] = sprintf('%s="%s"', $key, $val);
    }

    return implode(' ', $params);
}

function echo_return($content, $echo = false) {
    if ($echo) {
        echo gs_wp_kses($content);
    } else {
        return $content;
    }
}

function get_query($atts) {

    $args = array_merge([
        'order'                => 'DESC',
        'orderby'              => 'date',
        'posts_per_page'       => -1,
        'paged'                => 1,
        'tax_query'            => [],
    ], $atts);

    $args['post_type'] = 'gs_coaches';

    return new \WP_Query(apply_filters('gs_coach_wp_query_args', $args));
}

function get_translation($translation_name) {
    return plugin()->builder->get_translation($translation_name);
}

function coach_description($shortcode_id, $max_length = 100, $echo = false, $is_excerpt = true, $has_link = true, $link_type = 'single_page') {

    $coach_id = get_the_ID();
    
    $description = $is_excerpt ? get_the_excerpt() : get_the_content();

    $description = sanitize_text_field($description);

    $gs_coach_more = get_translation('gs_coach_more');

    $gs_more_link = '';

    if ( $link_type == 'custom' ) {
        $custom_page_link = get_post_meta( $coach_id, '_gscoach_custom_page', true );
        if ( empty($custom_page_link) ) {
            $default_link_type = getoption('single_link_type', 'single_page');
            if ( $default_link_type == 'none' ) {
                $has_link = false;
            } else {
                $link_type = $default_link_type;
            }
        }
    }

    if ($has_link) {

        if ($link_type == 'single_page') {

            $gs_more_link = sprintf('...<a href="%s">%s</a>', get_the_permalink(), $gs_coach_more);
        } else if ($link_type == 'popup') {

            global $popup_style;

            $popup_style = empty($popup_style) ? 'default' : $popup_style;

            $gs_more_link = sprintf('...<a class="gs_coach_pop open-popup-link" data-mfp-src="#gs_coach_popup_%s_%s" href="javascript:void(0)" data-theme="%s">%s</a>', $coach_id, $shortcode_id, 'gs-coach-popup--' . esc_attr($popup_style), esc_html($gs_coach_more));
        } else if ($link_type == 'panel') {

            $gs_more_link = sprintf('...<a class="gs_coach_pop gs_coach_panelslide_link" id="gscoachlink_%1$s_%2$s" href="#gscoach_%1$s_%2$s">%3$s</a>', $coach_id, $shortcode_id, esc_html($gs_coach_more));
        } else if ($link_type == 'drawer') {

            $gs_more_link = sprintf('...<a href="%s">%s</a>', get_the_permalink(), esc_html($gs_coach_more));
        } else if ($link_type == 'custom') {

            $target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
            $gs_more_link = sprintf('...<a href="%s" %s>%s</a>', esc_url($custom_page_link), $target, esc_html($gs_coach_more));
        }
    }

    // Reduce the description length
    if ($max_length > 0 && mb_strlen($description) > $max_length) {
        $description = mb_substr($description, 0, $max_length) . $gs_more_link;
    }

    return echo_return($description, $echo);
}

function coach_thumbnail($size, $echo = false) {

    $disable_lazy_load = getoption('disable_lazy_load', 'off');
    $lazy_load_class   = getoption('lazy_load_class', 'skip-lazy');

    $coach_id = get_the_ID();

    if (has_post_thumbnail()) {

        $size = apply_filters('gs_coach_coach_thumbnail_size', $size, $coach_id);
        if (empty($size)) $size = 'large';

        $classes = ['gs_coach_coach--image'];

        if ($disable_lazy_load == 'on' && !empty($lazy_load_class)) {
            $classes[] = $lazy_load_class;
        }

        $classes = (array) apply_filters('gs_coach_thumbnail_classes', $classes);

        $thumbnail = get_the_post_thumbnail($coach_id, $size, [
            'class' => implode(' ', $classes),
            'alt' => get_the_title(),
            'itemprop' => 'image'
        ]);
    } else {

        $thumbnail = sprintf('<img src="%s" alt="%s" itemprop="image"/>', GSCOACH_PLUGIN_URI . '/assets/img/no_img.jpg', get_the_title());
    }

    $thumbnail = apply_filters('gs_coach_coach_thumbnail_html', $thumbnail, $coach_id);

    return echo_return($thumbnail, $echo);
}

function coach_thumbnail_custom($size, $shortcode_id, $has_link = true, $link_type = 'single_page', $echo = false) {

    $disable_lazy_load = getoption('disable_lazy_load', 'off');
    $lazy_load_class   = getoption('lazy_load_class', 'skip-lazy');

    $coach_id = get_the_ID();

    if (has_post_thumbnail()) {

        $size = apply_filters('gs_coach_coach_thumbnail_size', $size, $coach_id);
        if (empty($size)) $size = 'large';

        $classes = ['gs_coach_coach--image'];

        if ($disable_lazy_load == 'on' && !empty($lazy_load_class)) {
            $classes[] = $lazy_load_class;
        }

        $classes = (array) apply_filters('gs_coach_thumbnail_classes', $classes);

        $thumbnail = get_the_post_thumbnail($coach_id, $size, [
            'class' => implode(' ', $classes),
            'alt' => get_the_title(),
            'itemprop' => 'image'
        ]);

        if ( $link_type == 'custom' ) {
            $custom_page_link = get_post_meta( $coach_id, '_gscoach_custom_page', true );
            if ( empty($custom_page_link) ) {
                $default_link_type = getoption('single_link_type', 'single_page');
                if ( $default_link_type == 'none' ) {
                    $has_link = false;
                } else {
                    $link_type = $default_link_type;
                }
            }
        }

        if ($has_link) {

            if ($link_type == 'single_page') {

                $linked_thumb = sprintf('<a href="%s">%s <div class="gs_coach_image__overlay"></div></a>', get_the_permalink(), $thumbnail);
            } else if ($link_type == 'popup') {

                global $popup_style;

                $popup_style = empty($popup_style) ? 'default' : $popup_style;

                $linked_thumb = sprintf('<a class="gs_coach_pop open-popup-link" data-mfp-src="#gs_coach_popup_%s_%s" data-theme="%s" href="javascript:void(0)">%s <div class="gs_coach_image__overlay"></div></a>', get_the_ID(), $shortcode_id, 'gs-coach-popup--' . esc_attr($popup_style), $thumbnail);
            } else if ($link_type == 'panel') {

                $linked_thumb = sprintf('<a class="gs_coach_pop gs_coach_panelslide_link" id="gscoachlinkp_%1$s_%2$s" href="#gscoach_%1$s_%2$s">%3$s <div class="gs_coach_image__overlay"></div></a>', get_the_ID(), $shortcode_id, $thumbnail);
            } else if ($link_type == 'drawer') {

                $linked_thumb = sprintf('<a href="%s">%s <div class="gs_coach_image__overlay"></div></a>', get_the_permalink(), $thumbnail);
            } else if ($link_type == 'custom') {

                $target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
                $linked_thumb = sprintf('<a href="%s" %s>%s <div class="gs_coach_image__overlay"></div></a>', esc_url($custom_page_link), $target, $thumbnail);
            }

            return echo_return($linked_thumb, $echo);
        }
    } else {
        $thumbnail = sprintf('<img src="%s" alt="%s" itemprop="image"/>', GSCOACH_PLUGIN_URI . '/assets/img/no_img.jpg', get_the_title());
    }

    $thumbnail = apply_filters('gs_coach_coach_thumbnail_html', $thumbnail, $coach_id);

    return echo_return($thumbnail, $echo);
}

function coach_thumbnail_with_link($shortcode_id, $size, $has_link = false, $link_type = 'single_page', $overlay = false, $extra_link_class = '') {

    $coach_id = get_the_ID();
    $image_html = coach_thumbnail($size, false);

    $img_overlay = '';
    if ($overlay) {
        $img_overlay = '<div class="gs_coach_image__overlay"></div>';
    }

    $before = $after = '';

    if ( $link_type == 'custom' ) {
        $custom_page_link = get_post_meta( $coach_id, '_gscoach_custom_page', true );
        if ( empty($custom_page_link) ) {
            $default_link_type = getoption('single_link_type', 'single_page');
            if ( $default_link_type == 'none' ) {
                $has_link = false;
            } else {
                $link_type = $default_link_type;
            }
        }
    }

    if ($has_link) {

        if ($link_type == 'single_page') {

            $before = sprintf('<a class="%s" href="%s">', esc_attr($extra_link_class), get_the_permalink());
        } else if ($link_type == 'popup') {

            global $popup_style;

            $popup_style = empty($popup_style) ? 'default' : $popup_style;

            $before = sprintf('<a class="gs_coach_pop open-popup-link %s" data-mfp-src="#gs_coach_popup_%s_%s" data-theme="%s" href="javascript:void(0);">', esc_attr($extra_link_class), $coach_id, $shortcode_id, 'gs-coach-popup--' . esc_attr($popup_style));
        } else if ($link_type == 'panel') {

            $before = sprintf('<a class="gs_coach_pop gs_coach_panelslide_link %1$s" id="gscoachlink_%2$s_%3$s" href="#gscoach_%2$s_%3$s">', esc_attr($extra_link_class), $coach_id, $shortcode_id);
        } else if ($link_type == 'drawer') {

            $before = sprintf('<a class="%s" href="%s">', esc_attr($extra_link_class), get_the_permalink());
        } else if ($link_type == 'custom') {

            $target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
            $before = sprintf('<a class="%s" %s href="%s">', esc_attr($extra_link_class), $target, esc_url($custom_page_link));
        }

        $after = '</a>';
    }

    return $before . $image_html . $img_overlay . $after;
}

function coach_name($shortcode_id, $echo = false, $has_link = true, $link_type = 'single_page', $tag = 'div', $extra_classes = '', $no_default_class = false, $custom_title = '') {

    $coach_id = get_the_ID();

    if (empty($tag) || !in_array($tag, ['div', 'p', 'span', 'td', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) $tag = 'div';

    $the_title = $custom_title ?: get_the_title();

    if ( $link_type == 'custom' ) {
        $custom_page_link = get_post_meta( $coach_id, '_gscoach_custom_page', true );
        if ( empty($custom_page_link) ) {
            $default_link_type = getoption('single_link_type', 'single_page');
            if ( $default_link_type == 'none' ) {
                $has_link = false;
            } else {
                $link_type = $default_link_type;
            }
        }
    }

    if ($has_link) {

        if ($link_type == 'single_page') {

            $the_title = sprintf('<a href="%s">%s</a>', get_the_permalink(), $the_title);
        } else if ($link_type == 'popup') {

            global $popup_style;

            $popup_style = empty($popup_style) ? 'default' : $popup_style;

            $the_title = sprintf('<a class="gs_coach_pop open-popup-link" data-mfp-src="#gs_coach_popup_%s_%s" data-theme="%s" href="javascript:void(0)">%s</a>', get_the_ID(), $shortcode_id, 'gs-coach-popup--' . esc_attr($popup_style), $the_title);
        } else if ($link_type == 'panel') {

            $the_title = sprintf('<a class="gs_coach_pop gs_coach_panelslide_link" id="gscoachlinkp_%1$s_%2$s" href="#gscoach_%1$s_%2$s">%3$s</a>', get_the_ID(), $shortcode_id, $the_title);
        } else if ($link_type == 'drawer') {

            $the_title = sprintf('<a href="%s">%s</a>', get_the_permalink(), $the_title);
        } else if ($link_type == 'custom') {

            $target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
            $the_title = sprintf('<a href="%s" %s>%s</a>', esc_url($custom_page_link), $target, $the_title);
        }
    }

    $classes = $no_default_class ? '' : 'gs-coach-name ';

    $classes .= $extra_classes;

    $name = sprintf('<%1$s class="%2$s" itemprop="name">%3$s</%1$s>', $tag, $classes, $the_title);

    $name = apply_filters('gs_coach_coach_name_html', $name, $coach_id);

    return echo_return($name, $echo);
}

function getoption($option, $default = '') {
    $prefs = plugin()->builder->_get_shortcode_pref( false );
    return isset($prefs[$option]) ? $prefs[$option] : $default;
}

function coach_secondary_thumbnail($size, $echo = false) {

    $coach_id = get_the_ID();

    $thumbnail_id = get_post_meta($coach_id, 'second_featured_img', true);

    $size = apply_filters('gs_coach_coach_secondary_thumbnail_size', $size, $coach_id);
    if (empty($size)) $size = 'large';

    $thumbnail = '';

    if ($thumbnail_id) {

        $classes = (array) apply_filters('gs_coach_secondary_thumbnail_classes', ['gs_coach_coach--image']);

        $thumbnail = wp_get_attachment_image($thumbnail_id, $size, false, [
            'class' => implode(' ', $classes),
            'alt' => get_the_title(),
            'itemprop' => 'image'
        ]);
    }

    $thumbnail = apply_filters('gs_coach_coach_secondary_thumbnail_html', $thumbnail, $coach_id);

    return echo_return($thumbnail, $echo);
}

function coach_email_mailto($icon = '', $echo = false) {

    $coach_id = get_the_ID();

    $email = get_post_meta($coach_id, '_gscoach_email', true);
    $email_cc = get_post_meta($coach_id, '_gs_cc', true);
    $email_bcc = get_post_meta($coach_id, '_gs_bcc', true);

    // Remove spaces from comma separated emails_cc and emails_bcc & validate each email
    $email_cc = implode(',', array_filter(array_map('trim', explode(',', $email_cc)), 'is_email'));
    $email_bcc = implode(',', array_filter(array_map('trim', explode(',', $email_bcc)), 'is_email'));

    $email_link = sprintf('<a href="mailto:%1$s%2$s%3$s">%4$s%1$s</a>', $email, !empty($email_cc) ? '?cc=' . $email_cc : '', !empty($email_bcc) ? '&bcc=' . $email_bcc : '', $icon);

    $email_link = apply_filters('gs_coach_coach_email_link', $email_link, $coach_id);

    return echo_return($email_link, $echo);
}

function coach_custom() {

    $coach_id = get_the_ID();

    $thumbnail_id = get_post_meta($coach_id, 'second_featured_img', true);

    // $size = apply_filters( 'gs_coach_coach_secondary_thumbnail_size', $size, $coach_id );
    // if ( empty($size) ) $size = 'large';

    $thumbnail = '';

    if ($thumbnail_id) {

        $classes = (array) apply_filters('gs_coach_secondary_thumbnail_classes', ['gs_coach_coach--image']);

        $thumbnail = wp_get_attachment_image($thumbnail_id, array('50', '50'), false, [
            'class' => implode(' ', $classes),
            'alt' => get_the_title(),
            'itemprop' => 'image'
        ]);
    }

    $thumbnail = apply_filters('gs_coach_coach_secondary_thumbnail_html', $thumbnail, $coach_id);

    return echo_return($thumbnail, true);
}

function format_phone($num) {

    $num = preg_replace('/[^0-9]/', '', $num);
    $len = strlen($num);

    if ($len == 7) $num = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{1})/', '($1) $2$3-', $num);
    elseif ($len == 8) $num = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{1})/', '($1) $2$3-', $num);
    elseif ($len == 9) $num = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{1})([0-9]{2})/', '($1) $2$3-$4', $num);
    elseif ($len == 10) $num = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{1})([0-9]{3})/', '($1) $2$3-$4', $num);

    return $num;
}

function get_meta_values($meta_key, $args) {

    extract(shortcode_atts([
        'post_type' => 'gs_coaches',
        'status' => 'publish',
        'order_by' => true,
        'order' => 'ASC',
        'post_ids' => []
    ], $args));

    global $wpdb;

    if (empty($meta_key)) return [];

    if ($order_by) {
        $order == 'ASC' ? $order : 'DESC';
        $order_by = sprintf(' ORDER BY pm.meta_value %s', $order);
    } else {
        $order_by = '';
    }

    $query = $wpdb->prepare("
            SELECT pm.meta_value FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = %s 
            AND p.post_status = %s 
            AND p.post_type = %s 
        ", $meta_key, $status, $post_type);

    if (!empty($post_ids)) {
        $post_ids = implode("','", $post_ids);
        $query .= "AND p.ID IN ('" . $post_ids . "')";
    }

    $query .= $order_by;

    $result = $wpdb->get_col($query);

    $result = array_values(array_unique($result));

    return $result;
}

function get_meta_values_options($meta_key = '', $post_type = 'gs_coaches', $status = 'publish', $echo = true) {

    $meta_values = get_meta_values($meta_key, $post_type, $status);

    $html = '';

    foreach ($meta_values as $meta_value) {
        $html .= sprintf('<option value=".%s">%s</option>', sanitize_title($meta_value), esc_html($meta_value));
    }

    return echo_return($html, $echo);
}

function gs_cols_to_number($cols) {

    return (12 / (int) str_replace('_', '.', $cols));
}

function get_carousel_data($cols_desktop, $cols_tablet, $cols_mobile_portrait, $cols_mobile, $echo = true) {

    $carousel_data = [
        'data-carousel-desktop'         => gs_cols_to_number($cols_desktop),
        'data-carousel-tablet'             => gs_cols_to_number($cols_tablet),
        'data-carousel-mobile-portrait' => gs_cols_to_number($cols_mobile_portrait),
        'data-carousel-mobile'             => gs_cols_to_number($cols_mobile)
    ];

    $carousel_data = array_map(function ($key, $val) {
        return $key . '=' . esc_attr($val);
    }, array_keys($carousel_data), array_values($carousel_data));

    $carousel_data = implode(' ', $carousel_data);

    return echo_return($carousel_data, $echo);
}

function get_col_classes($desktop = '3', $tablet = '4', $mobile_portrait = '6', $mobile = '12') {
    return sprintf('gs-col-lg-%s gs-col-md-%s gs-col-sm-%s gs-col-xs-%s', $desktop, $tablet, $mobile_portrait, $mobile);
}


function gs_coach_get_terms($term_name, $order = 'ASC', $orderby = 'name', $exclude = [], $hide_empty = false) {

    $term_name = 'gs_' . str_replace('gs_', '', $term_name);

    $taxonomies = get_taxonomies([ 'type' => 'restricted', 'enabled' => true ]);

    if ( ! in_array( $term_name, $taxonomies ) ) return [];

    $args = [
        'taxonomy' => $term_name,
        'orderby'  => $orderby,
        'order'    => $order,
        'exclude' => (array) $exclude,
        'hide_empty' => $hide_empty
    ];

    $args = apply_filters('gs_coach_get_terms', $args);

    $terms = get_terms($args);

    return wp_list_pluck($terms, 'name', 'slug');
}

function string_to_array($terms = '') {
    if (empty($terms)) return [];
    return (array) array_filter(explode(',', $terms));
}

function get_taxonomies( $args = [] ) {

    $args = wp_parse_args( $args, [
        'enabled' => true,
        'restricted' => true
    ]);

    $taxonomies = [
        'group' => 'gs_coach_group',
        'tag' => 'gs_coach_tag',
        'language' => 'gs_coach_language',
        'location' => 'gs_coach_location',
        'gender' => 'gs_coach_gender',
        'specialty' => 'gs_coach_specialty',
        'extra_one' => 'gs_coach_extra_one',
        'extra_two' => 'gs_coach_extra_two',
        'extra_three' => 'gs_coach_extra_three',
        'extra_four' => 'gs_coach_extra_four',
        'extra_five' => 'gs_coach_extra_five'
    ];

    if ( $args['restricted'] && ! is_pro_active_and_valid() ) {
        $taxonomies = array_intersect_key($taxonomies, array_flip(['group', 'tag']));
    }

    if ( $args['enabled'] ) {
        $taxonomies = array_filter($taxonomies, function($taxonomy) {
            return plugin()->builder->get_tax_option( 'enable_' . $taxonomy . '_tax' ) == 'on';
        }, ARRAY_FILTER_USE_KEY);
    }

    return array_values( $taxonomies );
}

function get_terms_for_filter($term_name, $hide_empty = false, $include = '', $order = 'ASC', $orderby = 'name') {

    $term_name = 'gs_' . str_replace('gs_', '', $term_name);

    $taxonomies = get_taxonomies([ 'type' => 'restricted', 'enabled' => true ]);

    if ( ! in_array( $term_name, $taxonomies ) ) return [];

    $args = [
        'taxonomy'  => $term_name,
        'orderby'   => $orderby,
        'order'     => $order,
        'hide_empty' => $hide_empty,
        'include' => string_to_array($include)
    ];

    $args = apply_filters('gs_coach_get_terms_for_filter', $args);

    return get_terms( $args );
}

function get_terms_options($terms, $echo = true) {

    $html = '';

    foreach ($terms as $term) {
        $html .= sprintf('<option value=".%s">%s</option>', esc_attr($term->slug), esc_html($term->name));
    }

    return echo_return($html, $echo);
}

function setup_group_to_posts($query) {

    if (empty($query->posts)) return;

    foreach ($query->posts as $post_key => $post) {

        $terms = get_the_terms($post->ID, 'gs_coach_group');
        $terms = empty($terms) ? [] : wp_list_pluck($terms, 'slug');
        $query->posts[$post_key]->gs_coach_group = (array) $terms;
    }
}

function filter_posts_by_term($group_slug, $query_posts) {

    $posts = array_filter($query_posts, function ($post) use ($group_slug) {
        return in_array($group_slug, $post->gs_coach_group);
    });

    return array_values($posts);
}

function get_coach_terms_slugs($term_name, $separator = ' ') {

    global $post;

    $term_name = 'gs_' . str_replace('gs_', '', $term_name);

    $terms = get_the_terms($post->ID, $term_name);

    if (!empty($terms) && !is_wp_error($terms)) {
        $terms = implode($separator, wp_list_pluck($terms, 'slug'));
        return $terms;
    }

    return '';
}

function get_shortcodes() {

    return plugin()->builder->fetch_shortcodes(null, false, true);
}

function select_builder($name, $options, $selected = "", $selecttext = "", $class = "", $optionvalue = 'value') {

    if (is_array($options)) {

        $select_html = sprintf('<select name="%1$s" id="%1$s" class="%2$s">', esc_attr($name), esc_attr($class));

        if ($selecttext) $select_html .= sprintf('<option value="">%s</option>', esc_html($selecttext));

        foreach ($options as $key => $option) {
            $value = $optionvalue == 'value' ? $option : $key;
            $is_selected = $value == $selected ? 'selected="selected"' : '';
            $select_html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $is_selected, esc_html($option));
        }

        $select_html .= '</select>';
        echo gs_wp_kses($select_html);
    }
}

function terms_hierarchically(array &$cats, array &$into, $parentId = 0, $exclude_group = []) {

    foreach ($cats as $i => $cat) {
        if (in_array($cat->term_id, $exclude_group)) continue;
        if ($cat->parent == $parentId) {
            $into[$cat->term_id] = $cat;
            unset($cats[$i]);
        }
    }

    foreach ($into as $topCat) {
        $topCat->children = array();
        terms_hierarchically($cats, $topCat->children, $topCat->term_id, $exclude_group);
    }
}

function term_walker($term) {

    if (!empty($term->children)) : ?>
        <ul class="filter-cats--sub">
            <?php foreach ($term->children as $_term) :

                $has_child = !empty($_term->children);

            ?>

                <li class="filter <?php echo $has_child ? 'has-child' : ''; ?>">
                    <a href="javascript:void(0)" data-filter=".<?php echo esc_attr($_term->slug); ?>">
                        <span><?php echo esc_html($_term->name); ?></span>
                        <?php if ($has_child) : ?>
                            <span class="sub-arrow fa fa-angle-right"></span>
                        <?php endif; ?>
                    </a>
                    <?php term_walker($_term); ?>
                </li>

            <?php endforeach; ?>
        </ul>
<?php endif;
}

/*
 * @param $version          all | 1 | 2
 * @param $type             both | free | pro
 * @param $data_type        full | label | value
 */
function get_themes_list($version = 'all', $type = 'both', $data_type = 'full') {

    $themes = [];
    $versions = $version != 'all' ? [$version] : [1, 2];
    $methods = [];

    $versions = array_reverse($versions);

    foreach ($versions as $version) {
        if ($type == 'free' || $type == 'both') {
            $methods[] = 'get_' . 'free' . '_themes_v_' . $version;
        }
        if ($type == 'pro' || $type == 'both') {
            $methods[] = 'get_' . 'pro' . '_themes_v_' . $version;
        }
    }

    foreach ($methods as $method) {
        if (is_callable(['GSCOACH\Builder', $method], true, $callable_name)) {
            $themes = array_merge($themes, $callable_name());
        }
    }

    if ($data_type == 'full') return $themes;

    return wp_list_pluck($themes, $data_type);
}

function gs_coach_get_terms_names($term_name, $separator = ', ') {

    global $post;

    $terms = get_the_terms($post->ID, $term_name);

    if (!empty($terms) && !is_wp_error($terms)) {
        $terms = implode($separator, wp_list_pluck($terms, 'name'));
        return $terms;
    }
}

function gs_coach_coach_location($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_location', $separator);
}

function gs_coach_coach_language($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_language', $separator);
}

function gs_coach_coach_specialty($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_specialty', $separator);
}

function gs_coach_coach_gender($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_gender', $separator);
}

function gs_coach_coach_extra_one($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_extra_one', $separator);
}

function gs_coach_coach_extra_two($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_extra_two', $separator);
}

function gs_coach_coach_extra_three($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_extra_three', $separator);
}

function gs_coach_coach_extra_four($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_extra_four', $separator);
}

function gs_coach_coach_extra_five($separator = ', ') {
    return gs_coach_get_terms_names('gs_coach_extra_five', $separator);
}

function minimize_css_simple($css) {
    // https://datayze.com/howto/minify-css-with-php
    $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
    $css = preg_replace('/\s{2,}/', ' ', $css);
    $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return $css;
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

function get_social_links($post_id = null) {
    if (empty($post_id)) $post_id = get_the_ID();
    if (empty($post_id)) return [];
    $social_links = (array) get_post_meta($post_id, '_gscoach_socials', true);
    $social_links = array_filter($social_links);
    return (array) apply_filters('gs_coach_coach_social_links', $social_links, $post_id);
}

function get_skills($post_id = null) {
    if (empty($post_id)) $post_id = get_the_ID();
    if (empty($post_id)) return [];
    $skills = (array) get_post_meta($post_id, '_gscoach_skills', true);
    $skills = array_filter($skills);
    return (array) apply_filters('gs_coach_coach_skills', $skills, $post_id);
}

function get_certificate_ids($post_id = null) {
    if (empty($post_id)) $post_id = get_the_ID();
    if (empty($post_id)) return [];
    $certificate_ids = (array) get_post_meta($post_id, 'gscoach_certif_gallery', true);
    
    if( '' === $certificate_ids[0] ){
        return;
    }

    $certificate_ids = array_filter($certificate_ids);
    $certificate_ids = explode(',', $certificate_ids[0]);

    return $certificate_ids;
}

function isPreview() {
    return isset($_REQUEST['gscoach_shortcode_preview']) && !empty($_REQUEST['gscoach_shortcode_preview']);
}

function is_internal_url($url) {
    $home_url = home_url();
    return str_contains($url, $home_url);
}

function get_post_type_title() {
    return __( 'Coaches', 'gscoach' );
}

function gs_get_post_type_archive_title() {
    $archive_title = getoption('archive_page_title', get_post_type_title());
    if ( empty($archive_title) ) return get_post_type_title();
    return $archive_title;
}


function gs_star_rating( $args = array() ) {
	$defaults    = array(
		'rating' => 0,
		'type'   => 'rating',
		'number' => 0,
		'echo'   => true,
	);
	$parsed_args = wp_parse_args( $args, $defaults );

	// Non-English decimal places when the $rating is coming from a string.
	$rating = (float) str_replace( ',', '.', $parsed_args['rating'] );

	// Convert percentage to star rating, 0..5 in .5 increments.
	if ( 'percent' === $parsed_args['type'] ) {
		$rating = round( $rating / 10, 0 ) / 2;
	}

	// Calculate the number of each type of star needed.
	$full_stars  = floor( $rating );
	$half_stars  = ceil( $rating - $full_stars );
	$empty_stars = 5 - $full_stars - $half_stars;

	if ( $parsed_args['number'] ) {
		/* translators: Hidden accessibility text. 1: The rating, 2: The number of ratings. */
		$format = _n( '%1$s rating based on %2$s rating', '%1$s rating based on %2$s ratings', $parsed_args['number'] );
		$title  = sprintf( $format, number_format_i18n( $rating, 1 ), number_format_i18n( $parsed_args['number'] ) );
	} else {
		/* translators: Hidden accessibility text. %s: The rating. */
		$title = sprintf( __( '%s rating' ), number_format_i18n( $rating, 1 ) );
	}

	$output  = '<div class="star-rating">';
	$output .= '<span class="screen-reader-text">' . $title . '</span>';
	$output .= str_repeat( '<div class="star fas fa-star" aria-hidden="true"></div>', $full_stars );
	$output .= str_repeat( '<div class="star fas fa-star-half-alt" aria-hidden="true"></div>', $half_stars );
	$output .= str_repeat( '<div class="star far fa-star" aria-hidden="true"></div>', $empty_stars );
	$output .= '</div>';

	if ( $parsed_args['echo'] ) {
		echo $output;
	}

	return $output;
}


function get_meta_field_name( $field_key ) {
    $fields = [
        '_gscoach_profession' => get_translation('gs_coach_profession'),
        '_gscoach_experience' => get_translation('gs_coach_experience'),
        '_gscoach_education' => get_translation('gs_coach_education'),
        '_gscoach_ribbon' => get_translation('gs_coach_ribbon'),
        '_gscoach_address' => get_translation('gs_coach_address'),
        '_gscoach_state' => get_translation('gs_coach_state'),
        '_gscoach_country' => get_translation('gs_coach_country'),
        '_gscoach_contact' => get_translation('gs_coach_contact'),
        '_gscoach_email' => get_translation('gs_coach_email'),
        '_gscoach_shedule' => get_translation('gs_coach_schedule'),
        '_gscoach_available' => get_translation('gs_coach_availablity'),
        '_gscoach_psite' => get_translation('gs_coach_personal_site'),
        '_gscoach_psite_url' => get_translation('gs_coach_personal_site_url'),
        '_gscoach_psite_btn_text' => get_translation('gs_coach_personal_site_btn_text'),
        '_gscoach_psite_target' => get_translation('gs_coach_personal_site_btn_target'),
        '_gscoach_courselink' => get_translation('gs_coach_course_link'),
        '_gscoach_courselink_url' => get_translation('gs_coach_course_link_url'),
        '_gscoach_courselink_btn_text' => get_translation('gs_coach_course_link_btn_text'),
        '_gscoach_courselink_target' => get_translation('gs_coach_course_link_btn_target'),
        '_gscoach_fee' => get_translation('gs_coach_fee'),
        '_gscoach_review' => get_translation('gs_coach_review'),
        '_gscoach_rating' => get_translation('gs_coach_rating'),
        '_gscoach_custom_page' => get_translation('gs_coach_custom_page')
    ];
    
    return isset($fields[$field_key]) ? $fields[$field_key] : '';
}

function generate_meta_items( $fields ) {

    $array = [];

    foreach ($fields as $field) {
        $array[] = [
            'name' => get_meta_field_name($field),
            'key' => $field
        ];
    }

    return $array;
}

function gs_get_sort_metas_default(){
    return [
        '_gscoach_profession',
        '_gscoach_experience',
        '_gscoach_education',
        '_gscoach_address',
        '_gscoach_state',
        '_gscoach_country',
        '_gscoach_contact',
        '_gscoach_email',
        '_gscoach_shedule',
        '_gscoach_available',
        '_gscoach_psite',
        '_gscoach_courselink',
        '_gscoach_fee',
        '_gscoach_review',
        '_gscoach_rating'
    ];
}

function gs_get_sort_meta_keys() {
    $saved_meta = get_option('gs_coach_meta_order', gs_get_sort_metas_default());
    $saved_meta = array_merge( $saved_meta, gs_get_sort_metas_default() );
    return array_unique($saved_meta);
}

function gs_get_sort_metas() {
    $saved_meta = gs_get_sort_meta_keys();
    return generate_meta_items($saved_meta);
}

function gs_get_icon_class_by_meta_key( $key ){
    $icons = array(
        '_gscoach_profession' => 'fas fa-user-tie',
        '_gscoach_experience' => 'fas fa-cogs',
        '_gscoach_education' => 'fas fa-school',
        '_gscoach_address' => 'fas fa-address-book',
        '_gscoach_state' => 'fas fa-city',
        '_gscoach_country' => 'fas fa-globe',
        '_gscoach_contact' => 'far fa-id-badge',
        '_gscoach_email' => 'fas fa-at',
        '_gscoach_shedule' => 'fas fa-calendar-alt',
        '_gscoach_available' => 'fas fa-history',
        '_gscoach_psite' => 'fas fa-link',
        '_gscoach_courselink' => 'fas fa-book-reader',
        '_gscoach_fee' => 'fas fa-comment-dollar',
        '_gscoach_review' => 'fas fa-award',
        '_gscoach_rating' => 'fas fa-star-half-alt'
    );

    return isset($icons[$key]) ? $icons[$key] : '';
}

function get_current_full_url() {
    $protocol = is_ssl() ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'];
    $request  = $_SERVER['REQUEST_URI'];
    return $protocol . $host . $request;
}

function get_pagination( $shortcode_id, $items_per_page = 6 ) {

    // Generate page parameter name
    $param_name = 'paged' . $shortcode_id;
    
    // Current Page Number
    $current = max( 1, $_GET[$param_name] ?? 1 );

    // Calculate total pages
    $total_pages = ceil( $GLOBALS['gs_coach_loop']->found_posts / $items_per_page );

    // Generate the current URL with the page placeholder
    $current_url = get_current_full_url();
    $current_url = remove_query_arg( $param_name, $current_url );
    $current_url = add_query_arg( $param_name, '%#%', $current_url );
    
    // Print the pagination links
    $pagination = "<div class='gs-coach-pagination'>";
    $pagination .= paginate_links( array(
        'base' => $current_url,
        'current' => $current,
        'total' => $total_pages,
        'prev_next' => true,
        'next_text' => '<i class="fa fa-angle-right"></i>',
        'prev_text' => '<i class="fa fa-angle-left"></i>',
    ));
    $pagination .= "</div>";

    return $pagination;
}

function get_ajax_pagination( $shortcode_id, $items_per_page = 6, $paged = 1 ) {

    // Generate page parameter name
    $param_name = 'paged' . $shortcode_id;
    
    // Current Page Number
    $current = max( 1, $paged ?? 1 );

    // Calculate total pages
    $total_pages = ceil( $GLOBALS['gs_coach_loop']->found_posts / $items_per_page );

    // Generate the current URL with the page placeholder
    $current_url = get_current_full_url();
    $current_url = remove_query_arg( $param_name, $current_url );
    $current_url = add_query_arg( $param_name, '%#%', $current_url );
    
    // Print the pagination links
    $pagination = "<div class='gs-coach-pagination gs-coach-ajax-pagination-link'>";
    $pagination .= paginate_links( array(
        'base' => $current_url,
        'current' => $current,
        'total' => $total_pages,
        'prev_next' => true,
        'next_text' => '<i class="fa fa-angle-right"></i>',
        'prev_text' => '<i class="fa fa-angle-left"></i>',
    ));
    $pagination .= "</div>";

    return $pagination;
}

function gs_filter_title_search_only( $search, $wp_query ) {
    global $wpdb;

    // Get the search term
    $search_term = $wp_query->get('s');

    if ( $search_term ) {
        // Escape for SQL LIKE query
        $like = '%' . $wpdb->esc_like( $search_term ) . '%';

        // Modify the search to only apply to post_title
        $search = $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s ", $like);
    }

    return $search;
}

function is_display_pagination( $carousel_enabled, $filter_enabled, $filter_type ) {

    if( $carousel_enabled === 'on' ) {
        return false;
    }
    
    if( 'on' === $filter_enabled && $filter_type === 'normal-filter' ){
        return false;
    }

    return true;
    
}