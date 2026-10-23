<?php
/**
 * Contact form: Contact Form 7 renders the form; its markup lives in
 * cf7/contact-form.html so the fields keep the design's layout.
 */

// The form template is hand-written HTML; CF7 must not add <p>/<br>.
add_filter( 'wpcf7_autop_or_not', '__return_false' );

/** ID of the CF7 contact form chosen in Customizer, if CF7 is active. */
function ta_contact_form_id() {
	if ( ! function_exists( 'wpcf7_contact_form' ) ) {
		return 0;
	}
	$id = (int) get_theme_mod( 'ta_cf7_id', 0 );
	return ( $id && wpcf7_contact_form( $id ) ) ? $id : 0;
}

add_filter(
	'wpcf7_form_class_attr',
	function ( $class ) {
		return $class . ' ta-form';
	}
);
