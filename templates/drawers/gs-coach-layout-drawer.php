<?php
namespace GSCOACH;

if ( ! $_drawer_enabled ) return;

if ( $gs_coach_loop->have_posts() ) : while ( $gs_coach_loop->have_posts() ): $gs_coach_loop->the_post();

    ?>
    
    <div id="gs-coach-drawer-<?php echo get_the_id(); ?>-<?php echo $id; ?>" class="gridder-content">

    <?php

    if ( ! is_pro_active_and_valid() ) {
        include Template_Loader::locate_template( 'drawers/gs-coach-drawer-default.php' );
        return;
    }
    
    switch ( $drawer_style ) {
        
        case 'style-one': {
            include Template_Loader::locate_template( 'drawers/gs-coach-drawer-style-one.php' );
            break;
        }
        case 'style-two': {
            include Template_Loader::locate_template( 'drawers/gs-coach-drawer-style-two.php' );
            break;
        }
        case 'style-three': {
            include Template_Loader::locate_template( 'drawers/gs-coach-drawer-style-three.php' );
            break;
        }
        case 'style-four': {
            include Template_Loader::locate_template( 'drawers/gs-coach-drawer-style-four.php' );
            break;
        }
        case 'style-five': {
            include Template_Loader::locate_template( 'drawers/gs-coach-drawer-style-five.php' );
            break;
        }
        default: {
            include Template_Loader::locate_template( 'drawers/gs-coach-drawer-default.php' );
        }
    }

    ?>

    </div>

    <?php

endwhile; endif; ?>