<?php
/**
 * Header and footer menus, editable in 外観 → メニュー.
 * The 相談する button and the JP / EN switch stay fixed in header.php.
 */

/** Default items: label => site path. Used to fill the menus the first time. */
function ta_default_menu( $location ) {
	$items = array(
		'サービス'   => '/services/',
		'実績'     => '/works/',
		'自社ブランド' => '/brands/shizune/',
		'会社情報'   => '/company/',
	);
	if ( 'footer' === $location ) {
		$items += array(
			'採用情報'       => '/recruit/',
			'お問い合わせ'     => '/contact/',
			'プライバシーポリシー' => '/privacy-policy/',
		);
	}
	return $items;
}

add_action(
	'after_setup_theme',
	function () {
		register_nav_menus(
			array(
				'header' => 'ヘッダーメニュー',
				'footer' => 'フッターメニュー',
			)
		);
	}
);

// Create both menus with the default items once, so 外観 → メニュー starts
// from what the site shows instead of an empty menu.
add_action(
	'init',
	function () {
		if ( get_option( 'ta_menus_version' ) === '1' ) {
			return;
		}
		$locations = get_nav_menu_locations();
		foreach ( array( 'header' => 'ヘッダーメニュー', 'footer' => 'フッターメニュー' ) as $location => $name ) {
			if ( ! empty( $locations[ $location ] ) ) {
				continue;
			}
			$menu = wp_get_nav_menu_object( $name );
			$id   = $menu ? $menu->term_id : wp_create_nav_menu( $name );
			if ( is_wp_error( $id ) ) {
				continue;
			}
			if ( ! $menu ) {
				foreach ( ta_default_menu( $location ) as $label => $path ) {
					wp_update_nav_menu_item(
						$id,
						0,
						array(
							'menu-item-title'  => $label,
							'menu-item-url'    => home_url( $path ),
							'menu-item-type'   => 'custom',
							'menu-item-status' => 'publish',
						)
					);
				}
			}
			$locations[ $location ] = $id;
		}
		set_theme_mod( 'nav_menu_locations', $locations );
		update_option( 'ta_menus_version', '1' );
	}
);

/**
 * Prints a menu location as plain links in the design's style. Without an
 * assigned menu it prints the default items.
 */
function ta_menu_links( $location, $color ) {
	$links     = array();
	$locations = get_nav_menu_locations();
	$items     = empty( $locations[ $location ] ) ? false : wp_get_nav_menu_items( $locations[ $location ] );
	if ( $items ) {
		foreach ( $items as $item ) {
			if ( ! $item->menu_item_parent ) {
				$links[] = array( $item->title, $item->url, $item->target );
			}
		}
	} elseif ( false === $items ) {
		foreach ( ta_default_menu( $location ) as $label => $path ) {
			$links[] = array( $label, home_url( $path ), '' );
		}
	}
	foreach ( $links as $link ) {
		printf(
			'<a href="%s"%s style="color: %s; text-decoration: none;">%s</a>' . "\n",
			esc_url( $link[1] ),
			$link[2] ? ' target="' . esc_attr( $link[2] ) . '" rel="noopener"' : '',
			esc_attr( $color ),
			esc_html( $link[0] )
		);
	}
}
