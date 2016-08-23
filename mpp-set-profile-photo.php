<?php
/**
 * Plugin Name: MediaPress Set Profile Photo
 * Plugin URI: https://buddydev.com/plugins/mpp-set-profile-photo/
 * Version: 1.0.0
 * Author: BuddyDev Team
 * Author URI: https://buddydev.com
 * Description: Allows users to use MediaPress photo as their BuddyPress profile photo
 * 
 * License: GPL2 or Above
 * 
 */

// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MPP_Set_Profile_Photo_Helper {
	/**
	 * Singleton Instance
	 * 
	 * @var MPP_Set_Profile_Photo_Helper
	 */
	private static $instance = null;

	/**
	 * @var string the absolute path of this plugin directory
	 */
	private $path;

	private function __construct () {
		$this->setup();
	}
	
	/**
	 * Get the singleton instance
	 * 
	 * @return MPP_Set_Profile_Photo_Helper
	 */
	public static function get_instance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
		
	}
	/**
	 * Setup hooks 
	 */
	private function setup() {
		
		//setup plugin path
		$this->path = plugin_dir_path( __FILE__ );

		//load files when MediaPress is loaded
		add_action( 'bp_loaded', array( $this, 'load' ) );
		add_action( 'bp_init', array( $this, 'load_textdomain' ) );
	}
	
	/**
	 * Load required files
	 */
	public function load() {
		
		//$files array is an array of file paths(relative to this plugin's directory) to the files we want to include
		$files = array(
			'core/mpp-spp-functions.php',
			'core/mpp-spp-template-helper.php',
			'core/mpp-spp-hooks.php',
		);

		foreach ( $files as $file ) {
			require_once $this->path . $file ;
		}
		
	}

	/**
	 * Load plugin translations
	 */
	public function load_textdomain() {
		//Note: Developers, if possible, please use the plugin directory name as textdomain
		load_plugin_textdomain( 'mpp-set-profile-photo', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

}
//initialize
MPP_Set_Profile_Photo_Helper::get_instance();
