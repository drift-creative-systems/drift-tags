<?php
/**
 * Removes the saved code when the plugin is deleted.
 *
 * @package Drift_Tags
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( is_multisite() ) {
	$drift_tags_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $drift_tags_site_ids as $drift_tags_site_id ) {
		switch_to_blog( $drift_tags_site_id );
		delete_option( 'drift_tags_code' );
		restore_current_blog();
	}
} else {
	delete_option( 'drift_tags_code' );
}
