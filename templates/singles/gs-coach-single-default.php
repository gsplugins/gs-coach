<?php
namespace GSCOACH;
/**
 * GS Coach - Single Template 
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-coach/gs-coach-layout-single.php
 * 
 * @package GS_Coach/Templates
 * @version 1.0.2
 */

$designation = get_post_meta( get_the_id(), '_gscoach_profession', true );

?>


<div class="gs-coach-breadcumb">
    <?php $is_breadcumb_enabled = getoption('enable_breadcumb', 'off'); ?>
    <?php include Template_Loader::locate_template( 'partials/gs-coach-layout-breadcumb.php' ); ?>
    <?php do_action( 'gs_coach_after_breadcumb' ); ?>
</div>

<div class="gs-coach-single-content" itemscope="" itemtype="http://schema.org/Person">

    <div class="gscs-content-top">

        <div class="gscs-content-top-left">

            <div class="gs_coach_img">
                
                <div class="gs_ribon_wrapper">
                    
                    <!-- Coach Image -->
                    <?php coach_thumbnail( 'full', true ); ?>
                    <?php do_action( 'gs_coach_after_coach_thumbnail' ); ?>

                    <!-- Ribbon -->
                    <?php include Template_Loader::locate_template( 'partials/gs-coach-layout-ribon.php' ); ?>
                    
                </div>

            </div>

        </div>

        <div class="gscs-content-top-right">

            <!-- coach Name -->
            <h1 class="gs-sin-mem-name" itemprop="name"><?php the_title(); ?></h1>
            <?php do_action( 'gs_coach_after_coach_name' ); ?>

            <!-- coach Designation -->
            <div class="gs-sin-mem-desig" itemprop="jobtitle"><?php echo esc_html( $designation ); ?></div>
            <?php do_action( 'gs_coach_after_coach_designation' ); ?>

            <!-- Meta Details -->
            <?php include Template_Loader::locate_template( 'partials/gs-coach-layout-meta-details.php' ); ?>


            <div class="gs-tm-sicons">

                <!-- Social Links -->
                <?php $gs_coach_connect = 'on'; ?>
                <?php include Template_Loader::locate_template( 'partials/gs-coach-layout-social-links.php' ); ?>
                
            </div>


        </div>

    </div>


    <div class="gs_coach_details">

        <!-- Description -->
        <div class="gs-coach-desc" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
        <?php do_action( 'gs_coach_after_coach_details' ); ?>
        
        <!-- Skills -->
        <?php include Template_Loader::locate_template( 'partials/gs-coach-layout-skills.php' ); ?>
        
    </div>

    <div class="gs_coach_certificates">
        <!-- Certificates -->
         <?php $is_certificates_enabled = 'on'; ?>
        <?php include Template_Loader::locate_template( 'partials/gs-coach-layout-certificates.php' ); ?>
    </div>

    <div class="gs_coach_cv">
        <!-- Certificates -->
        <?php $is_cv_enabled = 'on'; ?>
        <?php include Template_Loader::locate_template( 'partials/gs-coach-layout-cv.php' ); ?>
    </div>

</div>