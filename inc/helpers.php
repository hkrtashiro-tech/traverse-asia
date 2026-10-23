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

/** URL of the English site. Set in Customizer; falls back to the Japanese top. */
function ta_en_url() {
	return get_theme_mod( 'ta_en_url', '' ) ?: home_url( '/' );
}

/** URL of the company brochure PDF. Set in Customizer. */
function ta_pdf_url() {
	return get_theme_mod( 'ta_pdf_url', '' ) ?: home_url( '/contact/' );
}

/**
 * Key of the design board used for the current request. It decides which
 * page stylesheet is loaded and which generated part is rendered.
 */
function ta_view_key() {
	if ( is_front_page() ) {
		return 'front';
	}
	if ( is_404() ) {
		return '404';
	}
	if ( is_singular( 'works' ) ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		return ta_part_exists( 'works/' . $slug ) ? 'work-' . $slug : 'article';
	}
	if ( is_post_type_archive( 'works' ) || is_tax( array( 'work_step', 'work_area' ) ) ) {
		return 'works';
	}
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		return ta_part_exists( 'pages/' . $slug ) ? $slug : 'article';
	}
	if ( is_singular() ) {
		return 'article';
	}
	return 'news';
}

function ta_part_exists( $name ) {
	return file_exists( TA_DIR . '/template-parts/' . $name . '.php' );
}

/** Date in the design's 2026.05.26 format. */
function ta_date( $post = null ) {
	return get_the_date( 'Y.m.d', $post );
}

/** Breadcrumb in the design's style. $trail is a list of [label, url|null]. */
function ta_breadcrumb( $trail ) {
	$items = array( '<a href="' . ta_url( '/' ) . '" style="color: #45524E;">トップ</a>' );
	foreach ( $trail as $crumb ) {
		$label   = esc_html( $crumb[0] );
		$items[] = empty( $crumb[1] ) ? '<span>' . $label . '</span>' : '<a href="' . esc_url( $crumb[1] ) . '" style="color: #45524E;">' . $label . '</a>';
	}
	echo '<nav class="pad" aria-label="パンくず" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 8px 40px 0; font-size: 13px; color: #45524E;">' . implode( '　/　', $items ) . '</nav>';
}

/** Shared CTA panel shown at the bottom of most pages. */
function ta_cta( $lines = array( 'まずは、', '相談する。' ) ) {
	get_template_part( 'template-parts/cta', null, array( 'lines' => $lines ) );
}
