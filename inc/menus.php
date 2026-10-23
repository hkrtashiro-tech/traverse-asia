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

/** Sub-items of サービス in the header: label => [path, EN label]. */
function ta_default_service_items() {
	return array(
		'ブランドをつくる' => array( '/services/create/', '01 CREATE' ),
		'市場を設計する'  => array( '/services/market-design/', '02 DESIGN THE MARKET' ),
		'現地とつなぐ'   => array( '/services/connect/', '03 CONNECT' ),
		'実際に売る'    => array( '/services/sell/', '04 SELL' ),
	);
}

// Once: put the four services under サービス in the header menu (shown on hover).
// The EN label is the menu item's description (外観 → メニュー → 表示オプション → 説明).
add_action(
	'init',
	function () {
		if ( get_option( 'ta_menus_version' ) !== '1' ) {
			return;
		}
		$locations = get_nav_menu_locations();
		$items     = empty( $locations['header'] ) ? array() : (array) wp_get_nav_menu_items( $locations['header'] );
		foreach ( $items as $item ) {
			if ( 'サービス' !== $item->title || $item->menu_item_parent ) {
				continue;
			}
			$has_children = array_filter( $items, fn( $c ) => (int) $c->menu_item_parent === $item->ID );
			if ( ! $has_children ) {
				foreach ( ta_default_service_items() as $label => $sub ) {
					wp_update_nav_menu_item(
						$locations['header'],
						0,
						array(
							'menu-item-title'       => $label,
							'menu-item-url'         => home_url( $sub[0] ),
							'menu-item-description' => $sub[1],
							'menu-item-parent-id'   => $item->ID,
							'menu-item-type'        => 'custom',
							'menu-item-status'      => 'publish',
						)
					);
				}
			}
			break;
		}
		update_option( 'ta_menus_version', '2' );
	},
	20
);

/**
 * Prints a menu location as plain links in the design's style. Without an
 * assigned menu it prints the default items. In the header, an item with
 * sub-items opens a small panel on hover / keyboard focus.
 */
function ta_menu_links( $location, $color ) {
	$links     = array();
	$locations = get_nav_menu_locations();
	$items     = empty( $locations[ $location ] ) ? false : wp_get_nav_menu_items( $locations[ $location ] );
	if ( $items ) {
		$children = array();
		foreach ( $items as $item ) {
			if ( $item->menu_item_parent ) {
				$children[ (int) $item->menu_item_parent ][] = array( $item->title, $item->url, $item->target, $item->description, array() );
			}
		}
		foreach ( $items as $item ) {
			if ( ! $item->menu_item_parent ) {
				$links[] = array( $item->title, $item->url, $item->target, '', $children[ $item->ID ] ?? array() );
			}
		}
	} elseif ( false === $items ) {
		foreach ( ta_default_menu( $location ) as $label => $path ) {
			$subs = array();
			if ( 'header' === $location && '/services/' === $path ) {
				foreach ( ta_default_service_items() as $sub_label => $sub ) {
					$subs[] = array( $sub_label, home_url( $sub[0] ), '', $sub[1], array() );
				}
			}
			$links[] = array( $label, home_url( $path ), '', '', $subs );
		}
	}
	foreach ( $links as $link ) {
		$a = sprintf(
			'<a href="%s"%s style="color: %s; text-decoration: none;">%s</a>',
			esc_url( $link[1] ),
			$link[2] ? ' target="' . esc_attr( $link[2] ) . '" rel="noopener"' : '',
			esc_attr( $color ),
			esc_html( $link[0] )
		);
		if ( 'header' !== $location || ! $link[4] ) {
			echo $a . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
			continue;
		}
		echo '<div class="nav-sub">' . $a . '<div class="nav-sub-panel">'; // phpcs:ignore WordPress.Security.EscapeOutput
		foreach ( $link[4] as $sub ) {
			printf(
				'<a href="%s"%s>%s<span class="nav-sub-ja">%s</span></a>',
				esc_url( $sub[1] ),
				$sub[2] ? ' target="' . esc_attr( $sub[2] ) . '" rel="noopener"' : '',
				$sub[3] ? '<span class="en nav-sub-en">' . esc_html( $sub[3] ) . '</span>' : '',
				esc_html( $sub[0] )
			);
		}
		echo "</div></div>\n";
	}
}
