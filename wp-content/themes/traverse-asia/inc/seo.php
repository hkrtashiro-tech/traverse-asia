<?php
/**
 * Page titles, meta descriptions and share (OGP) tags.
 *
 * Each designed page has a default title and description here. In the editor
 * a page's 「抜粋」 overrides the description and 「検索結果のタイトル」
 * (document settings) overrides the title.
 */

define( 'TA_BRAND', 'Traverse Asia' );

/** Defaults by page slug (front page: 'front'; lists: 'works', 'news'): [title, description]. */
function ta_seo_defaults() {
	return array(
		'front'               => array( 'Traverse Asia｜日本の商品の海外展開を、ブランドづくりから販売まで', '日本の商品・ブランドの海外展開を支援するTraverse Asia。ブランドづくり、市場調査と戦略、現地の代理店・バイヤー開拓、Amazon・Shopeeでの販売まで、中国・台湾・香港・東南アジアの7市場で一貫して伴走します。' ),
		'services'            => array( 'サービス', '海外展開に必要な「つくる・設計する・つなぐ・売る」の4つのステップを一社で提供。必要なステップだけのご依頼や、途中からのご相談も可能です。課題別の入口から、最適な支援内容を探せます。' ),
		'create'              => array( '海外向けブランドづくり（CREATE）', '海外で選ばれる価値を見つけ、ブランド戦略、コンセプト・ストーリー、ネーミング・パッケージ、多言語コンテンツまで整えます。現地の生活者の視点から、海外で伝わるブランドを設計します。' ),
		'market-design'       => array( '海外市場調査・海外展開戦略（DESIGN THE MARKET）', 'どの国で、どの販路で、いくらで売るか。市場調査、海外展開戦略、価格設計、販路・輸出スキームの設計から、補助金の申請、テスト販売・ローンチまで支援します。' ),
		'connect'             => array( '海外の代理店・バイヤー開拓（CONNECT）', '現地で暮らし、働いてきたメンバーのネットワークで、代理店・ディストリビューター・バイヤーとの商談を設定。展示会・物産フェアの運営や、現地プロモーション、Web広告の配信も行います。' ),
		'sell'                => array( '海外EC・販売運営（SELL）', 'Amazon・Shopeeなど海外ECの運営、卸・B2B、テスト販売、英国VAT登録まで。自社ブランドを海外で販売している経験をもとに、価格・物流・在庫・販促を含めた「売る」現場を支援します。' ),
		'shopee'              => array( 'Shopee出店・運営代行｜東南アジア・台湾の越境EC', '東南アジア・台湾最大級のECモール「Shopee」への出店から運営まで代行。自社でシンガポール・フィリピンのストアを運営する経験をもとに、商品ページ、広告、受注・物流まで日本語で支援します。' ),
		'uk-vat-registration' => array( '英国VAT登録代行｜5万円・日本語対応', '英国のAmazonなどで販売する日本企業向けに、英国VAT（付加価値税）の登録を5万円（税別）の定額で代行。日本人スタッフが日本語で対応し、登録完了の目安は1〜2か月です。登録後の申告もご相談ください。' ),
		'public-sector'       => array( '自治体・公的機関の海外販路開拓・プロモーション', '地域の産品と魅力を海外の市場へ。北海道庁、在中国日本国大使館、日本政府観光局（JNTO）などの事業で、海外での商談会・物産フェアの企画運営やプロモーション、成果報告まで対応しています。' ),
		'shizune'             => array( '自社ブランド SHIZUNE（静音）｜日本の抹茶', 'SHIZUNE（静音）は、Traverse Asiaが企画・開発・販売する抹茶ブランドです。国内の厳選された茶葉を使い、生産からパッケージングまで日本国内で行い、米国のAmazonなどで販売しています。' ),
		'company'             => array( '会社情報', 'Traverse Asia合同会社の会社情報。ミッション「日本の価値を、海外で育つ事業へ。」、代表メッセージ、海外経験の長いメンバー、沿革、会社概要をご紹介します。所在地は北海道苫小牧市です。' ),
		'recruit'             => array( '採用情報', '日本の良いものを、一緒に海外へ。Traverse Asiaの採用情報です。現在募集中の職種はありませんが、募集を始める際はこのページとお知らせでご案内します。' ),
		'works'               => array( '実績｜海外展開・プロモーションの事例', 'キユーピー、ポニーキャニオン、北海道庁、在中国日本国大使館、日本政府観光局（JNTO）などの海外展開・プロモーションの事例。中国・香港・台湾・東南アジアでの取り組みを、ステップや国・地域別に紹介します。' ),
		'news'                => array( 'お知らせ・インサイト', 'Traverse Asiaのプレスリリース、お知らせ、海外展開・越境EC・Shopeeに関するコラムを掲載しています。' ),
		'contact'             => array( 'お問い合わせ', '海外展開のご相談、お見積り、取材のご依頼はこちらから。対象国や商材が決まっていない段階のご相談も歓迎です。' ),
		'privacy-policy'      => array( 'プライバシーポリシー', 'Traverse Asia合同会社の個人情報の取り扱いについて定めたプライバシーポリシーです。' ),
	);
}

add_action(
	'init',
	function () {
		add_post_type_support( 'page', 'excerpt' );
		foreach ( array( 'page', 'post', 'news', 'works' ) as $type ) {
			register_post_meta(
				$type,
				'_ta_seo_title',
				array(
					'type'          => 'string',
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
);

/** Key into ta_seo_defaults() for the current request, or ''. */
function ta_seo_key() {
	if ( is_front_page() ) {
		return 'front';
	}
	if ( is_post_type_archive( 'works' ) ) {
		return 'works';
	}
	if ( is_post_type_archive( 'news' ) ) {
		return 'news';
	}
	if ( is_page() ) {
		return get_post_field( 'post_name', get_queried_object_id() );
	}
	return '';
}

/** Title shown in search results and browser tabs. */
function ta_seo_title() {
	$defaults = ta_seo_defaults();
	$key      = ta_seo_key();
	$custom   = is_singular() ? trim( (string) get_post_meta( get_queried_object_id(), '_ta_seo_title', true ) ) : '';
	if ( 'front' === $key ) {
		return $custom ?: $defaults['front'][0];
	}
	if ( $custom ) {
		$title = $custom;
	} elseif ( isset( $defaults[ $key ] ) ) {
		$title = $defaults[ $key ][0];
	} elseif ( is_singular() ) {
		$title = wp_strip_all_tags( get_the_title( get_queried_object_id() ) );
	} elseif ( is_404() ) {
		$title = 'ページが見つかりません';
	} elseif ( is_search() ) {
		$title = '「' . get_search_query() . '」の検索結果';
	} else {
		$title = wp_strip_all_tags( get_the_archive_title() );
	}
	return $title . '｜' . TA_BRAND;
}

/** Meta description for the current request. */
function ta_seo_description() {
	$defaults = ta_seo_defaults();
	$key      = ta_seo_key();
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( has_excerpt( $post ) ) {
			return ta_seo_trim( get_the_excerpt( $post ) );
		}
		if ( isset( $defaults[ $key ] ) ) {
			return $defaults[ $key ][1];
		}
		if ( 'works' === $post->post_type ) {
			$client = get_post_meta( $post->ID, '_ta_client', true );
			$area   = get_post_meta( $post->ID, '_ta_area_label', true );
			return ta_seo_trim( get_the_title( $post ) . '。' . ( $client ? $client . 'の' : '' ) . '海外展開・プロモーションの事例' . ( $area ? '（' . $area . '）' : '' ) . 'です。' );
		}
		if ( ! ta_has_sections( $post->post_content ) ) {
			$text = ta_seo_trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
			if ( $text ) {
				return $text;
			}
		}
	}
	if ( isset( $defaults[ $key ] ) ) {
		return $defaults[ $key ][1];
	}
	return $defaults['front'][1];
}

function ta_seo_trim( $text ) {
	$text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' ) ) );
	return mb_strlen( $text ) > 120 ? mb_substr( $text, 0, 119 ) . '…' : $text;
}

/** Share image: a case's card image, a page's featured image, else the default picture. */
function ta_seo_image() {
	if ( is_singular( 'works' ) ) {
		return ta_work_card_image( get_queried_object_id() );
	}
	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		return get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
	}
	return TA_URI . '/assets/img/og-default.jpg';
}

add_filter( 'pre_get_document_title', 'ta_seo_title' );

add_action(
	'wp_head',
	function () {
		$title = ta_seo_title();
		$desc  = ta_seo_description();
		$url   = is_singular() ? get_permalink( get_queried_object_id() ) : home_url( add_query_arg( array() ) );
		$tags  = array(
			array( 'name', 'description', $desc ),
			array( 'property', 'og:type', is_front_page() ? 'website' : 'article' ),
			array( 'property', 'og:site_name', TA_BRAND ),
			array( 'property', 'og:title', $title ),
			array( 'property', 'og:description', $desc ),
			array( 'property', 'og:url', $url ),
			array( 'property', 'og:image', ta_seo_image() ),
			array( 'property', 'og:locale', 'ja_JP' ),
			array( 'name', 'twitter:card', 'summary_large_image' ),
		);
		foreach ( $tags as $t ) {
			printf( '<meta %s="%s" content="%s">' . "\n", $t[0], esc_attr( $t[1] ), esc_attr( $t[2] ) );
		}
	},
	2
);

// 「検索結果のタイトル」 field in the editor's document settings.
add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_script( 'ta-seo', TA_URI . '/assets/js/seo.js', array( 'wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-data' ), TA_VERSION, true );
		$defaults = array();
		foreach ( ta_seo_defaults() as $key => $d ) {
			$defaults[ $key ] = $d[0];
		}
		$front = (int) get_option( 'page_on_front' );
		wp_add_inline_script( 'ta-seo', 'window.taSeo = ' . wp_json_encode( array( 'defaults' => $defaults, 'front' => $front, 'brand' => TA_BRAND ) ) . ';', 'before' );
	}
);
