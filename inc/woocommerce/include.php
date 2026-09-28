<?php
/**
 * If this file is called directly, abort.
 *
 * @package ThermalRight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'thermalright_product_single_additional_info_content' ) ) {
	/**
	 * Function that adds additional information content after product summary
	 */
	function thermalright_product_single_additional_info_content() {

		if ( qi_is_installed( 'woocommerce' ) ) {

			echo woocommerce_product_additional_information_tab(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	add_action( 'woocommerce_product_meta_end', 'thermalright_product_single_additional_info_content', 20 );
}

if ( ! function_exists( 'thermalright_woocommerce_product_attributes_add_line_break' ) ) {
	/**
	 * Function that change additional information attribute , to <br> tag
	 *
	 * @param string               $attribute_list - The attribute list markup.
	 * @param WC_Product_Attribute $attribute     - The product attribute object.
	 * @param array                $values         - The product attribute values.
	 */
	function thermalright_woocommerce_product_attributes_add_line_break( $attribute_list, $attribute, $values ) {
		// Only on product page.
		if ( ! is_product() ) {
			return $attribute_list;
		}
		$attribute_list = wpautop( wptexturize( implode( '<br>', $values ) ) );
		return $attribute_list;
	}

	add_filter( 'woocommerce_attribute', 'thermalright_woocommerce_product_attributes_add_line_break', 10, 3 );
}

if ( ! function_exists( 'thermalright_qi_addons_for_elementor_product_list_info_below_sku' ) ) {
	/**
	 * Function that adds SKU for qi-addons-for-elementor Product List Info Below Variation
	 */
	function thermalright_qi_addons_for_elementor_product_list_info_below_sku() {

		if ( class_exists( 'QiAddonsForElementor_Product_List_Shortcode' ) ) {
			add_action( 'qi_addons_for_elementor_action_product_list_item_additional_content', 'thermalright_product_list_item_sku' );
		}
	}

	add_action( 'init', 'thermalright_qi_addons_for_elementor_product_list_info_below_sku' );
}

if ( ! function_exists( 'thermalright_product_list_item_sku' ) ) {
	/**
	 * Function that renders the product SKU below the product info.
	 */
	function thermalright_product_list_item_sku() {
		global $product;

		if ( ! ( $product instanceof WC_Product ) ) {
			return;
		}

		$sku = $product->get_sku();

		if ( ! empty( $sku ) ) {
			echo '<span class="qodef-info-below-sku">' . esc_html( $sku ) . '</span>';
		}
	}
}

if ( ! function_exists( 'thermalright_qi_addons_for_elementor_product_list_info_below_excerpt' ) ) {
	/**
	 * Function that adds excerpt for qi-addons-for-elementor Product List Info Below Variation
	 */
	function thermalright_qi_addons_for_elementor_product_list_info_below_excerpt() {

		if ( class_exists( 'QiAddonsForElementor_Product_List_Shortcode' ) ) {
			add_action( 'qi_addons_for_elementor_action_product_list_item_additional_content', 'thermalright_product_list_item_excerpt' );
		}
	}

	add_action( 'init', 'thermalright_qi_addons_for_elementor_product_list_info_below_excerpt' );
}

if ( ! function_exists( 'thermalright_product_list_item_excerpt' ) ) {
	/**
	 * Function that renders the product short description below the product info.
	 */
	function thermalright_product_list_item_excerpt() {
		global $product;

		if ( ! ( $product instanceof WC_Product ) ) {
			return;
		}

		$excerpt = $product->get_short_description();

		if ( ! empty( $excerpt ) ) {
			echo '<span class="qodef-info-below-excerpt">' . wp_kses_post( $excerpt ) . '</span>';
		}
	}
}
