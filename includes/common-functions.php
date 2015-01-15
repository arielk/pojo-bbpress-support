<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Common Functions
 */

/**
 * Checks if the current forum is a premium one
 *
 * @return bool
 */
function edd_bbp_d_is_premium_forum( $forum_id ) {
	$premium_forum = get_post_meta( $forum_id, '_bbps_is_premium', true );

	if ( 1 == $premium_forum )
		return true;
	else
		return false;
}

/**
 * Checks if this is the support forum
 *
 * @param int $forum_id
 * @return bool
 */
function edd_bbp_d_is_support_forum( $forum_id ) {
	$support_forum = get_post_meta( $forum_id, '_bbps_is_support', true );

	if ( 1 == $support_forum )
		return true;
	else
		return false;
}


/**
 * Checks whether the topic is a premium topic
 *
 * @return bool
 */
function edd_bbp_d_is_topic_premium() {
	$is_premium = get_post_meta( bbp_get_topic_forum_id(), '_bbps_is_premium' );

	if ( 1 == $is_premium[0] )
		return true;
	else
		return false;
}


/**
 * Checks if the topic has been marked as resolved
 *
 * @return bool
 */
function edd_bbp_d_topic_resolved( $topic_id ) {
	return ( 2 == get_post_meta( $topic_id, '_bbps_topic_status', true ) );
}

/**
 * @param int $user_id
 *
 * @return bool
 */
function edd_bbp_d_is_user_can_write_in_forum( $user_id = 0 ) {
	global $wpdb;
	
	if ( 0 === absint( $user_id ) )
		return false;
	
	$cache_key = 'edd_bbp_d_is_user_can_write_in_forum-' . $user_id;
	
	$return = get_transient( $cache_key );
	if ( false === $return ) {
		$license_ids = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT `ID` FROM %1$s
					WHERE `post_author` = \'%2$d\'
						AND `post_type` = \'edd_license\';',
				$wpdb->posts,
				$user_id
			)
		);
		
		$return = 'false';
		if ( ! is_null( $license_ids ) ) {
			foreach ( $license_ids as $license_id ) {
				if ( in_array( edd_software_licensing()->get_license_status( $license_id ), array( 'active', 'inactive' ) ) ) {
					$return = 'true';
					break;
				}
			}
		}
		
		//set_transient( $cache_key, $return );
	}
	return 'true' === $return;
}