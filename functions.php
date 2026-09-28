<?php
/**
 * If this file is called directly, abort.
 *
 * @package ThermalRight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Thermalright theme constants.
define( 'THERMALRIGHT_INC_PATH', __DIR__ . '/inc' );
define( 'THERMALRIGHT_ROOT_DIR', get_stylesheet_directory() );


if ( ! function_exists( 'thermalright_enqueue_scripts' ) ) {
	/**
	 * Function that enqueue theme's child style - thermalright style
	 */
	function thermalright_enqueue_scripts() {
		$theme_version = function_exists( 'wp_get_theme' ) ? wp_get_theme()->get( 'Version' ) : false;
		$main_style    = 'qi-style';

		// Enqueue CSS.
		wp_enqueue_style( 'thermalright-style', get_stylesheet_directory_uri() . '/assets/front.css', array( $main_style ), $theme_version );

		// Enqueue JS.
		wp_enqueue_script(
			'thermalright-script',
			get_stylesheet_directory_uri() . '/assets/front.js',
			array(),
			$theme_version,
			true
		);
	}

	add_action( 'wp_enqueue_scripts', 'thermalright_enqueue_scripts' );
}

if ( ! function_exists( 'thermalright_include_modules' ) ) {
	/**
	 * Include child theme modules.
	 */
	function thermalright_include_modules() {

		foreach ( glob( THERMALRIGHT_ROOT_DIR . '/inc/*/include.php' ) as $module ) {
			include_once $module;
		}
	}

	// Call the function for file inclusion.
	thermalright_include_modules();
}
