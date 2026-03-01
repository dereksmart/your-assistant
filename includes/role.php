<?php
defined( 'ABSPATH' ) || exit;

function your_assistant_register_role() {
	add_role( 'ai_assistant', __( 'AI Assistant', 'your-assistant' ), array(
		'read'                 => true,
		'edit_posts'           => true,
		'edit_others_posts'    => true,
		'edit_published_posts' => true,
		'publish_posts'        => true,
		'edit_pages'           => true,
		'edit_others_pages'    => true,
		'edit_published_pages' => true,
		'upload_files'         => true,
	) );
}

add_action( 'init', function() {
	if ( ! get_role( 'ai_assistant' ) ) {
		your_assistant_register_role();
	}
} );
