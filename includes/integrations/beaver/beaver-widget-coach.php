<?php
namespace GSCOACH;
use FLBuilderModule;

class Beaver extends FLBuilderModule {

    public function __construct() {
        
        parent::__construct(array(
            'name'            => __( 'Coach', 'gscoach' ),
            'description'     => __( 'Coaches', 'gscoach' ),
            'group'           => __( 'GS Plugins', 'gscoach' ),
            'category'        => __( 'Basic', 'gscoach' ),
            'dir'             => GSCOACH_PLUGIN_DIR . 'includes/integrations/beaver/',
            'url'             => GSCOACH_PLUGIN_URI . '/includes/integrations/beaver/',
            'icon'            => 'icon.svg',
            'editor_export'   => true, // Defaults to true and can be omitted.
            'enabled'         => true, // Defaults to true and can be omitted.
            'partial_refresh' => false, // Defaults to false and can be omitted.
        ));
        
    }

    public function get_icon( $icon = '' ) {

        $path = GSCOACH_PLUGIN_DIR . 'assets/img/' . $icon;

        // check if $icon is referencing an included icon.
        if ( '' != $icon && file_exists( $path ) ) {
            $icon = file_get_contents( $path );
            return str_replace(
                ['width="32"', 'height="32"', 'width="21"', 'height="20"'],
                ['width="20"', 'height="20"', 'width="20"', 'height="20"'],
                $icon
            );
        }

        return '';
    }
}
