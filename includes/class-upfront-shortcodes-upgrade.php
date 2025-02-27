<?php

/**
 * The class responsible for plugin upgrade procedures.
 *
 * @since        1.0.0
 * @package      UpFront_Shortcodes
 * @subpackage   UpFront_Shortcodes/includes
 */
final class UpFront_Shortcodes_Upgrade {

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $current_version   The current version of the plugin.
	 */
	private $current_version;

	/**
	 * Name of the option which stores plugin version.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $saved_version_option   Name of the option which stores plugin version.
	 */
	private $saved_version_option;

	/**
	 * The full path of the upgrade file.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $upgrade_path   The full path of the upgrade file.
	 */
	private $upgrade_path;

	/**
	 * Define the functionality of the updater.
	 *
	 * @since   1.0.0
	 * @param string  $plugin_version The current version of the plugin.
	 */
	public function __construct( $plugin_version ) {

		$this->current_version      = $plugin_version;
		$this->saved_version_option = 'su_option_version';
		$this->upgrade_path         = '';

	}

	/**
	 * Run upgrades if version changed.
	 *
	 * @since  1.0.0
	 */
	public function maybe_upgrade() {

		if ( ! $this->is_version_changed() ) {
			return;
		}

		$this->maybe_upgrade_to( '1.0.0' );
		$this->maybe_upgrade_to( '5.0.7' );
		$this->maybe_upgrade_to( '5.1.1' );
		$this->maybe_upgrade_to( '5.2.0' );
		$this->maybe_upgrade_to( '1.0.0' );
		$this->maybe_upgrade_to( '5.6.0' );
		$this->maybe_upgrade_to( '5.9.1' );

		$this->update_saved_version();

	}

	/**
	 * Helper function to register a new upgrade routine.
	 *
	 * @since 1.0.0
	 * @param string $version New version number.
	 */
	private function maybe_upgrade_to( $version ) {

		if ( ! $this->is_saved_version_lower_than( $version ) ) {
			return;
		}

		$this->upgrade_to( $version );

	}

	/**
	 * Helper function to test a new upgrade routine.
	 *
	 * @since 5.6.0
	 * @param string $version New version number.
	 */
	private function upgrade_to( $version ) {

		$this->upgrade_path = plugin_dir_path( __FILE__ ) . 'upgrade/' . $version . '.php';

		if ( ! file_exists( $this->upgrade_path ) ) {
			return;
		}

		include $this->upgrade_path;

	}

	/**
	 * Conditional check if plugin was updated.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return boolean True if plugin was updated, False otherwise.
	 */
	private function is_version_changed() {
		return $this->is_saved_version_lower_than( $this->current_version );
	}

	/**
	 * Conditional check if previous version of the plugin lower than passed one.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return boolean True if previous version of the plugin lower than passed one, False otherwise.
	 */
	private function is_saved_version_lower_than( $version ) {

		return version_compare(
			get_option( $this->saved_version_option, 0 ),
			$version,
			'<'
		);

	}

	/**
	 * Save current version number.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function update_saved_version() {
		update_option( $this->saved_version_option, $this->current_version, false );
	}

}
