<?php
/**
 * If this file is called directly, abort.
 *
 * @package ThermalRight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'thermalright_disable_block_editor_styles_frontend' ) ) {
	/**
	 * Disable block editor styles on the frontend.
	 */
	function thermalright_disable_block_editor_styles_frontend() {
		// Check if we are NOT in the WordPress admin panel or editor.
		if ( ! is_admin() ) {
			// Dequeue block editor styles.
			// Removes core block styles.
			wp_dequeue_style( 'wp-block-library' );
			// Removes theme block styles.
			wp_dequeue_style( 'wp-block-library-theme' );
		}
	}

	add_action( 'wp_enqueue_scripts', 'thermalright_disable_block_editor_styles_frontend', 100 );
}

if ( ! function_exists( 'thermalright_disable_heartbeat_frontend' ) ) {
	/**
	 * Disable the WordPress Heartbeat API on the frontend but keep it active in the admin and post editor.
	 */
	function thermalright_disable_heartbeat_frontend() {
		// Check if we are on the frontend and NOT in the admin or post editor.
		if ( ! is_admin() ) {
			// Removes Heartbeat API script from loading.
			wp_deregister_script( 'heartbeat' );
		}
	}

	add_action( 'wp_enqueue_scripts', 'thermalright_disable_heartbeat_frontend', 100 );
}

if ( ! function_exists( 'thermalright_control_heartbeat_settings' ) ) {
	/**
	 * Control the Heartbeat API execution based on user area.
	 *
	 * @param array $settings Heartbeat settings.
	 * @return array Modified heartbeat settings.
	 */
	function thermalright_control_heartbeat_settings( $settings ) {
		// If we are on the frontend, reduce the heartbeat frequency.
		if ( ! is_admin() ) {
			// Set Heartbeat interval to 60 seconds (default is usually 15-60 sec).
			$settings['interval'] = 60;
		}

		return $settings;
	}

	add_filter( 'heartbeat_settings', 'thermalright_control_heartbeat_settings' );
}

if ( ! function_exists( 'thermalright_deregister_dashicons_non_admin' ) ) {
	/**
	 * Deregister Dashicons stylesheet on the frontend for non-admin users.
	 *
	 * This function checks if the current request is on the frontend and if the
	 * current user does not have the 'manage_options' capability (typically admins).
	 * If both conditions are met, it deregisters the 'dashicons' stylesheet.
	 */
	function thermalright_deregister_dashicons_non_admin() {
		// Check if we are on the frontend.
		// is_admin() returns true if in the admin area, false otherwise.
		if ( ! is_admin() ) {
			// Check if the current user does NOT have 'manage_options' capability.
			// Users with 'manage_options' are typically administrators.
			if ( ! current_user_can( 'manage_options' ) ) {
				// Deregister the 'dashicons' stylesheet.
				// This prevents it from being enqueued on the frontend for non-admin users.
				wp_deregister_style( 'dashicons' );
			}
		}
	}

	add_action( 'wp_enqueue_scripts', 'thermalright_deregister_dashicons_non_admin' );
}

if ( ! function_exists( 'thermalright_remove_version_query_strings' ) ) {
	/**
	 * Removes version query strings (?ver=X.X) from static CSS and JavaScript file URLs.
	 *
	 * This function hooks into the 'script_loader_src' and 'style_loader_src' filters
	 * to modify the URL of enqueued scripts and stylesheets. By removing the 'ver'
	 * query parameter, it aims to improve cacheability.
	 *
	 * @param string $src The original URL of the script or stylesheet.
	 * @return string The modified URL with the 'ver' query string removed.
	 */
	function thermalright_remove_version_query_strings( $src ) {
		// Check if the 'ver' query argument exists in the URL.
		// If it does, remove it using remove_query_arg().
		if ( strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	// Hook the function to the 'script_loader_src' filter for JavaScript files.
	add_filter( 'script_loader_src', 'thermalright_remove_version_query_strings', 15 );

	// Hook the function to the 'style_loader_src' filter for CSS files.
	add_filter( 'style_loader_src', 'thermalright_remove_version_query_strings', 15 );
}

if ( ! function_exists( 'thermalright_add_cache_control_headers' ) ) {
	/**
	 * Adds Cache-Control, Expires, and Pragma headers for non-logged-in visitors
	 * on frontend GET requests to improve cacheability.
	 *
	 * This function is hooked into 'template_redirect', which fires before
	 * WordPress determines which template to load, allowing us to set headers
	 * based on user status and request method.
	 *
	 * @since 1.0.0
	 */
	function thermalright_add_cache_control_headers() {
		// 1. Apply only for non-logged-in users.
		// is_user_logged_in() checks if the current user is logged in.
		if ( is_user_logged_in() ) {
			// Do not apply caching headers for logged-in users.
			return;
		}

		// 2. Apply only for GET requests.
		// POST requests and other methods often involve dynamic data submission
		// and should generally not be cached.
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		// 3. Apply only for public pages (frontend) and avoid specific non-cacheable pages.
		// is_admin() checks if currently in the admin area.
		// is_preview() checks if currently viewing a post/page preview.
		// is_feed() checks if currently viewing an RSS/Atom feed.
		// is_404() checks if currently viewing a 404 error page.
		if ( is_admin() || is_preview() || is_feed() || is_404() ) {
			return;
		}

		// 4. Respect existing 'DONOTCACHEPAGE' or 'DONOTCACHEOBJECT' constants.
		// Many caching plugins define these constants to prevent caching for specific pages.
		if ( ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) || ( defined( 'DONOTCACHEOBJECT' ) && DONOTCACHEOBJECT ) ) {
			return;
		}

		// If all checks pass, set the caching headers.

		// Cache-Control Header:
		// - 'public': Indicates that the response can be cached by any cache (browser, CDN, proxy).
		// - 'max-age=3600': Tells the browser/cache that the resource is fresh for 3600 seconds (1 hour).
		// After this time, the cached copy is considered stale.
		// - 'must-revalidate': Instructs the cache to revalidate with the origin server
		// before serving a stale copy. This ensures users get fresh content after max-age expires.
		header( 'Cache-Control: public, max-age=3600, must-revalidate' );

		// Expires Header:
		// - This is an older HTTP/1.0 header, largely superseded by Cache-Control.
		// - It specifies a date/time after which the response is considered stale.
		// - Included for backward compatibility with older caching mechanisms.
		// - Set to 1 hour from now, aligning with max-age.
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 3600 ) . ' GMT' );

		// Pragma Header:
		// - This is another HTTP/1.0 header, primarily used for backward compatibility.
		// - 'public' explicitly allows caching, reinforcing the Cache-Control: public intent.
		// - 'no-cache' is more commonly used to prevent caching, so 'public' is used here.
		header( 'Pragma: public' );
	}

	add_action( 'template_redirect', 'thermalright_add_cache_control_headers' );
}

if ( ! function_exists( 'thermalright_disable_emojis' ) ) {
	/**
	 * Disable all WordPress emoji scripts and styles.
	 */
	function thermalright_disable_emojis() {
		// Remove emoji script from frontend and admin.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

		// Remove emoji styles from frontend and admin.
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );

		// Prevent emojis from being injected in the RSS feed.
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Remove TinyMCE emoji support (editor compatibility).
		add_filter( 'tiny_mce_plugins', 'thermalright_disable_emojis_tinymce' );
		add_filter( 'wp_resource_hints', 'thermalright_disable_emojis_remove_dns_prefetch', 10, 2 );
	}

	add_action( 'init', 'thermalright_disable_emojis' );
}

if ( ! function_exists( 'thermalright_disable_emojis_tinymce' ) ) {
	/**
	 * Remove TinyMCE emoji plugin support in the WordPress editor.
	 *
	 * @param array $plugins List of TinyMCE plugins.
	 * @return array Modified list of TinyMCE plugins.
	 */
	function thermalright_disable_emojis_tinymce( $plugins ) {
		// Bail if the plugins is not an array.
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		// Remove the `wpemoji` plugin and return everything else.
		return array_diff( $plugins, array( 'wpemoji' ) );
	}
}

if ( ! function_exists( 'thermalright_disable_emojis_remove_dns_prefetch' ) ) {
	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param  array  $urls          URLs to print for resource hints.
	 * @param  string $relation_type The relation type the URLs are printed for.
	 * @return array                 Difference betwen the two arrays.
	 */
	function thermalright_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			/** This filter is documented in wp-includes/formatting.php */
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

			$urls = array_diff( $urls, array( $emoji_svg_url ) );
		}

		return $urls;
	}
}

if ( ! function_exists( 'thermalright_disable_wp_oembed' ) ) {
	/**
	 * Remove all oEmbed-related scripts and discovery links from WordPress.
	 */
	function thermalright_disable_wp_oembed() {
		// Remove oEmbed discovery links from <head> (prevents unnecessary requests).
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Remove oEmbed-specific JavaScript that loads on the frontend.
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		// Disable REST API oEmbed endpoints (prevents external sites from embedding WP content).
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );

		// Remove oEmbed filtering from content processing (stops WP auto-converting URLs).
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
		remove_filter( 'oembed_response_data', 'get_oembed_response_data', 10 );

		// Disable automatic oEmbed URL conversion for posts/comments.
		remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
		remove_filter( 'widget_text_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
	}

	add_action( 'init', 'thermalright_disable_wp_oembed' );
}

if ( ! function_exists( 'thermalright_disable_self_pingbacks' ) ) {
	/**
	 * Disable self-pingbacks in WordPress to prevent unnecessary notifications.
	 *
	 * Pingbacks allow automatic notifications when linking to a post on another site.
	 * However, self-pingbacks occur when a site links to its own posts, cluttering comments.
	 * This function removes links from the ping process if they belong to the same domain.
	 *
	 * @param array $links List of ping URLs.
	 */
	function thermalright_disable_self_pingbacks( &$links ) {
		// Get the site's base URL.
		$home_url = home_url();

		foreach ( $links as $key => $link ) {
			// Check if the link belongs to this site.
			if ( strpos( $link, $home_url ) === 0 ) {
				// Remove self-pingback link.
				unset( $links[ $key ] );
			}
		}
	}

	add_action( 'pre_ping', 'thermalright_disable_self_pingbacks' );
}

if ( ! function_exists( 'thermalright_modify_speculation_rules' ) ) {
	/**
	 * Modify wp_speculation_rules_configuration to use 'prerender' mode with 'moderate' eagerness.
	 *
	 * Prerendering helps improve perceived performance by preloading the next likely page.
	 *
	 * @param array $config Speculation rules configuration.
	 * @return array Modified speculation rules configuration.
	 */
	function thermalright_modify_speculation_rules( $config ) {
		if ( is_array( $config ) ) {
			$config['mode']      = 'prerender';
			$config['eagerness'] = 'moderate';
		}

		return $config;
	}

	add_filter( 'wp_speculation_rules_configuration', 'thermalright_modify_speculation_rules' );
}

if ( ! function_exists( 'thermalright_limit_post_revisions' ) ) {
	/**
	 * Limit WordPress post revisions to 5.
	 *
	 * This reduces unnecessary database storage while retaining useful revisions for editing.
	 */
	function thermalright_limit_post_revisions() {
		// Set the max number of revisions.
		return 5;
	}

	add_filter( 'wp_revisions_to_keep', 'thermalright_limit_post_revisions' );
}

if ( ! function_exists( 'thermalright_disable_capital_p_dangit' ) ) {
	/**
	 * Disable the `capital_P_dangit` function in WordPress.
	 *
	 * This function is normally applied to content, titles, comments, and feeds.
	 * Removing it prevents WordPress from auto-correcting "WordPress" to "WordPress."
	 */
	function thermalright_disable_capital_p_dangit() {
		remove_filter( 'the_content', 'capital_P_dangit', 11 );
		remove_filter( 'the_title', 'capital_P_dangit', 11 );
		remove_filter( 'wp_title', 'capital_P_dangit', 11 );
		remove_filter( 'document_title', 'capital_P_dangit', 11 );
		remove_filter( 'widget_text_content', 'capital_P_dangit', 11 );
		remove_filter( 'comment_text', 'capital_P_dangit', 31 );
	}

	add_action( 'init', 'thermalright_disable_capital_p_dangit' );
}

if ( ! function_exists( 'thermalright_offload_multiple_stylesheets' ) ) {
	/**
	 * Adds the rel='preload' to stylesheets and onload replace it with rel='stylesheet' in order to offload them.
	 *
	 * @param string $html The complete HTML <link> tag.
	 * @param string $handle The stylesheet's registered handle.
	 *
	 * @return string The modified HTML <link> tag with rel='preload' attribute, or original HTML.
	 */
	function thermalright_offload_multiple_stylesheets( $html, $handle ) {
		// Define an array of stylesheet handles that you want to offload.
		$offload_handles = array(
			'qode-essential-addons-style',
			'qode-essential-addons-theme-style',
			'qi-grid',
			'qi-main',
			'qi-style',
			'swiper',
			'elementor-icons',
			'elementor-frontend',
			'v4-shims',
			'qi-addons-for-elementor-helper-parts-style',
			'qi-addons-for-elementor-grid-style',
			'qi-addons-for-elementor-style',
			'brands-styles',
		);

		// Check if the current stylesheet's handle is in our list of offload handles.
		if ( in_array( $handle, $offload_handles, true ) ) {
			// If it is, preload styles
			// return str_replace( "media='stylesheet'", "rel='preload' as='style'" . "onload=\"this.rel='stylesheet'\"", $html ).
			return str_replace( "rel='stylesheet'", 'rel="preload" as="style" onload="this.rel=&#39;stylesheet&#39;"', $html );
		}

		// If the handle is not in our list, return the original HTML.
		return $html;
	}

	add_filter( 'style_loader_tag', 'thermalright_offload_multiple_stylesheets', 10, 2 );
}
