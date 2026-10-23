<?php
/**
 * Custom post types and taxonomies.
 *
 * news / news_category keep the names and URLs of the previous theme so the
 * existing posts survive the theme switch unchanged.
 */

add_action(
	'init',
	function () {
		register_post_type(
			'works',
			array(
				'label'         => '実績',
				'labels'        => array(
					'name'          => '実績',
					'singular_name' => '実績',
					'add_new_item'  => '実績を追加',
					'edit_item'     => '実績を編集',
				),
				'public'        => true,
				'has_archive'   => 'works',
				'rewrite'       => array( 'slug' => 'works', 'with_front' => false ),
				'menu_position' => 5,
				'menu_icon'     => 'dashicons-portfolio',
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			)
		);

		register_taxonomy(
			'work_step',
			'works',
			array(
				'label'        => 'ステップ',
				'hierarchical' => true,
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'query_var'    => false,
				'rewrite'      => false,
			)
		);

		register_taxonomy(
			'work_area',
			'works',
			array(
				'label'        => '国・地域',
				'hierarchical' => true,
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'query_var'    => false,
				'rewrite'      => false,
			)
		);

		register_post_type(
			'news',
			array(
				'label'         => 'お知らせ',
				'labels'        => array(
					'name'          => 'お知らせ',
					'singular_name' => 'お知らせ',
					'add_new_item'  => 'お知らせを追加',
					'edit_item'     => 'お知らせを編集',
				),
				'public'        => true,
				'has_archive'   => 'news',
				'rewrite'       => array( 'slug' => 'news', 'with_front' => false ),
				'menu_position' => 6,
				'menu_icon'     => 'dashicons-megaphone',
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			)
		);

		register_taxonomy(
			'news_category',
			'news',
			array(
				'label'        => 'お知らせカテゴリー',
				'hierarchical' => true,
				'public'       => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => 'news_category', 'with_front' => false ),
			)
		);
	}
);

/** Step terms of the works taxonomy, in display order: slug => [EN label, JA label]. */
function ta_steps() {
	return array(
		'create'        => array( 'CREATE', '01 ブランドをつくる' ),
		'market-design' => array( 'DESIGN', '02 市場を設計する' ),
		'connect'       => array( 'CONNECT', '03 現地とつなぐ' ),
		'sell'          => array( 'SELL', '04 実際に売る' ),
	);
}

/** Area terms of the works taxonomy, in display order: slug => label. */
function ta_areas() {
	return array(
		'china'       => '中国',
		'hong-kong'   => '香港',
		'taiwan'      => '台湾',
		'singapore'   => 'シンガポール',
		'philippines' => 'フィリピン',
	);
}

/** Create the fixed step and area terms once. */
add_action(
	'init',
	function () {
		if ( get_option( 'ta_terms_version' ) === '1' ) {
			return;
		}
		foreach ( ta_steps() as $slug => $labels ) {
			if ( ! term_exists( $slug, 'work_step' ) ) {
				wp_insert_term( $labels[1], 'work_step', array( 'slug' => $slug ) );
			}
		}
		foreach ( ta_areas() as $slug => $label ) {
			if ( ! term_exists( $slug, 'work_area' ) ) {
				wp_insert_term( $label, 'work_area', array( 'slug' => $slug ) );
			}
		}
		update_option( 'ta_terms_version', '1' );
	},
	20
);

add_action( 'after_switch_theme', 'flush_rewrite_rules' );
