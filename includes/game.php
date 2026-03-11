<?php
defined( 'ABSPATH' ) || exit;

/**
 * Register Kevin's Balcony as a hidden admin page.
 */
add_action( 'admin_menu', function() {
	add_submenu_page(
		null,
		"Kevin's Balcony",
		"Kevin's Balcony",
		'manage_options',
		'kevins-balcony',
		'your_assistant_kevins_balcony_page'
	);
} );

function your_assistant_kevins_balcony_page() {
	wp_enqueue_script(
		'kevins-balcony',
		YOUR_ASSISTANT_URL . 'includes/kevin.js',
		array(),
		YOUR_ASSISTANT_VERSION,
		true
	);
	?>
	<div class="wrap" style="max-width:600px;margin:30px auto;text-align:center;">
		<h1 style="font-size:1.6em;">🐱 Kevin's Balcony</h1>
		<p style="color:#777;margin-bottom:16px;">
			You're Kevin. You're on the wrong side of the fence — three stories up.<br>
			Dodge the hands. Don't fall. <em>Do not trust the help.</em>
		</p>
		<canvas id="kevins-balcony-canvas" width="560" height="400"
			style="border:2px solid #c3c4c7;border-radius:6px;background:#87CEEB;display:block;margin:0 auto;"></canvas>
		<p style="margin-top:12px;color:#555;font-size:0.9em;">
			← → arrow keys to move &nbsp;|&nbsp; tap left/right on mobile
		</p>
		<button id="kb-restart" class="button button-primary" style="margin-top:8px;display:none;">Play Again</button>
	</div>
	<?php
}
