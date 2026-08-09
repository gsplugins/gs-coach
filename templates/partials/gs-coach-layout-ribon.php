<?php

namespace GSCOACH;

if ( $display_ribbon !== 'on' ) return;

$ribbon = get_post_meta( get_the_id(), '_gscoach_ribbon', true );
$gs_coach_ribbon_style = empty( $gs_coach_ribbon_style ) ? 'default' : $gs_coach_ribbon_style;

if ( !empty($ribbon) ): ?>

    <div class="gs_coach_ribbon gs-coach-ribbon-<?php echo esc_attr( $gs_coach_ribbon_style ); ?>"><?php echo esc_html( $ribbon ); ?></div>

    <?php do_action( 'gs_coach_after_coach_ribbon' ); ?>
<?php endif; ?>