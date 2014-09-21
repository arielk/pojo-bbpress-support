<?php
/**
 * Filters
 *
 * Copyright (c) 2013, Easy Digital Downloads.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_bbp_get_subforums( $args ) {
	$args['nopaging'] = true;
	return $args;
}
add_filter( 'bbp_after_forum_get_subforums_parse_args', 'edd_bbp_get_subforums' );