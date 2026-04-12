<?php
namespace GSCOACH;
/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Dummy_Data' ) ) {

    final class Dummy_Data {

        public function __construct() {

            add_action( 'wp_ajax_gscoach_import_coach_data', array($this, 'import_coach_data') );

            add_action( 'wp_ajax_gscoach_remove_coach_data', array($this, 'remove_coach_data') );

            add_action( 'wp_ajax_gscoach_import_shortcode_data', array($this, 'import_shortcode_data') );

            add_action( 'wp_ajax_gscoach_remove_shortcode_data', array($this, 'remove_shortcode_data') );

            add_action( 'wp_ajax_gscoach_import_all_data', array($this, 'import_all_data') );

            add_action( 'wp_ajax_gscoach_remove_all_data', array($this, 'remove_all_data') );

            add_action( 'gs_after_shortcode_submenu', array($this, 'register_sub_menu') );

            add_action( 'admin_init', array($this, 'maybe_auto_import_all_data') );

            // Remove dummy indicator
            add_action( 'edit_post_gs_coach', array($this, 'remove_dummy_indicator'), 10 );

            // Import Process
            add_action( 'gscoach_dummy_attachments_process_start', function() {

                // Force delete option if have any
                delete_option( 'gscoach_dummy_coach_data_created' );

                // Force update the process
                set_transient( 'gscoach_dummy_coach_data_creating', 1, 3 * MINUTE_IN_SECONDS );

            });
            
            add_action( 'gscoach_dummy_attachments_process_finished', function() {

                $this->create_dummy_terms();

            });
            
            add_action( 'gscoach_dummy_terms_process_finished', function() {

                $this->create_dummy_coaches();

            });
            
            add_action( 'gscoach_dummy_coaches_process_finished', function() {

                // clean the record that we have started a process
                delete_transient( 'gscoach_dummy_coach_data_creating' );

                // Add a track so we never duplicate the process
                update_option( 'gscoach_dummy_coach_data_created', 1 );

            });
            
            // Shortcodes
            add_action( 'gscoach_dummy_shortcodes_process_start', function() {

                // Force delete option if have any
                delete_option( 'gscoach_dummy_shortcode_data_created' );

                // Force update the process
                set_transient( 'gscoach_dummy_shortcode_data_creating', 1, 3 * MINUTE_IN_SECONDS );

            });

            add_action( 'gscoach_dummy_shortcodes_process_finished', function() {

                // clean the record that we have started a process
                delete_transient( 'gscoach_dummy_shortcode_data_creating' );

                // Add a track so we never duplicate the process
                update_option( 'gscoach_dummy_shortcode_data_created', 1 );

            });
            
        }

        public function register_sub_menu() {

            $builder = plugin()->builder;

            add_submenu_page(
                'edit.php?post_type=gs_coaches', 'Install Demo', 'Install Demo', 'publish_pages', 'gs-coach-shortcode#/demo-data', array( $builder, 'view' )
            );

        }

        public function get_taxonomy_list() {
            $taxonomies = ['gs_coach_group', 'gs_coach_tag', 'gs_coach_language', 'gs_coach_location', 'gs_coach_gender', 'gs_coach_specialty'];
            return array_filter( $taxonomies, 'taxonomy_exists' );
        }

        public function remove_dummy_indicator( $post_id ) {

            if ( empty( get_post_meta( $post_id, 'gscoach-demo_data', true ) ) ) return;
            
            $taxonomies = $this->get_taxonomy_list();

            // Remove dummy indicator from texonomies
            $dummy_terms = wp_get_post_terms( $post_id, $taxonomies, [
                'fields' => 'ids',
                'meta_key' => 'gscoach-demo_data',
                'meta_value' => 1,
            ]);

            if ( !empty($dummy_terms) ) {
                foreach( $dummy_terms as $term_id ) {
                    delete_term_meta( $term_id, 'gscoach-demo_data', 1 );
                }
            }

            // Remove dummy indicator from attachments
            $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
            $thumbnail_flip_id = get_post_meta( $post_id, 'second_featured_img', true );
            if ( !empty($thumbnail_id) ) delete_post_meta( $thumbnail_id, 'gscoach-demo_data', 1 );
            if ( !empty($thumbnail_flip_id) ) delete_post_meta( $thumbnail_flip_id, 'gscoach-demo_data', 1 );
            delete_transient( 'gscoach_dummy_attachments' );
            
            // Remove dummy indicator from post
            delete_post_meta( $post_id, 'gscoach-demo_data', 1 );
            delete_transient( 'gscoach_dummy_coaches' );

        }

        public function maybe_auto_import_all_data() {

            if ( get_option('gs_coach_autoimport_done') == true ) return;

            $coaches = get_posts([
                'numberposts' => -1,
                'post_type' => 'gs_coaches',
                'fields' => 'ids'
            ]);

            $shortcodes = plugin()->builder->fetch_shortcodes();

            if ( empty($coaches) && empty($shortcodes) ) {
                $this->_import_coach_data( false );
                $this->_import_shortcode_data( false );
            }

            update_option( 'gs_coach_autoimport_done', true );
        }

        public function import_all_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );

            $response = [
                'coach' => $this->_import_coach_data( false ),
                'shortcode' => $this->_import_shortcode_data( false )
            ];

            if ( wp_doing_ajax() ) wp_send_json_success( $response, 200 );

            return $response;

        }

        public function remove_all_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );

            $response = [
                'coach' => $this->_remove_coach_data( false ),
                'shortcode' => $this->_remove_shortcode_data( false )
            ];

            if ( wp_doing_ajax() ) wp_send_json_success( $response, 200 );

            return $response;

        }

        public function import_coach_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );

            // Start importing
            $this->_import_coach_data();

        }

        public function remove_coach_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );

            // Remove coach data
            $this->_remove_coach_data();

        }

        public function import_shortcode_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );

            // Start importing
            $this->_import_shortcode_data();

        }

        public function remove_shortcode_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gscoach_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gscoach'), 401 );

            // Remove coach data
            $this->_remove_shortcode_data();

        }

        public function _import_coach_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            // Data already imported
            if ( get_option('gscoach_dummy_coach_data_created') !== false || get_transient('gscoach_dummy_coach_data_creating') !== false ) {

                $message_202 = __( 'Dummy Coaches already imported', 'gscoach' );

                if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
                
                return [
                    'status' => 202,
                    'message' => $message_202
                ];

            }
            
            // Importing demo data
            $this->create_dummy_attachments();

            $message = __( 'Dummy Coaches imported', 'gscoach' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _remove_coach_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            $this->delete_dummy_attachments();
            $this->delete_dummy_terms();
            $this->delete_dummy_coaches();

            delete_option( 'gscoach_dummy_coach_data_created' );
            delete_transient( 'gscoach_dummy_coach_data_creating' );

            $message = __( 'Dummy Coaches deleted', 'gscoach' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _import_shortcode_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            // Data already imported
            if ( get_option('gscoach_dummy_shortcode_data_created') !== false || get_transient('gscoach_dummy_shortcode_data_creating') !== false ) {

                $message_202 = __( 'Dummy Shortcodes already imported', 'gscoach' );

                if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
                
                return [
                    'status' => 202,
                    'message' => $message_202
                ];

            }
            
            // Importing demo shortcodes
            $this->create_dummy_shortcodes();

            $message = __( 'Dummy Shortcodes imported', 'gscoach' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _remove_shortcode_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            $this->delete_dummy_shortcodes();

            delete_option( 'gscoach_dummy_shortcode_data_created' );
            delete_transient( 'gscoach_dummy_shortcode_data_creating' );

            $message = __( 'Dummy Shortcodes deleted', 'gscoach' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function get_taxonomy_ids_by_slugs( $taxonomy_group, $taxonomy_slugs = [] ) {

            $_terms = $this->get_dummy_terms();

            if ( empty($_terms) ) return [];
            
            $_terms = wp_filter_object_list( $_terms, [ 'taxonomy' => $taxonomy_group ] );
            $_terms = array_values( $_terms );      // reset the keys
            
            if ( empty($_terms) ) return [];
            
            $term_ids = [];
            
            foreach ( $taxonomy_slugs as $slug ) {
                $key = array_search( $slug, array_column($_terms, 'slug') );
                if ( $key !== false ) $term_ids[] = $_terms[$key]['term_id'];
            }

            return $term_ids;

        }

        public function get_attachment_id_by_filename( $filename ) {

            $attachments = $this->get_dummy_attachments();
            
            if ( empty($attachments) ) return '';
            
            $attachments = wp_filter_object_list( $attachments, [ 'post_name' => $filename ] );
            if ( empty($attachments) ) return '';
            
            $attachments = array_values( $attachments );
            
            return $attachments[0]->ID;

        }

        public function get_tax_inputs( $tax_inputs = [] ) {

            if ( empty($tax_inputs) ) return $tax_inputs;

            $_tax_inputs = [];

            foreach( $tax_inputs as $taxonomy => $tax_params ) {
                if ( taxonomy_exists( $taxonomy ) ) $_tax_inputs[$taxonomy] = $this->get_taxonomy_ids_by_slugs( $taxonomy, $tax_params );
            }

            return $_tax_inputs;
        }

        public function get_meta_inputs( $meta_inputs = [] ) {

            $meta_inputs['_thumbnail_id'] = $this->get_attachment_id_by_filename( $meta_inputs['_thumbnail_id'] );
            // $meta_inputs['second_featured_img'] = $this->get_attachment_id_by_filename( $meta_inputs['second_featured_img'] );

            return $meta_inputs;

        }

        // Coaches
        public function create_dummy_coaches() {

            do_action( 'gscoach_dummy_coaches_process_start' );

            $post_status = 'publish';
            $post_type = 'gs_coaches';

            $coaches = [];

            $coaches[] = array(
                'post_title'    => "Ethan Carter",
                'post_content'  => "Certified Business Coach helping startups and entrepreneurs scale faster with proven growth strategies. I specialize in mindset transformation, leadership development, and sustainable business systems.\r\n\r\nWith years of real-world experience, I guide clients to break limiting beliefs and build high-performing businesses with clarity and confidence.",
                'post_status'   => $post_status,
                'post_type'     => $post_type,
                'post_date'     => '2020-08-10 07:01:44',
                'tax_input'     => $this->get_tax_inputs([
                    "gs_coach_group" => ['business-coaching', 'startup-growth'],
                    "gs_coach_tag" => ['entrepreneur', 'mentor'],
                    "gs_coach_language" => ['english'],
                    "gs_coach_location" => ['new-york', 'london'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['leadership', 'growth-strategy']
                ]),
                'meta_input'    => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-1',
                    '_gscoach_profession' => "Business Coach",
                    '_gscoach_experience' => "8 Years",
                    '_gscoach_education' => "MBA",
                    '_gscoach_ribbon' => "Top Rated",
                    '_gscoach_address' => "Manhattan Business Hub",
                    '_gscoach_state' => "NY",
                    '_gscoach_country' => "USA",
                    '_gscoach_contact' => "+1 212-555-0187",
                    '_gscoach_email' => "ethan@coachpro.com",
                    '_gscoach_shedule' => "10:00",
                    '_gscoach_available' => "April 2, 2026 to April 10, 2026",
                    '_gscoach_psite' => "https://coachpro.com/",
                    '_gscoach_courselink' => "https://coachpro.com/course",
                    '_gscoach_fee' => "120",
                    '_gscoach_review' => "Helped me scale my startup in 6 months!",
                    '_gscoach_rating' => "4.8",
                    '_gscoach_custom_page' => "https://coachpro.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/ethancoach'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/in/ethancoach'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Leadership', 'percent' => 100],
                        ['skill' => 'Business Growth', 'percent' => 95],
                        ['skill' => 'Mindset Coaching', 'percent' => 90],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Sophia Martinez",
                'post_content'  => "Professional Life Coach focused on helping individuals unlock their full potential and build a balanced, fulfilling life.\r\n\r\nI work with clients on clarity, purpose, and emotional intelligence to create meaningful personal and professional transformations.",
                'post_status'   => $post_status,
                'post_type'     => $post_type,
                'post_date'     => '2020-08-11 07:01:44',
                'tax_input'     => $this->get_tax_inputs([
                    "gs_coach_group" => ['life-coaching'],
                    "gs_coach_tag" => ['self-growth', 'mindfulness'],
                    "gs_coach_language" => ['english', 'spanish'],
                    "gs_coach_location" => ['california', 'madrid'],
                    "gs_coach_gender" => ['female'],
                    "gs_coach_specialty" => ['self-development']
                ]),
                'meta_input'    => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-2',
                    '_gscoach_profession' => "Life Coach",
                    '_gscoach_experience' => "6 Years",
                    '_gscoach_education' => "Psychology",
                    '_gscoach_ribbon' => "Best Coach",
                    '_gscoach_address' => "Sunset Blvd",
                    '_gscoach_state' => "CA",
                    '_gscoach_country' => "USA",
                    '_gscoach_contact' => "+1 310-555-0145",
                    '_gscoach_email' => "sophia@lifecoach.com",
                    '_gscoach_shedule' => "14:00",
                    '_gscoach_available' => "April 2, 2026 to April 10, 2026",
                    '_gscoach_psite' => "https://lifecoach.com/",
                    '_gscoach_courselink' => "https://lifecoach.com/program",
                    '_gscoach_fee' => "90",
                    '_gscoach_review' => "Changed my life perspective completely.",
                    '_gscoach_rating' => "4.9",
                    '_gscoach_custom_page' => "https://lifecoach.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/sophiacoach'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/in/sophiacoach'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Emotional Intelligence', 'percent' => 95],
                        ['skill' => 'Clarity Building', 'percent' => 100],
                        ['skill' => 'Goal Setting', 'percent' => 90],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Daniel Kim",
                'post_content'  => "Career Coach helping professionals land better opportunities, negotiate salaries, and grow their careers strategically.\r\n\r\nI specialize in resume optimization, interview coaching, and career transition planning.",
                'post_status'   => $post_status,
                'post_type'     => $post_type,
                'post_date'     => '2020-08-12 07:01:44',
                'tax_input'     => $this->get_tax_inputs([
                    "gs_coach_group" => ['career-coaching'],
                    "gs_coach_tag" => ['job-growth', 'resume'],
                    "gs_coach_language" => ['english', 'korean'],
                    "gs_coach_location" => ['seoul', 'toronto'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['career-development']
                ]),
                'meta_input'    => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-3',
                    '_gscoach_profession' => "Career Coach",
                    '_gscoach_experience' => "7 Years",
                    '_gscoach_education' => "Human Resources",
                    '_gscoach_address' => "Tech Park Seoul",
                    '_gscoach_state' => "Seoul",
                    '_gscoach_country' => "South Korea",
                    '_gscoach_contact' => "+82 10-555-0199",
                    '_gscoach_email' => "daniel@careerboost.com",
                    '_gscoach_shedule' => "11:00",
                    '_gscoach_available' => "April 10, 2026 to April 14, 2026",
                    '_gscoach_psite' => "https://careerboost.com/",
                    '_gscoach_courselink' => "https://careerboost.com/masterclass",
                    '_gscoach_fee' => "110",
                    '_gscoach_review' => "Landed my dream job within weeks.",
                    '_gscoach_rating' => "4.7",
                    '_gscoach_custom_page' => "https://careerboost.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/in/danielkim'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Resume Optimization', 'percent' => 100],
                        ['skill' => 'Interview Prep', 'percent' => 95],
                        ['skill' => 'Career Planning', 'percent' => 90],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Lucas Bennett",
                'post_content'  => "Certified Fitness Coach helping individuals transform their bodies and build sustainable healthy lifestyles.\r\n\r\nI focus on strength training, fat loss, and habit building to ensure long-term results. My coaching blends science-backed training with personalized guidance.",
                'post_status'   => $post_status,
                'post_type'     => $post_type,
                'post_date'     => '2020-08-13 07:01:44',
                'tax_input'     => $this->get_tax_inputs([
                    "gs_coach_group" => ['fitness-coaching'],
                    "gs_coach_tag" => ['fitness', 'health'],
                    "gs_coach_language" => ['english'],
                    "gs_coach_location" => ['sydney', 'melbourne'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['fat-loss', 'strength-training']
                ]),
                'meta_input'    => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-4',
                    '_gscoach_profession' => "Fitness Coach",
                    '_gscoach_experience' => "5 Years",
                    '_gscoach_education' => "Sports Science",
                    '_gscoach_address' => "Fitness Lab Center",
                    '_gscoach_state' => "NSW",
                    '_gscoach_country' => "Australia",
                    '_gscoach_contact' => "+61 400-555-112",
                    '_gscoach_email' => "lucas@fitcoach.com",
                    '_gscoach_shedule' => "08:00",
                    '_gscoach_available' => "April 18, 2026 to April 26, 2026",
                    '_gscoach_psite' => "https://fitcoach.com/",
                    '_gscoach_courselink' => "https://fitcoach.com/program",
                    '_gscoach_fee' => "80",
                    '_gscoach_review' => "Lost 12kg in 3 months. Highly recommended!",
                    '_gscoach_rating' => "4.5",
                    '_gscoach_custom_page' => "https://fitcoach.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-instagram', 'link' => 'https://instagram.com/lucasfit'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Strength Training', 'percent' => 100],
                        ['skill' => 'Fat Loss', 'percent' => 95],
                        ['skill' => 'Nutrition Guidance', 'percent' => 90],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Emma Richardson",
                'post_content'  => "Relationship Coach helping individuals and couples build deeper emotional connections and healthier communication patterns.\r\n\r\nI guide clients through conflict resolution, trust rebuilding, and emotional awareness to create stronger, lasting relationships.",
                'post_status'   => $post_status,
                'post_type'     => $post_type,
                'post_date'     => '2020-08-14 07:01:44',
                'tax_input'     => $this->get_tax_inputs([
                    "gs_coach_group" => ['relationship-coaching'],
                    "gs_coach_tag" => ['relationships', 'communication'],
                    "gs_coach_language" => ['english'],
                    "gs_coach_location" => ['london'],
                    "gs_coach_gender" => ['female'],
                    "gs_coach_specialty" => ['couples-coaching']
                ]),
                'meta_input'    => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-5',
                    '_gscoach_profession' => "Relationship Coach",
                    '_gscoach_experience' => "9 Years",
                    '_gscoach_education' => "Psychology",
                    '_gscoach_ribbon' => "Certified Expert",
                    '_gscoach_address' => "Central London",
                    '_gscoach_state' => "London",
                    '_gscoach_country' => "UK",
                    '_gscoach_contact' => "+44 20-555-8899",
                    '_gscoach_email' => "emma@relationshipcoach.com",
                    '_gscoach_shedule' => "16:00",
                    '_gscoach_available' => "April 2, 2026 to April 10, 2026",
                    '_gscoach_psite' => "https://relationshipcoach.com/",
                    '_gscoach_courselink' => "https://relationshipcoach.com/course",
                    '_gscoach_fee' => "150",
                    '_gscoach_review' => "Saved our marriage. Truly life-changing sessions.",
                    '_gscoach_rating' => "4.9",
                    '_gscoach_custom_page' => "https://relationshipcoach.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/emma.coach'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Conflict Resolution', 'percent' => 100],
                        ['skill' => 'Communication Skills', 'percent' => 95],
                        ['skill' => 'Emotional Intelligence', 'percent' => 90],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Noah Williams",
                'post_content'  => "Mindset Coach helping individuals overcome self-doubt, build confidence, and achieve personal breakthroughs.\r\n\r\nThrough powerful coaching frameworks, I help clients rewire limiting beliefs and unlock their full potential in life and business.",
                'post_status'   => $post_status,
                'post_type'     => $post_type,
                'post_date'     => '2020-08-15 07:01:44',
                'tax_input'     => $this->get_tax_inputs([
                    "gs_coach_group" => ['mindset-coaching'],
                    "gs_coach_tag" => ['confidence', 'self-growth'],
                    "gs_coach_language" => ['english'],
                    "gs_coach_location" => ['toronto'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['mindset', 'self-development']
                ]),
                'meta_input'    => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-6',
                    '_gscoach_profession' => "Mindset Coach",
                    '_gscoach_experience' => "6 Years",
                    '_gscoach_education' => "Personal Development",
                    '_gscoach_address' => "Downtown Toronto",
                    '_gscoach_state' => "ON",
                    '_gscoach_country' => "Canada",
                    '_gscoach_contact' => "+1 416-555-2233",
                    '_gscoach_email' => "noah@mindsetcoach.com",
                    '_gscoach_shedule' => "12:00",
                    '_gscoach_available' => "April 22, 2026 to April 30, 2026",
                    '_gscoach_psite' => "https://mindsetcoach.com/",
                    '_gscoach_courselink' => "https://mindsetcoach.com/mastery",
                    '_gscoach_fee' => "100",
                    '_gscoach_review' => "Helped me break mental barriers and grow fast.",
                    '_gscoach_rating' => "4.8",
                    '_gscoach_custom_page' => "https://mindsetcoach.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/in/noahcoach'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Confidence Building', 'percent' => 100],
                        ['skill' => 'Mindset Shift', 'percent' => 95],
                        ['skill' => 'Goal Execution', 'percent' => 90],
                    ],
                ])
            );

            foreach ( $coaches as $coach ) {
                // Insert the post into the database
                $post_id = wp_insert_post( $coach );
                // Add meta value for demo
                if ( $post_id ) add_post_meta( $post_id, 'gscoach-demo_data', 1 );
            }

            do_action( 'gscoach_dummy_coaches_process_finished' );

        }

        public function delete_dummy_coaches() {
            
            $coaches = $this->get_dummy_coaches();

            if ( empty($coaches) ) return;

            foreach ($coaches as $coach) {
                wp_delete_post( $coach->ID, true );
            }

            delete_transient( 'gscoach_dummy_coaches' );

        }

        public function get_dummy_coaches() {

            $coaches = get_transient( 'gscoach_dummy_coaches' );

            if ( false !== $coaches ) return $coaches;

            $coaches = get_posts( array(
                'numberposts' => -1,
                'post_type'   => 'gs_coaches',
                'meta_key' => 'gscoach-demo_data',
                'meta_value' => 1,
            ));
            
            if ( is_wp_error($coaches) || empty($coaches) ) {
                delete_transient( 'gscoach_dummy_coaches' );
                return [];
            }
            
            set_transient( 'gscoach_dummy_coaches', $coaches, 3 * MINUTE_IN_SECONDS );

            return $coaches;

        }

        public function http_request_args( $args ) {
            
            $args['sslverify'] = false;

            return $args;

        }

        // Attachments
        public function create_dummy_attachments() {

            do_action( 'gscoach_dummy_attachments_process_start' );

            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            $attachment_files = [
                'gscoach-image-1.jpg',
                'gscoach-image-2.jpg',
                'gscoach-image-3.jpg',
                'gscoach-image-4.jpg',
                'gscoach-image-5.jpg',
                'gscoach-image-6.jpg',
            ];

            add_filter( 'http_request_args', [ $this, 'http_request_args' ] );

            wp_raise_memory_limit( 'image' );

            foreach ( $attachment_files as $file ) {

                $file = GSCOACH_PLUGIN_URI . '/assets/img/dummy-data/' . $file;

                $filename = basename($file);

                $get = wp_remote_get( $file );
                $type = wp_remote_retrieve_header( $get, 'content-type' );
                $mirror = wp_upload_bits( $filename, null, wp_remote_retrieve_body( $get ) );
                
                // Prepare an array of post data for the attachment.
                $attachment = array(
                    'guid'           => $mirror['url'],
                    'post_mime_type' => $type,
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                
                // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $mirror['file'] );
                
                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                add_post_meta( $attach_id, 'gscoach-demo_data', 1 );

            }

            remove_filter( 'http_request_args', [ $this, 'http_request_args' ] );

            do_action( 'gscoach_dummy_attachments_process_finished' );

        }

        public function delete_dummy_attachments() {
            
            $attachments = $this->get_dummy_attachments();

            if ( empty($attachments) ) return;

            foreach ($attachments as $attachment) {
                wp_delete_attachment( $attachment->ID, true );
            }

            delete_transient( 'gscoach_dummy_attachments' );

        }

        public function get_dummy_attachments() {

            $attachments = get_transient( 'gscoach_dummy_attachments' );

            if ( false !== $attachments ) return $attachments;

            $attachments = get_posts( array(
                'numberposts' => -1,
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'meta_key' => 'gscoach-demo_data',
                'meta_value' => 1,
            ));
            
            if ( is_wp_error($attachments) || empty($attachments) ) {
                delete_transient( 'gscoach_dummy_attachments' );
                return [];
            }
            
            set_transient( 'gscoach_dummy_attachments', $attachments, 3 * MINUTE_IN_SECONDS );

            return $attachments;
        }
        
        // Terms
        public function create_dummy_terms() {

            do_action( 'gscoach_dummy_terms_process_start' );
            
            $terms = [
                // GROUPS (Core Niches)
                [
                    "name" => "Business Coaching",
                    "slug" => "business-coaching",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "Life Coaching",
                    "slug" => "life-coaching",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "Career Coaching",
                    "slug" => "career-coaching",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "Fitness Coaching",
                    "slug" => "fitness-coaching",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "Relationship Coaching",
                    "slug" => "relationship-coaching",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "Mindset Coaching",
                    "slug" => "mindset-coaching",
                    "group" => "gs_coach_group",
                ],

                // TAGS
                [
                    "name" => "Entrepreneur",
                    "slug" => "entrepreneur",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Self Growth",
                    "slug" => "self-growth",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Resume",
                    "slug" => "resume",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Fitness",
                    "slug" => "fitness",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Relationships",
                    "slug" => "relationships",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Confidence",
                    "slug" => "confidence",
                    "group" => "gs_coach_tag",
                ],

                // LANGUAGES
                [
                    "name" => "English",
                    "slug" => "english",
                    "group" => "gs_coach_language",
                ],
                [
                    "name" => "Spanish",
                    "slug" => "spanish",
                    "group" => "gs_coach_language",
                ],
                [
                    "name" => "Korean",
                    "slug" => "korean",
                    "group" => "gs_coach_language",
                ],

                // LOCATIONS
                [
                    "name" => "New York",
                    "slug" => "new-york",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "California",
                    "slug" => "california",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Toronto",
                    "slug" => "toronto",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "London",
                    "slug" => "london",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Sydney",
                    "slug" => "sydney",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Seoul",
                    "slug" => "seoul",
                    "group" => "gs_coach_location",
                ],

                // GENDER
                [
                    "name" => "Male",
                    "slug" => "male",
                    "group" => "gs_coach_gender",
                ],
                [
                    "name" => "Female",
                    "slug" => "female",
                    "group" => "gs_coach_gender",
                ],

                // SPECIALTIES
                [
                    "name" => "Leadership",
                    "slug" => "leadership",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Self Development",
                    "slug" => "self-development",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Career Development",
                    "slug" => "career-development",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Strength Training",
                    "slug" => "strength-training",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Couples Coaching",
                    "slug" => "couples-coaching",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Mindset",
                    "slug" => "mindset",
                    "group" => "gs_coach_specialty",
                ],
            ];

            foreach( $terms as $term ) {

                $response = wp_insert_term( $term['name'], $term['group'], array('slug' => $term['slug']) );
    
                if ( ! is_wp_error($response) ) {
                    add_term_meta( $response['term_id'], 'gscoach-demo_data', 1 );
                }

            }

            do_action( 'gscoach_dummy_terms_process_finished' );

        }
        
        public function delete_dummy_terms() {
            
            $terms = $this->get_dummy_terms();

            if ( empty($terms) ) return;
    
            foreach ( $terms as $term ) {
                wp_delete_term( $term['term_id'], $term['taxonomy'] );
            }

        }

        public function get_dummy_terms() {

            $taxonomies = $this->get_taxonomy_list();

            $terms = get_terms( array(
                'taxonomy' => $taxonomies,
                'hide_empty' => false,
                'meta_key' => 'gscoach-demo_data',
                'meta_value' => 1,
            ));
            
            if ( is_wp_error($terms) || empty($terms) ) return [];

            return json_decode( json_encode( $terms ), true ); // Object to Array

        }

        // Shortcode
        public function create_dummy_shortcodes() {

            do_action( 'gscoach_dummy_shortcodes_process_start' );

            plugin()->builder->create_dummy_shortcodes();

            do_action( 'gscoach_dummy_shortcodes_process_finished' );

        }

        public function delete_dummy_shortcodes() {
            
            plugin()->builder->delete_dummy_shortcodes();

        }

    }

}

