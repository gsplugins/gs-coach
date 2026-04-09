<?php

namespace GSCOACH;

$single_page_style = getoption( 'single_page_style', 'default' );

$single_page_style = apply_filters( 'gs_coach_single_page_style', $single_page_style );

if ( ! is_pro_active_and_valid() ) {
	$single_page_style = 'default';
}

?>

<div class="gs-containeer gs-single-container <?php echo 'gs-single-' . esc_attr($single_page_style); ?>">
	
	<div class="gs_coach" id="gs_coach_single">

		<?php
		
		while ( have_posts() ) : the_post();

			if ( ! is_pro_active_and_valid() ) {
				include Template_Loader::locate_template( 'singles/gs-coach-single-default.php' );
				return;
			}

			switch ( $single_page_style ) {

				case 'style-one': {
					include Template_Loader::locate_template( 'singles/gs-coach-single-style-one.php' );
					break;
				}

				case 'style-two': {
					include Template_Loader::locate_template( 'singles/gs-coach-single-style-two.php' );
					break;
				}

				case 'style-three': {
					include Template_Loader::locate_template( 'singles/gs-coach-single-style-three.php' );
					break;
				}

				case 'style-four': {
					include Template_Loader::locate_template( 'singles/gs-coach-single-style-four.php' );
					break;
				}

				case 'style-five': {
					include Template_Loader::locate_template( 'singles/gs-coach-single-style-five.php' );
					break;
				}

				default: {
					include Template_Loader::locate_template( 'singles/gs-coach-single-default.php' );
				}

			}

		endwhile;

		include Template_Loader::locate_template( 'partials/gs-coach-layout-navigation.php' ); ?>

	</div>
    
</div>