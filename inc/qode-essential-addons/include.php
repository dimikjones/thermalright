<?php
/**
 * If this file is called directly, abort.
 *
 * @package ThermalRight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'thermalright_breadcrumbs_home_label' ) ) {
	/**
	 * Function that overrides the breadcrumbs home label with the static front page title
	 *
	 * @param array $labels - breadcrumbs labels.
	 *
	 * @return array
	 */
	function thermalright_breadcrumbs_home_label( $labels ) {

		if ( 'page' === get_option( 'show_on_front' ) ) {
			$front_page_id = get_option( 'page_on_front' );

			if ( ! empty( $front_page_id ) ) {
				$labels['home'] = get_the_title( $front_page_id );
			}
		}

		return $labels;
	}

	add_filter( 'qode_essential_addons_filter_breadcrumbs_label', 'thermalright_breadcrumbs_home_label' );
}
