<?php namespace WooCommerce\Compatible_Products;

/**
 * Class Helpers
 *
 * @since 1.0
 *
 * @package WooCommerce\Compatible_Products
 */
final class Helpers
{
	/**
	 * Text Domain
	 *
	 * @var string
	 */
	static $text_domain = WC_CP_DOMAIN;

	/**
	 * Force debugging mode
	 *
	 * @var bool
	 */
	static $force_debugging = false;

	/**
	 * Strip html tags with content
	 *
	 * @param string $text
	 * @param string $tags
	 * @param bool   $invert
	 *
	 * @return mixed
	 */
	public static function strip_tags_content( $text, $tags = '', $invert = false )
	{
		preg_match_all( '/<(.+?)[\s]*\/?[\s]*>/si', trim( $tags ), $tags );
		$tags = array_unique( $tags[1] );

		if ( is_array( $tags ) AND count( $tags ) > 0 )
		{
			if ( $invert == false )
			{
				return preg_replace( '@<(?!(?:' . implode( '|', $tags ) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text );
			}
			else
			{
				return preg_replace( '@<(' . implode( '|', $tags ) . ')\b.*?>.*?</\1>@si', '', $text );
			}
		}
		elseif ( $invert == false )
		{
			return preg_replace( '@<(\w+)\b.*?>.*?</\1>@si', '', $text );
		}

		return $text;
	}

	/**
	 * Dump data
	 *
	 * @return void
	 */
	public static function dump()
	{
		foreach ( func_get_args() as $arg )
		{
			echo '<pre>';
			print_r( $arg );
			echo '</pre>';
		}
	}

	/**
	 * Var dump data
	 *
	 * @return void
	 */
	public static function var_dump()
	{
		foreach ( func_get_args() as $arg )
		{
			echo '<pre>';
			var_dump( $arg );
			echo '</pre>';
		}
	}

	/**
	 * Check if the given URL is valid
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function is_valid_url( $url )
	{
		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) )
		{
			// Must start with http:// or https://
			return false;
		}

		if ( !filter_var( $url, FILTER_VALIDATE_URL ) )
		{
			// Must pass validation
			return false;
		}

		return true;
	}

	/**
	 * Plugin Version
	 *
	 * @return string
	 */
	public static function plugin_version()
	{
		return Plugin::get_instance()->version;
	}

	/**
	 * Check if target plugin is active wrapper
	 *
	 * @param string $plugin_file
	 *
	 * @return bool
	 */
	public static function is_plugin_active( $plugin_file )
	{
		if ( !function_exists( 'is_plugin_active' ) )
		{
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_file );
	}

	/**
	 * Check if target plugin is inactive wrapper
	 *
	 * @param string $plugin_file
	 *
	 * @return bool
	 */
	public static function is_plugin_inactive( $plugin_file )
	{
		if ( !function_exists( 'is_plugin_inactive' ) )
		{
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_inactive( $plugin_file );
	}

	/**
	 * Sanitizes a hex color.
	 *
	 * Returns either '', a 3 or 6 digit hex color (with #), or null.
	 * For sanitizing values without a #, see self::sanitize_hex_color_no_hash().
	 *
	 * @since 1.0
	 *
	 * @param string $color
	 *
	 * @return string|null
	 */
	public static function sanitize_hex_color( $color )
	{
		if ( '' === $color )
		{
			return '';
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) )
		{
			return $color;
		}

		return null;
	}

	/**
	 * Sanitizes a hex color without a hash. Use sanitize_hex_color() when possible.
	 *
	 * Saving hex colors without a hash puts the burden of adding the hash on the
	 * UI, which makes it difficult to use or upgrade to other color types such as
	 * rgba, hsl, rgb, and html color names.
	 *
	 * Returns either '', a 3 or 6 digit hex color (without a #), or null.
	 *
	 * @since 1.0
	 * @uses self::sanitize_hex_color()
	 *
	 * @param string $color
	 *
	 * @return string|null
	 */
	public static function sanitize_hex_color_no_hash( $color )
	{
		$color = ltrim( $color, '#' );

		if ( '' === $color )
		{
			return '';
		}

		return sanitize_hex_color( '#' . $color ) ? $color : null;
	}

	/**
	 * Current visitor/session IP address
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function get_visitor_IP()
	{
		$client  = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? $_SERVER['HTTP_CLIENT_IP'] : null;
		$forward = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;

		if ( $client && filter_var( $client, FILTER_VALIDATE_IP ) )
		{
			return $client;
		}

		if ( $client && filter_var( $forward, FILTER_VALIDATE_IP ) )
		{
			return $forward;
		}

		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Determine scripts and styles enqueues suffix
	 *
	 * @since 1.0
	 * @return string
	 */
	public static function enqueue_suffix()
	{
		return self::is_script_debugging() ? '' : '.min';
	}

	/**
	 * Determine scripts and styles enqueues base directory URL
	 *
	 * @return string
	 */
	public static function enqueue_base_url()
	{
		return self::is_script_debugging() ? 'assets/src/' : 'assets/dist/';
	}

	/**
	 * Check whether script debugging enable or not
	 *
	 * @return bool
	 */
	public static function is_script_debugging()
	{
		return self::$force_debugging ? true : defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	/**
	 * URL Redirect
	 *
	 * @param string $target
	 * @param number $status
	 *
	 * @return void
	 */
	public static function redirect( $target = '', $status = 302 )
	{
		if ( '' == $target && isset( $_REQUEST['_wp_http_referer'] ) )
		{
			$target = esc_url( $_REQUEST['_wp_http_referer'] );
		}

		wp_redirect( $target, $status );
		die();
	}

	/**
	 * Modified version of sanitize_text_field with line-breaks preserved
	 *
	 * @see sanitize_text_field
	 * @since 2.9.0
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function sanitize_text_field_with_linebreaks( $str )
	{
		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos( $filtered, '<' ) !== false )
		{
			$filtered = wp_pre_kses_less_than( $filtered );

			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, true );
		}

		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) )
		{
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found )
		{
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
		}

		/**
		 * Filter a sanitized text field string.
		 *
		 * @since 2.9.0
		 *
		 * @param string $filtered The sanitized string.
		 * @param string $str The string prior to being sanitized.
		 */
		return apply_filters( 'sanitize_text_field_with_linebreaks', $filtered, $str );
	}

	/**
	 * Parse/Join html attributes
	 *
	 * @param array $attrs
	 *
	 * @return string
	 */
	public static function parse_attributes( $attrs )
	{
		if ( empty( $attrs ) )
		{
			return '';
		}

		array_walk( $attrs, function ( &$item, $key )
		{
			$item = $key . '="' . esc_attr( is_array( $item ) ? implode( ' ', $item ) : $item ) . '"';
		} );

		return implode( ' ', $attrs );
	}
}