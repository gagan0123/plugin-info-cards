<?php
/**
 * Helper class containing some modified WordPress functions and helper functions.
 *
 * @package Plugin_Info_Cards
 */

if ( ! class_exists( 'Plugin_Info_Cards_Helper' ) ) {

	/**
	 * Contains helper methods replicating some WordPress functions.
	 *
	 * @since 1.0
	 */
	class Plugin_Info_Cards_Helper {

		/**
		 * Contains list of required keys returned by the API.
		 *
		 * @since 1.0
		 * @access private
		 *
		 * @var array
		 */
		private $required_keys;

		/**
		 * Constructor
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param array $required_keys Array of keys needed for the plugin data.
		 */
		public function __construct( $required_keys ) {
			$this->required_keys = array_flip( $required_keys );
		}

		/**
		 * Retrieves plugin info from the WordPress.org Plugins API.
		 *
		 * Supported arguments per action:
		 *
		 * | Argument Name        | query_plugins | plugin_information | hot_tags | hot_categories |
		 * | -------------------- | :-----------: | :----------------: | :------: | :------------: |
		 * | `$slug`              | No            |  Yes               | No       | No             |
		 * | `$per_page`          | Yes           |  No                | No       | No             |
		 * | `$page`              | Yes           |  No                | No       | No             |
		 * | `$number`            | No            |  No                | Yes      | Yes            |
		 * | `$search`            | Yes           |  No                | No       | No             |
		 * | `$tag`               | Yes           |  No                | No       | No             |
		 * | `$author`            | Yes           |  No                | No       | No             |
		 * | `$user`              | Yes           |  No                | No       | No             |
		 * | `$browse`            | Yes           |  No                | No       | No             |
		 * | `$locale`            | Yes           |  Yes               | No       | No             |
		 * | `$installed_plugins` | Yes           |  No                | No       | No             |
		 * | `$is_ssl`            | Yes           |  Yes               | No       | No             |
		 * | `$fields`            | Yes           |  Yes               | No       | No             |
		 *
		 * @since 1.0
		 * @access private
		 *
		 * @param array|object $args   {
		 *     Optional. Array or object of arguments to serialize for the Plugin Info API.
		 *
		 *     @type string  $slug              The plugin slug. Default empty.
		 *     @type int     $per_page          Number of plugins per page. Default 24.
		 *     @type int     $page              Number of current page. Default 1.
		 *     @type int     $number            Number of tags or categories to be queried.
		 *     @type string  $search            A search term. Default empty.
		 *     @type string  $tag               Tag to filter plugins. Default empty.
		 *     @type string  $author            Username of an plugin author to filter plugins. Default empty.
		 *     @type string  $user              Username to query for their favorites. Default empty.
		 *     @type string  $browse            Browse view: 'popular', 'new', 'beta', 'recommended'.
		 *     @type string  $locale            Locale to provide context-sensitive results. Default is the value
		 *                                      of get_locale().
		 *     @type string  $installed_plugins Installed plugins to provide context-sensitive results.
		 *     @type bool    $is_ssl            Whether links should be returned with https or not. Default false.
		 *     @type array   $fields            {
		 *         Array of fields which should or should not be returned.
		 *
		 *         @type bool $short_description Whether to return the plugin short description. Default true.
		 *         @type bool $description       Whether to return the plugin full description. Default false.
		 *         @type bool $sections          Whether to return the plugin readme sections: description, installation,
		 *                                       FAQ, screenshots, other notes, and changelog. Default false.
		 *         @type bool $tested            Whether to return the 'Compatible up to' value. Default true.
		 *         @type bool $requires          Whether to return the required WordPress version. Default true.
		 *         @type bool $rating            Whether to return the rating in percent and total number of ratings.
		 *                                       Default true.
		 *         @type bool $ratings           Whether to return the number of rating for each star (1-5). Default true.
		 *         @type bool $downloaded        Whether to return the download count. Default true.
		 *         @type bool $downloadlink      Whether to return the download link for the package. Default true.
		 *         @type bool $last_updated      Whether to return the date of the last update. Default true.
		 *         @type bool $added             Whether to return the date when the plugin was added to the wordpress.org
		 *                                       repository. Default true.
		 *         @type bool $tags              Whether to return the assigned tags. Default true.
		 *         @type bool $compatibility     Whether to return the WordPress compatibility list. Default true.
		 *         @type bool $homepage          Whether to return the plugin homepage link. Default true.
		 *         @type bool $versions          Whether to return the list of all available versions. Default false.
		 *         @type bool $donate_link       Whether to return the donation link. Default true.
		 *         @type bool $reviews           Whether to return the plugin reviews. Default false.
		 *         @type bool $banners           Whether to return the banner images links. Default false.
		 *         @type bool $icons             Whether to return the icon links. Default false.
		 *         @type bool $active_installs   Whether to return the number of active installs. Default false.
		 *         @type bool $group             Whether to return the assigned group. Default false.
		 *         @type bool $contributors      Whether to return the list of contributors. Default false.
		 *     }
		 * }
		 *
		 * @param string       $action API action to perform: 'query_plugins', 'plugin_information',
		 *                             'hot_tags' or 'hot_categories'.
		 *
		 * @return object|array|WP_Error Response object or array on success, WP_Error on failure. See the
		 *         {@link https://developer.wordpress.org/reference/functions/plugins_api/ function reference article}
		 *         for more information on the make-up of possible return values depending on the value of `$action`.
		 */
		private function plugins_api( $args = array(), $action = 'plugin_information' ) {

			if ( is_array( $args ) ) {
				$args = (object) $args;
			}

			if ( ! isset( $args->per_page ) ) {
				$args->per_page = 24;
			}

			$http_url    = 'http://api.wordpress.org/plugins/info/1.0/';
			$url         = $http_url;
			$ssl         = wp_http_supports( array( 'ssl' ) );

			if ( $ssl ) {
				$url = set_url_scheme( $url, 'https' );
			}

			$http_args   = array(
				'timeout'    => 15,
				'body'       => array(
					'action'     => $action,
					'request'    => maybe_serialize( $args ),
				),
			);
			$request     = wp_remote_post( $url, $http_args );

			if ( $ssl && is_wp_error( $request ) ) {
				// Falling back to HTTP protocol when can't fetch from HTTPS.
				$request = wp_remote_post( $http_url, $http_args );
			}

			if ( is_wp_error( $request ) ) {
				$res = new WP_Error(
					'plugins_api_failed', sprintf(
						/* translators: %s: support forums URL */
						__( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.', 'plugin-info-cards' ), __( 'https://wordpress.org/support/', 'plugin-info-cards' )
					), $request->get_error_message()
				);
			} else {
				$res = maybe_unserialize( wp_remote_retrieve_body( $request ) );
				if ( ! is_object( $res ) && ! is_array( $res ) ) {
					$res = new WP_Error(
						'plugins_api_failed', sprintf(
							/* translators: %s: support forums URL */
							__( 'An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.', 'plugin-info-cards' ), __( 'https://wordpress.org/support/', 'plugin-info-cards' )
						), wp_remote_retrieve_body( $request )
					);
				}
			}

			return $res;
		}

		/**
		 * Output a HTML element with a star rating for a given rating.
		 *
		 * Outputs a HTML element with the star rating exposed on a 0..5 scale in
		 * half star increments (ie. 1, 1.5, 2 stars). Optionally, if specified, the
		 * number of ratings may also be displayed by passing the $number parameter.
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param array $args {
		 *     Optional. Array of star ratings arguments.
		 *
		 *     @type int    $rating The rating to display, expressed in either a 0.5 rating increment,
		 *                          or percentage. Default 0.
		 *     @type string $type   Format that the $rating is in. Valid values are 'rating' (default),
		 *                          or, 'percent'. Default 'rating'.
		 *     @type int    $number The number of ratings that makes up this rating. Default 0.
		 *     @type bool   $echo   Whether to echo the generated markup. False to return the markup instead
		 *                          of echoing it. Default true.
		 * }
		 *
		 * @return string Output HTML for star ratings.
		 */
		public function wp_star_rating( $args = array() ) {
			$defaults    = array(
				'rating' => 0,
				'type'   => 'rating',
				'number' => 0,
				'echo'   => true,
			);
			$r           = wp_parse_args( $args, $defaults );

			// Non-english decimal places when the $rating is coming from a string.
			$rating = str_replace( ',', '.', $r['rating'] );

			// Convert Percentage to star rating, 0..5 in .5 increments.
			if ( 'percent' === $r['type'] ) {
				$rating = round( $rating / 10, 0 ) / 2;
			}

			// Calculate the number of each type of star needed.
			$full_stars  = floor( $rating );
			$half_stars  = ceil( $rating - $full_stars );
			$empty_stars = 5 - $full_stars - $half_stars;

			if ( $r['number'] ) {
				/* translators: 1: The rating, 2: The number of ratings */
				$format  = _n( '%1$s rating based on %2$s rating', '%1$s rating based on %2$s ratings', $r['number'], 'plugin-info-cards' );
				$title   = sprintf( $format, number_format_i18n( $rating, 1 ), number_format_i18n( $r['number'] ) );
			} else {
				/* translators: 1: The rating */
				$title = sprintf( __( '%s rating', 'plugin-info-cards' ), number_format_i18n( $rating, 1 ) );
			}

			$output  = '<div class="star-rating">';
			$output  .= '<span class="screen-reader-text">' . esc_html( $title ) . '</span>';
			$output  .= str_repeat( '<div class="star star-full" aria-hidden="true"></div>', $full_stars );
			$output  .= str_repeat( '<div class="star star-half" aria-hidden="true"></div>', $half_stars );
			$output  .= str_repeat( '<div class="star star-empty" aria-hidden="true"></div>', $empty_stars );
			$output  .= '</div>';

			if ( $r['echo'] ) {
				$allowed_html = array(
					'div'    => array(
						'class'          => true,
						'aria-hidden'    => true,
					),
					'span'   => array(
						'class' => true,
					),
				);

				echo wp_kses( $output, $allowed_html );
			}

			return $output;
		}

		/**
		 * Retrieve the plugin by a given slug.
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param string $slug Plugin slug to be fetched.
		 *
		 * @return array|WP_Error Array of plugin data or WP_Error is unable to retrieve.
		 */
		public function get_plugin_by_slug( $slug ) {
			$plugin = get_transient( 'pic_plugin_' . $slug );
			if ( false === $plugin ) {
				$plugin  = $this->plugins_api(
					array(
						'slug'   => $slug,
						'locale' => get_locale(),
						'fields' => array(
							'sections'           => false,
							'icons'              => true,
							'active_installs'    => true,
							'versions'           => false,
							'screenshots'        => false,
							'short_description'  => true,
						),
					)
				);
				if ( ! is_wp_error( $plugin ) ) {
					$plugin = $this->remove_unused_params( $plugin );
					set_transient( 'pic_plugin_' . $slug, $plugin, 1 * HOUR_IN_SECONDS );
				}
			}
			return $plugin;
		}

		/**
		 * Retrieves the plugins by given author.
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param string $author Author of the plugin.
		 *
		 * @return array|WP_Error Returns an array of plugins by given author.
		 */
		public function get_plugin_by_author( $author ) {
			$plugins = get_transient( 'pic_author_' . $author );
			if ( false === $plugins ) {
				$result = $this->plugins_api(
					array(
						'author' => $author,
						'locale' => get_locale(),
						'fields' => array(
							'sections'           => false,
							'icons'              => true,
							'active_installs'    => true,
							'versions'           => false,
							'short_description'  => true,
						),
					), 'query_plugins'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}
				if ( isset( $result->plugins ) && is_array( $result->plugins ) ) {
					$plugins = array_map( array( $this, 'remove_unused_params' ), $result->plugins );
				}
				set_transient( 'pic_author_' . $author, $plugins, 1 * HOUR_IN_SECONDS );
			}
			return $plugins;
		}

		/**
		 * Reduces the size of array being stored in the transient.
		 *
		 * Not all the keys returned by the WordPress plugin API are needed for
		 * this plugin so storing only the keys that are needed.
		 *
		 * @since 1.0
		 * @access private
		 *
		 * @param object|array $plugin Either an object or array containing plugin data.
		 *
		 * @return array Returns an array of plugin data with only the keys needed.
		 */
		private function remove_unused_params( $plugin ) {
			return array_intersect_key( (array) $plugin, $this->required_keys );
		}

	}

}
