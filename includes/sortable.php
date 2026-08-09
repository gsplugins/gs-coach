<?php

namespace GSCOACH;

/**
 * Protect direct access
 */
defined('ABSPATH') || exit;

class Sortable {

	public function __construct() {
		
		// Add sortable menu in admin
		add_action('admin_menu', [$this, 'gs_coach_sortable']);
		
		// Set object_type if not set & Redirect to the correct page
		add_action('admin_init', [$this, 'maybe_redirect']);
		
		// Update coach order via AJAX
		add_action('wp_ajax_update_coaches_order', array($this, 'update_coaches_order'));
		
		// Update  filters order via AJAX
		add_action('wp_ajax_update_coach_filters_order', array($this, 'update_coach_filters_order'));
		
		// Update  filters order via AJAX
		add_action('wp_ajax_update_coach_meta_order', array($this, 'update_coach_meta_order'));
		
		// Enqueue admin scripts for sorting
		add_action('admin_enqueue_scripts', array($this, 'sort_scripts'));
		
		// Set custom order for posts
		add_filter('posts_orderby', array($this, 'order_posts'));
	}

	/**
	 * Update Coach Filters Order
	 */
	public function update_coach_filters_order() {

		if ( ! is_pro_active_and_valid() ) wp_send_json_error();

		if (empty($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_gscoach_update_order_gs_')) {
			wp_send_json_error(__('Unauthorised Request', 'gscoach'), 401);
		}

		$order = explode(',', sanitize_text_field($_POST['order']));
		update_option('gs_coach_filters_order', $order);

		wp_send_json_success();
	}

	/**
	 * Update Coach Metas Order
	 */
	public function update_coach_meta_order() {

		if ( ! is_pro_active_and_valid() ) wp_send_json_error();

		if (empty($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_gscoach_update_order_gs_')) {
			wp_send_json_error(__('Unauthorised Request', 'gscoach'), 401);
		}

		$order = explode(',', sanitize_text_field($_POST['order']));
		update_option('gs_coach_meta_order', $order);

		wp_send_json_success();
	}

	/**
	 * Order Posts
	 */
	public function order_posts($orderby) {
		
		global $wpdb;
		global $wp_query;
		
		if ( ! isset($wp_query) || ! is_main_query() ) return $orderby;

		if ( is_post_type_archive('gs_coaches') ) {
			$orderby = "{$wpdb->posts}.menu_order, {$wpdb->posts}.post_date DESC";
		}

		return ($orderby);
	}

	/**
	 * Enqueue Sortable Scripts
	 */
	public function sort_scripts($hook) {

		if ( $hook != 'gs_coaches_page_sort_gs_coach' ) return;
		
		wp_enqueue_style('gs-coach-sort', GSCOACH_PLUGIN_URI . '/assets/admin/css/sort.min.css', ['gs-font-awesome-5'], GSCOACH_VERSION);
		wp_enqueue_script('gs-coach-sort', GSCOACH_PLUGIN_URI . '/assets/admin/js/sort.min.js', array('jquery', 'jquery-ui-sortable'), GSCOACH_VERSION, true);

		if ( is_pro_active_and_valid() ) {

			if ( empty($_GET['object_type']) || $_GET['object_type'] == 'gs_coaches' ) {
				$action = 'update_coaches_order';
			} else if ( $_GET['object_type'] == 'gs_coach_filters' ) {
				$action = 'update_coach_filters_order';
			} else if ( $_GET['object_type'] == 'gs_coach_meta' ) {
				$action = 'update_coach_meta_order';
			} else {
				$action = 'update_coaches_order';
			}

			$data = [
				'is_pro' => wp_validate_boolean( is_pro_active_and_valid() ),
				'nonce' => wp_create_nonce('_gscoach_update_order_gs_'),
				'action' => $action
			];

			wp_localize_script('gs-coach-sort', '_gscoach_sort_data', $data);
		}

	}

	/**
	 * Update Coaches Order
	 */
	public function update_coaches_order() {

		if (empty($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_gscoach_update_order_gs_')) {
			wp_send_json_error(__('Unauthorised Request', 'gscoach'), 401);
		}

		global $wpdb;

		$order = explode(',', $_POST['order']);
		$counter = 0;

		foreach ($order as $post_id) {
			$wpdb->update($wpdb->posts, array('menu_order' => $counter), array('ID' => (int) $post_id));
			$counter++;
		}

		return wp_send_json_success();
	}

	/**
	 * Redirect to the correct page
	 */
	public function maybe_redirect() {
		if ( isset($_GET['post_type']) && $_GET['post_type'] == 'gs_coaches' && isset($_GET['page']) && $_GET['page'] === 'sort_gs_coach' && empty($_GET['object_type']) ) {
			wp_redirect( $this->get_url_with_object_type() );
			exit;
		}
	}

	/**
	 * Add Sortable Menu
	 */
	public function gs_coach_sortable() {
		add_submenu_page(
			'edit.php?post_type=gs_coaches',
			'Sort Order',
			'Sort Order',
			'publish_pages',
			'sort_gs_coach',
			[$this, 'gs_coach_sortable_callback']
		);
	}

	/**
	 * Get the full URL
	 */
	public function get_full_url() {
		// Get the protocol
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

		// Get the host
		$host = $_SERVER['HTTP_HOST'];

		// Get the request URI
		$uri = $_SERVER['REQUEST_URI'];

		// Combine them to get the full URL
		$full_url = $protocol . $host . $uri;

		return $full_url;
	}

	/**
	 * Get the URL with object type
	 */
	public function get_url_with_object_type( $object = 'gs_coaches' ) {
		return add_query_arg( 'object_type', $object, $this->get_full_url() );
	}

	/**
	 * Sortable Callback
	 */
	public function gs_coach_sortable_callback() {
		
		$object_type = isset($_GET['object_type']) ? $_GET['object_type'] : 'gs_coaches';

		if ( ! is_pro_active_and_valid() ) : ?>

		<div class="gs-coach-disable--sort-page">
			<div class="gs-coach-disable--sort-inner">
				<div class="gs-coach-disable--term-message"><a href="https://www.gsplugins.com/product/wordpress-coaches-plugin/#pricing">Upgrade to PRO</a></div>
			</div>
		</div>

		<?php endif; ?>

		<div class="gs-plugins--sort-page">

			<div class="gs-plugins--sort-links">
				<a class="<?php echo $object_type === 'gs_coaches' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type('gs_coaches') ); ?>">Coaches</a>
				<a class="<?php echo $object_type === 'gs_coach_filters' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type('gs_coach_filters') ); ?>">Coach Filters</a>
				<a class="<?php echo $object_type === 'gs_coach_meta' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type('gs_coach_meta') ); ?>">Meta Fields</a>
			</div>

			<div class="gs-plugins--sort-content">

				<!-- <h2>Sort Order <img src="<?php // bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h2> -->
				
				<?php if ($object_type === 'gs_coaches') : ?>

					<?php $this->sort_coaches(); ?>

				<?php elseif ($object_type === 'gs_coach_filters') : ?>

					<?php $this->sort_coach_filters(); ?>

				<?php elseif ($object_type === 'gs_coach_meta') : ?>

					<?php $this->sort_coach_meta(); ?>

				<?php else : ?>

					<?php $this->sort_coaches(); ?>

				<?php endif; ?>
			</div>

		</div>

		<?php

	}

	/**
	 * Sort Coaches
	 */
	public function sort_coaches() {

		$sortable = new \WP_Query('post_type=gs_coaches&posts_per_page=-1&orderby=menu_order&order=ASC');

		?>

		<div class="gs-coach--sort-wrap <?php echo is_pro_active() ? 'sort--wrap-active' : ''; ?>">

			<div class="gscoach-sort--columns">

				<div class="gscoach-sort--left-area">

					<h3><?php esc_html_e('Step 1: Drag & Drop to rearrangees', 'gscoach'); ?><img src="<?php // bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h3>

					<?php if ($sortable->have_posts()) : ?>

						<ul id="sortable-list">
							<?php while ($sortable->have_posts()) :

								$sortable->the_post();
								$term_obj_list = get_the_terms(get_the_ID(), 'gs_coach_group');
								$terms_string = '';

								if (is_array($term_obj_list) || is_object($term_obj_list)) {
									$terms_string = join('</span><span>', wp_list_pluck($term_obj_list, 'name'));
								}

								if (!empty($terms_string)) $terms_string = '<span>' . $terms_string . '</span>';

							?>

								<li id="<?php the_id(); ?>">
									<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
									<div class="sortable-content sortable-thumbnail"><span><?php coach_thumbnail('thumbnail', true); ?></span></div>
									<div class="sortable-content sortable-title"><?php the_title(); ?></div>
									<div class="sortable-content sortable-group"><?php echo wp_kses_post($terms_string); ?></div>
								</li>

							<?php endwhile; ?>
						</ul>

					<?php else : ?>

						<div class="notice notice-warning" style="margin-top: 30px;">
							<h3><?php _e('No Coach coach Found!', 'gscoach'); ?></h3>
							<p style="font-size: 14px;"><?php _e('We didn\'t find any coach.</br>Please add some coaches to sort them.', 'gscoach'); ?></p>
							<a href="<?php echo admin_url('post-new.php?post_type=gs_coaches'); ?>" style="margin-top: 10px; margin-bottom: 20px;" class="button button-primary button-large"><?php _e('Add coach', 'gscoach'); ?></a>
						</div>

					<?php endif; ?>

				</div>

				<div class="gscoach-sort--right-area">
					
					<h3><?php esc_html_e('Step 2: Query Settings for coaches', 'gscoach'); ?></h3>

					<div style="background: #fff; border-radius: 6px; padding: 30px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.12); font-size: 1.3em; line-height: 1.6; margin-top: 30px">
						
						<ol style="list-style: numeric; padding-left: 20px; margin: 0">
							<li>Create or Edit a Shortcode From <strong>GS Coach > Coach Shortcode</strong>.</li>
							<li>Then proceed to the 3rd tab labeled <strong>Query Settings</strong>.</li>
							<li>Set <strong>Order by</strong> to <strong>Custom Order</strong>.</li>
							<li>Set <strong>Order</strong> to <strong>ASC</strong>.</li>
						</ol>
	
						<ul style="list-style: circle; padding-left: 20px; margin-top: 20px">
							<li>Follow <a href="https://docs.gsplugins.com/gs-coach/manage-gs-coach/sort-order/" target="_blank">Documentation</a> to learn more.</li>
							<li><a href="https://www.gsplugins.com/contact/" target="_blank">Contact us</a> for support.</li>
						</ul>

					</div>

				</div>

			</div>

		</div><!-- #wrap -->

		<?php

	}

	/**
	 * Get Coach Filters Strings
	 */
	public static function get_coach_filters_strings() {
		$translations = plugin()->builder->get_translation_srtings();
		$coach_filters = [
			'search_by_name' => $translations['instant-search-by-name'],
			'gs_coach_tag' => $translations['gs-coach-srch-by-tag'],
			'filter_by_designation' => $translations['filter-by-designation'],
			'gs_coach_language' => $translations['filter-by-language'],
			'gs_coach_location' => $translations['filter-by-location'],
			'gs_coach_gender' => $translations['filter-by-gender'],
			'gs_coach_specialty' => $translations['filter-by-speciality'],
			'gs_coach_extra_one' => $translations['filter-by-extra-one'],
			'gs_coach_extra_two' => $translations['filter-by-extra-two'],
			'gs_coach_extra_three' => $translations['filter-by-extra-three'],
			'gs_coach_extra_four' => $translations['filter-by-extra-four'],
			'gs_coach_extra_five' => $translations['filter-by-extra-five']
		];
		return $coach_filters;
	}

	/**
	 * Get Coach Filters
	 */
	public static function get_coach_filters() {
		$defaults = [
			'search_by_name',
			'gs_coach_tag',
			'filter_by_designation',
			'gs_coach_language',
			'gs_coach_location',
			'gs_coach_gender',
			'gs_coach_specialty',
			'gs_coach_extra_one',
			'gs_coach_extra_two',
			'gs_coach_extra_three',
			'gs_coach_extra_four',
			'gs_coach_extra_five',
		];
		$saved = get_option( 'gs_coach_filters_order', $defaults );
		$filter_strings = self::get_coach_filters_strings();
		return array_merge(array_flip($saved), $filter_strings);
	}

	/**
	 * Sort Coach Filters
	 */
	public function sort_coach_filters() {

		$filters = self::get_coach_filters();

		?>

		<div class="gs-coach--sort-wrap <?php echo is_pro_active() ? 'sort--wrap-active' : ''; ?>">

			<div class="gscoach-sort--columns">

				<div class="gscoach-sort--left-area">

					<h3><?php esc_html_e('Step 1: Drag & Drop to rearrange filters', 'gscoach'); ?><img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h3>

					<?php if (!empty($filters)) : ?>

						<ul id="sortable-list">
							<?php foreach ($filters as $filter => $filter_title) : ?>

								<li id="<?php echo esc_attr($filter); ?>">
									<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
									<div class="sortable-content sortable-title"><?php echo esc_html($filter_title); ?></div>
								</li>

							<?php endforeach; ?>
						</ul>

					<?php endif; ?>

				</div>

				<div class="gscoach-sort--right-area">
					
					<h3><?php esc_html_e('Step 2: Apply Filter Order', 'gscoach'); ?></h3>

					<div style="background: #fff; border-radius: 6px; padding: 30px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.12); font-size: 1.3em; line-height: 1.6; margin-top: 30px">
						
						<ol style="list-style: numeric; padding-left: 20px; margin: 0">
							<li>Create or Edit a Shortcode From <strong>GS Coach > Coach Shortcode</strong>.</li>
							<li>Enable <strong>Filter</strong> from the shortcode settings.</li>
							<li>Turn on the individual filters you want to display.</li>
							<li>The filter order set here will apply automatically on the frontend.</li>
						</ol>
	
						<ul style="list-style: circle; padding-left: 20px; margin-top: 20px">
							<li>Follow <a href="https://docs.gsplugins.com/gs-coach/manage-gs-coach/sort-order/" target="_blank">Documentation</a> to learn more.</li>
							<li><a href="https://www.gsplugins.com/contact/" target="_blank">Contact us</a> for support.</li>
						</ul>

					</div>

				</div>

			</div>

		</div><!-- #wrap -->

		<?php
	}


	/**
	 * Sort Coach Meta
	 */
	public function sort_coach_meta() {	

		$metas = gs_get_sort_metas();

		?>

		<div class="gs-coach--sort-wrap <?php echo is_pro_active() ? 'sort--wrap-active' : ''; ?>">

			<div class="gscoach-sort--columns">

				<div class="gscoach-sort--left-area">

					<h3><?php esc_html_e('Step 1: Drag & Drop to rearrange meta fields', 'gscoach'); ?><img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h3>

					<?php if (!empty($metas)) : ?>

						<ul id="sortable-list">
							<?php foreach ($metas as $meta) : ?>

								<li id="<?php echo esc_attr($meta['key']); ?>">
									<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
									<div class="sortable-content sortable-title"><?php echo esc_html($meta['name']); ?></div>
								</li>

							<?php endforeach; ?>
						</ul>

					<?php endif; ?>

				</div>

				<div class="gscoach-sort--right-area">
					
					<h3><?php esc_html_e('Step 2: Apply Meta Fields Order', 'gscoach'); ?></h3>

					<div style="background: #fff; border-radius: 6px; padding: 30px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.12); font-size: 1.3em; line-height: 1.6; margin-top: 30px">
						
						<ol style="list-style: numeric; padding-left: 20px; margin: 0">
							<li>Create or Edit a Shortcode From <strong>GS Coach > Coach Shortcode</strong>.</li>
							<li>Use a theme/layout that shows coach meta details (popup, panel, drawer, or single).</li>
							<li>Fill in the meta fields for each coach under the coach edit screen.</li>
							<li>The meta fields order set here will apply automatically on the frontend.</li>
						</ol>
	
						<ul style="list-style: circle; padding-left: 20px; margin-top: 20px">
							<li>Follow <a href="https://docs.gsplugins.com/gs-coach/manage-gs-coach/sort-order/" target="_blank">Documentation</a> to learn more.</li>
							<li><a href="https://www.gsplugins.com/contact/" target="_blank">Contact us</a> for support.</li>
						</ul>

					</div>

				</div>

			</div>

		</div><!-- #wrap -->

		<?php
	}

}