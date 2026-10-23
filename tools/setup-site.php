<?php
/**
 * One-time setup for the renewal. Run after activating the Traverse Asia theme:
 *
 *   wp eval-file tools/setup-site.php
 *
 * Safe to run again: it only creates what is missing and updates the fields
 * the new design depends on. It
 *  1. creates the pages that have designed layouts (existing pages are kept),
 *  2. moves the 9 case-study posts (category casestudy) to the works post type
 *     under their new slugs, or creates them if they do not exist,
 *  3. moves the 3 Shopee columns to the blog category (/shopee/x/ → /blog/x/),
 *  4. creates the Contact Form 7 form from the theme's template and selects it,
 *  5. flushes permalinks.
 * The matching 301 redirects are in redirects-traverse-asia.csv.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with: wp eval-file tools/setup-site.php\n" );
}

function ta_setup_log( $msg ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( $msg );
	} else {
		echo $msg . "\n";
	}
}

/* 1. Pages ---------------------------------------------------------------- */
$ta_pages = array(
	// path => title. Parents come before children.
	'home'                  => 'トップ',
	'services'              => 'サービス',
	'services/create'       => 'ブランドをつくる',
	'services/market-design' => '市場を設計する',
	'services/connect'      => '現地とつなぐ',
	'services/sell'         => '実際に売る',
	'services/sell/shopee'  => 'Shopee出店・運営サポート',
	'uk-vat-registration'   => '英国VAT登録代行',
	'public-sector'         => '自治体・公的機関の皆様へ',
	'brands'                => '自社ブランド',
	'brands/shizune'        => '自社ブランド SHIZUNE',
	'company'               => '会社情報',
	'recruit'               => '採用情報',
	'privacy-policy'        => 'プライバシーポリシー',
	'contact'               => 'お問い合わせ',
	'contact/thanks'        => '送信完了',
);
foreach ( $ta_pages as $path => $title ) {
	if ( get_page_by_path( $path ) ) {
		continue;
	}
	$parent = 0;
	if ( false !== strpos( $path, '/' ) ) {
		$parent_page = get_page_by_path( dirname( $path ) );
		$parent      = $parent_page ? $parent_page->ID : 0;
	}
	wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $title,
			'post_name'   => basename( $path ),
			'post_parent' => $parent,
		)
	);
	ta_setup_log( "page created: /$path/" );
}
if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', get_page_by_path( 'home' )->ID );
	ta_setup_log( 'front page set to /home/ (served at /)' );
}
// Thanks page must not be indexed.
update_post_meta( get_page_by_path( 'contact/thanks' )->ID, '_ta_noindex', '1' );

/* 2. Works ------------------------------------------------------------------ */
$ta_works = array(
	// new slug => [old slug (as stored, URL-encoded), title, steps, areas, client, area label, image, alt, position]
	'shopee-management-support'        => array( 'shopee-management-support', 'Shopee越境EC運営支援｜シンガポール・フィリピンでの実践型EC運営', array( 'sell' ), array( 'singapore', 'philippines' ), '自社運営ストア', 'SINGAPORE / PHILIPPINES', 'sell-matcha.jpg', '出荷を待つ抹茶と段ボール', 'center' ),
	'kewpie-china-digital-marketing'   => array( 'kewpie-china-digital-marketing', 'WeChat・Weiboから公式サイト・ファンクラブまで、統合コミュニケーション支援', array( 'create', 'connect' ), array( 'china' ), 'キユーピー', 'CHINA', 'kewpie-cp.jpg', 'キユーピー中国のキャンペーン画面', 'center' ),
	'event-promotion-beijing'          => array( 'event-promotion-beijing', '天皇誕生日祝賀レセプションで、日本の地域の魅力を発信', array( 'connect' ), array( 'china' ), '在中国日本国大使館', 'BEIJING', 'embassy.jpg', '天皇誕生日祝賀レセプションの会場スクリーン', 'center' ),
	'shopee-singapore-philippines'     => array( 'shopee-casestudy', '広告・アフィリエイト・イベントを組み合わせたShopeeの販売拡大', array( 'sell' ), array( 'singapore', 'philippines' ), '自社運営ストア', 'SINGAPORE / PHILIPPINES', 'shopee-metrics.jpg', 'Shopeeストアの指標画面', 'left top' ),
	'hokkaido-seafood-singaporetaiwan' => array( 'hokkaido-seafood-singaporetaiwan', 'シンガポールと台湾で、北海道産活貝フェアを実施', array( 'connect' ), array( 'singapore', 'taiwan' ), '北海道庁', 'SINGAPORE / TAIWAN', 'fair-sg.jpg', '北海道産貝の試食会の案内', 'center' ),
	'hokkaido-seafood-hongkong-taiwan' => array( '%e9%a6%99%e6%b8%af%e3%81%8a%e3%82%88%e3%81%b3%e5%8f%b0%e6%b9%be%e3%81%ab%e3%81%8a%e3%81%91%e3%82%8b%e9%81%93%e7%94%a3%e6%b0%b4%e7%94%a3%e7%89%a9%e5%8a%a0%e5%b7%a5%e5%93%81%e3%81%ae%e8%b2%a9%e8%b7%af', '香港・台湾での道産水産加工品の販路拡大プロモーション', array( 'market-design', 'connect' ), array( 'hong-kong', 'taiwan' ), '北海道庁', 'HONG KONG / TAIWAN', 'hk-booth.jpg', '香港の売場での北海道水産加工品展', 'center' ),
	'pony-canyon-philippines'          => array( 'pony-canyon-philippines', 'インフルエンサーを活用した楽曲プロモーション', array( 'create', 'connect' ), array( 'philippines' ), 'ポニーキャニオン', 'PHILIPPINES', 'pony.jpg', 'フィリピンのインフルエンサーによる動画', 'center' ),
	'matcha-launch-singapore'          => array( '%e9%81%8e%e5%8e%bb%e4%ba%8b%e4%be%8b%ef%bd%9c%e8%bc%b8%e5%87%ba%e3%82%b5%e3%83%9d%e3%83%bc%e3%83%88%e4%ba%8b%e6%a5%ad', 'シンガポール市場での抹茶の新商品ローンチ', array( 'market-design', 'create', 'sell' ), array( 'singapore' ), 'Yamato Matcha', 'SINGAPORE', 'yamato.jpg', 'Yamato Matchaのパッケージと茶筅', 'center' ),
	'web-ads-singapore'                => array( 'advertisement', '訪日客の増加を目的とした、シンガポールでのWeb広告配信', array( 'connect' ), array( 'singapore' ), '日本政府観光局（JNTO）', 'SINGAPORE', 'sg-v.jpg', 'シンガポールの街並み', 'center 60%' ),
);
$ta_order = 0;
foreach ( $ta_works as $slug => $w ) {
	++$ta_order;
	$existing = get_page_by_path( $slug, OBJECT, 'works' );
	if ( ! $existing ) {
		$old = get_posts(
			array(
				'name'        => $w[0],
				'post_type'   => 'post',
				'post_status' => 'any',
				'numberposts' => 1,
			)
		);
		$existing = $old ? $old[0] : null;
	}
	$data = array(
		'post_type'   => 'works',
		'post_name'   => $slug,
		'post_title'  => $w[1],
		'post_status' => 'publish',
		'menu_order'  => $ta_order,
	);
	if ( $existing ) {
		$data['ID'] = $existing->ID;
		$id         = wp_update_post( $data );
		wp_set_post_categories( $id, array() );
		ta_setup_log( "work updated: {$existing->post_name} → /works/$slug/" );
	} else {
		$id = wp_insert_post( $data );
		ta_setup_log( "work created: /works/$slug/" );
	}
	wp_set_object_terms( $id, $w[2], 'work_step' );
	wp_set_object_terms( $id, $w[3], 'work_area' );
	update_post_meta( $id, '_ta_client', $w[4] );
	update_post_meta( $id, '_ta_area_label', $w[5] );
	update_post_meta( $id, '_ta_card_image', $w[6] );
	update_post_meta( $id, '_ta_card_alt', $w[7] );
	update_post_meta( $id, '_ta_card_pos', $w[8] );
}

/* 3. Shopee columns → blog category ---------------------------------------- */
$ta_blog = get_category_by_slug( 'blog' );
if ( ! $ta_blog ) {
	$ta_blog = get_term( wp_insert_term( 'コラム', 'category', array( 'slug' => 'blog' ) )['term_id'], 'category' );
}
foreach ( array( 'shopee-startguide', 'shopee-products-on-trend', 'shopee-operation-cost' ) as $slug ) {
	$posts = get_posts( array( 'name' => $slug, 'post_type' => 'post', 'numberposts' => 1 ) );
	if ( $posts ) {
		wp_set_post_categories( $posts[0]->ID, array( $ta_blog->term_id ) );
		ta_setup_log( "column moved to /blog/$slug/" );
	}
}

/* 4. Contact Form 7 ---------------------------------------------------------- */
if ( class_exists( 'WPCF7_ContactForm' ) && ! ta_contact_form_id() ) {
	$form = WPCF7_ContactForm::get_template( array( 'title' => 'お問い合わせ（サイト）' ) );
	$form->set_properties(
		array(
			'form'     => file_get_contents( get_template_directory() . '/cf7/contact-form.html' ),
			'mail'     => array(
				'active'             => true,
				'subject'            => '[Traverse Asia] お問い合わせ：[inquiry-type]（[your-org] [your-name] 様）',
				'sender'             => 'Traverse Asia <wordpress@[_site_domain]>',
				'recipient'          => '[_site_admin_email]',
				'body'               => "お問い合わせの種類：[inquiry-type]\nお名前：[your-name]\n会社名・団体名：[your-org]\nメールアドレス：[your-email]\n電話番号：[your-tel]\nご相談内容：[topics]\n対象の国・地域：[areas]\n検討時期：[timing]\n\n詳しい内容：\n[your-message]\n\n--\n[_site_url] から送信",
				'additional_headers' => 'Reply-To: [your-email]',
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
			'mail_2'   => array(
				'active'             => true,
				'subject'            => '【Traverse Asia】お問い合わせを受け付けました',
				'sender'             => 'Traverse Asia <wordpress@[_site_domain]>',
				'recipient'          => '[your-email]',
				'body'               => "[your-name] 様\n\nTraverse Asiaへのお問い合わせありがとうございます。\n以下の内容で受け付けました。内容を確認のうえ、担当者よりご返信します。\n\nお問い合わせの種類：[inquiry-type]\n会社名・団体名：[your-org]\nご相談内容：[topics]\n対象の国・地域：[areas]\n\n詳しい内容：\n[your-message]\n\n--\nTraverse Asia合同会社\ncontact@traverse-asia.com\n[_site_url]",
				'additional_headers' => 'Reply-To: contact@traverse-asia.com',
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
		)
	);
	$form_id = $form->save();
	set_theme_mod( 'ta_cf7_id', $form_id );
	ta_setup_log( "contact form created (#$form_id) and selected" );
}

/* 5. Permalinks --------------------------------------------------------------- */
flush_rewrite_rules();
ta_setup_log( 'done' );
