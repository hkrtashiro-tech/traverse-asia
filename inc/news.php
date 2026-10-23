<?php
/**
 * News & Insights: /news/ lists news posts (press releases, announcements)
 * together with columns (regular posts) newest first.
 */

/** Filter tabs on /news/: query value => label. */
function ta_news_kinds() {
	return array(
		''              => 'すべて',
		'press-release' => 'プレスリリース',
		'information'   => 'お知らせ',
		'column'        => 'コラム',
	);
}

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'kind';
		return $vars;
	}
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'news' ) ) {
			return;
		}
		$kind = $q->get( 'kind' );
		$q->set( 'posts_per_page', 10 );
		if ( 'column' === $kind ) {
			$q->set( 'post_type', 'post' );
		} elseif ( $kind && isset( ta_news_kinds()[ $kind ] ) ) {
			$q->set( 'post_type', 'news' );
			$q->set(
				'tax_query',
				array(
					array(
						'taxonomy' => 'news_category',
						'field'    => 'slug',
						'terms'    => $kind,
					),
				)
			);
		} else {
			$q->set( 'post_type', array( 'news', 'post' ) );
		}
	}
);

/** Label shown on a news row: the news category, or コラム for posts. */
function ta_news_label( $post = null ) {
	$post = get_post( $post );
	if ( 'news' === $post->post_type ) {
		$terms = get_the_terms( $post, 'news_category' );
		return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'お知らせ';
	}
	return 'コラム';
}

/** Thumbnail URL for a news row, with a neutral fallback. */
function ta_news_thumb( $post = null, $size = 'ta-thumb' ) {
	$url = get_the_post_thumbnail_url( $post, $size );
	return $url ? esc_url( $url ) : ta_img( 'col-eye.jpg' );
}

/** The latest news and columns, as used on the top page. */
function ta_news_rows_simple( $count = 3 ) {
	$q = new WP_Query(
		array(
			'post_type'           => array( 'news', 'post' ),
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	while ( $q->have_posts() ) {
		$q->the_post();
		?>
<a href="<?php the_permalink(); ?>" class="row" style="display: flex; flex-wrap: wrap; align-items: baseline; gap: 6px 28px; padding: 22px 12px; border-bottom: 1px solid #CDD3C8; color: #22322F; text-decoration: none;"><span class="en" style="font-size: 14px; color: #2E3D39; width: 100px;"><?php echo esc_html( ta_date() ); ?></span><span style="font-size: 13px; font-weight: 700; color: #2F5A3A; border: 1px solid #2F5A3A; border-radius: 999px; padding: 0 12px;"><?php echo esc_html( ta_news_label() ); ?></span><span style="flex: 1 1 320px; min-width: 0; font-size: 16px; font-weight: 500;"><?php the_title(); ?></span><span class="ra" aria-hidden="true">→</span></a>
		<?php
	}
	wp_reset_postdata();
}

/** One row with a thumbnail, as on /news/ and under articles. */
function ta_news_row() {
	?>
<a href="<?php the_permalink(); ?>" class="row nrow" style="display: grid; gap: 8px 28px; padding: 20px 12px; border-bottom: 1px solid #CDD3C8; color: #22322F; text-decoration: none; align-items: center;"><img src="<?php echo ta_news_thumb(); ?>" alt="" loading="lazy" style="width: 100%; aspect-ratio: 3 / 2; object-fit: cover; border-radius: 12px; display: block; border: 1px solid #E2E6DE;"><span style="display: flex; flex-direction: column; gap: 8px; min-width: 0;"><span style="display: flex; gap: 14px; align-items: baseline;"><span class="en" style="font-size: 14px; color: #2E3D39;"><?php echo esc_html( ta_date() ); ?></span><span style="font-size: 12.5px; font-weight: 700; color: #2F5A3A; border: 1px solid #2F5A3A; border-radius: 999px; padding: 1px 12px; white-space: nowrap;"><?php echo esc_html( ta_news_label() ); ?></span></span><span style="font-size: 17px; font-weight: 700; line-height: 1.7;"><?php the_title(); ?></span></span><span class="ra" aria-hidden="true">→</span></a>
	<?php
}

/** Rows of the main query on /news/. */
function ta_news_list() {
	if ( ! have_posts() ) {
		echo '<p style="padding: 40px 12px; margin: 0;">記事はまだありません。</p>';
		return;
	}
	while ( have_posts() ) {
		the_post();
		ta_news_row();
	}
}

/** Filter tabs on /news/. */
function ta_news_tabs() {
	$current = (string) get_query_var( 'kind' );
	echo '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
	foreach ( ta_news_kinds() as $kind => $label ) {
		$url   = $kind ? add_query_arg( 'kind', $kind, get_post_type_archive_link( 'news' ) ) : get_post_type_archive_link( 'news' );
		$style = $kind === $current
			? 'background: #22322F; color: #FFFFFF;'
			: 'border: 1px solid #B9C1B5; color: #22322F;';
		printf(
			'<a href="%s" %s style="font-size: 14px; font-weight: 700; border-radius: 999px; padding: 6px 18px; text-decoration: none; %s">%s</a>',
			esc_url( $url ),
			$kind === $current ? 'aria-current="page"' : '',
			$style,
			esc_html( $label )
		);
	}
	echo '</div>';
}

/** Round page numbers in the design's style. */
function ta_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'prev_text' => '←',
			'next_text' => '→',
			'mid_size'  => 1,
		)
	);
	if ( ! $links ) {
		return;
	}
	echo '<nav class="en ta-pager" aria-label="ページ送り" style="display: flex; justify-content: center; gap: 8px; padding-top: 12px;">' . implode( '', $links ) . '</nav>';
}

/**
 * Article body with ids on h2 headings, plus the list of those headings for
 * the table of contents.
 */
function ta_content_with_toc() {
	$html = apply_filters( 'the_content', get_the_content() );
	$toc  = array();
	$html = preg_replace_callback(
		'/<h2([^>]*)>(.*?)<\/h2>/s',
		function ( $m ) use ( &$toc ) {
			$id    = 'h-' . ( count( $toc ) + 1 );
			$toc[] = array( $id, wp_strip_all_tags( $m[2] ) );
			if ( preg_match( '/\sid="([^"]+)"/', $m[1], $idm ) ) {
				$toc[ count( $toc ) - 1 ][0] = $idm[1];
				return $m[0];
			}
			return '<h2 id="' . $id . '"' . $m[1] . '>' . $m[2] . '</h2>';
		},
		$html
	);
	return array( $html, $toc );
}

// With post_type set to [news, post] the template hierarchy no longer finds
// archive-news.php on its own, so point it there explicitly.
add_filter(
	'template_include',
	function ( $template ) {
		if ( is_post_type_archive( 'news' ) && ! is_feed() ) {
			return locate_template( 'archive-news.php' ) ?: $template;
		}
		return $template;
	}
);
