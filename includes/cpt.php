<?php 

namespace GSCOACH;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Cpt {

	// Constructor
	public function __construct() {

		add_action( 'init', [ $this, 'register' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'after_setup_theme', [ $this, 'theme_support' ] );

		if ( ! is_pro_active_and_valid() ) {
			$this->dummy_tax();
		}
		
	}

	// Register Custom Post Type
	function register() {
		$labels = array(
			'name'               => is_admin() ? get_post_type_title() : gs_get_post_type_archive_title(),
			'singular_name'      => __( 'Coach', 'gscoach' ),
			'menu_name'          => _x( 'GS Coach', 'admin menu', 'gscoach' ),
			'name_admin_bar'     => _x( 'GS Coach', 'add new on admin bar', 'gscoach' ),
			'add_new'            => __( 'Add New Coach', 'gscoach' ),
			'add_new_item'       => __( 'Add New Coach', 'gscoach' ),
			'new_item'           => __( 'New Coach', 'gscoach' ),
			'edit_item'          => __( 'Edit Coach', 'gscoach' ),
			'view_item'          => __( 'View Coach', 'gscoach' ),
			'all_items'          => __( 'All Coaches', 'gscoach' ),
			'search_items'       => __( 'Search Coaches', 'gscoach' ),
			'parent_item_colon'  => __( 'Parent Coaches:', 'gscoach' ),
			'not_found'          => __( 'No Coaches found.', 'gscoach' ),
			'not_found_in_trash' => __( 'No Coaches found in Trash.', 'gscoach' ),
		);

		$gs_coaches_slug = getoption( 'gs_coaches_slug', 'coaches' );
		$replace_custom_slug = getoption( 'replace_custom_slug', 'off' ) === 'off';

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $gs_coaches_slug, 'with_front' => $replace_custom_slug ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => GSCOACH_MENU_POSITION,
			'menu_icon'          => GSCOACH_PLUGIN_URI . '/assets/img/icon.svg',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'wpml_cf_fields'     => true,
			'show_in_wpml_language_switcher' => true
		);

		register_post_type( 'gs_coaches', $args );
	}

	// Register Taxonomies
	public function register_taxonomies() {
		
		$this->group();
		$this->tag();

		if ( is_pro_active_and_valid() ) {
			$this->language();
			$this->location();
			$this->gender();
			$this->specialty();
			$this->extra_one();
			$this->extra_two();
			$this->extra_three();
			$this->extra_four();
			$this->extra_five();
		}
	}
	
	// Register Group Taxonomy For Coach
	function group() {

		if ( plugin()->builder->get_tax_option('enable_group_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('group_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('group_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('group_tax_archive_slug', 'gs-coach-group'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_group', array( 'gs_coaches' ), $args );

	}
	
	// Register Tag Taxonomy For Coach
	function tag() {

		if ( plugin()->builder->get_tax_option('enable_tag_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('tag_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('tag_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('tag_tax_archive_slug', 'gs-coach-tag'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_tag', array( 'gs_coaches' ), $args );

	}

	// Register Language Taxonomy For Coach
	function language() {

		if ( plugin()->builder->get_tax_option('enable_language_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('language_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('language_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('language_tax_archive_slug', 'gs-coach-language'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_language', array( 'gs_coaches' ), $args );

	}

	// Register Location Taxonomy For Coach
	function location() {

		if ( plugin()->builder->get_tax_option('enable_location_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('location_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('location_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('location_tax_archive_slug', 'gs-coach-location'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_location', array( 'gs_coaches' ), $args );

	}

	// Register Gender Taxonomy For Coach
	function gender() {

		if ( plugin()->builder->get_tax_option('enable_gender_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('gender_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('gender_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('gender_tax_archive_slug', 'gs-coach-gender'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_gender', array( 'gs_coaches' ), $args );

	}

	// Register Specialty Taxonomy For Coach
	function specialty() {

		if ( plugin()->builder->get_tax_option('enable_specialty_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('specialty_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('specialty_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('specialty_tax_archive_slug', 'gs-coach-specialty'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_specialty', array( 'gs_coaches' ), $args );

	}

	// Register Extra One Taxonomy For Coach
	function extra_one() {

		if ( plugin()->builder->get_tax_option('enable_extra_one_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_one_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_one_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_one_tax_archive_slug', 'gs-coach-extra-one'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_extra_one', array( 'gs_coaches' ), $args );

	}

	// Register Extra Two Taxonomy For Coach
	function extra_two() {

		if ( plugin()->builder->get_tax_option('enable_extra_two_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_two_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_two_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_two_tax_archive_slug', 'gs-coach-extra-two'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_extra_two', array( 'gs_coaches' ), $args );

	}

	// Register Extra Three Taxonomy For Coach
	function extra_three() {

		if ( plugin()->builder->get_tax_option('enable_extra_three_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_three_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_three_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_three_tax_archive_slug', 'gs-coach-extra-three'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_extra_three', array( 'gs_coaches' ), $args );

	}

	// Register Extra Four Taxonomy For Coach
	function extra_four() {

		if ( plugin()->builder->get_tax_option('enable_extra_four_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_four_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_four_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_four_tax_archive_slug', 'gs-coach-extra-four'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_extra_four', array( 'gs_coaches' ), $args );

	}

	// Register Extra Five Taxonomy For Coach
	function extra_five() {

		if ( plugin()->builder->get_tax_option('enable_extra_five_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_five_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_five_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gscoach' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_five_tax_archive_slug', 'gs-coach-extra-five'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_coach_extra_five', array( 'gs_coaches' ), $args );

	}

	// Add theme support for Featured Images
	function theme_support() {
		// Add Shortcode support in text widget
		add_filter( 'widget_text', 'do_shortcode' );
	}

	// Add Pro Guard
    function add_pro_guard() {
		echo '<div class="gs-coach-disable--term-pages"><div class="gs-coach-pro-field--inner"><div class="gs-coach-pro-field--content"><a href="' . esc_url( pricing_url( 'taxonomy-page' ) ) . '">Upgrade to PRO</a></div></div></div>';
    }

	// Remove Actions
    function term_remove_actions() {
        return [];
    }

	// Dummy Tax
	function dummy_tax() {

		add_action( 'gs_coach_language_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_location_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_gender_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_specialty_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_one_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_two_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_three_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_four_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_five_pre_add_form', [ $this, 'add_pro_guard' ] );
	
		add_action( 'gs_coach_language_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_location_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_gender_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_specialty_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_one_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_two_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_three_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_four_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_coach_extra_five_pre_edit_form', [ $this, 'add_pro_guard' ] );
	
		add_filter( "gs_coach_language_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_coach_location_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_coach_gender_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_coach_specialty_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_coach_extra_one_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_coach_extra_two_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_coach_extra_three_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_coach_extra_four_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_coach_extra_five_row_actions", [ $this, 'term_remove_actions' ] );
		
	}

}