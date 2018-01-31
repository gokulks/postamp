<?php
/**
 * Plugin Name: POSTAMP
 * Description: Adding  AMP support to your WordPress Posts, 
 * Plugin URI: https://github.com/
 * Author: Gokulakrishnan KS
 * Author URI: https://about.me/sivasamygokul
 * Version: 0.0.1
 * Text Domain: POSTAMP
 * License: GPLv2 or later
 */

define( 'POSTAMP_FILE', __FILE__ );
define( 'POSTAMP_DIR', dirname( __FILE__ ) );
define( 'POSTAMP_VERSION', '0.0.1' );

register_activation_hook( __FILE__, 'pa_activate' );
function postamp_activate() {
	if ( ! did_action( 'postamp_init' ) ) {
		postamp_init();
	}
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'postamp_deactivate' );
function postamp_deactivate() {
	// We need to manually remove the amp endpoint
	global $wp_rewrite;
	foreach ( $wp_rewrite->endpoints as $index => $endpoint ) {
		if ( POSTAMP_QUERY_VAR === $endpoint[1] ) {
			unset( $wp_rewrite->endpoints[ $index ] );
			break;
		}
	}

	flush_rewrite_rules();
}


// Call on startup
add_action( 'init', 'postamp_init',10 );

function postamp_init() {
	
	if ( false === apply_filters( 'postamp_is_enabled', true ) ) {
		return;
	}

	// Defining URL for post support
	define( 'POSTAMP_QUERY_VAR', apply_filters( 'postamp_query_var', 'postamp' ) );
	do_action( 'postamp_init' );

	// Adding AMP Support for Posts
	// https://codex.wordpress.org/Rewrite_API/add_rewrite_endpoint
	// Refer above URL for rewrite permissions and other related specs

	add_rewrite_endpoint( POSTAMP_QUERY_VAR, EP_PERMALINK );
	add_post_type_support( 'post', POSTAMP_QUERY_VAR );

}



//Adding Conanical URL on main page

add_action( 'wp_head', 'postamp_add_amp_meta' );

function postamp_add_amp_meta() {
	if ( false === apply_filters( 'postamp_add_amp_meta', true ) ) {
		return;
	}

	$structure = get_option( 'permalink_structure' );
	if ( empty( $structure ) ) {
		$amp_url = add_query_arg( POSTAMP_QUERY_VAR, 1, get_permalink( $post_id ) );
	} else {
		$amp_url = trailingslashit( get_permalink( $post_id ) ) . user_trailingslashit( POSTAMP_QUERY_VAR, 'single_amp' );
	}
	
	
	printf( '<link rel="amphtml" href="%s" />', esc_url( $amp_url ) );
}

// Initializing the plugin process
add_action( 'wp', 'postamp_all_actions' );

function postamp_all_actions() {

	if ( ! is_singular() || is_feed() ) {
		return;
	}

	global $wp_query;
	$post = $wp_query->post;

//Check is the URL related with AMP
	if(false !== get_query_var( POSTAMP_QUERY_VAR, false ))
	{
		$file = "templates\post.amp.php";
		include( $file ); 
		exit();
	}
	
	
}



