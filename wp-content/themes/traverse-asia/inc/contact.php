<?php
/**
 * Contact form: Contact Form 7 renders the form; its markup lives in
 * cf7/contact-form.html so the fields keep the design's layout.
 */

// The form template is hand-written HTML; CF7 must not add <p>/<br>.
add_filter( 'wpcf7_autop_or_not', '__return_false' );

/** ID of the CF7 contact form for the current language (Japanese or English), if CF7 is active. */
function ta_contact_form_id() {
	if ( ! function_exists( 'wpcf7_contact_form' ) ) {
		return 0;
	}
	$id = (int) get_theme_mod( ta_is_en() ? 'ta_cf7_id_en' : 'ta_cf7_id', 0 );
	return ( $id && wpcf7_contact_form( $id ) ) ? $id : 0;
}

add_filter(
	'wpcf7_form_class_attr',
	function ( $class ) {
		return $class . ' ta-form';
	}
);

/**
 * CF7 puts the Turnstile check at the top of a form whose template has no
 * [turnstile] tag; show it just above the consent and send button instead.
 */
add_filter(
	'wpcf7_form_elements',
	function ( $html ) {
		if ( preg_match( '#<div [^>]*class="wpcf7-turnstile cf-turnstile"[^>]*></div>#', $html, $m ) && false !== strpos( $html, '<span class="consent">' ) ) {
			$html = str_replace( $m[0], '', $html );
			$html = preg_replace( '#<span class="consent">#', $m[0] . '<span class="consent">', $html, 1 );
		}
		return $html;
	},
	20
);
