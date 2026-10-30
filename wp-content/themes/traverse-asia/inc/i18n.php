<?php
/**
 * English pages. The English site is the page /en/ and its child pages; they
 * are built from the same section blocks as the Japanese pages. Here: which
 * language a request is in, the matching page in the other language, the
 * lang attribute and hreflang links.
 */

/** Japanese page path => English page path (page paths without slashes at the ends). */
function ta_lang_pairs() {
	return array(
		''                      => 'en',
		'services'              => 'en/services',
		'services/create'       => 'en/services/create',
		'services/market-design' => 'en/services/market-design',
		'services/connect'      => 'en/services/connect',
		'services/sell'         => 'en/services/sell',
		'works'                 => 'en/works',
		'brands/shizune'        => 'en/shizune',
		'company'               => 'en/about',
		'contact'               => 'en/contact',
		'contact/thanks'        => 'en/contact/thanks',
	);
}

/** True on /en/ and the pages under it. */
function ta_is_en() {
	if ( ! is_page() ) {
		return false;
	}
	$uri = get_page_uri( get_queried_object_id() );
	return 'en' === $uri || 0 === strpos( (string) $uri, 'en/' );
}

/** $ja on Japanese pages, $en on English pages. */
function ta_t( $ja, $en ) {
	return ta_is_en() ? $en : $ja;
}

/** Home of the current language. */
function ta_home() {
	return ta_is_en() ? home_url( '/en/' ) : home_url( '/' );
}

/** Path of the current request in ta_lang_pairs() terms, or null. */
function ta_current_path() {
	if ( is_front_page() ) {
		return '';
	}
	if ( is_post_type_archive( 'works' ) ) {
		return 'works';
	}
	if ( is_page() ) {
		return get_page_uri( get_queried_object_id() );
	}
	return null;
}

/** URL of a published page path ('' = top), or ''. */
function ta_page_url( $path ) {
	if ( '' === $path ) {
		return home_url( '/' );
	}
	if ( 'works' === $path ) {
		return get_post_type_archive_link( 'works' );
	}
	$page = get_page_by_path( $path, OBJECT, array( 'page' ) );
	return ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : '';
}

/** True once the English home page is published. */
function ta_en_ready() {
	return '' !== ta_page_url( 'en' );
}

/** The same page in the other language: [ja URL, en URL]; a missing side falls back to that language's home. */
function ta_lang_urls() {
	$path  = ta_current_path();
	$pairs = ta_lang_pairs();
	$ja    = home_url( '/' );
	$en    = ta_en_ready() ? ta_page_url( 'en' ) : '';
	if ( null !== $path ) {
		if ( isset( $pairs[ $path ] ) ) {
			$ja = ta_page_url( $path ) ?: $ja;
			$en = ta_page_url( $pairs[ $path ] ) ?: $en;
		} elseif ( false !== ( $ja_path = array_search( $path, $pairs, true ) ) ) {
			$ja = ta_page_url( $ja_path ) ?: $ja;
			$en = ta_page_url( $path ) ?: $en;
		}
	}
	return array( $ja, $en );
}

add_filter(
	'language_attributes',
	function ( $output ) {
		return ta_is_en() ? preg_replace( '/lang="[^"]*"/', 'lang="en"', $output ) : $output;
	}
);

// hreflang links between a page and its translation.
add_action(
	'wp_head',
	function () {
		$path  = ta_current_path();
		$pairs = ta_lang_pairs();
		if ( null === $path || ( ! isset( $pairs[ $path ] ) && ! in_array( $path, $pairs, true ) ) ) {
			return;
		}
		$ja_path = isset( $pairs[ $path ] ) ? $path : array_search( $path, $pairs, true );
		$ja      = ta_page_url( $ja_path );
		$en      = ta_page_url( $pairs[ $ja_path ] );
		if ( ! $ja || ! $en || 'contact/thanks' === $ja_path ) {
			return;
		}
		printf( '<link rel="alternate" hreflang="ja" href="%s">' . "\n", esc_url( $ja ) );
		printf( '<link rel="alternate" hreflang="en" href="%s">' . "\n", esc_url( $en ) );
		printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( $ja ) );
	},
	3
);

/** Key of the page's initial design content: the slug, or "en-…" for English pages. */
function ta_page_design_key( $post = null ) {
	$post = get_post( $post );
	$uri  = get_page_uri( $post );
	if ( 'en' === $uri || 0 === strpos( (string) $uri, 'en/' ) ) {
		return str_replace( '/', '-', $uri );
	}
	return $post->post_name;
}
