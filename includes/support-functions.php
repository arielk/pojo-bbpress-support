<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Support Forum Functions
 */

function edd_bbp_d_add_support_forum_features() {
	if ( edd_bbp_d_is_support_forum( bbp_get_forum_id() ) ) {
		$topic_id = bbp_get_topic_id();
		$status   = edd_bbp_d_get_topic_status( $topic_id );
	?>
	<div id="bbps_support_forum_status" class="bbps-topic-options">
		<div class="bbps-topic-options-inner">
			<?php if ( current_user_can( 'moderate' ) ) :
				edd_bbp_d_generate_status_options( $topic_id, $status );
			else : ?>
				<?php _e( 'This topic is:', 'pojo-bbpress-support' ); ?> <?php _e( ucwords( $status ), 'pojo-bbpress-support' ); ?>
			<?php endif; ?>
		</div>
	</div>
	<?php
	}
}
add_action( 'bbp_template_before_single_topic', 'edd_bbp_d_add_support_forum_features' );

function edd_bbp_d_get_all_mods() {
	$wp_user_search = new WP_User_Query( array( 'role' => 'administrator', 'blog_id' => 1 ) );
	$admins = $wp_user_search->get_results();

	$wp_user_search = new WP_User_Query( array( 'role' => 'bbp_moderator' ) );
	$moderators = $wp_user_search->get_results();

	$wp_user_search = new WP_User_Query( array( 'role' => 'bbp_keymaster' ) );
	$keymasters = $wp_user_search->get_results();
	
	$return = $users = array();
	foreach ( array_merge( $moderators, $keymasters, $admins ) as $user ) {
		if ( in_array( $user->ID, $users ) )
			continue;
		
		$users[] = $user->ID;
		$return[] = $user;
	}
	
	return $return;
}

function edd_bbp_d_get_topic_status( $topic_id ) {
	$default = get_option( '_bbps_default_status' );

	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );

	if ( $status )
		$switch = $status;
	else
		$switch = $default;

	switch ( $switch ) {
		case 1 :
			return 'not resolved';
			break;
		case 2 :
			return 'resolved';
			break;
		case 3 :
			return 'not a support question';
			break;
	}
}

/**
 * Generates a drop down list for administrators and moderators to change
 * the status of a forum topic
 */
function edd_bbp_d_generate_status_options( $topic_id ) {
	$dropdown_options = get_option( '_bbps_used_status' );
	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );
	$default = get_option( '_bbps_default_status' );

	//only use the default value as selected if the topic doesnt ahve a status set
	if ( $status )
		$value = $status;
	else
		$value = $default;
	?>
	<form id="bbps-topic-status" name="bbps_support" action="" method="post">
		<label for="bbps_support_options"><?php _e( 'This topic is', 'pojo-bbpress-support' ); ?>: </label>
		<select name="bbps_support_option" id="bbps_support_options">
		<?php
			// we only want to display the options the user has selected. the long term goal is to let users add their own forum statuses
			if ( isset( $dropdown_options['res'] ) ) { ?><option value="1"<?php selected( $value, 1 ) ; ?>><?php _e( 'Not Resolved', 'pojo-bbpress-support' ); ?></option><?php }
			if ( isset( $dropdown_options['notres'] ) ) {?><option value="2"<?php selected( $value, 2 ) ; ?>><?php _e( 'Resolved', 'pojo-bbpress-support' ); ?></option><?php }
			if ( isset( $dropdown_options['notsup'] ) ) {?><option value="3"<?php selected( $value, 3 ) ; ?>><?php _e( 'Not a Support Question', 'pojo-bbpress-support' ); ?></option><?php
		} ?>
		</select>
		<input type="submit" class="button" value="<?php _e( 'Update', 'pojo-bbpress-support' ); ?>" name="bbps_support_submit" />
		<input type="hidden" value="bbps_update_status" name="bbps_action"/>
		<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
	</form>
	<?php
}

function edd_bbp_d_update_status() {
	$topic_id = absint( $_POST['bbps_topic_id'] );
	$status   = sanitize_text_field( $_POST['bbps_support_option'] );
	update_post_meta( $topic_id, '_bbps_topic_status', $status );
}

function edd_bbp_d_count_tickets_of_mod( $mod_id = 0 ) {
	$args = array(
		'post_type' => 'topic',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'bbps_topic_assigned',
				'value' => $mod_id,
			),
			array(
				'key' => '_bbps_topic_status',
				'value' => '1',
			)
		),
		'nopaging' => true,
		//'post_parent__not_in' => array( pojo_get_option( 'pojo_features_forum_id' ) ),
		'post_parent' => pojo_get_option( 'pojo_support_forum_id' ),
	);

	$query = new WP_Query( $args );

	return $query->post_count;
}


function edd_bbp_d_assign_topic_form() {
	if ( get_option( '_bbps_topic_assign' ) == 1 && current_user_can( 'moderate' ) ) {
		$topic_id = bbp_get_topic_id();
		$topic_assigned = edd_bbp_get_topic_assignee_id( $topic_id );

		global $current_user;
		get_currentuserinfo();
		?>
		<div id="bbps_support_forum_options" class="bbps-topic-options">
			<div class="bbps-topic-options-inner">
				<?php
				$user_login = $current_user->user_login;
				if ( ! empty( $topic_assigned ) ) {
					$assigned_user_name = edd_bbp_get_topic_assignee_name( $topic_assigned ); ?>
					<div class="bbps-support-forums-message pojo-inline-block"><?php _e( 'Topic assigned to:', 'pojo-bbpress-support' ); ?> <?php echo $assigned_user_name; ?></div><?php
				}
				?>
				<div id="bbps_support_topic_assign" class="pojo-inline-block">
					<form id="bbps-topic-assign" class="pojo-inline-block" name="bbps_support_topic_assign" action="" method="post">
						<?php edd_bbp_d_user_assign_dropdown(); ?>
						<input class="button" type="submit" value="<?php echo esc_attr( __( 'Assign', 'pojo-bbpress-support' ) ); ?>" name="bbps_support_topic_assign" />
						<input type="hidden" value="bbps_assign_topic" name="bbps_action"/>
						<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
					</form>
					<form id="bbs-topic-assign-me" class="pojo-inline-block" name="bbps_support_topic_assign" action="" method="post">
						<input class="button" type="submit" value="<?php echo esc_attr( __( 'Assign To Me', 'pojo-bbpress-support' ) ); ?>" name="bbps_support_topic_assign" />
						<input type="hidden" value="<?php echo get_current_user_id(); ?>" name="bbps_assign_list" />
						<input type="hidden" value="bbps_assign_topic" name="bbps_action"/>
						<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
					</form>
				</div>
			</div>
		</div>
		<?php
	}

}
add_action( 'bbp_template_before_single_topic' , 'edd_bbp_d_assign_topic_form' );

function edd_bbp_d_user_assign_dropdown() {

	$all_users = edd_bbp_d_get_all_mods();

	$topic_id = bbp_get_topic_id();
	$claimed_user_id = get_post_meta( $topic_id, 'bbps_topic_assigned', true );

	if ( ! empty( $all_users ) ) {
		if ( $claimed_user_id > 0 ) {
			$text = __( 'Reassign topic to:', 'pojo-bbpress-support' );;
		} else {
			$text = __( 'Assign topic to:', 'pojo-bbpress-support' );
		}

		echo $text . ' ';
?>
		<select name="bbps_assign_list" id="bbps_support_options">
		<option value=""><?php _e( 'Unassigned', 'pojo-bbpress-support' ); ?></option><?php
		foreach ( $all_users as $user ) {
?>
			<option value="<?php echo $user->ID; ?>"<?php selected( $user->ID, $claimed_user_id ); ?>> <?php echo $user->user_firstname . ' ' . $user->user_lastname ; ?></option>
		<?php
		}
		?> </select> <?php
	}

}

function edd_bbp_d_assign_topic() {
	$user_id  = absint( $_POST['bbps_assign_list'] );
	$topic_id = absint( $_POST['bbps_topic_id'] );

	if ( $user_id > 0 ) {
		$userinfo = get_userdata( $user_id );
		$user_email = $userinfo->user_email;
		$post_link = get_permalink( $topic_id );
		//add the user as a subscriber to the topic and send them an email to let them know they have been assigned to a topic
		bbp_add_user_subscription( $user_id, $topic_id );
		/*update the post meta with the assigned users id*/
		$assigned = update_post_meta( $topic_id, 'bbps_topic_assigned', $user_id );
		if ( $user_id != get_current_user_id() ) {
			$current_user = get_userdata( get_current_user_id() );
			$message = sprintf( __( 'You have been assigned to the following topic, by %s. Please take a look at it when you get a chance.', 'pojo-bbpress-support' ), $current_user->display_name );
			$message .= PHP_EOL . $post_link;
			if ( $assigned == true ) {
				wp_mail( $user_email, __( 'A forum topic has been assigned to you', 'pojo-bbpress-support' ), $message );
			}
		}
	}
}

function edd_bbp_d_ping_topic_assignee() {
	$topic_id = absint( $_POST['bbps_topic_id'] );
	$user_id  = get_post_meta( $topic_id, 'bbps_topic_assigned', true );

	if ( $user_id ) {
		$userinfo   = get_userdata( $user_id );
		$user_email = $userinfo->user_email;
		$post_link  = get_permalink( $topic_id );

		$message = __( 'A ticket that has been assigned to you is in need of attention.', 'pojo-bbpress-support' );
		$message .= PHP_EOL . $post_link;
		wp_mail( $user_email, sprintf( __( '%s Ticket Ping', 'pojo-bbpress-support' ), get_bloginfo() ), $message );
	}
}

function edd_bbp_d_ping_asignee_button() {
	if ( get_option( '_bbps_topic_assign' ) == 1 && edd_bbp_d_is_support_forum( bbp_get_forum_id() ) ) {
		$topic_id = bbp_get_topic_id();
		$topic_assigned = edd_bbp_get_topic_assignee_id( $topic_id );
		$status = edd_bbp_d_get_topic_status( $topic_id );
		$forum_id = bbp_get_forum_id();
		$user_id = get_current_user_id();

		if ( current_user_can( 'moderate' ) && $topic_assigned ) {
?>
		<div id="bbps_support_forum_ping" class="bbps-topic-options">
			<div class="bbps-topic-options-inner">
				<form id="bbps-topic-ping" name="bbps_support_topic_ping" action="" method="post">
					<input type="submit" class="button" value="<?php echo esc_attr( __( 'Ping Assignee', 'pojo-bbpress-support' ) ); ?>" name="bbps_topic_ping_submit" />
					<input type="hidden" value="bbps_ping_topic" name="bbps_action"/>
					<input type="hidden" value="<?php echo $topic_id ?>" name="bbps_topic_id" />
					<input type="hidden" value="<?php echo $forum_id ?>" name="bbp_old_forum_id" />
				</form>
			</div>
		</div>
		<?php
		}
	}
}
add_action( 'bbp_template_before_single_topic', 'edd_bbp_d_ping_asignee_button' );

// adds a class and status to the front of the topic title
function edd_bbp_d_modify_title( $title, $topic_id = 0 ) {
	$topic_id = bbp_get_topic_id( $topic_id );
	if ( edd_bbp_d_topic_resolved( $topic_id ) )
		echo '<span class="resolved">[' . __( 'Resolved', 'pojo-bbpress-support' ) . '] </span>';
}
add_action( 'bbp_theme_before_topic_title', 'edd_bbp_d_modify_title' );


function edd_bbp_bbp_get_topic_class( $classes, $topic_id ) {
	$topic_id = bbp_get_topic_id( $topic_id );
	if ( edd_bbp_d_topic_resolved( $topic_id ) )
		$classes[] = 'topic-resolved';
		
	return $classes;
}
add_action( 'bbp_get_topic_class', 'edd_bbp_bbp_get_topic_class', 10, 2 );

function edd_bbp_add_topic_meta( $topic_id = 0, $topic ) {
	if ( $topic->post_type != 'topic' )
		return;

	$status = get_post_meta( $topic_id, '_bbps_topic_status', true );

	if ( ! $status )
		add_post_meta( $topic_id, '_bbps_topic_status', '1' );

	add_post_meta( $topic_id, '_bbps_topic_pending', '1' );

}
add_action( 'wp_insert_post', 'edd_bbp_add_topic_meta', 10, 2 );

function edd_bbp_d_maybe_remove_pending( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author ) {
	if ( user_can( $reply_author, 'moderate' ) ) {
		// If the new reply is posted by the assignee, remove the pending flag
		delete_post_meta( $topic_id, '_bbps_topic_pending' );
	} else {
		// If the reply is posted by anyone else, add the pending reply
		update_post_meta( $topic_id, '_bbps_topic_pending', '1' );
	}
	
	
}
add_action( 'bbp_new_reply', 'edd_bbp_d_maybe_remove_pending', 20, 5 );

function edd_bbp_d_bulk_remove_pending() {
	if ( ! current_user_can( 'moderate' ) )
		return;

	if ( empty( $_POST['tickets'] ) )
		return;

	$tickets = array_map( 'absint', $_POST['tickets'] );

	foreach ( $tickets as $ticket ) {
		delete_post_meta( $ticket, '_bbps_topic_pending' );
	}
}
add_action( 'edd_remove_ticket_pending_status', 'edd_bbp_d_bulk_remove_pending', 20, 5 );

function edd_bbp_d_bulk_close_tickets() {
	if ( ! current_user_can( 'moderate' ) )
		return;

	if ( empty( $_POST['tickets'] ) )
		return;

	$tickets = array_map( 'absint', $_POST['tickets'] );

	foreach ( $tickets as $ticket ) {
		update_post_meta( $ticket, '_bbps_topic_status', 2 );
	}
}
add_action( 'edd_pbbp_close_tickets', 'edd_bbp_d_bulk_close_tickets', 20, 5 );

function edd_bbp_d_assign_on_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author ) {

	if ( ! edd_bbp_get_topic_assignee_id( $topic_id ) && user_can( $reply_author, 'moderate' ) ) {
		update_post_meta( $topic_id, 'bbps_topic_assigned', $reply_author );
	}
}
add_action( 'bbp_new_reply', 'edd_bbp_d_assign_on_reply', 20, 5 );

function edd_bbp_d_force_remove_pending() {
	if ( ! isset( $_GET['topic_id'] ) )
		return;
	
	if ( ! isset( $_GET['bbps_action'] ) || $_GET['bbps_action'] != 'remove_pending' )
		return;
	
	if ( ! current_user_can( 'moderate' ) )
		return;

	delete_post_meta( $_GET['topic_id'], '_bbps_topic_pending' );
	wp_redirect( remove_query_arg( array( 'topic_id', 'bbps_action' ) ) ); exit;
}
add_action( 'init', 'edd_bbp_d_force_remove_pending' );

function edd_bbp_d_add_user_purchases_link() {
	if ( ! current_user_can( 'moderate' ) )
		return;

	if ( ! function_exists( 'edd_get_users_purchases' ) )
		return;

	$user_email = bbp_get_displayed_user_field( 'user_email' );

	echo '<div class="edd_users_purchases">';
	echo '<h4>' . __( 'User\'s Purchases', 'pojo-bbpress-support' ) . ':</h4>';
	switch_to_blog( 1 );
	$purchases = edd_get_users_purchases( $user_email, 100, false, 'any' );
	if ( $purchases ) :
		echo '<ul>';
		foreach ( $purchases as $purchase ) {

			echo '<li><strong><a href="' . admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $purchase->ID ) . '">#' . $purchase->ID . ' - ' . edd_get_payment_status( $purchase, true ) . '</a></strong></li>';
			$downloads = edd_get_payment_meta_downloads( $purchase->ID );
			foreach ( $downloads as $download ) {
				echo '<li>' . get_the_title( $download['id'] ) . ' - ' . date( 'F j, Y', strtotime( $purchase->post_date ) )    . '</li>';
			}

			if( function_exists( 'edd_software_licensing' ) ) {
				echo '<li><strong>' . __( 'Licenses', 'pojo-bbpress-support' ) . ':</strong></li>';
				$licenses  = edd_software_licensing()->get_licenses_of_purchase( $purchase->ID );
				if( $licenses ) {
					foreach ( $licenses as $license ) {
						echo '<li>' . get_the_title( $license->ID ) . ' - ' . edd_software_licensing()->get_license_status( $license->ID ) . '</li>';
					}
				}
				echo '<li><hr/></li>';
			}
		}
		echo '</ul>';
	else :
		echo '<p>' . __( 'This user has never purchased anything.', 'pojo-bbpress-support' ) . '</p>';
	endif;
	echo '</div>';
	
	restore_current_blog();
}
add_action( 'bbp_template_after_user_profile', 'edd_bbp_d_add_user_purchases_link' );

function edd_bbp_d_reply_and_resolve( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false ) {
	if ( isset( $_POST['bbp_reply_close'] ) ) {
		update_post_meta( $topic_id, '_bbps_topic_status', 2 );
	}

	if ( isset( $_POST['bbp_reply_open'] ) ) {
		update_post_meta( $topic_id, '_bbps_topic_status', 1 );
	}
}
add_action( 'bbp_new_reply', 'edd_bbp_d_reply_and_resolve', 0, 6 );

function edd_bbp_get_topic_assignee_id( $topic_id = NULL ) {
	if ( empty( $topic_id ) )
		$topic_id = get_the_ID();

	if ( empty( $topic_id ) )
		return false;

	$topic_assignee_id = get_post_meta( $topic_id, 'bbps_topic_assigned', true );

	return $topic_assignee_id;
}

function edd_bbp_get_topic_assignee_name( $user_id = NULL ) {
	if ( empty( $user_id ) )
		return false;

	$user_info = get_userdata( $user_id );
	$topic_assignee_name = trim( $user_info->user_firstname . ' ' . $user_info->user_lastname );

	if ( empty( $topic_assignee_name ) )
		$topic_assignee_name = $user_info->user_nicename;

	return $topic_assignee_name;
}

function edd_bbp_d_new_topic_notice() {
	if ( bbp_is_single_forum() )
		echo '<div class="bbp-template-notice"><p>' . __( 'Please search the forums for existing questions before posting a new one.', 'pojo-bbpress-support' ) . '</p></div>';
}

add_action( 'bbp_template_notices', 'edd_bbp_d_new_topic_notice' );

function edd_bbp_pojo_after_page_title() {
	if ( ! Pojo_Compatibility::is_bbpress_installed() || ! is_bbpress() )
		return;
	
	if ( current_user_can( 'moderate' ) ) :
		echo '<a href="' . get_permalink( pojo_get_option( 'pojo_support_panel_page_id' ) ) . '" class="go-to-support-panel button">' . __( 'Support Panel', 'pojo-bbpress-support' ) . '</a>';
	endif;
}
add_action( 'pojo_after_page_title', 'edd_bbp_pojo_after_page_title' );

function edd_bbp_pojo_is_topic_closed( $closed, $topic_id ) {
	if ( ! $closed && edd_bbp_d_topic_resolved( bbp_get_topic_id( $topic_id ) ) ) {
		$is_topic_author = bbp_get_topic_author_id( $topic_id ) === get_current_user_id();
		if ( ! current_user_can( 'moderate' ) && ! $is_topic_author ) {
			$closed = true;
		}
	}
	return $closed;
}
add_filter( 'bbp_is_topic_closed', 'edd_bbp_pojo_is_topic_closed', 50, 2 );

function edd_bbp_d_reopen_topic_to_write( $reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false ) {
	$is_topic_author = bbp_get_topic_author_id( $topic_id ) === get_current_user_id();
	
	$topic_status = get_post_meta( $topic_id, '_bbps_topic_status', true );
	if ( 2 === (int) $topic_status && $is_topic_author )
		update_post_meta( $topic_id, '_bbps_topic_status', '1' );
}
add_action( 'bbp_new_reply', 'edd_bbp_d_reopen_topic_to_write', 50, 6 );

function edd_bbp_pojo_is_forum_closed( $closed, $forum_id, $check_ancestors ) {
	if ( ! $closed ) {
		if ( ! current_user_can( 'moderate' ) && ! edd_bbp_d_is_user_can_write_in_forum( get_current_user_id() ) ) {
			$closed = true;
		}
	}
	return $closed;
}
add_action( 'bbp_is_forum_closed', 'edd_bbp_pojo_is_forum_closed', 50, 3 );

function edd_bbp_close_old_tickets() {
	$args = array(
		'post_type' => 'topic',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => '_bbps_topic_status',
				'value' => '1', // Open tickets only
			),
			array(
				'key' => '_bbps_topic_pending',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key' => '_bbp_last_active_time',
				'value' => date( 'Y-n-d H:i:s', strtotime( '-7 days' ) ),
				'compare' => '<=', // Tickets older than nine days
				'type' => 'DATETIME'
			),
		),
		'posts_per_page' => 300,
		'post_parent' => pojo_get_option( 'pojo_support_forum_id' ),
	);
	$tickets = get_posts( $args );
	if ( $tickets ) {
		foreach ( $tickets as $ticket ) {
			update_post_meta( $ticket->ID, '_bbps_topic_status', 2 );
		}
	}
}
add_action( 'edd_daily_scheduled_events', 'edd_bbp_close_old_tickets' );

function edd_bbp_pojo_messages() {
	if ( ! Pojo_Compatibility::is_bbpress_installed() || ! is_bbpress() )
		return;
	
	$msg = '';
	
	if ( ! is_user_logged_in() ) {
		$msg = __( 'The support forum is open for members only. To add a topic or reply in the forum, please <a href="http://pojo.me/my-account/login/">login</a> or <a href="http://pojo.me/my-account/register/">register</a>.', 'pojo-bbpress-support' );
	} else {
		$status = edd_bbp_d_get_current_license_status( get_current_user_id() );
		if ( 'none' === $status ) {
			$msg = __( 'The support forum is open for members only. To add a topic or reply in the forum, please purchase one of our products.', '' );
		} elseif ( 'expired' === $status ) {
			$msg = __( 'The support forum is open for members only. Your subscription has expired, please <a href="http://pojo.me/my-account/">renew</a> it in order to add a topic or reply in the forum.', 'pojo-bbpress-support' );
		}
	}
	
	if ( ! empty( $msg ) ) {
		pojo_alert( $msg, false, false, 'info' );
	}
}
add_action( 'pojo_after_page_title', 'edd_bbp_pojo_messages', 20 );

function edd_bbp_pojo_register_support_sidebar() {
	register_sidebar( array(
		'id'            => 'pojo-' . sanitize_title( 'Forum Support' ),
		'name'          => __( 'Forum Support', 'pojo-bbpress-support' ),
		'description'   => __( 'These are widgets for the Forum Support', 'pojo-bbpress-support' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-inner">',
		'after_widget'  => '</div></section>',
		'before_title'  => '<h5 class="widget-title"><span>',
		'after_title'   => '</span></h5>',
	) );
}
add_action( 'widgets_init', 'edd_bbp_pojo_register_support_sidebar' );

function edd_bbp_pojo_display_sidebar() {
	if ( ! Pojo_Compatibility::is_bbpress_installed() || ! is_bbpress() )
		return;
	
	if ( ! edd_bbp_d_is_user_can_write_in_forum( get_current_user_id() ) )
		return;

	dynamic_sidebar( 'pojo-' . sanitize_title( 'Forum Support' ) );
	
	echo '<a href="#new-post">' . __( 'New Post', 'pojo-bbpress-support' ) . '</a>';
}
add_action( 'pojo_after_page_title', 'edd_bbp_pojo_display_sidebar', 30 );