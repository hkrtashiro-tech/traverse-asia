<?php
/**
 * Works (case studies): card fields, list filters and the grid on /works/.
 */

/** Card fields stored as post meta: key => admin label. */
function ta_work_fields() {
	return array(
		'_ta_client'     => 'クライアント名（例：北海道庁）',
		'_ta_area_label' => '地域の表記（例：SINGAPORE / TAIWAN）',
		'_ta_card_image' => 'カード画像（テーマ内の画像ファイル名。空ならアイキャッチ）',
		'_ta_card_alt'   => 'カード画像の代替テキスト',
		'_ta_card_pos'   => 'カード画像の表示位置（例：center / left top）',
	);
}

add_action(
	'init',
	function () {
		foreach ( array_keys( ta_work_fields() ) as $key ) {
			register_post_meta(
				'works',
				$key,
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

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'ta-work-card', '一覧カードの表示', 'ta_work_meta_box', 'works', 'side' );
	}
);

function ta_work_meta_box( $post ) {
	wp_nonce_field( 'ta_work_card', 'ta_work_card_nonce' );
	foreach ( ta_work_fields() as $key => $label ) {
		printf(
			'<p><label for="%1$s" style="display:block;font-weight:600;margin-bottom:4px">%2$s</label><input type="text" id="%1$s" name="%1$s" value="%3$s" style="width:100%%"></p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( get_post_meta( $post->ID, $key, true ) )
		);
	}
}

add_action(
	'save_post_works',
	function ( $post_id ) {
		if ( ! isset( $_POST['ta_work_card_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ta_work_card_nonce'] ), 'ta_work_card' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( array_keys( ta_work_fields() ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
	}
);

/** Slug of the case shown large at the top of /works/. */
function ta_featured_work() {
	return get_theme_mod( 'ta_featured_work', 'shopee-management-support' );
}

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'step';
		$vars[] = 'area';
		return $vars;
	}
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'works' ) ) {
			return;
		}
		$q->set( 'posts_per_page', -1 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$tax = array();
		if ( isset( ta_steps()[ $q->get( 'step' ) ] ) ) {
			$tax[] = array( 'taxonomy' => 'work_step', 'field' => 'slug', 'terms' => $q->get( 'step' ) );
		}
		if ( isset( ta_areas()[ $q->get( 'area' ) ] ) ) {
			$tax[] = array( 'taxonomy' => 'work_area', 'field' => 'slug', 'terms' => $q->get( 'area' ) );
		}
		if ( $tax ) {
			$q->set( 'tax_query', $tax );
		} else {
			$featured = get_page_by_path( ta_featured_work(), OBJECT, 'works' );
			if ( $featured ) {
				$q->set( 'post__not_in', array( $featured->ID ) );
			}
		}
	}
);

/** EN step labels of a work, e.g. [ 'CREATE', 'CONNECT' ]. */
function ta_work_step_labels( $post = null ) {
	$terms = get_the_terms( get_post( $post ), 'work_step' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}
	$labels = array();
	foreach ( ta_steps() as $slug => $l ) {
		foreach ( $terms as $t ) {
			if ( $t->slug === $slug ) {
				$labels[] = $l[0];
			}
		}
	}
	return $labels;
}

/**
 * Card image: the bundled theme image named in meta (set for the launch cases),
 * else the featured image.
 */
function ta_work_card_image( $post = null ) {
	$post = get_post( $post );
	$file = get_post_meta( $post->ID, '_ta_card_image', true );
	if ( $file && file_exists( TA_DIR . '/assets/img/' . $file ) ) {
		return ta_img( $file );
	}
	$url = get_the_post_thumbnail_url( $post, 'ta-card' );
	return $url ? esc_url( $url ) : ta_img( 'hero-harbor.jpg' );
}

function ta_work_card() {
	$id  = get_the_ID();
	$pos = get_post_meta( $id, '_ta_card_pos', true ) ?: 'center';
	?>
<a href="<?php the_permalink(); ?>" class="card en-card" style="border: 1px solid #CDD3C8; border-radius: 24px; padding: 12px 12px 22px; display: flex; flex-direction: column; gap: 12px; color: #22322F; text-decoration: none; min-width: 0;"><img src="<?php echo ta_work_card_image(); ?>" alt="<?php echo esc_attr( get_post_meta( $id, '_ta_card_alt', true ) ); ?>" loading="lazy" style="width: 100%; aspect-ratio: 16 / 10; object-fit: cover; object-position: <?php echo esc_attr( $pos ); ?>; border-radius: 16px; display: block;"><span style="margin: 4px 10px 0; display: flex; flex-wrap: wrap; gap: 6px;">
	<?php foreach ( ta_work_step_labels() as $label ) : ?>
<span class="en" style="font-size: 11.5px; letter-spacing: 0.1em; font-weight: 600; border-radius: 999px; padding: 2px 10px; border: 1px solid #B9C1B5; color: #2F5A3A;"><?php echo esc_html( $label ); ?></span>
	<?php endforeach; ?>
</span><span style="margin: 0 10px; font-size: 16.5px; font-weight: 700; line-height: 1.65;"><?php the_title(); ?></span><span style="margin: auto 10px 0; display: flex; justify-content: space-between; gap: 8px; font-size: 13px; color: #45524E;"><span><?php echo esc_html( get_post_meta( $id, '_ta_client', true ) ); ?></span><span class="en" style="letter-spacing: 0.06em;"><?php echo esc_html( get_post_meta( $id, '_ta_area_label', true ) ); ?></span></span></a>
	<?php
}

function ta_works_grid() {
	if ( ! have_posts() ) {
		echo '<p style="grid-column: 1 / -1; margin: 0; padding: 24px 0;">該当する実績はありません。</p>';
		return;
	}
	while ( have_posts() ) {
		the_post();
		ta_work_card();
	}
}

/** Step and area filters on /works/, as links that keep the other filter. */
function ta_works_filters() {
	$base = get_post_type_archive_link( 'works' );
	$step = (string) get_query_var( 'step' );
	$area = (string) get_query_var( 'area' );
	$link = function ( $s, $a ) use ( $base ) {
		return esc_url( add_query_arg( array_filter( array( 'step' => $s, 'area' => $a ) ), $base ) );
	};
	echo '<div style="display: flex; flex-direction: column; gap: 16px; border-top: 1px solid #22322F; padding-top: 28px;">';
	echo '<div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;"><span style="font-size: 13px; color: #45524E; margin-right: 8px; font-weight: 700;">ステップで絞り込む</span>';
	$steps = array( '' => 'すべて' ) + array_map( fn( $l ) => $l[1], ta_steps() );
	foreach ( $steps as $slug => $label ) {
		$on = $slug === $step;
		printf(
			'<a href="%s"%s style="font-size: 14px; font-weight: 700; border-radius: 999px; padding: 6px 16px; text-decoration: none; %s">%s</a>',
			$link( $slug, $area ),
			$on ? ' aria-current="true"' : '',
			$on ? 'background: #22322F; color: #FFFFFF;' : 'border: 1px solid #B9C1B5; color: #22322F;',
			esc_html( $label )
		);
	}
	echo '</div><div style="display: flex; flex-wrap: wrap; gap: 8px 18px; align-items: center;"><span style="font-size: 13px; color: #45524E; margin-right: 6px; font-weight: 700;">国・地域</span>';
	foreach ( ta_areas() as $slug => $label ) {
		$on = $slug === $area;
		printf(
			'<a href="%s"%s style="font-size: 13.5px; padding: 2px 2px; text-decoration: none; color: %s; border-bottom: %s;">%s</a>',
			$on ? $link( $step, '' ) : $link( $step, $slug ),
			$on ? ' aria-current="true"' : '',
			$on ? '#2F5A3A; font-weight: 700' : '#22322F',
			$on ? '2px solid #2F5A3A' : '1px solid #B9C1B5',
			esc_html( $label )
		);
	}
	echo '</div></div>';
}
