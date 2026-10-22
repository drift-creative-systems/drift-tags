<?php
/**
 * Plugin Name:       Drift: Tags
 * Plugin URI:        https://driftcreativesystems.co.uk/
 * Description:       Add tracking and verification code (Google Analytics, Google Tag Manager, Meta Pixel, site verification tags and so on) to the head, the start of the body and the footer of every page, without editing theme files.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Drift Creative Systems
 * Author URI:        https://driftcreativesystems.co.uk/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       drift-creative-tags
 *
 * @package Drift_Tags
 */

defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| Duplicate install guard
|--------------------------------------------------------------------------
| A second copy (e.g. a GitHub "Source code" zip unpacked alongside the real
| folder) would fatal on redeclared functions. Bail and tell the admin instead.
*/
if ( defined( 'DRIFT_TAGS_VERSION' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Drift: Tags is installed more than once. Please delete the duplicate plugin folder.', 'drift-creative-tags' ) . '</p></div>';
		}
	);
	return;
}

define( 'DRIFT_TAGS_VERSION', '1.0.0' );
define( 'DRIFT_TAGS_FILE', __FILE__ );
define( 'DRIFT_TAGS_URL', plugin_dir_url( __FILE__ ) );
define( 'DRIFT_TAGS_OPTION', 'drift_tags_code' );
define( 'DRIFT_TAGS_OPTION_GROUP', 'drift_tags_settings' );
define( 'DRIFT_TAGS_PAGE_SLUG', 'drift-creative-tags' );

/**
 * NOTE ON ESCAPING (read before "fixing" this):
 * This plugin's whole purpose is to let a trusted administrator add raw
 * <script>, <noscript>, <meta> and <link> tags to the front end, so the saved
 * code is printed unescaped. esc_html()/wp_kses_post() would strip exactly
 * the tags it exists to output.
 *
 * The trade-off is contained by:
 *  - Saving needs both the plugin capability (manage_options by default) and
 *    unfiltered_html, the core capability that already allows raw scripts in
 *    posts. On multisite only super admins have it; DISALLOW_UNFILTERED_HTML
 *    removes it for everyone.
 *  - Nonce and capability checks through the Settings API.
 *  - No public-facing input. Only an authenticated admin can set the value.
 *  - Textareas in wp-admin use esc_textarea(), so code can't break out of them.
 */

// ---------------------------------------------------------------------------
// Locations and stored code
// ---------------------------------------------------------------------------

/**
 * Where code can be added. An add-on can add locations with the
 * drift_tags_locations filter; saved code for a location that is later
 * removed is kept, not deleted.
 *
 * @return array<string, array{label: string, description: string, hook: string, priority: int, rows: int}>
 */
function drift_tags_locations() {
	$locations = array(
		'head'   => array(
			'label'       => __( 'Head', 'drift-creative-tags' ),
			'description' => __( 'Printed as early as possible inside <head> on every front-end page. Use for the Google Analytics or Google Tag Manager <script> snippet and site verification <meta> tags.', 'drift-creative-tags' ),
			'hook'        => 'wp_head',
			'priority'    => 1,
			'rows'        => 10,
		),
		'body'   => array(
			'label'       => __( 'Body', 'drift-creative-tags' ),
			'description' => __( 'Printed straight after the opening <body> tag. Use for the Google Tag Manager <noscript> snippet. Your theme must call wp_body_open(), as all default themes do.', 'drift-creative-tags' ),
			'hook'        => 'wp_body_open',
			'priority'    => 1,
			'rows'        => 6,
		),
		'footer' => array(
			'label'       => __( 'Footer', 'drift-creative-tags' ),
			'description' => __( 'Printed just before the closing </body> tag, after the scripts WordPress loads. Use for chat widgets and scripts that should not hold up the page.', 'drift-creative-tags' ),
			'hook'        => 'wp_footer',
			'priority'    => 100,
			'rows'        => 6,
		),
	);

	/**
	 * Filters the places code can be added.
	 *
	 * @param array $locations Location ID => label, description, hook, priority, rows.
	 */
	$locations = apply_filters( 'drift_tags_locations', $locations );

	return is_array( $locations ) ? $locations : array();
}

/**
 * Saved code, keyed by location ID.
 *
 * @return array<string, string>
 */
function drift_tags_get_code() {
	$code = get_option( DRIFT_TAGS_OPTION, array() );

	return is_array( $code ) ? $code : array();
}

/**
 * Capability needed to see the settings screen.
 *
 * @return string
 */
function drift_tags_capability() {
	/**
	 * Filters the capability needed to see the Drift: Tags settings.
	 * Saving also needs unfiltered_html, whatever this returns.
	 *
	 * @param string $capability Default manage_options.
	 */
	return (string) apply_filters( 'drift_tags_capability', 'manage_options' );
}

/**
 * Whether the current user may change the code.
 *
 * @return bool
 */
function drift_tags_user_can_edit() {
	return current_user_can( drift_tags_capability() ) && current_user_can( 'unfiltered_html' );
}

// ---------------------------------------------------------------------------
// Settings registration
// ---------------------------------------------------------------------------

add_action( 'admin_init', 'drift_tags_register_settings' );
/**
 * Registers the option and one field per location.
 *
 * @return void
 */
function drift_tags_register_settings() {
	register_setting(
		DRIFT_TAGS_OPTION_GROUP,
		DRIFT_TAGS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'drift_tags_sanitize_code',
			'default'           => array(),
			'show_in_rest'      => false,
		)
	);

	add_settings_section( 'drift_tags_main', '', '__return_false', DRIFT_TAGS_PAGE_SLUG );

	foreach ( drift_tags_locations() as $id => $location ) {
		add_settings_field(
			'drift_tags_' . $id,
			esc_html( $location['label'] ),
			'drift_tags_render_field',
			DRIFT_TAGS_PAGE_SLUG,
			'drift_tags_main',
			array(
				'id'        => $id,
				'location'  => $location,
				'label_for' => 'drift-tags-' . $id,
			)
		);
	}
}

/*
 * options.php checks manage_options for every option group unless told
 * otherwise, so a filtered capability could see the page but not save.
 */
add_filter( 'option_page_capability_' . DRIFT_TAGS_OPTION_GROUP, 'drift_tags_capability' );

/**
 * Trims each location's code. Deliberately does not strip tags: see the
 * escaping note above. Without permission the saved code is kept as it was.
 *
 * @param mixed $input Submitted values.
 * @return array<string, string>
 */
function drift_tags_sanitize_code( $input ) {
	$saved = drift_tags_get_code();

	if ( ! drift_tags_user_can_edit() ) {
		add_settings_error( DRIFT_TAGS_OPTION, 'drift_tags_no_permission', __( 'You need permission to add unfiltered HTML to change this code. Nothing was saved.', 'drift-creative-tags' ) );
		return $saved;
	}

	if ( ! is_array( $input ) ) {
		return $saved;
	}

	$clean = $saved;
	foreach ( array_keys( drift_tags_locations() ) as $id ) {
		$clean[ $id ] = ( isset( $input[ $id ] ) && is_string( $input[ $id ] ) ) ? trim( $input[ $id ] ) : '';
	}

	return $clean;
}

// ---------------------------------------------------------------------------
// Admin screen
// ---------------------------------------------------------------------------

add_action( 'admin_menu', 'drift_tags_add_menu' );
/**
 * Adds Settings → Drift: Tags.
 *
 * @return void
 */
function drift_tags_add_menu() {
	add_options_page(
		__( 'Drift: Tags', 'drift-creative-tags' ),
		__( 'Drift: Tags', 'drift-creative-tags' ),
		drift_tags_capability(),
		DRIFT_TAGS_PAGE_SLUG,
		'drift_tags_render_settings_page'
	);
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'drift_tags_add_settings_link' );
/**
 * Adds a Settings link on the Plugins screen.
 *
 * @param array $links Action links.
 * @return array
 */
function drift_tags_add_settings_link( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . DRIFT_TAGS_PAGE_SLUG ) ) . '">' . esc_html__( 'Settings', 'drift-creative-tags' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

add_action( 'admin_enqueue_scripts', 'drift_tags_admin_assets' );
/**
 * Styles and the core code editor, on the settings screen only.
 *
 * @param string $hook_suffix Current admin page.
 * @return void
 */
function drift_tags_admin_assets( $hook_suffix ) {
	if ( 'settings_page_' . DRIFT_TAGS_PAGE_SLUG !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style( 'drift-tags-admin', DRIFT_TAGS_URL . 'assets/admin.css', array(), DRIFT_TAGS_VERSION );

	if ( ! drift_tags_user_can_edit() ) {
		return;
	}

	// Core's CodeMirror. Returns false when the user has turned syntax highlighting off.
	$editor = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
	if ( false === $editor ) {
		return;
	}

	wp_enqueue_script( 'drift-tags-admin', DRIFT_TAGS_URL . 'assets/admin.js', array( 'jquery', 'code-editor' ), DRIFT_TAGS_VERSION, true );
	wp_localize_script( 'drift-tags-admin', 'driftTagsEditor', $editor );
}

/**
 * Drift monogram. Fixed markup; only the fill is variable.
 *
 * @param string $fill Fill colour.
 * @param int    $size Width and height in px.
 * @return string
 */
function drift_tags_mark_svg( $fill = 'currentColor', $size = 32 ) {
	$paths = array(
		'M17 12H54C77 12 91 29 91 50C91 72 76 88 54 88H45L55 76H54C70 76 79 64 79 50C79 35 69 24 54 24H29V44H17Z',
		'M17 55H29L17 67Z',
		'M41 55H54L25 88H12Z',
		'M55 76H70L59 88H43Z',
	);

	$svg = sprintf( '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 100 100" aria-hidden="true" focusable="false">', (int) $size );
	foreach ( $paths as $d ) {
		$svg .= '<path fill="' . esc_attr( $fill ) . '" d="' . esc_attr( $d ) . '"/>';
	}

	return $svg . '</svg>';
}

/**
 * One textarea per location.
 *
 * @param array $args Field arguments from add_settings_field().
 * @return void
 */
function drift_tags_render_field( $args ) {
	$id       = $args['id'];
	$location = $args['location'];
	$code     = drift_tags_get_code();
	$value    = isset( $code[ $id ] ) ? $code[ $id ] : '';
	$field_id = 'drift-tags-' . $id;
	?>
	<textarea
		name="<?php echo esc_attr( DRIFT_TAGS_OPTION . '[' . $id . ']' ); ?>"
		id="<?php echo esc_attr( $field_id ); ?>"
		class="large-text code drift-tags-code"
		rows="<?php echo (int) $location['rows']; ?>"
		aria-describedby="<?php echo esc_attr( $field_id . '-description' ); ?>"
		<?php disabled( ! drift_tags_user_can_edit() ); ?>
	><?php echo esc_textarea( $value ); ?></textarea>
	<p class="description" id="<?php echo esc_attr( $field_id . '-description' ); ?>"><?php echo esc_html( $location['description'] ); ?></p>
	<?php
}

/**
 * Settings screen.
 *
 * @return void
 */
function drift_tags_render_settings_page() {
	if ( ! current_user_can( drift_tags_capability() ) ) {
		return;
	}
	?>
	<div class="wrap drift-tags">
		<h1 class="drift-tags__title">
			<span class="drift-tags__mark"><?php echo drift_tags_mark_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed markup, attributes escaped in drift_tags_mark_svg(). ?></span>
			<?php echo esc_html( get_admin_page_title() ); ?>
		</h1>

		<?php settings_errors( DRIFT_TAGS_OPTION ); ?>

		<?php if ( ! drift_tags_user_can_edit() ) : ?>
			<div class="notice notice-warning inline">
				<p><?php esc_html_e( 'You can view this code but not change it. Saving needs the unfiltered_html capability, which on multisite only network admins have.', 'drift-creative-tags' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( DRIFT_TAGS_OPTION_GROUP ); ?>

			<div class="drift-tags__card">
				<p class="drift-tags__intro"><?php esc_html_e( 'Only add code from sources you trust. It runs, unescaped, on every front-end page. If it loads a third-party service, make sure your privacy policy and cookie consent cover it.', 'drift-creative-tags' ); ?></p>
				<?php do_settings_sections( DRIFT_TAGS_PAGE_SLUG ); ?>
			</div>

			<?php
			/**
			 * Fires after the code fields, inside the settings form.
			 */
			do_action( 'drift_tags_settings_after_fields' );

			if ( drift_tags_user_can_edit() ) {
				submit_button();
			}
			?>
		</form>
	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Front-end output
// ---------------------------------------------------------------------------

add_action( 'init', 'drift_tags_hook_locations' );
/**
 * Hooks each location that has code. Runs on init so add-ons loaded after
 * this plugin can still register locations.
 *
 * @return void
 */
function drift_tags_hook_locations() {
	if ( is_admin() ) {
		return;
	}

	$code = drift_tags_get_code();

	foreach ( drift_tags_locations() as $id => $location ) {
		if ( empty( $code[ $id ] ) || empty( $location['hook'] ) ) {
			continue;
		}

		add_action(
			$location['hook'],
			function () use ( $id ) {
				drift_tags_print( $id );
			},
			isset( $location['priority'] ) ? (int) $location['priority'] : 10
		);
	}
}

/**
 * Prints one location's code.
 *
 * @param string $id Location ID.
 * @return void
 */
function drift_tags_print( $id ) {
	$code  = drift_tags_get_code();
	$value = isset( $code[ $id ] ) ? $code[ $id ] : '';

	/**
	 * Filters the code printed at a location. Return an empty string to skip it.
	 *
	 * @param string $value Saved code.
	 * @param string $id    Location ID.
	 */
	$value = (string) apply_filters( 'drift_tags_output', $value, $id );

	if ( '' === $value ) {
		return;
	}

	echo "\n" . $value . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional; see the escaping note at the top of this file.
}
