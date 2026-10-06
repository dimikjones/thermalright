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

if ( ! function_exists( 'thermalright_woo_breadcrumbs_title' ) ) {
	/**
	 * Function that overrides WooCommerce breadcrumbs to prepend the shop page link
	 *
	 * @param string $wrap_child - breadcrumbs content.
	 * @param array  $settings   - breadcrumbs settings.
	 *
	 * @return string
	 */
	function thermalright_woo_breadcrumbs_title( $wrap_child, $settings ) {

		if ( ! function_exists( 'qi_is_woo_page' ) || ! qi_is_woo_page( 'any' ) ) {
			return $wrap_child;
		}

		$shop_id    = qi_woo_get_main_shop_page_id();
		$shop_title = ! empty( $shop_id ) ? get_the_title( $shop_id ) : esc_html__( 'Shop', 'thermalright' );
		$shop_link  = ! empty( $shop_id ) ? sprintf( $settings['link'], get_permalink( $shop_id ), $shop_title ) : '';

		$wrap_child = '';

		if ( qi_is_woo_page( 'shop' ) ) {
			$wrap_child .= sprintf( $settings['current_item'], $shop_title );

		} elseif ( qi_is_woo_page( 'category' ) || qi_is_woo_page( 'tag' ) ) {
			$taxonomy_slug = qi_is_woo_page( 'tag' ) ? 'product_tag' : 'product_cat';
			$taxonomy      = get_term( get_queried_object_id(), $taxonomy_slug );

			if ( ! empty( $shop_link ) ) {
				$wrap_child .= $shop_link . $settings['separator'];
			}

			if ( isset( $taxonomy->parent ) && 0 !== $taxonomy->parent ) {
				$parent      = get_term( $taxonomy->parent );
				$wrap_child .= sprintf( $settings['link'], get_term_link( $parent->term_id ), $parent->name ) . $settings['separator'];
			}

			if ( ! empty( $taxonomy ) ) {
				$wrap_child .= sprintf( $settings['current_item'], esc_attr( $taxonomy->name ) );
			}

		} elseif ( qi_is_woo_page( 'single' ) ) {
			$post_terms = wp_get_post_terms( get_the_ID(), 'product_cat' );

			if ( ! empty( $shop_link ) ) {
				$wrap_child .= $shop_link . $settings['separator'];
			}

			if ( ! empty( $post_terms ) ) {
				$post_term = $post_terms[0];

				if ( isset( $post_term->parent ) && 0 !== $post_term->parent ) {
					$parent      = get_term( $post_term->parent );
					$wrap_child .= sprintf( $settings['link'], get_term_link( $parent->term_id ), $parent->name ) . $settings['separator'];
				}

				$wrap_child .= sprintf( $settings['link'], get_term_link( $post_term ), $post_term->name ) . $settings['separator'];
			}

			$wrap_child .= sprintf( $settings['current_item'], get_the_title() );
		}

		return $wrap_child;
	}

	add_filter( 'qode_essential_addons_filter_breadcrumbs_content', 'thermalright_woo_breadcrumbs_title', 20, 2 );
}
