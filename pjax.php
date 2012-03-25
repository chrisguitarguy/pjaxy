<?php
/**
 * Finds the PJAX template to load with preference given to child themes
 * 
 * @since 0.1
 * @param string|array $templates Template files to search fo
 * @return string
 */
function pjaxy_find_template( $templates )
{
    $located = '';
    foreach( (array) $templates as $t ) {
        if( ! $t ) {
            continue;
        } elseif( file_exists( STYLESHEETPATH . '/pjax-templates/' . $t ) ) {
            $located = STYLESHEETPATH . '/pjax-templates/' . $t;
            break;
        } elseif( file_exists( TEMPLATEPATH . '/pjax-templates/' . $t ) ) {
            $located = TEMPLATEPATH . '/pjax-templates/' . $t;
            break;
        }
    }
    return $located;
}

/**
 * Is this a PJAX request?  Return true if it is.
 * 
 * @return bool
 */
function is_pjax()
{
    if( isset( $_SERVER['HTTP_X_PJAX'] ) && strtolower( $_SERVER['HTTP_X_PJAX'] ) == 'true' ) {
        return true;
    }
    return false;
}

/**
 * Includes a file with some basic stuff to help render the page. Ala
 * page title, body class, etc.
 */
function get_pjaxy_page_info() 
{
    $info = array(
        'body_class'    => join( ' ', get_body_class( 'pjax-loaded' ) ),
        'page_title'    => wp_title( '', false ),
    );
    if( current_theme_supports( 'custom-header' ) )
    {
        $info['header_img'] = get_header_image();
        $info['header_width'] = defined( 'HEADER_IMAGE_WIDTH' ) ? HEADER_IMAGE_WIDTH : 0;
        $info['header_height'] = defined( 'HEADER_IMAGE_HEIGHT' ) ? HEADER_IMAGE_HEIGHT : 0;
    }
    echo "<script type='text/javascript' id='pjaxy-page-info'>\n";
    echo "var pjaxy_page_info = {\n";
    foreach( apply_filters( 'pjaxy_page_info', $info ) as $key => $val )
    {
        echo "'{$key}': ";
        echo '"' . esc_js( $val ) . '"';
        echo ",\n";
    }
    echo "}\n";
    echo '</script>';
}

add_action( 'wp_enqueue_scripts', 'pjaxy_enqueue_pjax' );
/**
 * Enqueue the PJAX script
 * 
 * @uses wp_enqueue_script
 */
function pjaxy_enqueue_pjax()
{
    $uri = trailingslashit( get_stylesheet_directory_uri() );
    $dir = trailingslashit( basename( dirname( __FILE__ ) ) );
    $uri = $uri . $dir;
    wp_enqueue_script(
        'jquery-pjax', 
        $uri . 'pjax/jquery.pjax.js',
        array( 'jquery' ),
        NULL
    );

    if( ! defined( 'PJAXY_CONTAINER' ) ) return;

    wp_enqueue_script(
        'pjaxy-core',
        $uri . 'js/pjaxy.js',
        array( 'jquery-pjax' ),
        NULL
    );

    wp_localize_script(
        'pjaxy-core',
        'pjaxy_core',
        array(
            'container' => PJAXY_CONTAINER,
            'header'    => defined( 'PJAXY_HEADER_IMG' ) ? PJAXY_HEADER_IMG : false
        )
    );

}

add_filter( 'home_template', 'pjaxy_index_template' );
function pjaxy_home_template( $template )
{
    if( is_pjax() && $t = pjaxy_find_template( array( 'home.php', 'index.php' ) ) ) {
        $template = $t;
    }
    return $template;
}

add_filter( 'front_page_template', 'pjaxy_front_template' );
function pjaxy_front_template( $template )
{
    if( is_pjax() && $t = pjaxy_find_template( 'front-page.php' ) ) {
        $template = $t;
    }
    return $template;
}

add_filter( 'index_template', 'pjaxy_index_template' );
function pjaxy_index_template( $template )
{
    if( is_pjax() && $t = pjaxy_find_template( array( 'index.php' ) ) ) {
        $template = $t;
    }
    return $template;
}

add_filter( 'archive_template', 'pjaxy_archive_template' );
function pjaxy_archive_template( $template )
{
    if( is_pjax() ) {
        $post_type = get_query_var( 'post_type' );
        $temps = array();

        if( $post_type ) $temps[] = "archive-{$post_type}.php";
        $temps[] = 'archive.php';
        if( $t = pjaxy_find_template( $temps ) ) {
            $template = $t;
        }
    }
    return $template;
}

add_filter( 'author_template', 'pjaxy_author_template' );
function pjaxy_author_template( $template )
{
    if( is_pjax() ) {
        $author = get_queried_object();
        $temps = array( "author-{$author->user_nicename}.php" );
        $temps[] = "author-{$author->ID}.php";
        $temps[] = 'author.php';
        if( $t = pjaxy_find_template( $temps ) ) {
            $template = $t;
        }
    }
    return $template;
}

add_filter( 'category_template', 'pjaxy_taxonomy_template' );
add_filter( 'tag_template', 'pjaxy_taxonomy_template' );
add_filter( 'taxonomy_template', 'pjaxy_taxonomy_template' );
function pjaxy_taxonomy_template( $template )
{
    if( is_pjax() ) {
        $term = get_queried_object();
        $temps = array( "taxnomy-{$term->taxonomy}-{$term->slug}.php" );
        $temps[] = "taxonomy-{$term->taxonomy}.php";
        $temps[] = 'taxonomy.php';
        if( $t = pjaxy_find_template( $temps ) ) {
            $template = $t;
        }
    }
    return $template;
}

add_filter( 'date_template', 'pjaxy_date_template' );
function pjaxy_date_template( $template )
{
    if( is_pjax() && $t = pjaxy_find_template( array( 'date.php' ) ) ) {
        $template = $t;
    }
    return $template;
}

add_filter( 'page_template', 'pjaxy_page_template' );
function pjaxy_page_template( $template )
{
    if( is_pjax() ) {
        $page = get_queried_object();
        $temps = array();
        if( $custom = get_post_meta( $page->ID, '_wp_page_template', true ) ) {
            if( 'default' != $custom ) $temps[] = $custom;
        }
        if( $page->post_name ) {
            $temps[] = "page-{$page->post_name}.php";
        }
        $temps[] = "page-{$page->ID}.php";
        $temps[] = 'page.php';
        if( $t = pjaxy_find_template( $temps ) ) {
            $template = $t;
        }
    }
    return $template;
}

add_filter( 'single_template', 'pjaxy_singular_template' );
function pjaxy_singular_template( $template )
{
    if( is_pjax() ) {
        $post = get_queried_object();
        $temps = array( "single-{$post->post_type}.php" );
        $temps[] = 'single.php';
        if( $t = pjaxy_find_template( $temps ) ) {
            $template = $t;
        }
    }
    return $template;
}

add_filter( 'attachment_template', 'pjaxy_attachment_template' );
function pjaxy_attachment_template( $template )
{
    if( is_pjax() ) {
        global $posts;
        $type = explode( '/', $posts[0]->post_mime_type );
        $temps = array( "{$type[0]}.php", "{$type[1]}.php", "{$type[0]}-{$type[1]}.php", 'attachment.php' );
        if( $t = pjaxy_find_template( $temps ) ) {
            $template = $t;
        }
    }
    return $template;
}

add_filter( 'paged_template', 'pjaxy_paged_template' );
function pjaxy_paged_template( $template )
{
    if( is_pjax() && $t = pjaxy_find_template( array( 'paged.php' ) ) ) {
        $template = $t;
    }
    return $template;
}
