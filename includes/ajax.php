<?php
defined( 'ABSPATH' ) || exit;

/**
 * AJAX: Create assistant user.
 */
add_action( 'wp_ajax_your_assistant_create', function() {
	check_ajax_referer( 'your_assistant_create', '_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Not allowed.' );
	}

	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	if ( empty( $name ) ) {
		wp_send_json_error( 'Name is required.' );
	}

	// Check if assistant already exists.
	$existing = get_users( array( 'role' => 'ai_assistant', 'number' => 1 ) );
	if ( ! empty( $existing ) ) {
		wp_send_json_error( 'An assistant already exists.' );
	}

	$username = sanitize_user( strtolower( str_replace( ' ', '_', $name ) ) . '_assistant' );
	$email    = $username . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

	$user_id = wp_insert_user( array(
		'user_login'   => $username,
		'user_email'   => $email,
		'display_name' => $name,
		'role'         => 'ai_assistant',
		'user_pass'    => wp_generate_password( 32 ),
	) );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( $user_id->get_error_message() );
	}

	$avatar = sanitize_text_field( wp_unslash( $_POST['avatar'] ?? '🤖' ) );
	update_user_meta( $user_id, 'ya_avatar_emoji', $avatar );

	$context = array(
		'vibe'          => sanitize_text_field( wp_unslash( $_POST['vibe'] ?? '' ) ),
		'goals'         => sanitize_textarea_field( wp_unslash( $_POST['goals'] ?? '' ) ),
		'extra'         => sanitize_textarea_field( wp_unslash( $_POST['extra'] ?? '' ) ),
		'site_title'    => sanitize_text_field( wp_unslash( $_POST['site_title'] ?? '' ) ),
		'site_tagline'  => sanitize_text_field( wp_unslash( $_POST['site_tagline'] ?? '' ) ),
		'about_content' => sanitize_textarea_field( wp_unslash( $_POST['about_content'] ?? '' ) ),
		'created'       => current_time( 'mysql' ),
	);
	update_user_meta( $user_id, 'ya_context', $context );

	// Initialize activity log.
	update_user_meta( $user_id, 'ya_activity_log', array(
		array(
			'date'   => current_time( 'Y-m-d H:i' ),
			'action' => 'Came to life 🎉',
		),
	) );

	wp_send_json_success( array(
		'user_id'  => $user_id,
		'chat_url' => admin_url( 'users.php?page=your-assistant-chat' ),
	) );
} );

/**
 * AJAX: Mock chat action (log activity).
 */
add_action( 'wp_ajax_your_assistant_mock_action', function() {
	check_ajax_referer( 'your_assistant_chat', '_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Not allowed.' );
	}

	$user_id = intval( $_POST['assistant_id'] ?? 0 );
	$action  = sanitize_text_field( wp_unslash( $_POST['mock_action'] ?? '' ) );

	if ( ! $user_id || ! $action ) {
		wp_send_json_error( 'Missing data.' );
	}

	$log = get_user_meta( $user_id, 'ya_activity_log', true ) ?: array();
	$log[] = array(
		'date'   => current_time( 'Y-m-d H:i' ),
		'action' => $action,
	);
	update_user_meta( $user_id, 'ya_activity_log', $log );

	wp_send_json_success();
} );
