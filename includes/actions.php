<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Actions
 *
 * Copyright (c) 2013, Sunny Ratilal.
 */

function edd_bbp_d_process_actions() {
	if ( ! empty( $_POST['bbps_support_topic_assign'] ) ) {
		edd_bbp_d_assign_topic( $_POST );
	}

	if ( ! empty( $_POST['bbps_support_submit'] ) ) {
		edd_bbp_d_update_status( $_POST );
	}


	if ( ! empty( $_POST['bbps_topic_ping_submit'] ) ) {
		edd_bbp_d_ping_topic_assignee( $_POST );
	}
}
add_action( 'init', 'edd_bbp_d_process_actions' );
