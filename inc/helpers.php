<?php
/**
 * Small helpers used by the templates (most of them by the generated design parts).
 */

/** Escaped URL for a site path, e.g. ta_url( '/works/' ). */
function ta_url( $path = '/' ) {
	return esc_url( home_url( $path ) );
}

/** Escaped URL of an image bundled with the theme. */
function ta_img( $file ) {
	return esc_url( TA_URI . '/assets/img/' . $file );
}

/** EN link: the English version of the current page (Customizer can name another URL). */
function ta_en_url() {
	$custom = get_theme_mod( 'ta_en_url', '' );
	if ( $custom ) {
		return $custom;
	}
	$en = ta_lang_urls()[1];
	return $en ?: home_url( '/' );
}

/** JP link: the Japanese version of the current page. */
function ta_ja_url() {
	return ta_lang_urls()[0];
}

/** URL of the company brochure PDF: the one bundled with the theme, unless Customizer names another. */
function ta_pdf_url() {
	return get_theme_mod( 'ta_pdf_url', '' ) ?: TA_URI . '/assets/pdf/traverse-asia-company-profile.pdf';
}

/** Date in the design's 2026.05.26 format. */
function ta_date( $post = null ) {
	return get_the_date( 'Y.m.d', $post );
}

/** Breadcrumb in the design's style. $trail is a list of [label, url|null]. */
function ta_breadcrumb( $trail ) {
	$items = array( '<a href="' . esc_url( ta_home() ) . '" style="color: #45524E;">' . esc_html( ta_t( 'トップ', 'Home' ) ) . '</a>' );
	foreach ( $trail as $crumb ) {
		$label   = esc_html( $crumb[0] );
		$items[] = empty( $crumb[1] ) ? '<span>' . $label . '</span>' : '<a href="' . esc_url( $crumb[1] ) . '" style="color: #45524E;">' . $label . '</a>';
	}
	echo '<nav class="pad" aria-label="' . esc_attr( ta_t( 'パンくず', 'Breadcrumb' ) ) . '" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 8px 40px 0; font-size: 13px; color: #45524E;">' . implode( '　/　', $items ) . '</nav>';
}

/** Shared CTA panel shown at the bottom of most pages. */
function ta_cta( $lines = array( 'まずは、', '相談する。' ) ) {
	get_template_part( 'template-parts/cta', null, array( 'lines' => $lines ) );
}
