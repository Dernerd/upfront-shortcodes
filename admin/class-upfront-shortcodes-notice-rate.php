<?php

/**
 * The "Rate plugin" notice.
 *
 * @since        1.0.0
 *
 * @package      UpFront_Shortcodes
 * @subpackage   UpFront_Shortcodes/admin
 */
final class UpFront_Shortcodes_Notice_Rate extends UpFront_Shortcodes_Notice {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since  1.0.0
	 */
	public function __construct( $notice_id, $template_file ) {

		parent::__construct( $notice_id, $template_file );

		$this->defer_delay      = 3 * DAY_IN_SECONDS;
		$this->first_time_delay = 7 * DAY_IN_SECONDS;

	}

	/**
	 * Display the notice.
	 *
	 * @since  1.0.0
	 */
	public function display_notice() {

		// Make sure this is the Plugins screen
		if ( $this->get_current_screen_id() !== 'plugins' ) {
			return;
		}

		// Check user capability
		if ( ! $this->current_user_can_view() ) {
			return;
		}

		// Make sure the notice is not dismissed
		if ( $this->is_dismissed() ) {
			return;
		}

		// Display the notice
		$this->include_template();

	}

}
