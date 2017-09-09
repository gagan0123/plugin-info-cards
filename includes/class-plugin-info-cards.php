<?php
/**
 * Main class of the plugin interacting with the WordPress.
 *
 * @package Plugin_Info_Cards
 */

if ( ! class_exists( 'Plugin_Info_Cards' ) ) {

	/**
	 * Main class of the plugin dealing with interactions with WordPress
	 *
	 * @since 1.0
	 */
	class Plugin_Info_Cards {

		/**
		 * The instance of the class Plugin_Info_Cards
		 *
		 * @since 1.0
		 * @access protected
		 *
		 * @var Plugin_Info_Cards
		 */
		protected static $instance = null;

		/**
		 * Object of the Plugin_Info_Cards_Helper class.
		 *
		 * @var Plugin_Info_Cards_Helper
		 */
		private $helper;

		/**
		 * Constructor.
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct() {
			add_shortcode( 'plugin-info-cards', array( $this, 'render_shortcode' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

			require_once GS_PIC_PATH . 'includes/class-plugin-info-cards-helper.php';

			$required_keys = array(
				'icons',
				'slug',
				'name',
				'version',
				'last_updated',
				'author',
				'short_description',
				'rating',
				'num_ratings',
				'active_installs',
				'downloaded',
				'download_link',
			);

			$this->helper = new Plugin_Info_Cards_Helper( $required_keys );
		}

		/**
		 * Returns the current instance of the class object, in case some other
		 * plugin needs to use its public methods.
		 *
		 * @since 1.0
		 * @access public
		 * @static
		 *
		 * @return Plugin_Info_Cards Returns the current instance of the class object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Enqueues the scripts and styles for this plugin.
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return void
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_style( 'plugin-info-cards', GS_PIC_URL . 'public/css/plugin-info-cards.min.css', array( 'dashicons' ), '1.2' );
		}

		/**
		 * Renders the shortcode 'plugin-info-card' and returns the output
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param array $atts {
		 *      An array of arguments, either slug or array key 0 should be given.
		 *      @type string 0...n Slugs of the plugins to be displayed. At least
		 *                         one slug should be present
		 * }
		 *
		 * @return string Returns the plugin card HTML
		 */
		public function render_shortcode( $atts ) {

			$output  = '';
			$author  = isset( $atts['author'] ) ? filter_var( $atts['author'] ) : false;
			if ( false !== $author ) {
				$plugins = $this->helper->get_plugin_by_author( $author );
				if ( ! is_wp_error( $plugins ) && ! empty( $plugins ) ) {
					foreach ( $plugins as $plugin ) {
						$output .= $this->generate_html( $plugin );
					}
				} else {
					/* translators: 1: Slug of the author. */
					$output .= sprintf( __( 'Failed to retrieve plugins for author %s', 'plugin-info-cards' ), $author );
				}
			}

			foreach ( $atts as $key => $slug ) {
				if ( is_int( $key ) ) {
					$plugin = $this->helper->get_plugin_by_slug( $slug );
					if ( ! is_wp_error( $plugin ) ) {
						$output .= $this->generate_html( $plugin );
					} else {
						/* translators: 1: Slug of the plugin. */
						$output .= sprintf( __( 'Failed to retreive plugin information for the plugin %s', 'plugin-info-cards' ), $slug ) . '<br>';
					}
				}
			}

			return $output;
		}

		/**
		 * Generates the HTML output for frontend.
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param array|object $plugin {
		 *      Array of plugin fields.
		 *
		 *      @type string    $slug               Slug of the plugin.
		 *      @type string    $name               Name of the plugin.
		 *      @type string    $version            Plugin version.
		 *      @type string    $last_updated       Last time the plugin was updated.
		 *      @type string    $author             Author of the plugin with or without anchor tag.
		 *      @type float     $rating             Rating of the plugin out of 100.
		 *      @type int       $num_ratings        Number of ratings.
		 *      @type int       $active_installs    Number of Active Installs.
		 *      @type int       $downloaded         Number of times the plugin is downloaded.
		 *      @type string    $short_description  Short description of the plugin.
		 *      @type string    $download_link      Download link for the latest version of the plugin.
		 *      @type array     $icons {
		 *          Array of icons for the plugin, at least one key must exist.
		 *
		 *          @type string $1x       (Optional) Link to the 128x128 icon.
		 *          @type string $2x       (Optional) Link to the 256x256 icon.
		 *          @type string $default  (Optional) Base 64 URI encoded icon file data.
		 *
		 *      }
		 * }
		 *
		 * @return string Returns the HTML output for the given plugin.
		 */
		public function generate_html( $plugin ) {
			$plugins_allowedtags = array(
				'a'          => array(
					'href'       => array(),
					'title'      => array(),
					'target'     => array(),
					'data-slug'  => array(),
					'aria-label' => array(),
					'class'      => array(),
					'data-name'  => array(),
				),
				'abbr'       => array(
					'title' => array(),
				),
				'acronym'    => array(
					'title' => array(),
				),
				'cite'       => array(),
				'code'       => array(),
				'pre'        => array(),
				'em'         => array(),
				'strong'     => array(),
				'ul'         => array(
					'class' => array(),
				),
				'ol'         => array(),
				'li'         => array(),
				'p'          => array(),
				'br'         => array(),
			);

			if ( is_object( $plugin ) ) {
				$plugin = (array) $plugin;
			}
			$details_link = 'https://wordpress.org/plugins/' . $plugin['slug'];

			if ( ! empty( $plugin['icons']['svg'] ) ) {
				$plugin_icon_url = $plugin['icons']['svg'];
			} elseif ( ! empty( $plugin['icons']['2x'] ) ) {
				$plugin_icon_url = $plugin['icons']['2x'];
			} elseif ( ! empty( $plugin['icons']['1x'] ) ) {
				$plugin_icon_url = $plugin['icons']['1x'];
			} else {
				$plugin_icon_url = $plugin['icons']['default'];
			}
			$title   = $plugin['name'];
			$version = wp_kses( $plugin['version'], $plugins_allowedtags );
			$name    = strip_tags( $title . ' ' . $version );

			$action_links = array();

			/* translators: 1: Plugin Name */
			$action_links[] = '<a class="download button" data-slug="' . esc_attr( $plugin['slug'] ) . '" href="' . esc_url( $plugin['download_link'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Download %s now', 'plugin-info-cards' ), $name ) ) . '" data-name="' . esc_attr( $plugin['name'] ) . '">' . __( 'Download', 'plugin-info-cards' ) . '</a>';

			/* translators: 1: Plugin name and version. */
			$action_links[] = '<a href="' . esc_url( $details_link ) . '" class="thickbox open-plugin-details-modal" aria-label="' . esc_attr( sprintf( __( 'More information about %s', 'plugin-info-cards' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details', 'plugin-info-cards' ) . '</a>';

			$last_updated_timestamp = strtotime( $plugin['last_updated'] );

			$author = wp_kses( $plugin['author'], $plugins_allowedtags );
			if ( ! empty( $author ) ) {
				/* translators: 1: Author name, eg. By John Doe */
				$author = ' <cite>' . sprintf( __( 'By %s', 'plugin-info-cards' ), $author ) . '</cite>';
			}

			// Remove any HTML from the description.
			$description = strip_tags( $plugin['short_description'] );
			ob_start();
			?>
			<div class="plugin-info-card plugin-card-<?php echo sanitize_html_class( $plugin['slug'] ); ?>">
				<div class="plugin-card-top">
					<div class="name column-name">
						<h3>
							<a href="<?php echo esc_url( $details_link ); ?>" class="thickbox open-plugin-details-modal">
								<?php echo esc_html( $title ); ?>
								<img src="<?php echo esc_attr( $plugin_icon_url ); ?>" class="plugin-icon" alt="">
							</a>
						</h3>
					</div>
					<div class="action-links">
						<?php
						if ( $action_links ) {
							echo wp_kses( '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>', $plugins_allowedtags );
						}
						?>
					</div>
					<div class="desc column-description">
						<p><?php echo esc_html( $description ); ?></p>
						<p class="authors">
							<?php
							echo wp_kses( $author, $plugins_allowedtags );
							?>
						</p>
					</div>
				</div>
				<div class="plugin-card-bottom">
					<div class="vers column-rating">
						<?php
						$this->helper->wp_star_rating(
							array(
								'rating' => $plugin['rating'],
								'type'   => 'percent',
								'number' => $plugin['num_ratings'],
							)
						);
						?>
						<span class="num-ratings" aria-hidden="true">(<?php echo esc_html( number_format_i18n( $plugin['num_ratings'] ) ); ?>)</span>
					</div>
					<div class="column-updated">
						<strong><?php esc_html_e( 'Last Updated:', 'plugin-info-cards' ); ?></strong>
						<?php
						/* translators: 1: Time difference in human readable form eg. 1 min ago */
						echo esc_html( sprintf( __( '%s ago', 'plugin-info-cards' ), human_time_diff( $last_updated_timestamp ) ) );
						?>
					</div>
					<div class="column-downloaded">
						<?php
						$plugin['active_installs'] = intval( $plugin['active_installs'] );
						if ( $plugin['active_installs'] >= 1000000 ) {
							$active_installs_text = _x( '1+ Million', 'Active plugin installs', 'plugin-info-cards' );
						} elseif ( 0 === $plugin['active_installs'] ) {
							$active_installs_text = _x( 'Less Than 10', 'Active plugin installs', 'plugin-info-cards' );
						} else {
							$active_installs_text = number_format_i18n( $plugin['active_installs'] ) . '+';
						}
						/* translators: 1: Number of active installs of the plugin */
						echo esc_html( sprintf( __( '%s Active Installs', 'plugin-info-cards' ), $active_installs_text ) );
						?>
					</div>
					<div class="column-compatibility">
						<?php
						$plugin['downloaded'] = intval( $plugin['downloaded'] );
						if ( $plugin['downloaded'] >= 1000000 ) {
							$downloaded_text = _x( '1+ Million', 'Total Downloads', 'plugin-info-cards' );
						} elseif ( 0 === $plugin['downloaded'] ) {
							$downloaded_text = _x( 'Less Than 10', 'Total Downloads', 'plugin-info-cards' );
						} else {
							$downloaded_text = number_format_i18n( $plugin['downloaded'] );
						}
						/* translators: 1: Number of downloads */
						echo esc_html( sprintf( __( '%s Total Downloads', 'plugin-info-cards' ), $downloaded_text ) );
						?>
					</div>
				</div>
			</div>
			<?php
			$output = ob_get_clean();
			return $output;
		}

	}

	Plugin_Info_Cards::get_instance();
}
