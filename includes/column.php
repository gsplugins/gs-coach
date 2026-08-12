<?php

namespace GSCOACH;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Column {

    public function __construct() {
        add_filter( 'manage_edit-gs_coaches_columns', [ $this, 'screen_columns' ] );
        add_action( 'manage_gs_coaches_posts_custom_column', [ $this, 'columns_content' ], 10, 2 );
        add_filter( 'manage_edit-gs_coaches_sortable_columns', [ $this, 'sort' ] );
    }

    function get_tax_option( $key ) {
        return plugin()->builder->get_tax_option( $key );
    }

    function screen_columns( $columns ) {

        $is_enabled_group_tax       = $this->get_tax_option('enable_group_tax') === 'on';
        $is_enabled_tag_tax         = $this->get_tax_option('enable_tag_tax') === 'on';
        $is_enabled_language_tax    = is_pro_active_and_valid() && $this->get_tax_option('enable_language_tax') === 'on';
        $is_enabled_location_tax    = is_pro_active_and_valid() && $this->get_tax_option('enable_location_tax') === 'on';
        $is_enabled_gender_tax      = is_pro_active_and_valid() && $this->get_tax_option('enable_gender_tax') === 'on';
        $is_enabled_specialty_tax   = is_pro_active_and_valid() && $this->get_tax_option('enable_specialty_tax') === 'on';
        $is_enabled_extra_one_tax   = is_pro_active_and_valid() && $this->get_tax_option('enable_extra_one_tax') === 'on';
        $is_enabled_extra_two_tax   = is_pro_active_and_valid() && $this->get_tax_option('enable_extra_two_tax') === 'on';
        $is_enabled_extra_three_tax = is_pro_active_and_valid() && $this->get_tax_option('enable_extra_three_tax') === 'on';
        $is_enabled_extra_four_tax  = is_pro_active_and_valid() && $this->get_tax_option('enable_extra_four_tax') === 'on';
        $is_enabled_extra_five_tax  = is_pro_active_and_valid() && $this->get_tax_option('enable_extra_five_tax') === 'on';

        $new_columns = [];

        if ( isset( $columns['cb'] ) ) {
            $new_columns['cb'] = $columns['cb'];
        }

        $new_columns['gscoach_featured_image'] = __( 'Coach Image', 'gscoach' );
        $new_columns['title']                  = __( 'Coach Name', 'gscoach' );
        $new_columns['_gscoach_profession']    = get_meta_field_name( '_gscoach_profession' ) ?: __( 'Profession', 'gscoach' );

        if ( $is_enabled_group_tax ) $new_columns['taxonomy-gs_coach_group'] = $this->get_tax_option('group_tax_plural_label');
        if ( $is_enabled_tag_tax ) $new_columns['taxonomy-gs_coach_tag'] = $this->get_tax_option('tag_tax_plural_label');
        if ( $is_enabled_language_tax ) $new_columns['taxonomy-gs_coach_language'] = $this->get_tax_option('language_tax_plural_label');
        if ( $is_enabled_location_tax ) $new_columns['taxonomy-gs_coach_location'] = $this->get_tax_option('location_tax_plural_label');
        if ( $is_enabled_gender_tax ) $new_columns['taxonomy-gs_coach_gender'] = $this->get_tax_option('gender_tax_plural_label');
        if ( $is_enabled_specialty_tax ) $new_columns['taxonomy-gs_coach_specialty'] = $this->get_tax_option('specialty_tax_plural_label');
        if ( $is_enabled_extra_one_tax ) $new_columns['taxonomy-gs_coach_extra_one'] = $this->get_tax_option('extra_one_tax_plural_label');
        if ( $is_enabled_extra_two_tax ) $new_columns['taxonomy-gs_coach_extra_two'] = $this->get_tax_option('extra_two_tax_plural_label');
        if ( $is_enabled_extra_three_tax ) $new_columns['taxonomy-gs_coach_extra_three'] = $this->get_tax_option('extra_three_tax_plural_label');
        if ( $is_enabled_extra_four_tax ) $new_columns['taxonomy-gs_coach_extra_four'] = $this->get_tax_option('extra_four_tax_plural_label');
        if ( $is_enabled_extra_five_tax ) $new_columns['taxonomy-gs_coach_extra_five'] = $this->get_tax_option('extra_five_tax_plural_label');

        $new_columns['date'] = __( 'Date', 'gscoach' );

        return $new_columns;
    }

    function featured_image( $post_ID ) {

        $post_thumbnail_id = get_post_thumbnail_id( $post_ID );

        if ( $post_thumbnail_id ) {
            $post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id );
            if ( empty($post_thumbnail_img) ) return '';
            return $post_thumbnail_img[0];
        }
    }

    function columns_content( $column_name, $post_ID ) {
        if ( 'gscoach_featured_image' === $column_name ) {
            $post_featured_image = $this->featured_image( $post_ID );
            if ( $post_featured_image ) {
                echo '<img src="' . esc_url( $post_featured_image ) . '" style="border-radius: 10%;" width="50" alt="" />';
            }
            return;
        }

        if ( '_gscoach_profession' === $column_name ) {
            echo esc_html( get_post_meta( $post_ID, '_gscoach_profession', true ) );
        }
    }

    function sort( $columns ) {
        $columns['taxonomy-gs_coach_group'] = 'taxonomy-gs_coach_group';
        return $columns;
    }

}
