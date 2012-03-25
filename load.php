<?php
add_action( 'after_setup_theme', 'pjaxy_maybe_load', 11 );
/**
 * Loads the pjax core files if the current theme supports PJAX.
 *
 * @uses require_if_theme_supports
 */
function pjaxy_maybe_load()
{
    require_if_theme_supports( 
        'pjax', 
        trailingslashit( dirname( __FILE__ ) ) . 'pjax.php'
    );
}
