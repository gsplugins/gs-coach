<?php

namespace GSCOACH;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Builder' ) ) {

    final class Builder {

        private $option_name = 'gs_coach_shortcode_prefs';
        private $taxonomy_option_name = 'gs_coach_taxonomy_settings';

        public function __construct() {
            
            add_action( 'admin_menu', array( $this, 'register_sub_menu') );
            add_action( 'admin_enqueue_scripts', array( $this, 'scripts') );
            add_action( 'wp_enqueue_scripts', array( $this, 'preview_scripts') );

            add_action( 'wp_ajax_gscoach_create_shortcode', array($this, 'create_shortcode') );
            add_action( 'wp_ajax_gscoach_clone_shortcode', array($this, 'clone_shortcode') );
            add_action( 'wp_ajax_gscoach_get_shortcode', array($this, 'get_shortcode') );
            add_action( 'wp_ajax_gscoach_update_shortcode', array($this, 'update_shortcode') );
            add_action( 'wp_ajax_gscoach_delete_shortcodes', array($this, 'delete_shortcodes') );
            add_action( 'wp_ajax_gscoach_temp_save_shortcode_settings', array($this, 'temp_save_shortcode_settings') );
            add_action( 'wp_ajax_gscoach_get_shortcodes', array($this, 'get_shortcodes') );

            add_action( 'wp_ajax_gscoach_get_shortcode_pref', array($this, 'get_shortcode_pref') );
            add_action( 'wp_ajax_gscoach_save_shortcode_pref', array($this, 'save_shortcode_pref') );

            add_action( 'wp_ajax_gscoach_get_taxonomy_settings', array($this, 'get_taxonomy_settings') );
            add_action( 'wp_ajax_gscoach_save_taxonomy_settings', array($this, 'save_taxonomy_settings') );

            add_action( 'template_include', array($this, 'populate_shortcode_preview') );
            add_action( 'show_admin_bar', array($this, 'hide_admin_bar_from_preview') );

            return $this;

        }

        public function is_preview() {

            return isset( $_REQUEST['gscoach_shortcode_preview'] ) && !empty($_REQUEST['gscoach_shortcode_preview']);

        }

        public function hide_admin_bar_from_preview( $visibility ) {

            if ( $this->is_preview() ) return false;

            return $visibility;

        }

        public function populate_shortcode_preview( $template ) {

            global $wp, $wp_query;
            
            if ( $this->is_preview() ) {

                // Create our fake post
                $post_id = 0;
                $post = new \stdClass();
                $post->ID = $post_id;
                $post->post_author = 1;
                $post->post_date = current_time( 'mysql' );
                $post->post_date_gmt = current_time( 'mysql', 1 );
                $post->post_title = __('Shortcode Preview', 'gscoach');
                $post->post_content = '[gscoach preview="yes" id="' . esc_attr($_REQUEST['gscoach_shortcode_preview']) . '"]';
                $post->post_status = 'publish';
                $post->comment_status = 'closed';
                $post->ping_status = 'closed';
                $post->post_name = 'fake-page-' . rand( 1, 99999 ); // append random number to avoid clash
                $post->post_type = 'page';
                $post->filter = 'raw'; // important!


                // Convert to WP_Post object
                $wp_post = new \WP_Post( $post );


                // Add the fake post to the cache
                wp_cache_add( $post_id, $wp_post, 'posts' );


                // Update the main query
                $wp_query->post = $wp_post;
                $wp_query->posts = array( $wp_post );
                $wp_query->queried_object = $wp_post;
                $wp_query->queried_object_id = $post_id;
                $wp_query->found_posts = 1;
                $wp_query->post_count = 1;
                $wp_query->max_num_pages = 1; 
                $wp_query->is_page = true;
                $wp_query->is_singular = true; 
                $wp_query->is_single = false; 
                $wp_query->is_attachment = false;
                $wp_query->is_archive = false; 
                $wp_query->is_category = false;
                $wp_query->is_tag = false; 
                $wp_query->is_tax = false;
                $wp_query->is_author = false;
                $wp_query->is_date = false;
                $wp_query->is_year = false;
                $wp_query->is_month = false;
                $wp_query->is_day = false;
                $wp_query->is_time = false;
                $wp_query->is_search = false;
                $wp_query->is_feed = false;
                $wp_query->is_comment_feed = false;
                $wp_query->is_trackback = false;
                $wp_query->is_home = false;
                $wp_query->is_embed = false;
                $wp_query->is_404 = false; 
                $wp_query->is_paged = false;
                $wp_query->is_admin = false; 
                $wp_query->is_preview = false; 
                $wp_query->is_robots = false; 
                $wp_query->is_posts_page = false;
                $wp_query->is_post_type_archive = false;


                // Update globals
                $GLOBALS['wp_query'] = $wp_query;
                $wp->register_globals();


                include GSCOACH_PLUGIN_DIR . 'includes/shortcode-builder/preview.php';

                return;

            }

            return $template;

        }

        public function register_sub_menu() {

            add_submenu_page( 
                'edit.php?post_type=gs_coaches', 'Coach Shortcode', 'Coach Shortcode', 'publish_pages', 'gs-coach-shortcode', array( $this, 'view' )
            );

            add_submenu_page( 
                'edit.php?post_type=gs_coaches', 'Preference', 'Preference', 'publish_pages', 'gs-coach-shortcode#/preferences', array( $this, 'view' )
            );

            do_action( 'gs_after_shortcode_submenu' );

        }

        public function view() {

            include GSCOACH_PLUGIN_DIR . 'includes/shortcode-builder/page.php';

        }

        public static function get_coach_terms( $term_name, $idsOnly = false ) {

            $taxonomies = get_taxonomies([ 'type' => 'restricted', 'enabled' => true ]);

            if ( ! in_array( $term_name, $taxonomies ) ) return [];

            $_terms = get_terms( $term_name, [
                'hide_empty' => false,
            ]);

            if ( empty($_terms) ) return [];
            
            if ( $idsOnly ) return wp_list_pluck( $_terms, 'term_id' );

            $terms = [];

            foreach ( $_terms as $term ) {
                $terms[] = [
                    'label' => $term->name,
                    'value' => $term->term_id
                ];
            }

            return $terms;

        }

        public function scripts( $hook ) {

            if ( 'gs_coaches_page_gs-coach-shortcode' != $hook ) {
                return;
            }

            wp_register_style( 'gs-zmdi-fonts', GSCOACH_PLUGIN_URI . '/assets/libs/material-design-iconic-font/css/material-design-iconic-font.min.css', '', GSCOACH_VERSION, 'all' );
            wp_enqueue_style( 'gs-coach-shortcode', GSCOACH_PLUGIN_URI . '/assets/admin/css/shortcode.min.css', array('gs-zmdi-fonts'), GSCOACH_VERSION, 'all' );

            if( ! is_pro_active_and_valid() ){
                wp_register_script( 'gs-coach-shortcode', GSCOACH_PLUGIN_URI . '/assets/admin/js/shortcode.min.js', array('jquery'), GSCOACH_VERSION, true );
            }

            do_action( 'gs_coach_enqueue_admin_scripts', $hook );

            wp_enqueue_script( 'gs-coach-shortcode' );

            $data = array(
                "nonce"    => wp_create_nonce( "_gscoach_admin_nonce_gs_" ),
                "ajaxurl"  => admin_url( "admin-ajax.php" ),
                "adminurl" => admin_url(),
                "siteurl"  => home_url()
            );

            $data['shortcode_settings'] = $this->get_shortcode_default_settings();
            $data['shortcode_options']  = $this->get_shortcode_default_options();
            $data['translations']       = $this->get_translation_srtings();
            $data['preference']         = $this->get_shortcode_default_prefs();
            $data['preference_options'] = $this->get_shortcode_prefs_options();
            $data['taxonomy_default_settings']  = $this->get_taxonomy_default_settings();
            $data['taxonomy_settings']  = $this->get_taxonomy_settings();
            $data['enabled_plugins']    = $this->get_enabled_plugins();
            $data['is_multilingual']    = $this->is_multilingual_enabled();

            $data['demo_data'] = [
                'coach_data'      => wp_validate_boolean( get_option('gscoach_dummy_coach_data_created') ),
                'shortcode_data' => wp_validate_boolean( get_option('gscoach_dummy_shortcode_data_created') )
            ];

            wp_localize_script( 'gs-coach-shortcode', '_gscoach_data', $data );
            
        }

        public function get_enabled_plugins() {
            
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            $plugins = [];

            if ( is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
                
                $coach_groups = \acf_get_field_groups([
                    'post_type'	=> 'gs_coaches'
                ]);
                
                if ( !empty($coach_groups) ) {
                    $plugins[] = 'advanced-custom-fields';
                }
            }

            return $plugins;
        }

        public function preview_scripts( $hook ) {
            
            if ( ! $this->is_preview() ) return;

            wp_enqueue_style( 'gs-coach-shortcode-preview', GSCOACH_PLUGIN_URI . '/assets/css/preview.min.css', '', GSCOACH_VERSION );
            
        }

        public function get_wpdb() {

            global $wpdb;
            
            if ( wp_doing_ajax() ) $wpdb->show_errors = false;

            return $wpdb;

        }

        public function has_db_error() {

            $wpdb = $this->get_wpdb();

            if ( $wpdb->last_error === '') return false;

            return true;

        }

        public function validate_shortcode_settings( $shortcode_settings ) {
            $shortcode_settings = shortcode_atts( $this->get_shortcode_default_settings(), $shortcode_settings );
            return array_map( 'sanitize_text_field', $shortcode_settings );
        }

        protected function get_db_columns() {

            return array(
                'shortcode_name'     => '%s',
                'shortcode_settings' => '%s',
                'created_at'         => '%s',
                'updated_at'         => '%s',
            );

        }

        public function _get_shortcode( $shortcode_id, $is_ajax = false ) {

            if ( empty($shortcode_id) ) {
                if ( $is_ajax ) wp_send_json_error( __('Shortcode ID missing', 'gscoach'), 400 );
                return false;
            }

            $shortcode = wp_cache_get( 'gs_coach_shortcode' . $shortcode_id, 'gs_coach_memebrs' );

            // Return the cache if found
            if ( $shortcode !== false ) {
                if ( $is_ajax ) wp_send_json_success( $shortcode );
                return $shortcode;
            }

            $wpdb = $this->get_wpdb();

            $shortcode = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}gs_coaches WHERE id = %d LIMIT 1", absint($shortcode_id) ), ARRAY_A );

            if ( $shortcode ) {

                $shortcode["shortcode_settings"] = json_decode( $shortcode["shortcode_settings"], true );
                $shortcode["shortcode_settings"] = $this->validate_shortcode_settings( $shortcode["shortcode_settings"] );

                wp_cache_add( 'gs_coach_shortcode' . $shortcode_id, $shortcode, 'gs_coach_memebrs' );

                if ( $is_ajax ) wp_send_json_success( $shortcode );

                return $shortcode;

            }

            if ( $is_ajax ) wp_send_json_error( __('No shortcode found', 'gscoach'), 404 );

            return false;

        }

        public function _update_shortcode( $shortcode_id, $nonce, $fields, $is_ajax ) {

            if ( ! wp_verify_nonce( $nonce, '_gscoach_admin_nonce_gs_') || ! current_user_can( 'publish_pages' ) ) {
                if ( $is_ajax ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );
                return false;
            }

            if ( empty($shortcode_id) ) {
                if ( $is_ajax ) wp_send_json_error( __('Shortcode ID missing', 'gscoach'), 400 );
                return false;
            }
        
            $_shortcode = $this->_get_shortcode( $shortcode_id, false );
        
            if ( empty($_shortcode) ) {
                if ( $is_ajax ) wp_send_json_error( __('No shortcode found to update', 'gscoach'), 404 );
                return false;
            }
        
            $shortcode_name = !empty( $fields['shortcode_name'] ) ? $fields['shortcode_name'] : $_shortcode['shortcode_name'];
            $shortcode_settings = !empty( $fields['shortcode_settings']) ? $fields['shortcode_settings'] : $_shortcode['shortcode_settings'];

            // Remove dummy indicator on update
            if ( isset($shortcode_settings['gscoach-demo_data']) ) unset($shortcode_settings['gscoach-demo_data']);
        
            $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );
        
            $wpdb = $this->get_wpdb();
        
            $data = array(
                "shortcode_name" 	    => $shortcode_name,
                "shortcode_settings" 	=> json_encode($shortcode_settings),
                "updated_at" 		    => current_time( 'mysql')
            );
        
            $update_id = $wpdb->update( "{$wpdb->prefix}gs_coaches" , $data, array( 'id' => absint( $shortcode_id ) ),  $this->get_db_columns() );
        
            if ( $this->has_db_error() ) {
                if ( $is_ajax ) wp_send_json_error( sprintf( __( 'Database Error: %1$s', 'gscoach'), $wpdb->last_error), 500 );
                return false;
            }

            // Delete the shortcode cache
            wp_cache_delete( 'gs_coach_shortcodes', 'gs_coach_memebrs' );
            wp_cache_delete( 'gs_coach_shortcode' . $shortcode_id, 'gs_coach_memebrs' );

            do_action( 'gs_coach_shortcode_updated', $update_id );
            do_action( 'gsp_shortcode_updated', $update_id );
        
            if ($is_ajax) wp_send_json_success( array(
                'message' => __('Shortcode updated', 'gscoach'),
                'shortcode_id' => $update_id
            ));
        
            return $update_id;

        }
        
        public function fetch_shortcodes( $shortcode_ids = [], $is_ajax = false, $minimal = false ) {

            $wpdb = $this->get_wpdb();
            $fields = $minimal ? 'id, shortcode_name' : '*';

            if ( empty( $shortcode_ids ) ) {
                
                $shortcodes = wp_cache_get( 'gs_coach_shortcodes', 'gs_coach_memebrs' );

                if ( $shortcodes === false ) {
                    $shortcodes = $wpdb->get_results( "SELECT {$fields} FROM {$wpdb->prefix}gs_coaches ORDER BY id DESC", ARRAY_A );
                    wp_cache_add( 'gs_coach_shortcodes', $shortcodes, 'gs_coach_memebrs' );
                }

            } else {

                $how_many = count($shortcode_ids);
                $placeholders = array_fill(0, $how_many, '%d');
                $format = implode(', ', $placeholders);
                $query = "SELECT {$fields} FROM {$wpdb->prefix}gs_coaches WHERE id IN($format)";
                $shortcodes = $wpdb->get_results( $wpdb->prepare($query, $shortcode_ids), ARRAY_A );

            }

            // check for database error
            if ( $this->has_db_error() ) wp_send_json_error( sprintf(__('Database Error: %s'), $wpdb->last_error) );

            if ( $is_ajax ) {
                wp_send_json_success( $shortcodes );
            }

            return $shortcodes;

        }

        public function create_shortcode() {

            // validate nonce && check permission
            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );

            $shortcode_settings  = !empty( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : '';
            $shortcode_name  = !empty( $_POST['shortcode_name'] ) ? $_POST['shortcode_name'] : __( 'Undefined', 'gscoach' );

            if ( empty($shortcode_settings) || !is_array($shortcode_settings) ) {
                wp_send_json_error( __('Please configure the settings properly', 'gscoach'), 206 );
            }

            $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );

            $wpdb = $this->get_wpdb();

            $data = array(
                "shortcode_name" => $shortcode_name,
                "shortcode_settings" => json_encode($shortcode_settings),
                "created_at" => current_time( 'mysql'),
                "updated_at" => current_time( 'mysql'),
            );

            $wpdb->insert( "{$wpdb->prefix}gs_coaches", $data, $this->get_db_columns() );

            // check for database error
            if ( $this->has_db_error() ) wp_send_json_error( sprintf(__('Database Error: %s'), $wpdb->last_error), 500 );

            // Delete the shortcode cache
            wp_cache_delete( 'gs_coach_shortcodes', 'gs_coach_memebrs' );

            do_action( 'gs_coach_shortcode_created', $wpdb->insert_id );
            do_action( 'gsp_shortcode_created', $wpdb->insert_id );

            // send success response with inserted id
            wp_send_json_success( array(
                'message' => __('Shortcode created successfully', 'gscoach'),
                'shortcode_id' => $wpdb->insert_id
            ));
        }

        public function clone_shortcode() {

            // validate nonce && check permission
            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );

            $clone_id  = !empty( $_POST['clone_id']) ? $_POST['clone_id'] : '';

            if ( empty($clone_id) ) wp_send_json_error( __('Clone Id not provided', 'gscoach'), 400 );

            $clone_shortcode = $this->_get_shortcode( $clone_id, false );

            if ( empty($clone_shortcode) ) wp_send_json_error( __('Clone shortcode not found', 'gscoach'), 404 );


            $shortcode_settings  = $clone_shortcode['shortcode_settings'];
            $shortcode_name  = $clone_shortcode['shortcode_name'] .' '. __('- Cloned', 'gscoach');

            $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );

            $wpdb = $this->get_wpdb();

            $data = array(
                "shortcode_name" => $shortcode_name,
                "shortcode_settings" => json_encode($shortcode_settings),
                "created_at" => current_time( 'mysql'),
                "updated_at" => current_time( 'mysql'),
            );

            $wpdb->insert( "{$wpdb->prefix}gs_coaches", $data, $this->get_db_columns() );

            // check for database error
            if ( $this->has_db_error() ) wp_send_json_error( sprintf(__('Database Error: %s'), $wpdb->last_error), 500 );

            // Delete the shortcode cache
            wp_cache_delete( 'gs_coach_shortcodes', 'gs_coach_memebrs' );

            // Get the cloned shortcode
            $shotcode = $this->_get_shortcode( $wpdb->insert_id, false );

            // send success response with inserted id
            wp_send_json_success( array(
                'message' => __('Shortcode cloned successfully', 'gscoach'),
                'shortcode' => $shotcode,
            ));
        }

        public function get_shortcode() {

            $shortcode_id = !empty( $_GET['id']) ? absint( $_GET['id'] ) : null;

            $this->_get_shortcode( $shortcode_id, wp_doing_ajax() );

        }

        public function update_shortcode( $shortcode_id = null, $nonce = null ) {

            if ( ! $shortcode_id ) {
                $shortcode_id = !empty( $_POST['id']) ? $_POST['id'] : null;
            }
            
            if ( ! $nonce ) {
                $nonce = $_POST['_wpnonce'] ?: null;
            }
    
            $this->_update_shortcode( $shortcode_id, $nonce, $_POST, true );

        }

        public function delete_shortcodes() {

            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') )
                wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );
    
            $ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : null;
    
            if ( empty( $ids ) ) {
                wp_send_json_error( __('No shortcode ids provided', 'gscoach'), 400 );
            }
    
            $wpdb = $this->get_wpdb();
    
            $count = count( $ids );
    
            $ids = implode( ',', array_map('absint', $ids) );
            $wpdb->query( "DELETE FROM {$wpdb->prefix}gs_coaches WHERE ID IN($ids)" );
    
            if ( $this->has_db_error() ) wp_send_json_error( sprintf(__('Database Error: %s'), $wpdb->last_error), 500 );

            // Delete the shortcode cache
            wp_cache_delete( 'gs_coach_shortcodes', 'gs_coach_memebrs' );

            do_action( 'gs_coach_shortcode_deleted' );
            do_action( 'gsp_shortcode_deleted' );
    
            $m = _n( "Shortcode has been deleted", "Shortcodes have been deleted", $count, 'gscoach' ) ;
    
            wp_send_json_success( ['message' => $m] );

        }

        public function get_shortcodes() {

            $this->fetch_shortcodes( null, wp_doing_ajax() );

        }

        public function temp_save_shortcode_settings() {

            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') )
                wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );
            
            $temp_key = isset( $_POST['temp_key'] ) ? $_POST['temp_key'] : null;
            $shortcode_settings = isset( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : [];

            if ( empty($temp_key) ) wp_send_json_error( __('No temp key provided', 'gscoach'), 400 );
            if ( empty($shortcode_settings) ) wp_send_json_error( __('No temp settings provided', 'gscoach'), 400 );

            delete_transient( $temp_key );

            $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );
            set_transient( $temp_key, $shortcode_settings, DAY_IN_SECONDS ); // save the transient for 1 day

            wp_send_json_success([
                'message' => __('Temp data saved', 'gscoach'),
            ]);

        }

        public function get_translation_srtings() {
            return [

                'image_filter'       => __( 'Image Filter', 'gscoach' ),
                'hover_image_filter' => __( 'Image Filter on Hover', 'gscoach' ),

                'location'  => __('Location', 'gscoach'),
                'location--details'  => __('Select specific coach location to show that specific location coaches', 'gscoach'),

                'specialty'  => __('Specialty', 'gscoach'),
                'specialty--details'  => __('Select specific coach specialty to show that specific specialty coaches', 'gscoach'),

                'language'  => __('Language', 'gscoach'),
                'language--details'  => __('Select specific coach language to show that specific language coaches', 'gscoach'),

                'gender'  => __('Gender', 'gscoach'),
                'gender--details'  => __('Select specific coach gender to show that specific gender coaches', 'gscoach'),

                'include_extra_one'  => __('Extra One', 'gscoach'),
                'include_extra_one--details'  => __('Select specific coach extra one to show that specific extra one coaches', 'gscoach'),

                'include_extra_two'  => __('Extra Two', 'gscoach'),
                'include_extra_two--details'  => __('Select specific coach extra two to show that specific extra two coaches', 'gscoach'),

                'include_extra_three'  => __('Extra Three', 'gscoach'),
                'include_extra_three--details'  => __('Select specific coach extra three to show that specific extra three coaches', 'gscoach'),

                'include_extra_four'  => __('Extra Four', 'gscoach'),
                'include_extra_four--details'  => __('Select specific coach extra four to show that specific extra four coaches', 'gscoach'),

                'include_extra_five'  => __('Extra Five', 'gscoach'),
                'include_extra_five--details'  => __('Select specific coach extra five to show that specific extra five coaches', 'gscoach'),

                'install-demo-data' => __('Install Demo Data', 'gscoach'),
                'install-demo-data-description' => __('Quick start with GS Plugins by installing the demo data', 'gscoach'),

                'export-data' => __('Export Data', 'gscoach'),
                'export-data--description' => __('Export GS Coach Plugins data', 'gscoach'),

                'import-data' => __('Import Data', 'gscoach'),
                'import-data--description' => __('Import GS Coach Plugins data', 'gscoach'),

                'bulk-import' => __('Bulk Import', 'gscoach'),
                'bulk-import-description' => __('Add Coaches faster by GS Bulk Import feature', 'gscoach'),

                'preference' => __('Preference', 'gscoach'),
                'save-preference' => __('Save Preference', 'gscoach'),
                'save-settings' => __('Save Settings', 'gscoach'),
                'coaches-slug' => __('Coaches Slug', 'gscoach'),
                'coaches-slug-details' => __('Customize Coaches Post Type Slug, by default it is set to coaches', 'gscoach'),
                'replace-custom-slug' => __('Ignore Base Permalink Prefix', 'gscoach'),
                'replace-custom-slug-details' => __('Enable this option to use a custom structure without the base prefix.', 'gscoach'),

                'archive-page-slug' => __('Archive Page Slug', 'gscoach'),
                'archive-page-slug-details' => __('Set Custom Archive Page Slug, now it is set to', 'gscoach') . ' ' . get_post_type_archive_link( 'gs_coaches' ),

                'archive-page-title' => __('Archive Page Title', 'gscoach'),
                'archive-page-title-details' => __('Set Custom Archive Page Title, now it is set to', 'gscoach') . ' ' . gs_get_post_type_archive_title(),

                'taxonomies-page' => __('Taxonomies', 'gscoach'),
                'taxonomies-page--des' => __('Global settings for Taxonomies', 'gscoach'),

                'taxonomy_group' => $this->get_tax_option( 'group_tax_plural_label' ),
                'taxonomy_tag' => $this->get_tax_option( 'tag_tax_plural_label' ),
                'taxonomy_language' => $this->get_tax_option( 'language_tax_plural_label' ),
                'taxonomy_location' => $this->get_tax_option( 'location_tax_plural_label' ),
                'taxonomy_gender' => $this->get_tax_option( 'gender_tax_plural_label' ),
                'taxonomy_specialty' => $this->get_tax_option( 'specialty_tax_plural_label' ),
                'taxonomy_extra_one' => $this->get_tax_option( 'extra_one_tax_plural_label' ),
                'taxonomy_extra_two' => $this->get_tax_option( 'extra_two_tax_plural_label' ),
                'taxonomy_extra_three' => $this->get_tax_option( 'extra_three_tax_plural_label' ),
                'taxonomy_extra_four' => $this->get_tax_option( 'extra_four_tax_plural_label' ),
                'taxonomy_extra_five' => $this->get_tax_option( 'extra_five_tax_plural_label' ),

                // Extra One Taxonomy Settings
                'enable_extra_tax' => __('Enable Taxonomy', 'gscoach'),
                'enable_extra_tax--details' => __('Enable Taxonomy for coaches', 'gscoach'),
                'extra_tax_label' => __('Taxonomy Label', 'gscoach'),
                'extra_tax_label--details' => __('Set Taxonomy Label', 'gscoach'),
                'extra_tax_plural_label' => __('Taxonomy Plural Label', 'gscoach'),
                'extra_tax_plural_label--details' => __('Set Taxonomy Plural Label', 'gscoach'),
                'enable_extra_tax_archive' => __('Enable Taxonomy Archive', 'gscoach'),
                'enable_extra_tax_archive--details' => __('Enable Taxonomy Archive', 'gscoach'),
                'extra_tax_archive_slug' => __('Taxonomy Archive Slug', 'gscoach'),
                'extra_tax_archive_slug--details' => __('Set Taxonomy Archive Slug', 'gscoach'),

                'disable-google-fonts' => __('Disable Google Fonts', 'gscoach'),
                'disable-google-fonts-details' => __('Disable Google Fonts Loading', 'gscoach'),
                
                'show-acf-fields' => __('Display ACF Fields', 'gscoach'),
                'show-acf-fields-details' => __('Display ACF fields in the single pages', 'gscoach'),
                
                'disable_lazy_load' => __('Disable Lazy Load', 'gscoach'),
                'disable_lazy_load-details' => __('Disable Lazy Load for coach images', 'gscoach'),
                
                'lazy_load_class' => __('Lazy Load Class', 'gscoach'),
                'lazy_load_class-details' => __('Add class to disable lazy loading, multiple classes should be separated by space', 'gscoach'),

                'acf-fields-position' => __('ACF Fields Position', 'gscoach'),
                'acf-fields-position-details' => __('Position to display ACF fields', 'gscoach'),
                
                'enable-multilingual' => __('Enable Multilingual', 'gscoach'),
                'enable-multilingual--details' => __('Enable Multilingual mode to translate below strings using any Multilingual plugin like wpml or loco translate.', 'gscoach'),
                
                'pref-filter-designation-text' => __('Filter Designation Text', 'gscoach'),
                'pref-serach-text' => __('Search Text', 'gscoach'),
                'gs_coachfliter_zip-text' => __('Zip Search Text', 'gscoach'),
                'gs_coachfliter_tag-text' => __('Tag Search Text', 'gscoach'),
                'pref-zip_code-text' => __('Zip Code', 'gscoach'),
                'pref-follow_me_on-text' => __('Follow Me On', 'gscoach'),
                'pref-skills-text' => __('Skills', 'gscoach'),
                'pref-search-all-fields' => __('Include fields when search', 'gscoach'),
                'pref-address' => __('Address', 'gscoach'),
                'pref-cell-phone' => __('Cell Phone', 'gscoach'),
                'pref-email' => __('Email', 'gscoach'),
                'pref-location' => __('Location', 'gscoach'),
                'pref-language' => __('Language', 'gscoach'),
                'pref-specialty' => __('Specialty', 'gscoach'),
                'pref-gender' => __('Gender', 'gscoach'),
                'pref-more' => __('More', 'gscoach'),
                'custom-css' => __('Custom CSS', 'gscoach'),

                'pref-profession' => __('Profession', 'gscoach'),
                'pref-experience' => __('Experience', 'gscoach'),
                'pref-education' => __('Education', 'gscoach'),
                'pref-state' => __('State/ City', 'gscoach'),
                'pref-country' => __('Country', 'gscoach'),
                'pref-schedule' => __('Schedule', 'gscoach'),
                'pref-availablity' => __('Availablity', 'gscoach'),
                'pref-personal-site' => __('Personal Site', 'gscoach'),
                'pref-course-link' => __('Course Link', 'gscoach'),
                'pref-fee' => __('Fee', 'gscoach'),
                'pref-review' => __('Review', 'gscoach'),
                'pref-rating' => __('Rating', 'gscoach'),
                
                'pref-filter-designation-text-details' => __('Replace with preferred text for Designation', 'gscoach'),
                'pref-serach-text-details' => __('Replace with preferred text for Search', 'gscoach'),
                'gs_coachfliter_zip-text-details' => __('Replace with preferred text for Zip Search', 'gscoach'),
                'gs_coachfliter_tag-text-details' => __('Replace with preferred text for Tag Search', 'gscoach'),
                'pref-address-details' => __('Replace with preferred text for Address', 'gscoach'),
                'pref-cell-phone-details' => __('Replace with preferred text for Cell Phone', 'gscoach'),
                'pref-email-details' => __('Replace with preferred text for Email', 'gscoach'),
                'pref-location-details' => __('Replace with preferred text for Location', 'gscoach'),
                'pref-language-details' => __('Replace with preferred text for Language', 'gscoach'),
                'pref-specialty-details' => __('Replace with preferred text for Specialty', 'gscoach'),
                'pref-gender-details' => __('Replace with preferred text for Gender', 'gscoach'),
                'pref-zip_code-text-details' => __('Replace with preferred text for Zip Code', 'gscoach'),
                'pref-follow_me_on-text-details' => __('Replace with preferred text for Follow Me On', 'gscoach'),
                'pref-skills-text-details' => __('Replace with preferred text for Skills', 'gscoach'),
                'pref-more-details' => __('Replace with preferred text for More', 'gscoach'),
                'pref-search-all-fields-details' => __('Enable searching through all fields', 'gscoach'),

                'pref-profession-details' => __('Replace with preferred text for Profession', 'gscoach'),
                'pref-experience-details' => __('Replace with preferred text for Experience', 'gscoach'),
                'pref-education-details' => __('Replace with preferred text for Education', 'gscoach'),
                'pref-state-details' => __('Replace with preferred text for State/ City', 'gscoach'),
                'pref-country-details' => __('Replace with preferred text for Country', 'gscoach'),
                'pref-schedule-details' => __('Replace with preferred text for Schedule', 'gscoach'),
                'pref-availablity-details' => __('Replace with preferred text for Availablity', 'gscoach'),
                'pref-personal-site-details' => __('Replace with preferred text for Personal Site', 'gscoach'),
                'pref-course-link-details' => __('Replace with preferred text for Course Link', 'gscoach'),
                'pref-fee-details' => __('Replace with preferred text for Fee', 'gscoach'),
                'pref-review-details' => __('Replace with preferred text for Review', 'gscoach'),
                'pref-rating-details' => __('Replace with preferred text for Rating', 'gscoach'),

                'enable-breadcumb' => __('Enable Breadcumb for Single page', 'gscoach'),
                'enable-breadcumb--details' => __('Enable Breadcumb for Single page', 'gscoach'),

                'cell-phone-link' => __('Link Cell Phone', 'gscoach'),
                'cell-phone-link--details' => __('Enable link for cell phone number', 'gscoach'),

                'email-link' => __('Link Email', 'gscoach'),
                'email-link--details' => __('Enable link for Email', 'gscoach'),

                'reset-filters' => __('Reset Filters Text', 'gscoach'),
                'reset-filters-details' => __('Replace with preferred text for Reset Filters button text', 'gscoach'),

                'prev' => __('Prev Text', 'gscoach'),
                'prev-details' => __('Replace with preferred text for carousel Prev text', 'gscoach'),

                'next' => __('Next Text', 'gscoach'),
                'next-details' => __('Replace with preferred text for carousel Next text', 'gscoach'),

                'filter_enabled' => __('Enable Filter', 'gscoach'),
                'filter_enabled__details' => __('Enable filter for this theme, it may not available for certain theme', 'gscoach'),

                'filter_type' => __('Filter Type', 'gscoach'),
                'filter_type__details' => __('Select filter type', 'gscoach'),
                
                'enable_pagination' => __('Enable Pagination', 'gscoach'),
                'enable_pagination__details' => __('Enable paginations like number pagination, load more button, On scroll load etc.', 'gscoach'),

                'pagination_type' => __('Pagination Type', 'gscoach'),
                'pagination_type__details' => __('Select pagination type.', 'gscoach'),

                'initial_items'     => __('Initial Items', 'gscoach'),
                'initial_items__details'    => __('Set initial number of items that shows on page load (before users interaction)', 'gscoach'),

                'load_per_click' => __('Per Click', 'gscoach'),
                'load_per_click__details' => __('Load coaches per button click', 'gscoach'),

                'coach_per_page' => __('Per Page', 'gscoach'),
                'coach_per_page__details' => __('Display coaches per page', 'gscoach'),

                'per_load' => __('Per Load', 'gscoach'),
                'per_load__details' => __('Display coaches per load', 'gscoach'),

                'load_button_text' => __('Button Text', 'gscoach'),
                'load_button_text__details' => __('Load more button text', 'gscoach'),

                'carousel_enabled' => __('Enable Carousel', 'gscoach'),
                'carousel_enabled__details' => __('Enable carousel for this theme, it may not available for certain theme', 'gscoach'),

                'carousel_navs_enabled' => __('Enable Carousel Navs', 'gscoach'),
                'carousel_navs_enabled__details' => __('Enable carousel navs for this theme, it may not available for certain theme', 'gscoach'),

                'gs_slider_nav_bg_color' => __('Nav BG Color', 'gscoach'),
                'gs_slider_nav_color' => __('Nav Color', 'gscoach'),
                'gs_slider_nav_hover_bg_color' => __('Nav Hover BG Color', 'gscoach'),
                'gs_slider_nav_hover_color' => __('Nav Hover Color', 'gscoach'),
                'gs_slider_dot_color' => __('Dots Color', 'gscoach'),
                'gs_slider_dot_hover_color' => __('Dots Active Color', 'gscoach'),

                'carousel_dots_enabled' => __('Enable Carousel Dots', 'gscoach'),
                'carousel_dots_enabled__details' => __('Enable carousel dots for this theme, it may not available for certain theme', 'gscoach'),

                'carousel_navs_style' => __('Carousel Navs Style', 'gscoach'),
                'carousel_navs_style__details' => __('Select carousel navs style, this is available for certain theme', 'gscoach'),

                'carousel_dots_style' => __('Carousel Dots Style', 'gscoach'),
                'carousel_dots_style__details' => __('Select carousel dots style, this is available for certain theme', 'gscoach'),

                'carousel_navs' => __('Carousel Navs Style', 'gscoach'),
                'carousel_navs__details' => __('Select carousel navs style, this is available for certain theme', 'gscoach'),

                'drawer_style' => __('Drawer Style', 'gscoach'),
                'drawer_style__details' => __('Select drawer style, this is available for certain theme', 'gscoach'),

                'panel_style' => __('Panel Style', 'gscoach'),
                'panel_style__details' => __('Select panel style, this is available for certain theme', 'gscoach'),

                'popup_style' => __('Popup Style', 'gscoach'),
                'popup_style__details' => __('Select popup style, this is available for certain theme', 'gscoach'),

                'filter_style' => __('Filter Style', 'gscoach'),
                'filter_text_color' => __('Filter Color', 'gscoach'),
                'filter_bg_color' => __('Filter BG Color', 'gscoach'),
                'filter_border_color' => __('Filter Border Color', 'gscoach'),
                'filter_active_text_color' => __('Filter Active Color', 'gscoach'),
                'filter_active_bg_color' => __('Filter Active BG Color', 'gscoach'),
                'filter_active_border_color' => __('Filter Active Border Color', 'gscoach'),

                'shortcodes' => __('Shortcodes', 'gscoach'),
                'shortcode' => __('Shortcode', 'gscoach'),
                'global-settings-for-gs-coaches' => __('Global Settings for GS Coaches', 'gscoach'),
                'all-shortcodes-for-gs-coach' => __('All shortcodes for GS Coach', 'gscoach'),
                'create-shortcode' => __('Create Shortcode', 'gscoach'),
                'create-new-shortcode' => __('Create New Shortcode', 'gscoach'),
                'name' => __('Name', 'gscoach'),
                'action' => __('Action', 'gscoach'),
                'actions' => __('Actions', 'gscoach'),
                'edit' => __('Edit', 'gscoach'),
                'clone' => __('Clone', 'gscoach'),
                'delete' => __('Delete', 'gscoach'),
                'delete-all' => __('Delete All', 'gscoach'),
                'create-a-new-shortcode-and' => __('Create a new shortcode & save it to use globally in anywhere', 'gscoach'),
                'edit-shortcode' => __('Edit Shortcode', 'gscoach'),
                'general-settings' => __('General Settings', 'gscoach'),
                'style-settings' => __('Style Settings', 'gscoach'),
                'query-settings' => __('Query Settings', 'gscoach'),
                'general-settings-short' => __('General', 'gscoach'),
                'style-settings-short' => __('Style', 'gscoach'),
                'query-settings-short' => __('Query', 'gscoach'),
                'link_preview_image'   => __( 'Link Image', 'gscoach' ),
                'preview_enabled__details'   => __( 'Link Image', 'gscoach' ),
                'columns' => __('Columns', 'gscoach'),
                'columns_desktop' => __('Desktop Slides', 'gscoach'),
                'columns_desktop_details' => __('Enter the slides number for desktop', 'gscoach'),
                'columns_tablet' => __('Tablet Slides', 'gscoach'),
                'columns_tablet_details' => __('Enter the slides number for tablet', 'gscoach'),
                'columns_mobile_portrait' => __('Portrait Mobile', 'gscoach'),
                'columns_mobile_portrait_details' => __('Enter the slides number for portrait or large display mobile', 'gscoach'),
                'columns_mobile' => __('Mobile Slides', 'gscoach'),
                'columns_mobile_details' => __('Enter the slides number for mobile', 'gscoach'),
                'style-theming' => __('Style & Theming', 'gscoach'),
                'coach-name' => __('Coach Name', 'gscoach'),
                'gs_coach_name_is_linked' => __('Link Coaches', 'gscoach'),
                'gs_coach_name_is_linked__details' => __('Add links to the Coaches name, description & image to display popup or to single coach page', 'gscoach'),
                'gs_coach_thumbnail_sizes' => __('Thumbnail Sizes', 'gscoach'),
                'gs_coach_thumbnail_sizes_details' => __('Select a thumbnail size', 'gscoach'),
                'gs_coach_link_type' => __('Link Type', 'gscoach'),
                'gs_coach_link_type__details' => __('Choose the link type of coaches', 'gscoach'),
                
                'coach-designation' => __('Designation', 'gscoach'),
                'coach-details' => __('Details', 'gscoach'),
                'social-connection' => __('Social Connection', 'gscoach'),
                'display-ribbon' => __('Display Ribbon', 'gscoach'),
                'show-or-hide-ribbon__details' => __('Show or Hide Ribbon', 'gscoach'),
                'ribbon_style' => __('Ribbon Style', 'gscoach'),
                'ribbon_style__details' => __('Select Preferred Ribbon Style', 'gscoach'),
                'pagination' => __('Pagination', 'gscoach'),
                'single_page_style' => __('Single Page Style', 'gscoach'),
                'single_link_type' => __('Single Link Type', 'gscoach'),
                'single_page_style__details' => __('Style for all single page', 'gscoach'),
                'single_link_type__details' => __('Set the default link type for link behaviour', 'gscoach'),
                'next-prev-coach' => __('Next / Prev Coach', 'gscoach'),
                'instant-search-by-name' => __('Search by Name', 'gscoach'),
                'gs-coach-srch-by-company' => __('Search by Company', 'gscoach'),
                'gs-coach-srch-by-company--details' => __('Show or Hide by Company', 'gscoach'),
                'gs-coach-srch-by-zip' => __('Search by Zip', 'gscoach'),
                'gs-coach-srch-by-tag' => __('Search by Tag', 'gscoach'),
                'filter-by-designation' => __('Filter by Designation', 'gscoach'),
                'filter-by-location' => __('Filter by Location', 'gscoach'),
                'filter-by-language' => __('Filter by Language', 'gscoach'),
                'filter-by-gender' => __('Filter by Gender', 'gscoach'),
                'filter-by-speciality' => __('Filter by Specialty', 'gscoach'),
                'filter-by-extra-one' => __('Filter by Extra One', 'gscoach'),
                'filter-by-extra-two' => __('Filter by Extra Two', 'gscoach'),
                'filter-by-extra-three' => __('Filter by Extra Three', 'gscoach'),
                'filter-by-extra-four' => __('Filter by Extra Four', 'gscoach'),
                'filter-by-extra-five' => __('Filter by Extra Five', 'gscoach'),
                'gs_coach_filter_columns' => __('Filter Columns', 'gscoach'),
                'social-link-target' => __('Social Link Target', 'gscoach'),
                'gs-desc-allow-html' => __('Allow HTML for Details', 'gscoach'),
                'gs-desc-allow-html--help' => __('Enable/Disable HTML content for the single coach descript, this will load whole content from the post type.', 'gscoach'),
                'details-control' => __('Details Control', 'gscoach'),
                'gs-desc-scroll-contrl' => __('Description Scroll Control', 'gscoach'),
                'gs-desc-scroll-contrl--help' => __('Enable/Disable scrollbar for description on popup, drawer & panel, useful when description has large content.', 'gscoach'),
                'gs-max-scroll-height' => __('Scroll Height', 'gscoach'),
                'gs-max-scroll-height--help' => __('Set the maximum height of the description, if content exceds the height, scrollbar will appear.', 'gscoach'),
                'popup-column' => __('Popup Column', 'gscoach'),
                'filter-category-position' => __('Filter Category Position', 'gscoach'),
                'panel' => __('Panel', 'gscoach'),
                'name-font-size' => __('Name Font Size', 'gscoach'),
                'name-font-weight' => __('Name Font Weight', 'gscoach'),
                'name-font-style' => __('Name Font Style', 'gscoach'),
                'name-color' => __('Name Color', 'gscoach'),
                'name-bg-color' => __('Name BG Color', 'gscoach'),
                'tm-bg-color' => __('Item BG Color', 'gscoach'),
                'tm-bg-color-hover' => __('Item Hover BG Color', 'gscoach'),
                'description-color' => __('Description Color', 'gscoach'),
                'description-link-color' => __('Description Link Color', 'gscoach'),
                'info-color' => __('Info Color', 'gscoach'),
                'info-icon-color' => __('Info Icon Color', 'gscoach'),
                'tooltip-bg-color' => __('Tooltip BG Color', 'gscoach'),
                'info-bg-color' => __('Info BG Color', 'gscoach'),
                'hover-icon-bg-color' => __('Hover Icon BG Color', 'gscoach'),
                'ribon-background-color' => __('Ribbon BG Color', 'gscoach'),
                'role-font-size' => __('Role Font Size', 'gscoach'),
                'role-font-weight' => __('Role Font Weight', 'gscoach'),
                'role-font-style' => __('Role Font Style', 'gscoach'),
                'role-color' => __('Role Color', 'gscoach'),
                'popup-arrow-color' => __('Popup Arrow Color', 'gscoach'),
                'coaches' => __('Coaches', 'gscoach'),
                'order' => __('Order', 'gscoach'),
                'order-by' => __('Order By', 'gscoach'),
                'taxonomy-order' => __('Taxonomy Order', 'gscoach'),
                'taxonomy-order-by' => __('Taxonomy Order By', 'gscoach'),
                'taxonomy_hide_empty' => __('Hide Empty Filters', 'gscoach'),
                'taxonomy_hide_empty__details' => __('Enable to hide the empty filters', 'gscoach'),
                'group' => __('Group', 'gscoach'),
                'group__help' => __('Select specific group to show that specific group coaches', 'gscoach'),
                'tags' => __('Tags', 'gscoach'),
                'tags__help' => __('Select specific tag to show that specific tagged coaches', 'gscoach'),
                'exclude_group' => __('Group', 'gscoach'),
                'exclude_group__help' => __('Select a specific coach group to hide that specific group coaches', 'gscoach'),
                'exclude_tags' => __('Tags', 'gscoach'),
                'exclude_tags__help' => __('Select a specific tag to hide that specific tagged coaches', 'gscoach'),
                'exclude_language' => __('Language', 'gscoach'),
                'exclude_language__help' => __('Select a specific language to hide that specific language coaches', 'gscoach'),
                'exclude_location' => __('Location', 'gscoach'),
                'exclude_location__help' => __('Select a specific location to hide that specific location coaches', 'gscoach'),
                'exclude_specialty' => __('Specialty', 'gscoach'),
                'exclude_specialty__help' => __('Select a specific specialty to hide that specific specialty coaches', 'gscoach'),
                'exclude_gender' => __('Gender', 'gscoach'),
                'exclude_gender__help' => __('Select a specific gender to hide that specific gender coaches', 'gscoach'),
                'exclude_extra_one' => __('Extra One', 'gscoach'),
                'exclude_extra_one__help' => __('Select a specific extra one to hide that specific extra one coaches', 'gscoach'),
                'exclude_extra_two' => __('Extra Two', 'gscoach'),
                'exclude_extra_two__help' => __('Select a specific extra two to hide that specific extra two coaches', 'gscoach'),
                'exclude_extra_three' => __('Extra Three', 'gscoach'),
                'exclude_extra_three__help' => __('Select a specific extra three to hide that specific extra three coaches', 'gscoach'),
                'exclude_extra_four' => __('Extra Four', 'gscoach'),
                'exclude_extra_four__help' => __('Select a specific extra four to hide that specific extra four coaches', 'gscoach'),
                'exclude_extra_five' => __('Extra Five', 'gscoach'),
                'exclude_extra_five__help' => __('Select a specific extra five to hide that specific extra five coaches', 'gscoach'),

                'theme' => __('Theme', 'gscoach'),
                'font-size' => __('Font Size', 'gscoach'),
                'font-weight' => __('Font Weight', 'gscoach'),
                'font-style' => __('Font Style', 'gscoach'),

                'select-number-of-coach-columns' => __('Select the number of Coach columns', 'gscoach'),
                'select-preffered-style-theme' => __('Select the preferred Style & Theme', 'gscoach'),
                'show-or-hide-coach-name' => __('Show or Hide Coach Name', 'gscoach'),
                'show-or-hide-coach-designation' => __('Show or Hide Coach Designation', 'gscoach'),
                'show-or-hide-coach-details' => __('Show or Hide Coach Details', 'gscoach'),
                'show-or-hide-coach-social-connections' => __('Show or Hide Coach Social Connections', 'gscoach'),
                'show-or-hide-coach-paginations' => __('Show or Hide Coach Paginations', 'gscoach'),
                'show-or-hide-next-prev-coach-link-at-single-coach-template' => __('Show or Hide Next / Prev link at Single Coach Template', 'gscoach'),
                'show-or-hide-instant-search-applicable-for-theme-9' => __('Show or Hide Instant Search', 'gscoach'),
                'gs-coach-srch-by-zip--details' => __('Show or Hide by Instant Zip Search', 'gscoach'),
                'gs-coach-srch-by-tag--details' => __('Show or Hide by Instant Tag Search', 'gscoach'),
                'filter-by-designation--des' => __('Show or Hide Filter by Designation', 'gscoach'),
                'filter-by-location--des' => __('Show or Hide Filter by Location', 'gscoach'),
                'filter-by-language--des' => __('Show or Hide Filter by Language', 'gscoach'),
                'filter-by-gender--des' => __('Show or Hide Filter by Gender', 'gscoach'),
                'filter-by-speciality--des' => __('Show or Hide Filter by Specialty', 'gscoach'),
                'filter-by-extra-one--des' => __('Show or Hide Filter by Extra One', 'gscoach'),
                'filter-by-extra-two--des' => __('Show or Hide Filter by Extra Two', 'gscoach'),
                'filter-by-extra-three--des' => __('Show or Hide Filter by Extra Three', 'gscoach'),
                'filter-by-extra-four--des' => __('Show or Hide Filter by Extra Four', 'gscoach'),
                'filter-by-extra-five--des' => __('Show or Hide Filter by Extra Five', 'gscoach'),
                'specify-target-to-load-the-links' => __('Specify the target to load the Links, Default New Tab', 'gscoach'),
                'define-maximum-number-of-characters' => __('Define the maximum number of characters in details. Default 100', 'gscoach'),
                'set-column-for-popup' => __('Set column for popup', 'gscoach'),
                'set-max-coach-numbers-you-want-to-show' => __('Set max coach numbers you want to show, set -1 for all coaches', 'gscoach'),

                'export-coaches-data' => __('Export Coaches', 'gscoach'),
                'export-shortcodes-data' => __('Export Shortcodes', 'gscoach'),
                'export-settings-data' => __('Export Settings', 'gscoach'),

                'enable-multi-select' => __('Enable Multi Select', 'gscoach'),
                'enable-multi-select--help' => __('Enable multi-selection on the filters, Default is Off', 'gscoach'),
                'multi-select-ellipsis' => __('Multi Select Ellipsis', 'gscoach'),
                'multi-select-ellipsis--help' => __('Show multi-selected values in ellipsis mode, Default is Off', 'gscoach'),

                'filter-all-enabled' => __('Enable All Filter', 'gscoach'),
                'filter-all-enabled--help' => __('Enable All filter in the filter templates, Default is On', 'gscoach'),

                'enable-child-cats' => __('Enable Child Filters', 'gscoach'),
                'enable-child-cats--help' => __('Enable child group filters, Default is Off', 'gscoach'),

                'enable-scroll-animation' => __('Enable Scroll Animation', 'gscoach'),
                'enable-scroll-animation--help' => __('Enable scroll animation, Default is On', 'gscoach'),

                'fitler-all-text' => __('All filter text', 'gscoach'),
                'fitler-all-text--help' => __('All filter text for filter templates, Default is All', 'gscoach'),

                'enable-clear-filters' => __('Reset Filters Button', 'gscoach'),
                'enable-clear-filters--help' => __('Enable Reset all filters button in filter themes, Default is Off ', 'gscoach'),

                'shortcode-name' => __('Shortcode Name', 'gscoach'),
                'save-shortcode' => __('Save Shortcode', 'gscoach'),
                'preview-shortcode' => __('Preview Shortcode', 'gscoach')
            ];
        }

        public static function _themes() {
            return [
                [
                    'label' => __( 'Grid 1', 'gscoach' ),
                    'value' => 'gs-grid-style-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 2', 'gscoach' ),
                    'value' => 'gs-grid-style-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 3', 'gscoach' ),
                    'value' => 'gs-grid-style-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 4', 'gscoach' ),
                    'value' => 'gs-grid-style-four',
                    'type' => 'free',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 5', 'gscoach' ),
                    'value' => 'gs-grid-style-five',
                    'type' => 'free',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 6', 'gscoach' ),
                    'value' => 'gs-grid-style-six',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 7', 'gscoach' ),
                    'value' => 'gs-grid-style-seven',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 8', 'gscoach' ),
                    'value' => 'gs-grid-style-eight',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 9', 'gscoach' ),
                    'value' => 'gs-grid-style-nine',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 10', 'gscoach' ),
                    'value' => 'gs_tm_theme1',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid 11', 'gscoach' ),
                    'value' => 'gs_tm_grid2',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid 12', 'gscoach' ),
                    'value' => 'gs_tm_theme20',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid 13', 'gscoach' ),
                    'value' => 'gs_tm_theme10',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid 14', 'gscoach' ),
                    'value' => 'gs_tm_theme_custom_10',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Popup', 'gscoach' ),
                    'value' => 'gs_tm_theme8',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Single', 'gscoach' ),
                    'value' => 'gs_tm_theme11',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Filter Single', 'gscoach' ),
                    'value' => 'gs_tm_theme22',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Filter Popup', 'gscoach' ),
                    'value' => 'gs_tm_theme9',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Slider', 'gscoach' ),
                    'value' => 'gs_tm_theme7',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Filter - Selected Cats', 'gscoach' ),
                    'value' => 'gs_tm_theme12',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Filter with vcard', 'gscoach' ),
                    'value' => 'gs_tm_theme24',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Panel Slide', 'gscoach' ),
                    'value' => 'gs_tm_theme19',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Drawer 1', 'gscoach' ),
                    'value' => 'gs_tm_theme13',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Drawer 2', 'gscoach' ),
                    'value' => 'gs_tm_drawer2',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Circle 1', 'gscoach' ),
                    'value' => 'gs-coach-circle-one',
                    'type' => 'free',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 2', 'gscoach' ),
                    'value' => 'gs-coach-circle-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 3', 'gscoach' ),
                    'value' => 'gs-coach-circle-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 4', 'gscoach' ),
                    'value' => 'gs-coach-circle-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 5', 'gscoach' ),
                    'value' => 'gs-coach-circle-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 6', 'gscoach' ),
                    'value' => 'gs_tm_theme2',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Circle 7', 'gscoach' ),
                    'value' => 'gs-coach-circle-seven',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 8', 'gscoach' ),
                    'value' => 'gs-coach-circle-eight',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 9', 'gscoach' ),
                    'value' => 'gs-coach-circle-nine',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 10', 'gscoach' ),
                    'value' => 'gs-coach-circle-ten',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 1', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 2', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 3', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 4', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 5', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 6', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-six',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 7', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-seven',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 8', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-eight',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 9', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-nine',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 10', 'gscoach' ),
                    'value' => 'gs-coach-horizontal-ten',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 1', 'gscoach' ),
                    'value' => 'gs-coach-flip-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 2', 'gscoach' ),
                    'value' => 'gs-coach-flip-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 3', 'gscoach' ),
                    'value' => 'gs-coach-flip-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 4', 'gscoach' ),
                    'value' => 'gs-coach-flip-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 5', 'gscoach' ),
                    'value' => 'gs-coach-flip-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 1', 'gscoach' ),
                    'value' => 'gs-coach-table-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 2', 'gscoach' ),
                    'value' => 'gs-coach-table-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 3', 'gscoach' ),
                    'value' => 'gs-coach-table-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 4', 'gscoach' ),
                    'value' => 'gs-coach-table-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 5', 'gscoach' ),
                    'value' => 'gs-coach-table-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 1', 'gscoach' ),
                    'value' => 'gs-coach-list-style-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 2', 'gscoach' ),
                    'value' => 'gs-coach-list-style-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 3', 'gscoach' ),
                    'value' => 'gs-coach-list-style-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 4', 'gscoach' ),
                    'value' => 'gs-coach-list-style-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 5', 'gscoach' ),
                    'value' => 'gs-coach-list-style-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 6', 'gscoach' ),
                    'value' => 'gs-coach-list-style-six',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 7', 'gscoach' ),
                    'value' => 'gs-coach-list-style-seven',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 8', 'gscoach' ),
                    'value' => 'gs-coach-list-style-eight',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 9', 'gscoach' ),
                    'value' => 'gs-coach-list-style-nine',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip', 'gscoach' ),
                    'value' => 'gs_tm_theme23',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 6 - Underline', 'gscoach' ),
                    'value' => 'gs_tm_theme14',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 7 - Box Border', 'gscoach' ),
                    'value' => 'gs_tm_theme15',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 8 - Odd Even', 'gscoach' ),
                    'value' => 'gs_tm_theme16',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 9 Filter', 'gscoach' ),
                    'value' => 'gs_tm_theme21',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 10 Filter Dense', 'gscoach' ),
                    'value' => 'gs_tm_theme21_dense',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Group Filter 1', 'gscoach' ),
                    'value' => 'gs_tm_theme25',
                    'type' => 'pro',
                    'version' => 1
                ]
            ];
        }

        public static function get_free_themes_v_1() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'version' => 1, 'type' => 'free' ] );
        }

        public static function get_free_themes_v_2() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'version' => 2, 'type' => 'free' ] );
        }

        public static function get_pro_themes_v_1() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'version' => 1, 'type' => 'pro' ] );
        }

        public static function get_pro_themes_v_2() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'version' => 2, 'type' => 'pro' ] );
        }

        public static function get_free_themes() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'type' => 'free' ] );
        }

        public static function get_pro_themes() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'type' => 'pro' ] );
        }

        public static function get_formated_themes( $themes ) {

            if ( ! is_pro_active_and_valid() ) {

                $_themes = array_map( function( $theme ) {
                    $theme['label'] = $theme['label'] . __(' (Pro)', 'gscoach');
                    return $theme;
                }, wp_list_filter( $themes, [ 'version' => 1, 'type' => 'pro' ] ) );
                $themes = shortcode_atts( $themes, $_themes );
    
                $_themes = array_map( function( $theme ) {
                    $theme['label'] = $theme['label'] . __(' (Pro)', 'gscoach');
                    return $theme;
                }, wp_list_filter( $themes, [ 'version' => 2, 'type' => 'pro' ] ) );
                $themes = shortcode_atts( $themes, $_themes );
                
                $_themes = wp_list_filter( $themes, [ 'type' => 'pro' ] );
                $_themes = self::add_pro_to_options( $_themes );

                $_themes = shortcode_atts( $themes, $_themes );
                $themes = wp_list_sort( $_themes, 'type', 'ASC' );

            }

            $themes = array_map( function( $theme ) {
                unset( $theme['type'] );
                unset( $theme['version'] );
                return $theme;
            }, $themes );

            return $themes;
        }

        public function get_shortcode_options_themes() {
            return self::get_formated_themes( self::_themes() );
        }

        public function get_shortcode_options_paginations() {

            $styles = [
                [
                    'label' => __( 'Normal Pagination', 'gscoach' ),
                    'value' => 'normal-pagination'
                ],
                [
                    'label' => __( 'Ajax Pagination', 'gscoach' ),
                    'value' => 'ajax-pagination'
                ],
                [
                    'label' => __( 'Load More Button', 'gscoach' ),
                    'value' => 'load-more-button'
                ],
                [
                    'label' => __( 'Load More on Scroll', 'gscoach' ),
                    'value' => 'load-more-scroll'
                ]

            ];

            if ( ! is_pro_active_and_valid() ) {
                $default = array_shift( $styles );
                $styles = array_merge( [$default], self::add_pro_to_options($styles) );
            }

            return $styles;

        }

        public function get_shortcode_options_link_types() {

            $free_options = [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Single Page', 'gscoach' ),
                    'value' => 'single_page'
                ],
                [
                    'label' => __( 'Popup', 'gscoach' ),
                    'value' => 'popup'
                ]
            ];

            $pro_options = [
                [
                    'label' => __( 'Panel', 'gscoach' ),
                    'value' => 'panel'
                ],
                [
                    'label' => __( 'Drawer', 'gscoach' ),
                    'value' => 'drawer'
                ],
                [
                    'label' => __( 'Custom URL', 'gscoach' ),
                    'value' => 'custom'
                ]
            ];

            if ( ! is_pro_active_and_valid() ) {
                $pro_options = self::add_pro_to_options( $pro_options );
            }

            return array_merge( $free_options, $pro_options );

        }

        public function get_carousel_navs_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gscoach' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gscoach' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gscoach' ),
                    'value' => 'style-three'
                ]

            ];

            if ( ! is_pro_active_and_valid() ) {
                $default = array_shift( $styles );
                $styles = array_merge( [$default], self::add_pro_to_options($styles) );
            }

            return $styles;

        }

        public function get_carousel_dots_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gscoach' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gscoach' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gscoach' ),
                    'value' => 'style-three'
                ]

            ];

            if ( ! is_pro_active_and_valid() ) {
                $default = array_shift( $styles );
                $styles = array_merge( [$default], self::add_pro_to_options($styles) );
            }

            return $styles;

        }

        public function get_drawer_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gscoach' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gscoach' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gscoach' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gscoach' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gscoach' ),
                    'value' => 'style-five'
                ]

            ];

            if ( ! is_pro_active_and_valid() ) {
                $styles = self::add_pro_to_options( $styles );
            }

            return $styles;

        }

        public static function add_pro_to_options( $options ) {
            return array_map( function( $item ) {
                $item['pro'] = true;
                return $item;
            }, $options );
        }

        public function get_panel_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gscoach' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gscoach' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gscoach' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gscoach' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gscoach' ),
                    'value' => 'style-five'
                ]

            ];

            if ( ! is_pro_active_and_valid() ) {
                $styles = self::add_pro_to_options($styles);
            }

            return $styles;

        }

        public function get_popup_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gscoach' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gscoach' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gscoach' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gscoach' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gscoach' ),
                    'value' => 'style-five'
                ],
                [
                    'label' => __( 'Style Six', 'gscoach' ),
                    'value' => 'style-six'
                ]

            ];

            if ( ! is_pro_active_and_valid() ) {
                $default = array_shift( $styles );
                $styles = array_merge( [$default], self::add_pro_to_options($styles) );
            }

            return $styles;

        }

        public function get_filter_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gscoach' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gscoach' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gscoach' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gscoach' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gscoach' ),
                    'value' => 'style-five'
                ]

            ];

            if ( ! is_pro_active_and_valid() ) {
                $styles = self::add_pro_to_options($styles);
            }

            return $styles;

        }

        public function get_shortcode_default_options() {
            return [
                'location' => self::get_coach_terms('gs_coach_location'),
                'specialty' => self::get_coach_terms('gs_coach_specialty'),
                'language' => self::get_coach_terms('gs_coach_language'),
                'gender' => self::get_coach_terms('gs_coach_gender'),
                'group' => self::get_coach_terms('gs_coach_group'),
                'tag' => self::get_coach_terms('gs_coach_tag'),
                'extra_one' => self::get_coach_terms('gs_coach_extra_one'),
                'extra_two' => self::get_coach_terms('gs_coach_extra_two'),
                'extra_three' => self::get_coach_terms('gs_coach_extra_three'),
                'extra_four' => self::get_coach_terms('gs_coach_extra_four'),
                'extra_five' => self::get_coach_terms('gs_coach_extra_five'),
                'gs_coach_cols' => $this->get_columns(),
                'drawer_style' => $this->get_drawer_styles(),
                'carousel_navs_style' => $this->get_carousel_navs_styles(),
                'carousel_dots_style' => $this->get_carousel_dots_styles(),
                'panel_style' => $this->get_panel_styles(),
                'popup_style' => $this->get_popup_styles(),
                'filter_style' => $this->get_filter_styles(),
                'gs_coach_thumbnail_sizes' => $this->getPossibleThumbnailSizes(),
                'gs_coach_ribbon_styles' => $this->get_ribbon_styles(),
                'gs_coach_cols_tablet' => $this->get_columns(),
                'gs_coach_cols_mobile_portrait' => $this->get_columns(),
                'gs_coach_cols_mobile' => $this->get_columns(),
                'gs_coach_theme' => $this->get_shortcode_options_themes(),
                'pagination_type' => $this->get_shortcode_options_paginations(),
                'gs_coach_link_type' => $this->get_shortcode_options_link_types(),
                'acf_fields_position' => $this->get_acf_fields_position(),
                'gs_coach_filter_type' => [
                    [
                        'label' => __( 'Normal Filter', 'gscoach' ),
                        'value' => 'normal-filter'
                    ],
                    [
                        'label' => __( 'Ajax Filter', 'gscoach' ),
                        'value' => 'ajax-filter'
                    ]
                ],
                'gs_coaches_pop_clm' => [
                    [
                        'label' => __( 'One', 'gscoach' ),
                        'value' => 'one'
                    ],
                    [
                        'label' => __( 'Two', 'gscoach' ),
                        'value' => 'two'
                    ],
                ],
                'gs_coach_filter_columns' => [
                    [
                        'label' => __( 'Two', 'gscoach' ),
                        'value' => 'two'
                    ],
                    [
                        'label' => __( 'Three', 'gscoach' ),
                        'value' => 'three'
                    ],
                ],
                'gs_tm_filter_cat_pos' => [
                    [
                        'label' => __( 'Left', 'gscoach' ),
                        'value' => 'left'
                    ],
                    [
                        'label' => __( 'Center', 'gscoach' ),
                        'value' => 'center'
                    ],
                    [
                        'label' => __( 'Right', 'gscoach' ),
                        'value' => 'right'
                    ]
                ],
                'panel' => [
                    [
                        'label' => __( 'Left', 'gscoach' ),
                        'value' => 'left'
                    ],
                    [
                        'label' => __( 'Center', 'gscoach' ),
                        'value' => 'center'
                    ],
                    [
                        'label' => __( 'Right', 'gscoach' ),
                        'value' => 'right'
                    ]
                ],
                'orderby' => [
                    [
                        'label' => __( 'Custom Order', 'gscoach' ),
                        'value' => 'menu_order'
                    ],
                    [
                        'label' => __( 'Coach ID', 'gscoach' ),
                        'value' => 'ID'
                    ],
                    [
                        'label' => __( 'Coach Name', 'gscoach' ),
                        'value' => 'title'
                    ],
                    [
                        'label' => __( 'Date', 'gscoach' ),
                        'value' => 'date'
                    ],
                    [
                        'label' => __( 'Random', 'gscoach' ),
                        'value' => 'rand'
                    ],
                ],
                'taxonomy_orderby' => [
                    [
                        'label' => __( 'Custom Order', 'gscoach' ),
                        'value' => 'term_order'
                    ],
                    [
                        'label' => __( 'Taxonomy ID', 'gscoach' ),
                        'value' => 'term_id'
                    ],
                    [
                        'label' => __( 'Taxonomy Name', 'gscoach' ),
                        'value' => 'name'
                    ],
                ],
                'order' => [
                    [
                        'label' => __( 'DESC', 'gscoach' ),
                        'value' => 'DESC'
                    ],
                    [
                        'label' => __( 'ASC', 'gscoach' ),
                        'value' => 'ASC'
                    ],
                ],

                'image_filter' => $this->get_image_filter_effects(),

                'hover_image_filter' => $this->get_image_filter_effects(),

                // Style Options
                'gs_tm_m_fntw' => [
                    [
                        'label' => __( '100 - Thin', 'gscoach' ),
                        'value' => 100
                    ],
                    [
                        'label' => __( '200 - Extra Light', 'gscoach' ),
                        'value' => 200
                    ],
                    [
                        'label' => __( '300 - Light', 'gscoach' ),
                        'value' => 300
                    ],
                    [
                        'label' => __( '400 - Regular', 'gscoach' ),
                        'value' => 400
                    ],
                    [
                        'label' => __( '500 - Medium', 'gscoach' ),
                        'value' => 500
                    ],
                    [
                        'label' => __( '600 - Semi-Bold', 'gscoach' ),
                        'value' => 600
                    ],
                    [
                        'label' => __( '700 - Bold', 'gscoach' ),
                        'value' => 700
                    ],
                    [
                        'label' => __( '800 - Extra Bold', 'gscoach' ),
                        'value' => 800
                    ],
                    [
                        'label' => __( '900 - Black', 'gscoach' ),
                        'value' => 900
                    ],
                ],
                'gs_tm_m_fnstyl' => [
                    [
                        'label' => __( 'Normal', 'gscoach' ),
                        'value' => 'normal'
                    ],
                    [
                        'label' => __( 'Italic', 'gscoach' ),
                        'value' => 'italic'
                    ],
                ],
                'gs_tm_role_fntw' => [
                    [
                        'label' => __( '100 - Thin', 'gscoach' ),
                        'value' => 100
                    ],
                    [
                        'label' => __( '200 - Extra Light', 'gscoach' ),
                        'value' => 200
                    ],
                    [
                        'label' => __( '300 - Light', 'gscoach' ),
                        'value' => 300
                    ],
                    [
                        'label' => __( '400 - Regular', 'gscoach' ),
                        'value' => 400
                    ],
                    [
                        'label' => __( '500 - Medium', 'gscoach' ),
                        'value' => 500
                    ],
                    [
                        'label' => __( '600 - Semi-Bold', 'gscoach' ),
                        'value' => 600
                    ],
                    [
                        'label' => __( '700 - Bold', 'gscoach' ),
                        'value' => 700
                    ],
                    [
                        'label' => __( '800 - Extra Bold', 'gscoach' ),
                        'value' => 800
                    ],
                    [
                        'label' => __( '900 - Black', 'gscoach' ),
                        'value' => 900
                    ],
                ],
                'gs_tm_role_fnstyl' => [
                    [
                        'label' => __( 'Normal', 'gscoach' ),
                        'value' => 'normal'
                    ],
                    [
                        'label' => __( 'Italic', 'gscoach' ),
                        'value' => 'italic'
                    ],
                ],
            ];
        }

        public function get_shortcode_default_settings() {
            return [
                'num'                             => -1,
                'order'                           => 'DESC',
                'orderby'                         => 'date',
                'taxonomy_orderby'                => 'name',
                'taxonomy_order'                     => 'ASC',
                'taxonomy_hide_empty'             => 'off',
                'gs_coach_theme'                  => 'gs-grid-style-five',
                'gs_coach_cols'                   => '3',
                'gs_coach_cols_tablet'            => '4',
                'gs_coach_cols_mobile_portrait'   => '6',
                'gs_coach_cols_mobile'            => '12',
                'group'                           => '',
                'tag'                             => '',
                'language'                        => '',
                'location'                        => '',
                'specialty'                       => '',
                'gender'                          => '',
                'include_extra_one'               => '',
                'include_extra_two'               => '',
                'include_extra_three'             => '',
                'include_extra_four'              => '',
                'include_extra_five'              => '',
                'exclude_group'                   => '',
                'exclude_tags'                    => '',
                'exclude_language'                => '',
                'exclude_location'                => '',
                'exclude_specialty'               => '',
                'exclude_gender'                  => '',
                'exclude_extra_one'               => '',
                'exclude_extra_two'               => '',
                'exclude_extra_three'             => '',
                'exclude_extra_four'              => '',
                'exclude_extra_five'              => '',
                'panel'                           => 'right',
                'gs_coaches_pop_clm'              => 'two',
                'gs_coach_connect'                => 'on',
                'display_ribbon'                  => 'on',        
                'gs_coach_ribbon_style'           => 'default',        
                'gs_slider_nav_color'             => '',
                'gs_slider_nav_bg_color'          => '',
                'gs_slider_nav_hover_color'       => '',
                'gs_slider_nav_hover_bg_color'    => '',
                'gs_slider_dot_color'             => '',
                'gs_slider_dot_hover_color'       => '',
                'filter_text_color'               => '',
                'filter_active_text_color'        => '',
                'filter_bg_color'                 => '',
                'filter_active_bg_color'          => '',
                'filter_border_color'             => '',
                'filter_active_border_color'      => '',
                'gs_tm_mname_color'               => '',
                'description_color'               => '',
                'info_color'                      => '',
                'info_icon_color'                 => '',
                'description_link_color'          => '',
                'tm_bg_color'                     => '',
                'tm_bg_color_hover'               => '',
                'gs_tm_info_background'           => '',
                'gs_tm_mname_background'          => '',
                'gs_tm_tooltip_background'        => '',
                'gs_tm_hover_icon_background'     => '',
                'gs_tm_ribon_color'               => '',
                'gs_tm_role_color'                => '',
                'gs_tm_arrow_color'               => '',
                'gs_coach_name'                   => 'on',
                'gs_coach_name_is_linked'         => 'on',
                'gs_coach_link_type'              => 'default',
                'gs_coach_role'                   => 'on',
                'gs_coach_details'                => 'on',
                'gs_desc_scroll_contrl'           => 'on',
                'gs_max_scroll_height'            => '',
                'gs_details_area_height'          => 'off',
                'filter_enabled'                  => 'off',
                'gs_coach_filter_type'            => 'normal-filter',
                'enable_pagination'               => 'off',
                'pagination_type'                 => 'load-more-button',
                'initial_items'                   => '6',
                'coach_per_page'                  => '6',
                'load_per_click'                  => '3',
                'per_load'                        => '3',
                'load_button_text'                => 'Load More',
                'carousel_enabled'                => 'off',
                'link_preview_image'              => 'off',
                'carousel_navs_enabled'           => 'on',
                'carousel_dots_enabled'           => 'on',
                'carousel_navs_style'             => 'default',
                'carousel_dots_style'             => 'default',
                'drawer_style'                    => 'default',
                'panel_style'                     => 'default',
                'popup_style'                     => 'default',
                'filter_style'                    => 'default',
                'gs_desc_allow_html'              => 'off',
                'gs_tm_details_contl'             => 100,
                'gs_coach_srch_by_name'          => 'on',
                'gs_coach_srch_by_zip'           => 'on',
                'gs_coach_srch_by_tag'           => 'off',
                'gs_coach_filter_by_desig'       => 'on',
                'gs_coach_filter_by_location'    => 'on',
                'gs_coach_filter_by_language'    => 'on',
                'gs_coach_filter_by_gender'      => 'on',
                'gs_coach_filter_by_speciality'  => 'on',
                'gs_coach_filter_by_extra_one'   => 'off',
                'gs_coach_filter_by_extra_two'   => 'off',
                'gs_coach_filter_by_extra_three' => 'off',
                'gs_coach_filter_by_extra_four'  => 'off',
                'gs_coach_filter_by_extra_five'  => 'off',
                'gs_coach_enable_clear_filters'  => 'off',
                'gs_coach_enable_multi_select'   => 'off',
                'gs_coach_multi_select_ellipsis' => 'off',
                'gs_filter_all_enabled'           => 'on',
                'enable_child_cats'               => 'off',
                'enable_scroll_animation'         => 'on',
                'fitler_all_text'                 => 'All',
                'gs_coach_filter_columns'          => 'two',
                'gs_tm_m_fz'                      => '',
                'gs_tm_m_fntw'                    => '',
                'image_filter'                    => 'none',
                'hover_image_filter'              => 'none',
                'gs_tm_m_fnstyl'                  => '',
                'gs_tm_role_fz'                   => '',
                'gs_tm_role_fntw'                 => '',
                'gs_tm_role_fnstyl'               => '',
                'gs_tm_filter_cat_pos'            => 'center',
                'gs_coach_thumbnail_sizes'       => 'large',
                'show_acf_fields'                 => 'off',
                'acf_fields_position'             => 'after_skills',
            ];
        }

        public function get_translation($translation_name) {

            $translations = $this->get_shortcode_default_translations();
        
            if ( ! array_key_exists( $translation_name, $translations ) ) return '';

            $prefs = $this->_get_shortcode_pref( false );

            if ( $prefs['gs_coach_enable_multilingual'] === 'on' ) return $translations[$translation_name];
        
            return $prefs[ $translation_name ];
        }

        public function get_shortcode_default_translations() {
            $translations = [
                'gs_coachfliter_designation' => __('Show All Designation', 'gscoach'),
                'gs_coachfliter_name' => __('Search By Name', 'gscoach'),
                'gs_coachfliter_zip' => __('Search By Zip', 'gscoach'),
                'gs_coachfliter_tag' => __('Search By Tag', 'gscoach'),
                'gs_coachcom_meta' => __('Company', 'gscoach'),
                'gs_coachadd_meta' => __('Address', 'gscoach'),
                'gs_coachcellPhone_meta' => __('Cell Phone', 'gscoach'),
                'gs_coachemail_meta' => __('Email', 'gscoach'),
                'gs_coach_zipcode_meta' => __('Zip Code', 'gscoach'),
                'gs_coach_follow_me_on' => __('Follow Me On', 'gscoach'),
                'gs_coach_skills' => __('Skills', 'gscoach'),
                'gs_coach_more' => __('More', 'gscoach'),
                'gs_coach_profession' => __('Profession', 'gscoach'),
                'gs_coach_experience' => __('Experience', 'gscoach'),
                'gs_coach_education' => __('Education', 'gscoach'),
                'gs_coach_ribbon' => __('Ribbon', 'gscoach'),
                'gs_coach_address' => __('Address', 'gscoach'),
                'gs_coach_state' => __('State/ City', 'gscoach'),
                'gs_coach_country' => __('Country', 'gscoach'),
                'gs_coach_contact' => __('Contact', 'gscoach'),
                'gs_coach_email' => __('Email', 'gscoach'),
                'gs_coach_schedule' => __('Schedule', 'gscoach'),
                'gs_coach_availablity' => __('Availablity', 'gscoach'),
                'gs_coach_personal_site' => __('Personal Site', 'gscoach'),
                'gs_coach_personal_site_url' => __('Site URL', 'gscoach'),
                'gs_coach_personal_site_btn_text' => __('Button Text', 'gscoach'),
                'gs_coach_personal_site_btn_target' => __('Target', 'gscoach'),
                'gs_coach_course_link' => __('Course Link', 'gscoach'),
                'gs_coach_course_link_url' => __('Course URL', 'gscoach'),
                'gs_coach_course_link_btn_text' => __('Button Text', 'gscoach'),
                'gs_coach_course_link_btn_target' => __('Target', 'gscoach'),
                'gs_coach_fee' => __('Fee', 'gscoach'),
                'gs_coach_review' => __('Review', 'gscoach'),
                'gs_coach_rating' => __('Rating', 'gscoach'),
                'gs_coach_custom_page' => __('Custom Page Link', 'gscoach'),
                'gs_coach_reset_filters_txt' => __('Reset Filters', 'gscoach'),
                'gs_coach_prev_txt' => __('Prev', 'gscoach'),
                'gs_coach_next_txt' => __('Next', 'gscoach')
            ];

            return $translations;
        }

        public function get_shortcode_default_prefs() {
            $prefs = [
                'gs_coach_nxt_prev'            => 'off',
                'single_page_style'             => 'default',
                'single_link_type'              => 'single_page',
                'gs_coach_search_all_fields'   => 'off',
                'gs_coach_enable_multilingual' => 'off',
                'gs_coaches_slug'               => 'coaches',
                'replace_custom_slug'           => 'off',
                'archive_page_slug'             => '',
                'archive_page_title'            => '',
                'disable_google_fonts'          => 'off',
                'show_acf_fields'               => 'off',
                'disable_lazy_load'             => 'off',

                'enable_breadcumb'              => 'off',
                'cell_phone_link'               => 'off',
                'email_link'                    => 'off',

                'lazy_load_class'               => 'skip-lazy',
                'acf_fields_position'           => 'after_skills',
                'gs_coach_custom_css'            => ''
            ];

            $translations = $this->get_shortcode_default_translations();

            $prefs = array_merge( $prefs, $translations );

            return $prefs;
        }

        public function get_taxonomy_default_settings() {

            return [

                // Group Taxonomy
                'enable_group_tax' => 'on',
                'group_tax_label' => __('Group', 'gscoach'),
                'group_tax_plural_label' => __('Groups', 'gscoach'),
                'enable_group_tax_archive' => 'on',
                'group_tax_archive_slug' => 'gs-coach-group',

                // Tag Taxonomy
                'enable_tag_tax' => 'on',
                'tag_tax_label' => __('Tag', 'gscoach'),
                'tag_tax_plural_label' => __('Tags', 'gscoach'),
                'enable_tag_tax_archive' => 'on',
                'tag_tax_archive_slug' => 'gs-coach-tag',

                // Language Taxonomy
                'enable_language_tax' => 'off',
                'language_tax_label' => __('Language', 'gscoach'),
                'language_tax_plural_label' => __('Languages', 'gscoach'),
                'enable_language_tax_archive' => 'on',
                'language_tax_archive_slug' => 'gs-coach-language',

                // Location Taxonomy
                'enable_location_tax' => 'off',
                'location_tax_label' => __('Location', 'gscoach'),
                'location_tax_plural_label' => __('Locations', 'gscoach'),
                'enable_location_tax_archive' => 'on',
                'location_tax_archive_slug' => 'gs-coach-location',

                // Gender Taxonomy
                'enable_gender_tax' => 'off',
                'gender_tax_label' => __('Gender', 'gscoach'),
                'gender_tax_plural_label' => __('Genders', 'gscoach'),
                'enable_gender_tax_archive' => 'on',
                'gender_tax_archive_slug' => 'gs-coach-gender',

                // Specialty Taxonomy
                'enable_specialty_tax' => 'off',
                'specialty_tax_label' => __('Specialty', 'gscoach'),
                'specialty_tax_plural_label' => __('Specialties', 'gscoach'),
                'enable_specialty_tax_archive' => 'on',
                'specialty_tax_archive_slug' => 'gs-coach-specialty',

                // Extra One Taxonomy
                'enable_extra_one_tax' => 'off',
                'extra_one_tax_label' => __('Extra 1', 'gscoach'),
                'extra_one_tax_plural_label' => __('Extra 1', 'gscoach'),
                'enable_extra_one_tax_archive' => 'on',
                'extra_one_tax_archive_slug' => 'gs-coach-extra-one',

                // Extra Two Taxonomy
                'enable_extra_two_tax' => 'off',
                'extra_two_tax_label' => __('Extra 2', 'gscoach'),
                'extra_two_tax_plural_label' => __('Extra 2', 'gscoach'),
                'enable_extra_two_tax_archive' => 'off',
                'extra_two_tax_archive_slug' => 'gs-coach-extra-two',

                // Extra Three Taxonomy
                'enable_extra_three_tax' => 'off',
                'extra_three_tax_label' => __('Extra 3', 'gscoach'),
                'extra_three_tax_plural_label' => __('Extra 3', 'gscoach'),
                'enable_extra_three_tax_archive' => 'off',
                'extra_three_tax_archive_slug' => 'gs-coach-extra-three',

                // Extra Four Taxonomy
                'enable_extra_four_tax' => 'off',
                'extra_four_tax_label' => __('Extra 4', 'gscoach'),
                'extra_four_tax_plural_label' => __('Extra 4', 'gscoach'),
                'enable_extra_four_tax_archive' => 'off',
                'extra_four_tax_archive_slug' => 'gs-coach-extra-four',

                // Extra Five Taxonomy
                'enable_extra_five_tax' => 'off',
                'extra_five_tax_label' => __('Extra 5', 'gscoach'),
                'extra_five_tax_plural_label' => __('Extra 5', 'gscoach'),
                'enable_extra_five_tax_archive' => 'off',
                'extra_five_tax_archive_slug' => 'gs-coach-extra-five',

            ];

        }

        public function get_tax_option( $option, $default = '' ) {
            $options = (array) get_option( $this->taxonomy_option_name, [] );
            $defaults = $this->get_taxonomy_default_settings();
            $options = array_merge($defaults, $options);

            if ( str_contains($option, '_label') && ( getoption('gs_coach_enable_multilingual', 'off') == 'on' ) ) {
                return $defaults[$option];
            }

            if ( str_contains($option, '_label') && empty($options[$option]) ) {
                return $defaults[$option];
            }

            if ( isset($options[$option]) ) return $options[$option];
            return $default;
        }

        public function get_columns() {

            return [
                [
                    'label' => __( '1 Column', 'gscoach' ),
                    'value' => '12'
                ],
                [
                    'label' => __( '2 Columns', 'gscoach' ),
                    'value' => '6'
                ],
                [
                    'label' => __( '3 Columns', 'gscoach' ),
                    'value' => '4'
                ],
                [
                    'label' => __( '4 Columns', 'gscoach' ),
                    'value' => '3'
                ],
                [
                    'label' => __( '5 Columns', 'gscoach' ),
                    'value' => '2_4'
                ],
                [
                    'label' => __( '6 Columns', 'gscoach' ),
                    'value' => '2'
                ],
            ];

        }

        public function get_acf_fields_position() {

            return [
                [
                    'label' => __( 'After Skills', 'gscoach' ),
                    'value' => 'after_skills'
                ],
                [
                    'label' => __( 'After Description', 'gscoach' ),
                    'value' => 'after_description'
                ],
                [
                    'label' => __( 'After Meta Details', 'gscoach' ),
                    'value' => 'after_meta_details'
                ],
            ];

        }

        public function get_image_filter_effects() {

            $effects = [
                [
                    'label' => __( 'None', 'gscoach' ),
                    'value' => 'none'
                ],
                [
                    'label' => __( 'Blur', 'gscoach' ),
                    'value' => 'blur'
                ],
                [
                    'label' => __( 'Brightness', 'gscoach' ),
                    'value' => 'brightness'
                ],
                [
                    'label' => __( 'Contrast', 'gscoach' ),
                    'value' => 'contrast'
                ],
                [
                    'label' => __( 'Grayscale', 'gscoach' ),
                    'value' => 'grayscale'
                ],
                [
                    'label' => __( 'Hue Rotate', 'gscoach' ),
                    'value' => 'hue_rotate'
                ],
                [
                    'label' => __( 'Invert', 'gscoach' ),
                    'value' => 'invert'
                ],
                [
                    'label' => __( 'Opacity', 'gscoach' ),
                    'value' => 'opacity'
                ],
                [
                    'label' => __( 'Saturate', 'gscoach' ),
                    'value' => 'saturate'
                ],
                [
                    'label' => __( 'Sepia', 'gscoach' ),
                    'value' => 'sepia'
                ]
            ];

            if ( ! is_pro_active_and_valid() ) {
                $effects = self::add_pro_to_options($effects);
            }

            return $effects;

        }

        public function get_single_page_style() {

            return [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gscoach' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gscoach' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gscoach' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gscoach' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gscoach' ),
                    'value' => 'style-five'
                ]
            ];

        }

        /**
         * Retrives WP registered possible thumbnail sizes.
         * 
         * @since  1.10.14
         * @return array   image sizes.
         */
        public function getPossibleThumbnailSizes() {
            
            $sizes = get_intermediate_image_sizes();

            if ( empty($sizes) ) return [];

            $result = [];

            foreach ( $sizes as $size ) {
                $result[] = [
                    'label' => ucwords( preg_replace('/_|-/', ' ', $size) ),
                    'value' => $size
                ];
            }
            
            return $result;
        }

        public function get_ribbon_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gscoach' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style - 01', 'gscoach' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style - 02', 'gscoach' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style - 03', 'gscoach' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style - 04', 'gscoach' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style - 05', 'gscoach' ),
                    'value' => 'style-five'
                ],
                [
                    'label' => __( 'Style - 06', 'gscoach' ),
                    'value' => 'style-six'
                ],
                [
                    'label' => __( 'Style - 07', 'gscoach' ),
                    'value' => 'style-seven'
                ],
                [
                    'label' => __( 'Style - 08', 'gscoach' ),
                    'value' => 'style-eight'
                ],
                [
                    'label' => __( 'Style - 09', 'gscoach' ),
                    'value' => 'style-nine'
                ],
                [
                    'label' => __( 'Style - 10', 'gscoach' ),
                    'value' => 'style-ten'
                ]

            ];

            if ( ! is_pro_active_and_valid() ) {

                $default = array_shift( $styles );
                $styles = array_merge( [$default], self::add_pro_to_options($styles) );
            }

            return $styles;

        }

        public function get_shortcode_prefs_options() {

            $acf_fields_position = $this->get_acf_fields_position();
            $single_page_style = $this->get_single_page_style();
            $single_link_type = [
                [
                    'label' => __( 'None', 'gscoach' ),
                    'value' => 'none'
                ],
                $this->get_shortcode_options_link_types()[1]
            ];

            if ( ! is_pro_active_and_valid() ) {
                $acf_fields_position = self::add_pro_to_options( $acf_fields_position );
                $default = array_shift( $single_page_style );
                $single_page_style = array_merge( [$default], self::add_pro_to_options($single_page_style) );
            }

            return [
                'acf_fields_position' => $acf_fields_position,
                'single_page_style' => $single_page_style,
                'single_link_type' => $single_link_type
            ];
        }

        public function is_multilingual_enabled() {
            return $this->_get_shortcode_pref( false )['gs_coach_enable_multilingual'] == 'on';
        }

        public function validate_shortcode_prefs( Array $settings ) {
            foreach ( $settings as $setting_key => $setting_val ) {
                if ( $setting_key == 'gs_coach_custom_css' ) {
                    $settings[ $setting_key ] = wp_strip_all_tags( $setting_val );
                } else {
                    $settings[ $setting_key ] = sanitize_text_field( $setting_val );
                }
            }
            return $settings;
        }

        public function _save_shortcode_pref( $settings, $is_ajax ) {

            if ( empty($settings) ) $settings = [];

            $settings = $this->validate_shortcode_prefs( $settings );
            update_option( $this->option_name, $settings, 'yes' );
            
            // Clean permalink flush
            delete_option( 'GS_Coach_plugin_permalinks_flushed' );

            do_action( 'gs_coach_preference_update' );
            do_action( 'gsp_preference_update' );
        
            if ( $is_ajax ) wp_send_json_success( __('Preference saved', 'gscoach') );

        }

        public function save_shortcode_pref() {

            check_ajax_referer( '_gscoach_admin_nonce_gs_' );
            
            if ( empty($_POST['prefs']) ) {
                wp_send_json_error( __('No preference provided', 'gscoach'), 400 );
            }
    
            $this->_save_shortcode_pref( $_POST['prefs'], true );

        }

        public function _get_shortcode_pref( $is_ajax ) {

            $pref = (array) get_option( $this->option_name, [] );
            $pref = shortcode_atts( $this->get_shortcode_default_prefs(), $pref );

            if ( $is_ajax ) {
                wp_send_json_success( $pref );
            }

            return $pref;
        }

        public function get_shortcode_pref() {
            return $this->_get_shortcode_pref( wp_doing_ajax() );
        }

        public function _get_taxonomy_settings( $is_ajax ) {

            $settings = (array) get_option( $this->taxonomy_option_name, [] );
            $settings = $this->validate_taxonomy_settings( $settings );

            if ( $is_ajax ) {
                wp_send_json_success( $settings );
            }

            return $settings;

        }

        public function validate_taxonomy_settings( $settings ) {

            $defaults = $this->get_taxonomy_default_settings();

            if ( empty($settings) ) {
                $settings = $defaults;
            } else {
                foreach ( $settings as $setting_key => $setting_val ) {
                    if ( str_contains($setting_key, '_label') && empty($setting_val) ) {
                        $settings[$setting_key] = $defaults[$setting_key];
                    }
                }
            }
            
            return array_map( 'sanitize_text_field', $settings );
        }

        public function get_taxonomy_settings() {
            return $this->_get_taxonomy_settings( wp_doing_ajax() );
        }

        public function _save_taxonomy_settings( $settings, $is_ajax ) {

            if ( empty($settings) ) $settings = [];

            $settings = $this->validate_taxonomy_settings( $settings );
            update_option( $this->taxonomy_option_name, $settings, 'yes' );
            
            // Clean permalink flush
            delete_option( 'GS_Coach_plugin_permalinks_flushed' );

            do_action( 'gs_coach_tax_settings_update' );
            do_action( 'gsp_tax_settings_update' );
        
            if ( $is_ajax ) wp_send_json_success( __('Taxonomy settings saved', 'gscoach') );
        }

        public function save_taxonomy_settings() {

            check_ajax_referer( '_gscoach_admin_nonce_gs_' );
            
            if ( empty($_POST['tax_settings']) ) {
                wp_send_json_error( __('No settings provided', 'gscoach'), 400 );
            }
    
            $this->_save_taxonomy_settings( $_POST['tax_settings'], true );
        }

        static function maybe_create_shortcodes_table() {

            global $wpdb;

            $gs_coach_db_version = '1.0';

            if ( get_option("{$wpdb->prefix}gs_coach_db_version") == $gs_coach_db_version ) return; // vail early
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gs_coaches (
            	id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
            	shortcode_name TEXT NOT NULL,
            	shortcode_settings LONGTEXT NOT NULL,
            	created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            	updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            	PRIMARY KEY (id)
            )".$wpdb->get_charset_collate().";";
                
            if ( get_option("{$wpdb->prefix}gs_coach_db_version") < $gs_coach_db_version ) {
                dbDelta( $sql );
            }

            update_option( "{$wpdb->prefix}gs_coach_db_version", $gs_coach_db_version );
        }

        public function create_dummy_shortcodes() {

            $Dummy_Data = new Dummy_Data();

            $request = wp_remote_get( GSCOACH_PLUGIN_URI . '/includes/demo-data/shortcodes.json', array('sslverify' => false) );

            if ( is_wp_error($request) ) return false;

            $shortcodes = wp_remote_retrieve_body( $request );

            $shortcodes = json_decode( $shortcodes, true );

            $wpdb = $this->get_wpdb();

            if ( ! $shortcodes || ! count($shortcodes) ) return;

            foreach ( $shortcodes as $shortcode ) {

                $shortcode['shortcode_settings'] = json_decode( $shortcode['shortcode_settings'], true );
                $shortcode['shortcode_settings']['gscoach-demo_data'] = true;

                if ( !empty( $group = $shortcode['shortcode_settings']['group']) ) {
                    $shortcode['shortcode_settings']['group'] = (array) $Dummy_Data->get_taxonomy_ids_by_slugs( 'gs_coach_group', explode(',', $group) );
                }

                if ( !empty( $exclude_group = $shortcode['shortcode_settings']['exclude_group']) ) {
                    $shortcode['shortcode_settings']['exclude_group'] = (array) $Dummy_Data->get_taxonomy_ids_by_slugs( 'gs_coach_group', explode(',', $exclude_group) );
                }
    
                $data = array(
                    "shortcode_name" => $shortcode['shortcode_name'],
                    "shortcode_settings" => json_encode($shortcode['shortcode_settings']),
                    "created_at" => current_time( 'mysql'),
                    "updated_at" => current_time( 'mysql'),
                );
    
                $wpdb->insert( "{$wpdb->prefix}gs_coaches", $data, $this->get_db_columns() );

            }

        }

        public function delete_dummy_shortcodes() {

            $wpdb = $this->get_wpdb();

            $needle = 'gscoach-demo_data';

            $wpdb->query( "DELETE FROM {$wpdb->prefix}gs_coaches WHERE shortcode_settings like '%$needle%'" );

            // Delete the shortcode cache
            wp_cache_delete( 'gs_coach_shortcodes', 'gs_coach_memebrs' );

        }

        public function maybe_upgrade_data( $old_version ) {

            // Do upgrade operations here when needed in future versions
            
        }

    }

}