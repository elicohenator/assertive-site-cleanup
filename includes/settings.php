<?php
/**
 * Settings screen.
 *
 * @package Assertive_Site_Cleanup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature labels and help text, grouped into settings sections.
 *
 * @return array<string, array{label: string, description: string, section: string}>
 */
function awpc_settings_fields() {
	return array(
		'disable_comments'     => array(
			'label'       => __( 'Disable comments', 'assertive-site-cleanup' ),
			'description' => __( 'Closes comments and trackbacks, and removes their admin screens, dashboard widget, feeds, and REST routes.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_main',
		),
		'disable_emojis'       => array(
			'label'       => __( 'Disable emojis', 'assertive-site-cleanup' ),
			'description' => __( 'Removes the emoji detection script, inline styles, and the emoji DNS prefetch.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_main',
		),
		'clean_head'           => array(
			'label'       => __( 'Clean wp_head', 'assertive-site-cleanup' ),
			'description' => __( 'Removes the generator tag, shortlink, oEmbed discovery links, REST API link, and RSD link.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_main',
		),
		'hide_version'         => array(
			'label'       => __( 'Hide WordPress version', 'assertive-site-cleanup' ),
			'description' => __( 'Empties the generator output, including in feeds.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_main',
		),
		'generic_login_errors' => array(
			'label'       => __( 'Generic login errors', 'assertive-site-cleanup' ),
			'description' => __( 'Replaces wrong-username and wrong-password messages. Password-reset errors stay specific.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_main',
		),
		'hide_rest_users'      => array(
			'label'       => __( 'Hide users from logged-out REST requests', 'assertive-site-cleanup' ),
			'description' => __( 'Removes /wp/v2/users for visitors. Logged-in users, including the block editor, still have it.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_main',
		),
		'disable_xmlrpc'       => array(
			'label'       => __( 'Disable XML-RPC', 'assertive-site-cleanup' ),
			'description' => __( 'Turns off XML-RPC, including pingbacks. Blocking xmlrpc.php in the server config is stronger than this.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_main',
		),
		'remove_dashboard_news' => array(
			'label'       => __( 'Remove the Events and News widget', 'assertive-site-cleanup' ),
			'description' => __( 'Removes the Events and News dashboard widget and the welcome panel.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_main',
		),
		'remove_feed_links'    => array(
			'label'       => __( 'Remove RSS feed links', 'assertive-site-cleanup' ),
			'description' => __( 'Strips the site RSS link tags from the head. Leave this off if anything still subscribes to the feed.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'hide_admin_bar'       => array(
			'label'       => __( 'Hide the front-end admin bar', 'assertive-site-cleanup' ),
			'description' => __( 'Hides the toolbar on the front end for everyone except administrators.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'block_author_enum'    => array(
			'label'       => __( 'Block author enumeration', 'assertive-site-cleanup' ),
			'description' => __( 'Redirects ?author=N requests to the homepage so the login name is not revealed.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'disable_file_editor'  => array(
			'label'       => __( 'Disable the file editor', 'assertive-site-cleanup' ),
			'description' => __( 'Stops plugins and themes from being edited in wp-admin. A constant already set in wp-config.php is left as it is.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'disable_speculation'  => array(
			'label'       => __( 'Disable speculative loading', 'assertive-site-cleanup' ),
			'description' => __( 'Stops WordPress from prefetching links for logged-out visitors. Leave this off unless prefetch shows up as extra traffic.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'limit_revisions'      => array(
			'label'       => __( 'Limit post revisions', 'assertive-site-cleanup' ),
			'description' => __( 'Keeps 5 revisions per post. Revisions already stored are left as they are.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'redirect_attachments' => array(
			'label'       => __( 'Redirect attachment pages', 'assertive-site-cleanup' ),
			'description' => __( 'Sends attachment pages to the file URL.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'disable_sitemaps'     => array(
			'label'       => __( 'Disable the core sitemap', 'assertive-site-cleanup' ),
			'description' => __( 'Turns off wp-sitemap.xml. Leave this off unless an SEO plugin already publishes a sitemap.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'disable_remote_patterns' => array(
			'label'       => __( 'Stop remote block patterns', 'assertive-site-cleanup' ),
			'description' => __( 'Stops the editor from downloading the WordPress.org pattern directory.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
		'dequeue_jquery_migrate' => array(
			'label'       => __( 'Remove jQuery Migrate', 'assertive-site-cleanup' ),
			'description' => __( 'Drops jQuery Migrate on the front end. Admin screens still load it. Older themes and plugins may depend on it.', 'assertive-site-cleanup' ),
			'section'     => 'awpc_section_optional',
		),
	);
}

/**
 * Register the settings group, sections, and fields.
 */
function awpc_register_settings() {
	register_setting(
		'awpc_settings_group',
		AWPC_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'awpc_sanitize_settings',
			'default'           => awpc_defaults(),
		)
	);

	add_settings_section(
		'awpc_section_main',
		__( 'Cleanup', 'assertive-site-cleanup' ),
		'awpc_section_main_intro',
		'assertive-site-cleanup'
	);

	add_settings_section(
		'awpc_section_optional',
		__( 'Optional', 'assertive-site-cleanup' ),
		'awpc_section_optional_intro',
		'assertive-site-cleanup'
	);

	foreach ( awpc_settings_fields() as $key => $field ) {
		add_settings_field(
			'awpc_' . $key,
			$field['label'],
			'awpc_render_checkbox',
			'assertive-site-cleanup',
			$field['section'],
			array(
				'label_for'   => 'awpc-' . $key,
				'key'         => $key,
				'description' => $field['description'],
			)
		);
	}
}
add_action( 'admin_init', 'awpc_register_settings' );

/**
 * Intro for the features that are on by default.
 */
function awpc_section_main_intro() {
	echo '<p>' . esc_html__( 'On for a typical site that does not use comments.', 'assertive-site-cleanup' ) . '</p>';
}

/**
 * Intro for the opt-in features.
 */
function awpc_section_optional_intro() {
	echo '<p>' . esc_html__( 'Off by default. These can remove feeds, hide the admin bar, or turn off the file editor.', 'assertive-site-cleanup' ) . '</p>';
}

/**
 * Keep only known flags, stored as booleans.
 *
 * Unchecked boxes are missing from the request, so those flags become false.
 *
 * @param mixed $input Posted settings.
 * @return array<string, bool>
 */
function awpc_sanitize_settings( $input ) {
	$clean = array();
	$input = is_array( $input ) ? $input : array();

	foreach ( array_keys( awpc_defaults() ) as $key ) {
		$clean[ $key ] = ! empty( $input[ $key ] );
	}

	return $clean;
}

/**
 * Add the settings page under Settings.
 */
function awpc_add_settings_page() {
	add_options_page(
		__( 'Assertive Site Cleanup', 'assertive-site-cleanup' ),
		__( 'Assertive Site Cleanup', 'assertive-site-cleanup' ),
		'manage_options',
		'assertive-site-cleanup',
		'awpc_render_settings_page'
	);
}
add_action( 'admin_menu', 'awpc_add_settings_page' );

/**
 * Render one checkbox. The field title is the label, via label_for.
 *
 * @param array{label_for: string, key: string, description: string} $args Field args.
 */
function awpc_render_checkbox( $args ) {
	printf(
		'<input type="checkbox" id="%1$s" name="%2$s[%3$s]" value="1" %4$s />',
		esc_attr( $args['label_for'] ),
		esc_attr( AWPC_OPTION ),
		esc_attr( $args['key'] ),
		checked( awpc_option( $args['key'] ), true, false )
	);

	if ( '' !== $args['description'] ) {
		printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
	}
}

/**
 * Render the settings page.
 */
function awpc_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'awpc_settings_group' );
			// Present even when every box is unchecked, so the option still saves.
			printf(
				'<input type="hidden" name="%1$s[__present]" value="1" />',
				esc_attr( AWPC_OPTION )
			);
			do_settings_sections( 'assertive-site-cleanup' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
