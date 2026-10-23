<?php
/**
 * Theme supports, assets and head output.
 */

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		add_image_size( 'ta-card', 960, 600, true );
		// The block editor previews sections with the site's own styles.
		add_theme_support( 'editor-styles' );
		add_editor_style(
			array(
				'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Instrument+Sans:wght@400;500;600&family=Noto+Sans+JP:wght@400;500;700&family=Shippori+Mincho+B1:wght@600;800&display=swap',
				'assets/css/design.generated.css',
				'assets/css/main.css',
				'assets/css/editor.css',
			)
		);
		add_image_size( 'ta-thumb', 540, 360, true );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'ta-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Instrument+Sans:wght@400;500;600&family=Noto+Sans+JP:wght@400;500;700&family=Shippori+Mincho+B1:wght@600;800&display=swap', array(), null );
		wp_enqueue_style( 'ta-design', TA_URI . '/assets/css/design.generated.css', array(), TA_VERSION );
		wp_enqueue_style( 'ta-main', TA_URI . '/assets/css/main.css', array( 'ta-design' ), TA_VERSION );
		wp_enqueue_script( 'ta-main', TA_URI . '/assets/js/main.js', array(), TA_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
);

add_filter(
	'wp_resource_hints',
	function ( $urls, $type ) {
		if ( 'preconnect' === $type ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		}
		return $urls;
	},
	10,
	2
);

// Motion is opt-in: main.js animates only when <html> has .m (set before first paint).
add_action(
	'wp_head',
	function () {
		echo '<script>if(!(window.matchMedia&&matchMedia("(prefers-reduced-motion: reduce)").matches)&&"IntersectionObserver" in window){document.documentElement.classList.add("m","pre");setTimeout(function(){if(!window.taMotion)document.documentElement.classList.remove("m","pre")},3000)}</script>' . "\n";
	},
	1
);


// The design has no WordPress emoji, block library or global styles on the front end.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_singular( array( 'post', 'news', 'works' ) ) ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'global-styles' );
		}
	},
	100
);

// Pages flagged by tools/setup-site.php (the form thanks page) stay out of search results.
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_singular() && get_post_meta( get_queried_object_id(), '_ta_noindex', true ) ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

// /brands/ has no page of its own yet; send visitors to the one brand.
add_action(
	'template_redirect',
	function () {
		if ( is_page( 'brands' ) && ! get_post_field( 'post_parent', get_queried_object_id() ) ) {
			wp_safe_redirect( home_url( '/brands/shizune/' ), 302 );
			exit;
		}
	}
);
