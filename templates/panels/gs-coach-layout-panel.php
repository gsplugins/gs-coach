<?php
namespace GSCOACH;
if ( ! $_panel_enabled ) return;

if ( $gs_coach_loop->have_posts() ) : ?>

    <!-- Panel -->
    <div class="gs-coach-panel-container <?php echo 'gs-coach-panel--' . esc_attr($panel_style); ?>">

        <?php
        
        plugin()->hooks->load_acf_fields( $show_acf_fields, $acf_fields_position );

        while ( $gs_coach_loop->have_posts() ): $gs_coach_loop->the_post();

            if ( ! is_pro_active_and_valid() ) {
                include Template_Loader::locate_template( 'panels/gs-coach-panel-default.php' );
                return;
            }

            switch ( $panel_style ) {
                case 'style-one': {
                    include Template_Loader::locate_template( 'panels/gs-coach-panel-style-one.php' );
                    break;
                }
                case 'style-two': {
                    include Template_Loader::locate_template( 'panels/gs-coach-panel-style-two.php' );
                    break;
                }
                case 'style-three': {
                    include Template_Loader::locate_template( 'panels/gs-coach-panel-style-three.php' );
                    break;
                }
                case 'style-four': {
                    include Template_Loader::locate_template( 'panels/gs-coach-panel-style-four.php' );
                    break;
                }
                case 'style-five': {
                    include Template_Loader::locate_template( 'panels/gs-coach-panel-style-five.php' );
                    break;
                }
                default: {
                    include Template_Loader::locate_template( 'panels/gs-coach-panel-default.php' );
                }
            }
        
        endwhile; ?>

        <div id="gscoach-overlay"></div>

    </div>
    
<?php endif; ?>