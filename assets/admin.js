/**
 * Drift: Tags settings screen. Turns each code textarea into core's
 * CodeMirror editor; settings come from wp_enqueue_code_editor().
 */
( function ( $ ) {
	'use strict';

	$( function () {
		if ( ! window.wp || ! wp.codeEditor || ! window.driftTagsEditor ) {
			return;
		}

		$( '.drift-tags-code' ).each( function () {
			wp.codeEditor.initialize( this, window.driftTagsEditor );
		} );
	} );
}( jQuery ) );
