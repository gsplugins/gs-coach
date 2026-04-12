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
                'post_title'    => "Morgan Freman",
                'post_content'  => "Experienced and innovative Web Developer with a passion for creating elegant and functional web solutions. Proficient in frontend and backend technologies, I excel at translating complex design and functionality requirements into clean, efficient, and user-friendly websites.\r\n\r\nWith a strong foundation in coding and problem-solving, I am dedicated to delivering high-quality projects on time and within scope.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-10 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_coach_group" => ['apps-development', 'graphic-design'],
                    "gs_coach_tag" => ['agency', 'freelancer'],
                    "gs_coach_language" => ['english', 'german'],
                    "gs_coach_location" => ['london', 'paris', 'sweden'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['graphic-design', 'marketing-ninja']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-1',
                    '_gscoach_profession' => "Web Developer",
                    '_gscoach_experience' => "3 Years",
                    '_gscoach_education' => "BBA",
                    '_gscoach_address' => "37/7, Hilton Road",
                    '_gscoach_state' => "LA",
                    '_gscoach_country' => "USA",
                    '_gscoach_contact' => "+1 202-555-0110",
                    '_gscoach_email' => "john.doe@gmail.com",
                    '_gscoach_shedule' => "13:00",
                    '_gscoach_available' => "2025-01-23",
                    '_gscoach_psite' => "https://yoursite.com/",
                    '_gscoach_courselink' => "https://courseurl.com/",
                    '_gscoach_fee' => "500",
                    '_gscoach_review' => "Lorem ipsum dolor sit amet",
                    '_gscoach_rating' => "3.5",
                    '_gscoach_custom_page' => "https://yoursite.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/john_doe'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/john_doe'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/john_doe'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/john_doe'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Communication', 'percent' => 100],
                        ['skill' => 'Growth Process', 'percent' => 90],
                        ['skill' => 'Analysis', 'percent' => 95],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Samuel Oliver",
                'post_content'  => "Dedicated and knowledgeable Corona Specialist with a proven background in effectively managing and mitigating challenges posed by the COVID-19 pandemic.\r\n\r\nLeveraging a multidisciplinary skill set, I am adept at developing and implementing comprehensive strategies for disease prevention, public health education, crisis management, and community outreach.\r\n\r\nBy staying informed about the latest developments, guidelines, and best practices, I am committed to fostering safer environments and contributing to the well-being of individuals and communities.\r\n\r\nDedicated and knowledgeable Corona Specialist with a proven background in effectively managing and mitigating challenges posed by the COVID-19 pandemic.\r\n\r\nLeveraging a multidisciplinary skill set, I am adept at developing and implementing comprehensive strategies for disease prevention, public health education, crisis management, and community outreach.\r\n\r\nBy staying informed about the latest developments, guidelines, and best practices, I am committed to fostering safer environments and contributing to the well-being of individuals and communities.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-11 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_coach_group" => ['content-creation', 'marketing'],
                    "gs_coach_tag" => ['fashion-design', 'interior-design'],
                    "gs_coach_language" => ['french', 'german'],
                    "gs_coach_location" => ['london', 'new-zealand', 'usa'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['graphic-design']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-2',
                    '_gscoach_profession' => "Web Developer",
                    '_gscoach_experience' => "3 Years",
                    '_gscoach_education' => "BBA",
                    '_gscoach_address' => "37/7, Hilton Road",
                    '_gscoach_state' => "LA",
                    '_gscoach_country' => "USA",
                    '_gscoach_contact' => "+1 202-555-0110",
                    '_gscoach_email' => "john.doe@gmail.com",
                    '_gscoach_shedule' => "13:00",
                    '_gscoach_available' => "2025-01-23",
                    '_gscoach_psite' => "https://yoursite.com/",
                    '_gscoach_courselink' => "https://courseurl.com/",
                    '_gscoach_fee' => "500",
                    '_gscoach_review' => "Lorem ipsum dolor sit amet",
                    '_gscoach_rating' => "3.5",
                    '_gscoach_custom_page' => "https://yoursite.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/john_doe'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/john_doe'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/john_doe'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/john_doe'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Graphic Design', 'percent' => 95],
                        ['skill' => 'UI/UX Design', 'percent' => 100],
                        ['skill' => 'Design Tools', 'percent' => 95],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Orlando Bloom",
                'post_content'  => "Dedicated and knowledgeable Corona Specialist with a proven background in effectively managing and mitigating challenges posed by the COVID-19 pandemic.\r\n\r\nLeveraging a multidisciplinary skill set, I am adept at developing and implementing comprehensive strategies for disease prevention, public health education, crisis management, and community outreach.\r\n\r\nBy staying informed about the latest developments, guidelines, and best practices, I am committed to fostering safer environments and contributing to the well-being of individuals and communities.\r\n\r\nDedicated and knowledgeable Corona Specialist with a proven background in effectively managing and mitigating challenges posed by the COVID-19 pandemic.\r\n\r\nLeveraging a multidisciplinary skill set, I am adept at developing and implementing comprehensive strategies for disease prevention, public health education, crisis management, and community outreach.\r\n\r\nBy staying informed about the latest developments, guidelines, and best practices, I am committed to fostering safer environments and contributing to the well-being of individuals and communities.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-12 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_coach_group" => ['apps-development', 'content-creation'],
                    "gs_coach_tag" => ['creative', 'programmer'],
                    "gs_coach_language" => ['english', 'german'],
                    "gs_coach_location" => ['australia', 'london'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['graphic-design', 'marketing-ninja']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-3',
                    '_gscoach_profession' => "Web Developer",
                    '_gscoach_experience' => "3 Years",
                    '_gscoach_education' => "BBA",
                    '_gscoach_address' => "37/7, Hilton Road",
                    '_gscoach_state' => "LA",
                    '_gscoach_country' => "USA",
                    '_gscoach_contact' => "+1 202-555-0110",
                    '_gscoach_email' => "john.doe@gmail.com",
                    '_gscoach_shedule' => "13:00",
                    '_gscoach_available' => "2025-01-23",
                    '_gscoach_psite' => "https://yoursite.com/",
                    '_gscoach_courselink' => "https://courseurl.com/",
                    '_gscoach_fee' => "500",
                    '_gscoach_review' => "Lorem ipsum dolor sit amet",
                    '_gscoach_rating' => "3.5",
                    '_gscoach_custom_page' => "https://yoursite.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/john_doe'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/john_doe'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/john_doe'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/john_doe'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Empathy', 'percent' => 100],
                        ['skill' => 'Social Skills', 'percent' => 80],
                        ['skill' => 'Active Listening', 'percent' => 85],
                    ]
                ])
            );

            $coaches[] = array(
                'post_title'    => "Juri Sepp",
                'post_content'  => "Creative and detail-oriented UI\/UX Designer with a passion for crafting exceptional user experiences. Proficient in translating user needs into visually appealing and intuitive designs. Strong collaborator who thrives in interdisciplinary coaches to deliver innovative digital solutions that combine aesthetics with functionality.\r\n\r\nPassionate UI Designer with a keen eye for detail and a drive to create exceptional digital experiences. Leveraging a strong foundation in design principles and an understanding of user behavior, I specialize in crafting interfaces that seamlessly blend aesthetics with usability.\r\n\r\nBy collaborating closely with cross-functional coaches, I consistently deliver innovative solutions that captivate users and elevate brands. With a dedication to staying at the forefront of design trends and technologies, I am committed to pushing the boundaries of visual and interactive design to create meaningful connections between users and products.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-13 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_coach_group" => ['apps-development', 'content-creation'],
                    "gs_coach_tag" => ['content-creation', 'iso-developer'],
                    "gs_coach_language" => ['german', 'spanish'],
                    "gs_coach_location" => ['australia', 'paris', 'rome'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['4g-expert', 'health-and-aging']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-4',
                    '_gscoach_profession' => "Web Developer",
                    '_gscoach_experience' => "3 Years",
                    '_gscoach_education' => "BBA",
                    '_gscoach_address' => "37/7, Hilton Road",
                    '_gscoach_state' => "LA",
                    '_gscoach_country' => "USA",
                    '_gscoach_contact' => "+1 202-555-0110",
                    '_gscoach_email' => "john.doe@gmail.com",
                    '_gscoach_shedule' => "13:00",
                    '_gscoach_available' => "2025-01-23",
                    '_gscoach_psite' => "https://yoursite.com/",
                    '_gscoach_courselink' => "https://courseurl.com/",
                    '_gscoach_fee' => "500",
                    '_gscoach_review' => "Lorem ipsum dolor sit amet",
                    '_gscoach_rating' => "3.5",
                    '_gscoach_custom_page' => "https://yoursite.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/john_doe'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/john_doe'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/john_doe'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/john_doe'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'FrontEnd Development', 'percent' => 100],
                        ['skill' => 'BackEnd Development', 'percent' => 95],
                        ['skill' => 'Server Management', 'percent' => 90],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Richard Gere",
                'post_content'  => "Results-driven SEO Manager with a proven track record of developing and executing successful search engine optimization strategies.\r\n\r\nAdept at analyzing data, identifying trends, and implementing actionable insights to improve organic search rankings and drive targeted traffic. Strong leadership skills and a passion for staying updated with industry trends, algorithms, and best practices.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-14 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_coach_group" => ['web-development', 'graphic-design'],
                    "gs_coach_tag" => ['back-end', 'full-stack'],
                    "gs_coach_language" => ['french', 'spanish'],
                    "gs_coach_location" => ['paris', 'rome'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['4g-expert', 'graphic-design', 'transmission']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-5',
                    '_gscoach_profession' => "Web Developer",
                    '_gscoach_experience' => "3 Years",
                    '_gscoach_education' => "BBA",
                    '_gscoach_address' => "37/7, Hilton Road",
                    '_gscoach_state' => "LA",
                    '_gscoach_country' => "USA",
                    '_gscoach_contact' => "+1 202-555-0110",
                    '_gscoach_email' => "john.doe@gmail.com",
                    '_gscoach_shedule' => "13:00",
                    '_gscoach_available' => "2025-01-23",
                    '_gscoach_psite' => "https://yoursite.com/",
                    '_gscoach_courselink' => "https://courseurl.com/",
                    '_gscoach_fee' => "500",
                    '_gscoach_review' => "Lorem ipsum dolor sit amet",
                    '_gscoach_rating' => "3.5",
                    '_gscoach_custom_page' => "https://yoursite.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/john_doe'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/john_doe'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/john_doe'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/john_doe'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Product Design', 'percent' => 95],
                        ['skill' => 'Competitor Analysis', 'percent' => 100],
                        ['skill' => 'Product Interaction', 'percent' => 95],
                    ],
                ])
            );

            $coaches[] = array(
                'post_title'    => "Hugh Jakman",
                'post_content'  => "Experienced and innovative Web Developer with a passion for creating elegant and functional web solutions. Proficient in frontend and backend technologies, I excel at translating complex design and functionality requirements into clean, efficient, and user-friendly websites.\r\n\r\nWith a strong foundation in coding and problem-solving, I am dedicated to delivering high-quality projects on time and within scope.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-15 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_coach_group" => ['apps-development', 'marketing'],
                    "gs_coach_tag" => ['graphic-design', 'ui-ux'],
                    "gs_coach_language" => ['english', 'german'],
                    "gs_coach_location" => ['germany', 'london', 'new-zealand'],
                    "gs_coach_gender" => ['male'],
                    "gs_coach_specialty" => ['executive-recruiter', 'networking', 'transmission', 'web-development']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gscoach-image-6',
                    '_gscoach_profession' => "Web Developer",
                    '_gscoach_experience' => "3 Years",
                    '_gscoach_education' => "BBA",
                    '_gscoach_address' => "37/7, Hilton Road",
                    '_gscoach_state' => "LA",
                    '_gscoach_country' => "USA",
                    '_gscoach_contact' => "+1 202-555-0110",
                    '_gscoach_email' => "john.doe@gmail.com",
                    '_gscoach_shedule' => "13:00",
                    '_gscoach_available' => "2025-01-23",
                    '_gscoach_psite' => "https://yoursite.com/",
                    '_gscoach_courselink' => "https://courseurl.com/",
                    '_gscoach_fee' => "500",
                    '_gscoach_review' => "Lorem ipsum dolor sit amet",
                    '_gscoach_rating' => "3.5",
                    '_gscoach_custom_page' => "https://yoursite.com/",
                    '_gscoach_socials' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/john_doe'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/john_doe'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/john_doe'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/john_doe'],
                    ],
                    '_gscoach_skills' => [
                        ['skill' => 'Cartoon Design', 'percent' => 85],
                        ['skill' => 'Product Mockup', 'percent' => 100],
                        ['skill' => 'Graphic Elements', 'percent' => 95],
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
                [
                    "name" => "Marketing",
                    "slug" => "marketing",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "Australia",
                    "slug" => "australia",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Marketing Ninja",
                    "slug" => "marketing-ninja",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Graphic Design",
                    "slug" => "graphic-design",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Agency",
                    "slug" => "agency",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "English",
                    "slug" => "english",
                    "group" => "gs_coach_language",
                ],
                [
                    "name" => "Germany",
                    "slug" => "germany",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Graphic Design",
                    "slug" => "graphic-design",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Interior Design",
                    "slug" => "interior-design",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Male",
                    "slug" => "male",
                    "group" => "gs_coach_gender",
                ],
                [
                    "name" => "German",
                    "slug" => "german",
                    "group" => "gs_coach_language",
                ],
                [
                    "name" => "Paris",
                    "slug" => "paris",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Web Development",
                    "slug" => "web-development",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Fashion Design",
                    "slug" => "fashion-design",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Female",
                    "slug" => "female",
                    "group" => "gs_coach_gender",
                ],
                [
                    "name" => "Spanish",
                    "slug" => "spanish",
                    "group" => "gs_coach_language",
                ],
                [
                    "name" => "Rome",
                    "slug" => "rome",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Networking",
                    "slug" => "networking",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Content Creation",
                    "slug" => "content-creation",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Web Development",
                    "slug" => "web-development",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "French",
                    "slug" => "french",
                    "group" => "gs_coach_language",
                ],
                [
                    "name" => "London",
                    "slug" => "london",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Transmission",
                    "slug" => "transmission",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "ISO Developer",
                    "slug" => "iso-developer",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Apps Development",
                    "slug" => "apps-development",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "New Zealand",
                    "slug" => "new-zealand",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "4G Expert",
                    "slug" => "4g-expert",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Back End",
                    "slug" => "back-end",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Programmer",
                    "slug" => "programmer",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Graphic Design",
                    "slug" => "graphic-design",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "Sweden",
                    "slug" => "sweden",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Executive Recruiter",
                    "slug" => "executive-recruiter",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "Full Stack",
                    "slug" => "full-stack",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Creative",
                    "slug" => "creative",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Content Creation",
                    "slug" => "content-creation",
                    "group" => "gs_coach_group",
                ],
                [
                    "name" => "USA",
                    "slug" => "usa",
                    "group" => "gs_coach_location",
                ],
                [
                    "name" => "Health and Aging",
                    "slug" => "health-and-aging",
                    "group" => "gs_coach_specialty",
                ],
                [
                    "name" => "UI/UX",
                    "slug" => "ui-ux",
                    "group" => "gs_coach_tag",
                ],
                [
                    "name" => "Freelancer",
                    "slug" => "freelancer",
                    "group" => "gs_coach_tag",
                ]
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

