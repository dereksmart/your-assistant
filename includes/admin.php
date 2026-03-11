<?php
defined( 'ABSPATH' ) || exit;

/**
 * Add "Create Your Assistant" button on Users page.
 */
add_action( 'admin_notices', function() {
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'users' ) {
		return;
	}
	// Check if assistant already exists.
	$existing = get_users( array( 'role' => 'ai_assistant', 'number' => 1 ) );
	if ( ! empty( $existing ) ) {
		return;
	}
	?>
	<div class="notice notice-info" style="display:flex;align-items:center;gap:12px;padding:12px 16px;">
		<span class="dashicons dashicons-superhero" style="font-size:24px;color:#2271b1;"></span>
		<div style="flex:1;">
			<strong><?php esc_html_e( 'Want a hand from Dolly?', 'your-assistant' ); ?></strong>
			<p style="margin:2px 0 0;"><?php esc_html_e( 'Create an AI assistant who knows your site and can help you manage it.', 'your-assistant' ); ?></p>
		</div>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=your-assistant-setup' ) ); ?>" class="button button-primary">
			<?php esc_html_e( 'Create Your Assistant', 'your-assistant' ); ?>
		</a>
	</div>
	<?php
} );

/**
 * Register admin pages.
 */
add_action( 'admin_menu', function() {
	// Setup page (hidden from menu).
	add_submenu_page(
		null,
		__( 'Create Your Assistant', 'your-assistant' ),
		__( 'Create Your Assistant', 'your-assistant' ),
		'manage_options',
		'your-assistant-setup',
		'your_assistant_setup_page'
	);

	// Chat page under Users.
	$assistant = get_users( array( 'role' => 'ai_assistant', 'number' => 1 ) );
	if ( ! empty( $assistant ) ) {
		$name = $assistant[0]->display_name;
		add_users_page(
			$name,
			'💬 ' . esc_html( $name ),
			'manage_options',
			'your-assistant-chat',
			'your_assistant_chat_page'
		);
	}
} );

/**
 * Setup wizard page.
 */
function your_assistant_setup_page() {
	// Get site context for step 3.
	$site_title   = get_bloginfo( 'name' );
	$site_tagline = get_bloginfo( 'description' );
	$about_page   = get_page_by_path( 'about' );
	$about_content = $about_page ? wp_strip_all_tags( $about_page->post_content ) : '';
	$about_content = mb_substr( $about_content, 0, 500 );

	$avatars = array(
		'🤖', '🧠', '✨', '🦊', '🐙', '🎯', '🌟', '🔮',
		'🦉', '🐝', '🎨', '🚀', '💡', '🌿', '🐱', '🦄',
	);
	?>
	<div class="wrap">
		<div id="your-assistant-setup" style="max-width:520px;margin:40px auto;">
			<!-- Step 1: Name -->
			<div class="ya-step" id="ya-step-1">
				<h2 style="font-size:1.5em;"><?php esc_html_e( 'What do you want to call them?', 'your-assistant' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Pick a name for your assistant. They\'ll show up as a real user on your site.', 'your-assistant' ); ?></p>
				<input type="text" id="ya-name" class="regular-text" placeholder="e.g. Scout, Sage, Buddy..." style="font-size:1.1em;padding:8px 12px;margin:16px 0;" autofocus />
				<br/>
				<button class="button button-primary button-hero" id="ya-next-1" disabled><?php esc_html_e( 'Next →', 'your-assistant' ); ?></button>
			</div>

			<!-- Step 2: Avatar -->
			<div class="ya-step" id="ya-step-2" style="display:none;">
				<h2 style="font-size:1.5em;"><span id="ya-name-display"></span><?php esc_html_e( ' needs a face', 'your-assistant' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Pick an avatar for your assistant.', 'your-assistant' ); ?></p>
				<div id="ya-avatars" style="display:grid;grid-template-columns:repeat(8,1fr);gap:8px;margin:16px 0;">
					<?php foreach ( $avatars as $i => $emoji ) : ?>
						<button class="ya-avatar-btn button" data-avatar="<?php echo esc_attr( $emoji ); ?>" style="font-size:28px;padding:8px;line-height:1;<?php echo $i === 0 ? 'box-shadow:0 0 0 2px #2271b1;' : ''; ?>">
							<?php echo $emoji; ?>
						</button>
					<?php endforeach; ?>
				</div>
				<button class="button button-primary button-hero" id="ya-next-2"><?php esc_html_e( 'Next →', 'your-assistant' ); ?></button>
			</div>

			<!-- Step 3: Context -->
			<div class="ya-step" id="ya-step-3" style="display:none;">
				<h2 style="font-size:1.5em;"><?php esc_html_e( 'What should ', 'your-assistant' ); ?><span id="ya-name-display-2"></span><?php esc_html_e( ' know about you?', 'your-assistant' ); ?></h2>

				<?php if ( $site_title || $site_tagline ) : ?>
				<div style="background:#f0f0f1;padding:12px 16px;border-radius:4px;margin:12px 0;">
					<strong><?php esc_html_e( 'From your site:', 'your-assistant' ); ?></strong><br/>
					<?php if ( $site_title ) : ?>
						📌 <?php echo esc_html( $site_title ); ?><br/>
					<?php endif; ?>
					<?php if ( $site_tagline ) : ?>
						📝 <?php echo esc_html( $site_tagline ); ?><br/>
					<?php endif; ?>
					<?php if ( $about_content ) : ?>
						📄 <?php echo esc_html( mb_substr( $about_content, 0, 120 ) ); ?>…
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<label for="ya-vibe" style="display:block;margin-top:16px;font-weight:600;"><?php esc_html_e( 'What\'s the vibe?', 'your-assistant' ); ?></label>
				<select id="ya-vibe" style="width:100%;margin:6px 0 12px;">
					<option value="casual"><?php esc_html_e( 'Casual & friendly', 'your-assistant' ); ?></option>
					<option value="professional"><?php esc_html_e( 'Professional & polished', 'your-assistant' ); ?></option>
					<option value="playful"><?php esc_html_e( 'Playful & creative', 'your-assistant' ); ?></option>
					<option value="minimal"><?php esc_html_e( 'Minimal & efficient', 'your-assistant' ); ?></option>
				</select>

				<label for="ya-goals" style="display:block;font-weight:600;"><?php esc_html_e( 'What do you want help with?', 'your-assistant' ); ?></label>
				<textarea id="ya-goals" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'e.g. Writing blog posts, keeping the site updated, SEO...', 'your-assistant' ); ?>" style="margin:6px 0 12px;"></textarea>

				<label for="ya-extra" style="display:block;font-weight:600;"><?php esc_html_e( 'Anything else?', 'your-assistant' ); ?></label>
				<textarea id="ya-extra" rows="2" class="large-text" placeholder="<?php esc_attr_e( 'Favorite topics, things to avoid, whatever you want...', 'your-assistant' ); ?>" style="margin:6px 0 16px;"></textarea>

				<button class="button button-primary button-hero" id="ya-create">
					<span class="dashicons dashicons-superhero" style="margin-top:4px;"></span>
					<?php esc_html_e( ' Bring them to life', 'your-assistant' ); ?>
				</button>
			</div>

			<!-- Step 4: Done -->
			<div class="ya-step" id="ya-step-4" style="display:none;text-align:center;">
				<div id="ya-done-avatar" style="font-size:64px;margin:20px 0;"></div>
				<div id="ya-done-message" style="font-size:1.2em;margin:20px 0;"></div>
				<a id="ya-done-chat" href="#" class="button button-primary button-hero"><?php esc_html_e( 'Let\'s hear it →', 'your-assistant' ); ?></a>
			</div>
		</div>
	</div>

	<script>
	jQuery(function($) {
		var name = '', avatar = '🤖';

		$('#ya-name').on('input', function() {
			name = $(this).val().trim();
			$('#ya-next-1').prop('disabled', !name);
		});

		$('#ya-next-1').on('click', function() {
			$('#ya-step-1').hide();
			$('#ya-name-display, #ya-name-display-2').text(name);
			$('#ya-step-2').fadeIn(200);
		});

		$('.ya-avatar-btn').on('click', function() {
			$('.ya-avatar-btn').css('box-shadow', 'none');
			$(this).css('box-shadow', '0 0 0 2px #2271b1');
			avatar = $(this).data('avatar');
		});

		$('#ya-next-2').on('click', function() {
			$('#ya-step-2').hide();
			$('#ya-step-3').fadeIn(200);
		});

		$('#ya-create').on('click', function() {
			var $btn = $(this);
			$btn.prop('disabled', true).text('Creating...');

			$.post(ajaxurl, {
				action: 'your_assistant_create',
				_nonce: '<?php echo wp_create_nonce( 'your_assistant_create' ); ?>',
				name: name,
				avatar: avatar,
				vibe: $('#ya-vibe').val(),
				goals: $('#ya-goals').val(),
				extra: $('#ya-extra').val(),
				site_title: <?php echo wp_json_encode( $site_title ); ?>,
				site_tagline: <?php echo wp_json_encode( $site_tagline ); ?>,
				about_content: <?php echo wp_json_encode( $about_content ); ?>
			}, function(resp) {
				if (resp.success) {
					$('#ya-step-3').hide();
					$('#ya-done-avatar').text(avatar);
					$('#ya-done-message').html(
						'<strong>' + $('<span>').text(name).html() + '</strong> noticed a few things about your site.<br/>Want to hear?'
					);
					$('#ya-done-chat').attr('href', resp.data.chat_url);
					$('#ya-step-4').fadeIn(200);
				} else {
					alert(resp.data || 'Something went wrong.');
					$btn.prop('disabled', false).text('Bring them to life');
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Chat page.
 */
function your_assistant_chat_page() {
	$assistant = get_users( array( 'role' => 'ai_assistant', 'number' => 1 ) );
	if ( empty( $assistant ) ) {
		echo '<div class="wrap"><p>' . esc_html__( 'No assistant found.', 'your-assistant' ) . '</p></div>';
		return;
	}

	$user = $assistant[0];
	$meta = get_user_meta( $user->ID, 'ya_context', true );
	$avatar = get_user_meta( $user->ID, 'ya_avatar_emoji', true ) ?: '🤖';

	wp_enqueue_script(
		'your-assistant-chat',
		YOUR_ASSISTANT_URL . 'chat-ui/dist/chat.js',
		array(),
		YOUR_ASSISTANT_VERSION,
		true
	);
	wp_enqueue_style(
		'your-assistant-chat',
		YOUR_ASSISTANT_URL . 'chat-ui/dist/chat.css',
		array(),
		YOUR_ASSISTANT_VERSION
	);

	wp_localize_script( 'your-assistant-chat', 'YourAssistantChat', array(
		'name'       => $user->display_name,
		'avatar'     => $avatar,
		'context'    => $meta ?: array(),
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'your_assistant_chat' ),
		'userId'     => $user->ID,
		'siteTitle'  => get_bloginfo( 'name' ),
	) );
	?>
	<div class="wrap">
		<h1 style="display:flex;align-items:center;gap:8px;">
			<span style="font-size:1.2em;"><?php echo esc_html( $avatar ); ?></span>
			<?php echo esc_html( $user->display_name ); ?>
		</h1>
		<div id="your-assistant-chat-root" style="margin-top:16px;max-width:720px;border:1px solid #c3c4c7;border-radius:4px;height:600px;background:#fff;"></div>
	</div>
	<?php
}

/**
 * Add info to assistant's profile page.
 */
add_action( 'show_user_profile', 'your_assistant_profile_section' );
add_action( 'edit_user_profile', 'your_assistant_profile_section' );

function your_assistant_profile_section( $user ) {
	if ( ! in_array( 'ai_assistant', $user->roles, true ) ) {
		return;
	}

	$meta = get_user_meta( $user->ID, 'ya_context', true );
	$avatar = get_user_meta( $user->ID, 'ya_avatar_emoji', true ) ?: '🤖';
	$activity = get_user_meta( $user->ID, 'ya_activity_log', true ) ?: array();
	?>
	<h2><?php echo esc_html( $avatar ); ?> <?php esc_html_e( 'Assistant Profile', 'your-assistant' ); ?></h2>
	<table class="form-table">
		<?php if ( ! empty( $meta['vibe'] ) ) : ?>
		<tr>
			<th><?php esc_html_e( 'Vibe', 'your-assistant' ); ?></th>
			<td><?php echo esc_html( ucfirst( $meta['vibe'] ) ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( ! empty( $meta['site_title'] ) ) : ?>
		<tr>
			<th><?php esc_html_e( 'Knows about', 'your-assistant' ); ?></th>
			<td>
				<?php echo esc_html( $meta['site_title'] ); ?>
				<?php if ( ! empty( $meta['site_tagline'] ) ) : ?>
					— <?php echo esc_html( $meta['site_tagline'] ); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( ! empty( $meta['goals'] ) ) : ?>
		<tr>
			<th><?php esc_html_e( 'Helping with', 'your-assistant' ); ?></th>
			<td><?php echo esc_html( $meta['goals'] ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( ! empty( $activity ) ) : ?>
		<tr>
			<th><?php esc_html_e( 'Recent Activity', 'your-assistant' ); ?></th>
			<td>
				<ul style="margin:0;">
				<?php foreach ( array_slice( array_reverse( $activity ), 0, 10 ) as $entry ) : ?>
					<li><?php echo esc_html( $entry['date'] . ' — ' . $entry['action'] ); ?></li>
				<?php endforeach; ?>
				</ul>
			</td>
		</tr>
		<?php endif; ?>
	</table>
	<?php
}

/**
 * Custom column for assistant role in users list.
 */
add_filter( 'manage_users_columns', function( $columns ) {
	return $columns;
} );

/**
 * Use emoji avatar for assistant users in the list.
 */
add_filter( 'get_avatar', function( $avatar, $id_or_email ) {
	$user_id = 0;
	if ( is_numeric( $id_or_email ) ) {
		$user_id = (int) $id_or_email;
	} elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
		$user_id = (int) $id_or_email->user_id;
	} elseif ( is_string( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
		if ( $user ) {
			$user_id = $user->ID;
		}
	}

	if ( $user_id ) {
		$user = get_userdata( $user_id );
		if ( $user && in_array( 'ai_assistant', $user->roles, true ) ) {
			$emoji = get_user_meta( $user_id, 'ya_avatar_emoji', true ) ?: '🤖';
			return '<span class="ya-emoji-avatar" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;font-size:24px;border-radius:50%;background:#f0f0f1;">' . esc_html( $emoji ) . '</span>';
		}
	}
	return $avatar;
}, 10, 2 );
