<?php
/**
 * 初期設定（外観 → 初期設定）. Run once after activating the theme on the live site:
 *  1. creates the pages that have designed layouts (existing pages are kept),
 *  2. moves the 9 case-study posts (category casestudy) to the works post type
 *     under their new slugs, or creates them if they do not exist,
 *  3. moves the 3 Shopee columns to the blog category (/shopee/x/ → /blog/x/),
 *  4. creates the Contact Form 7 form from cf7/contact-form.html and selects it,
 *  5. flushes permalinks.
 * Safe to run again: it only creates what is missing and updates the fields the
 * new design depends on. The matching 301 redirects are in redirects-traverse-asia.csv.
 */

/** A page (never a media item) by its path, e.g. 'contact/thanks'. */
function ta_find_page( $path ) {
	return get_page_by_path( $path, OBJECT, array( 'page' ) );
}

/** Runs the setup and returns what it did, one line per change. */
function ta_run_site_setup() {
	$log = array();

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
	// The site already has a front page; /home/ is only needed when it does not.
	if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
		unset( $ta_pages['home'] );
	}
	foreach ( $ta_pages as $path => $title ) {
		if ( ta_find_page( $path ) ) {
			continue;
		}
		$slug   = basename( $path );
		$parent = 0;
		if ( false !== strpos( $path, '/' ) ) {
			$parent_page = ta_find_page( dirname( $path ) );
			$parent      = $parent_page ? $parent_page->ID : 0;
		}
		// A media item with the same name (e.g. an image called "contact") takes
		// the URL before a page can; rename it so the page gets the address.
		foreach ( get_posts( array( 'post_type' => 'attachment', 'name' => $slug, 'post_status' => 'inherit', 'post_parent' => $parent, 'numberposts' => -1 ) ) as $att ) {
			wp_update_post( array( 'ID' => $att->ID, 'post_name' => $slug . '-image' ) );
			$log[] = "同じ名前のメディア（ID：{$att->ID}）の名前を {$slug}-image に変更しました";
		}
		// A page with this slug may exist under the wrong parent (it was created
		// while the parent URL was taken by media). Move it instead of duplicating.
		$stray = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'numberposts' => -1 ) );
		$stray = array_values( array_filter( $stray, fn( $c ) => $c->post_parent && 'page' !== get_post_type( $c->post_parent ) ) );
		if ( $stray ) {
			wp_update_post( array( 'ID' => $stray[0]->ID, 'post_parent' => $parent, 'post_name' => $slug ) );
			$log[] = "ページの位置を直しました：/$path/";
			continue;
		}
		wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_parent' => $parent,
			)
		);
		$log[] = "ページを作成しました：/$path/";
	}
	if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', ta_find_page( 'home' )->ID );
		$log[] = 'トップページ（/）の表示を設定しました';
	}
	// An empty /home/ page made by an earlier version of this setup duplicates the top page.
	$home = ta_find_page( 'home' );
	if ( $home && (int) get_option( 'page_on_front' ) !== $home->ID && '' === trim( $home->post_content ) ) {
		wp_trash_post( $home->ID );
		$log[] = '不要な空のページ /home/ をゴミ箱に移しました';
	}
	// Thanks page must not be indexed.
	$thanks = ta_find_page( 'contact/thanks' );
	if ( $thanks ) {
		update_post_meta( $thanks->ID, '_ta_noindex', '1' );
	}

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
			$log[] = "事例を実績に移しました：" . urldecode( $existing->post_name ) . " → /works/$slug/";
		} else {
			$id = wp_insert_post( $data );
			$log[] = "実績を作成しました：/works/$slug/";
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
			$log[] = "コラムを移動しました：/blog/$slug/";
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
					'body'               => "[your-name] 様\n\nTraverse Asiaへのお問い合わせありがとうございます。\n以下の内容で受け付けました。内容を確認のうえ、担当者よりご返信します。\n\nお問い合わせの種類：[inquiry-type]\n会社名・団体名：[your-org]\nご相談内容：[topics]\n対象の国・地域：[areas]\n\n詳しい内容：\n[your-message]\n\n--\nTraverse Asia合同会社\nsales@traverse-asia.com\n[_site_url]",
					'additional_headers' => 'Reply-To: sales@traverse-asia.com',
					'attachments'        => '',
					'use_html'           => false,
					'exclude_blank'      => true,
				),
			)
		);
		$form->save();
		$form_id = $form->id();
		set_theme_mod( 'ta_cf7_id', $form_id );
		$log[] = "お問い合わせフォームを作成して設定しました（ID：{$form_id}）";
	}

	// English form (used on /en/contact/).
	$en_form_id = (int) get_theme_mod( 'ta_cf7_id_en', 0 );
	if ( class_exists( 'WPCF7_ContactForm' ) && ( ! $en_form_id || ! wpcf7_contact_form( $en_form_id ) ) ) {
		switch_to_locale( 'en_US' ); // CF7's own messages (validation, sent …) in English
		$messages = array();
		foreach ( wpcf7_messages() as $key => $message ) {
			$messages[ $key ] = $message['default'];
		}
		restore_previous_locale();
		$form = WPCF7_ContactForm::get_template( array( 'title' => 'Contact (English site)' ) );
		$form->set_properties(
			array(
				'form'     => file_get_contents( get_template_directory() . '/cf7/contact-form-en.html' ),
				'mail'     => array(
					'active'             => true,
					'subject'            => '[Traverse Asia EN] 英語サイトからのお問い合わせ：[inquiry-type]（[your-org] [your-name]）',
					'sender'             => 'Traverse Asia <wordpress@[_site_domain]>',
					'recipient'          => '[_site_admin_email]',
					'body'               => "Inquiry type: [inquiry-type]\nName: [your-name]\nCompany: [your-org]\nEmail: [your-email]\nCountry / region: [your-country]\nInterested in: [topics]\n\nMessage:\n[your-message]\n\n--\nSent from [_site_url]/en/contact/",
					'additional_headers' => 'Reply-To: [your-email]',
					'attachments'        => '',
					'use_html'           => false,
					'exclude_blank'      => true,
				),
				'mail_2'   => array(
					'active'             => true,
					'subject'            => 'Traverse Asia: we have received your message',
					'sender'             => 'Traverse Asia <wordpress@[_site_domain]>',
					'recipient'          => '[your-email]',
					'body'               => "Dear [your-name],\n\nThank you for contacting Traverse Asia. We have received your message and will reply after reviewing it.\n\nInquiry type: [inquiry-type]\nCompany: [your-org]\nInterested in: [topics]\n\nMessage:\n[your-message]\n\n--\nTraverse Asia LLC\nsales@traverse-asia.com\n[_site_url]/en/",
					'additional_headers' => 'Reply-To: sales@traverse-asia.com',
					'attachments'        => '',
					'use_html'           => false,
					'exclude_blank'      => true,
				),
				'messages' => $messages,
			)
		);
		$form->save();
		set_theme_mod( 'ta_cf7_id_en', $form->id() );
		$log[] = '英語版のお問い合わせフォームを作成しました（ID：' . $form->id() . '）';
	}

	/* English pages ----------------------------------------------------------------- */
	// Created as drafts so they can be checked before publishing; existing pages are left alone.
	$en_pages = array(
		'en'                        => 'Home',
		'en/services'               => 'Services',
		'en/services/create'        => 'Create the brand',
		'en/services/market-design' => 'Design the market',
		'en/services/connect'       => 'Connect locally',
		'en/services/sell'          => 'Sell for real',
		'en/works'                  => 'Works',
		'en/shizune'                => 'SHIZUNE',
		'en/about'                  => 'About',
		'en/contact'                => 'Contact',
		'en/contact/thanks'         => 'Thank you',
	);
	foreach ( $en_pages as $path => $title ) {
		if ( get_page_by_path( $path, OBJECT, array( 'page' ) ) ) {
			continue;
		}
		$parent = 0;
		if ( false !== strpos( $path, '/' ) ) {
			$parent_page = get_page_by_path( dirname( $path ), OBJECT, array( 'page' ) );
			$parent      = $parent_page ? $parent_page->ID : 0;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_title'   => $title,
				'post_name'    => basename( $path ),
				'post_parent'  => $parent,
				'post_content' => wp_slash( ta_design_content( str_replace( '/', '-', $path ) ) ),
			)
		);
		if ( 'en/contact/thanks' === $path ) {
			update_post_meta( $id, '_ta_noindex', '1' );
		}
		$log[] = "英語版のページを下書きで作成しました：/$path/";
	}

	/* 5. Editable sections -------------------------------------------------------- */
	// Designed pages get the design as section blocks, so they can be edited in the
	// block editor. Pages already built from sections are left alone; the previous
	// text stays in the page's revisions.
	$targets = array();
	foreach ( $ta_pages as $path => $title ) {
		$page = ta_find_page( $path );
		if ( $page && ta_design_content( basename( $path ) ) ) {
			$targets[] = array( $page, basename( $path ) );
		}
	}
	$front = get_post( (int) get_option( 'page_on_front' ) );
	if ( $front ) {
		$targets[] = array( $front, 'front' );
	}
	foreach ( array_keys( $ta_works ) as $slug ) {
		$work = get_page_by_path( $slug, OBJECT, 'works' );
		if ( $work ) {
			$targets[] = array( $work, 'work-' . $slug );
		}
	}
	foreach ( ta_holders() as $key => $title ) {
		$holder = get_page_by_path( 'ta-holder-' . $key, OBJECT, array( 'page' ) );
		if ( ! $holder ) {
			$id     = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'private', 'post_title' => $title, 'post_name' => 'ta-holder-' . $key ) );
			$holder = get_post( $id );
			$log[]  = "編集用のページを作成しました：$title";
		}
		$targets[] = array( $holder, $key );
	}
	foreach ( $targets as $target ) {
		list( $post, $key ) = $target;
		if ( ta_has_sections( $post->post_content ) ) {
			continue;
		}
		wp_update_post( array( 'ID' => $post->ID, 'post_content' => wp_slash( ta_design_content( $key ) ) ) );
		$log[] = 'ブロックで編集できるようにしました：' . $post->post_title;
	}

	/* 5. Permalinks --------------------------------------------------------------- */
	flush_rewrite_rules();
	$log[] = 'パーマリンク設定を更新しました';

	update_option( 'ta_setup_done', current_time( 'mysql' ) );
	return $log;
}

add_action(
	'admin_menu',
	function () {
		add_theme_page( 'サイトの初期設定', '初期設定', 'manage_options', 'ta-setup', 'ta_setup_page' );
	}
);

add_action(
	'admin_post_ta_setup',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( '権限がありません。' );
		}
		check_admin_referer( 'ta_setup' );
		set_transient( 'ta_setup_log', ta_run_site_setup(), 600 );
		wp_safe_redirect( admin_url( 'themes.php?page=ta-setup&done=1' ) );
		exit;
	}
);

function ta_setup_page() {
	$done = get_option( 'ta_setup_done' );
	$log  = isset( $_GET['done'] ) ? get_transient( 'ta_setup_log' ) : false;
	?>
	<div class="wrap">
		<h1>サイトの初期設定</h1>
		<?php if ( is_array( $log ) ) : ?>
			<div class="notice notice-success"><p><strong>初期設定が完了しました。</strong></p>
			<ul style="list-style:disc;padding-left:20px">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul></div>
		<?php endif; ?>
		<p>新しいテーマに必要な設定をまとめて行います。次の内容を実行します。</p>
		<ol>
			<li>足りない固定ページ（サービス各ページ、自治体・公的機関向け、SHIZUNE、送信完了など）を作成します。既にあるページはそのままです。</li>
			<li>カテゴリー「事例」の9記事を「実績」に移し、新しいURL（/works/…）に変更します。</li>
			<li>Shopeeのコラム3記事をカテゴリー「ブログ」に移します（URLが /blog/… になります）。</li>
			<li>Contact Form 7 が有効なら、お問い合わせフォームを作成してサイトに設定します。</li>
			<li>デザインしたページ・実績・一覧ページを、ブロックエディターで編集できる形にします（既にブロックで作ったページはそのままです。元の本文はリビジョンに残ります）。</li>
			<li>パーマリンク設定を更新します。</li>
		</ol>
		<p>何度実行しても、作成済みのものが重複することはありません。実行前にバックアップを取っておくと安心です。</p>
		<?php if ( ! class_exists( 'WPCF7_ContactForm' ) ) : ?>
			<div class="notice notice-warning inline"><p>Contact Form 7 が有効になっていません。フォームも作成する場合は、先に有効化してください。</p></div>
		<?php endif; ?>
		<?php if ( $done ) : ?>
			<p>前回の実行：<?php echo esc_html( $done ); ?></p>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ta_setup">
			<?php wp_nonce_field( 'ta_setup' ); ?>
			<?php submit_button( $done ? '初期設定をもう一度実行する' : '初期設定を実行する', 'primary large', 'submit', false ); ?>
		</form>
	</div>
	<?php
}
