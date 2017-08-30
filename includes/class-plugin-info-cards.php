<?php

if ( !class_exists( 'Plugin_Info_Cards' ) ) {

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

		function __construct() {
			
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
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

	}

	Plugin_Info_Cards::get_instance();
}