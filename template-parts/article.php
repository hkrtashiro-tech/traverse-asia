<?php
/**
 * Single article (column post, news post, or a page/work without a designed layout).
 * Layout follows design board S-article.
 */
$ta_type = get_post_type();
if ( 'page' === $ta_type ) {
	ta_breadcrumb( array( array( get_the_title(), null ) ) );
} elseif ( 'works' === $ta_type ) {
	ta_breadcrumb( array( array( '実績', get_post_type_archive_link( 'works' ) ), array( get_the_title(), null ) ) );
} else {
	$ta_label = ta_news_label();
	$ta_kind  = 'post' === $ta_type ? 'column' : '';
	if ( 'news' === $ta_type ) {
		$ta_terms = get_the_terms( get_the_ID(), 'news_category' );
		$ta_kind  = ( $ta_terms && ! is_wp_error( $ta_terms ) ) ? $ta_terms[0]->slug : '';
	}
	$ta_news = get_post_type_archive_link( 'news' );
	ta_breadcrumb(
		array(
			array( 'お知らせ・インサイト', $ta_news ),
			array( $ta_label, $ta_kind ? add_query_arg( 'kind', $ta_kind, $ta_news ) : $ta_news ),
		)
	);
}
list( $ta_html, $ta_toc ) = ta_content_with_toc();
$ta_is_news = in_array( $ta_type, array( 'post', 'news' ), true );
?>
<section class="pad" data-sec="fv" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 40px 40px 40px; display: flex; flex-direction: column; gap: 22px;">
<?php if ( $ta_is_news ) : ?>
<span class="fv-a" style="display: flex; gap: 14px; align-items: baseline;"><span class="en" style="font-size: 14px; color: #2E3D39;"><?php echo esc_html( ta_date() ); ?></span><span style="font-size: 12.5px; font-weight: 700; color: #2F5A3A; border: 1px solid #2F5A3A; border-radius: 999px; padding: 1px 12px; white-space: nowrap;"><?php echo esc_html( ta_news_label() ); ?></span></span>
<?php endif; ?>
<h1 class="serif" style="margin: 0; font-size: clamp(28px, 3.4vw, 46px); font-weight: 800; line-height: 1.5; color: #1B2926;"><span class="fv-l" style="display: block;"><?php the_title(); ?></span></h1>
<?php
$ta_tags = 'post' === $ta_type ? get_the_tags() : false;
if ( $ta_tags ) :
	?>
<span class="fv-c"><span style="display: flex; flex-wrap: wrap; gap: 8px;">
	<?php foreach ( $ta_tags as $ta_tag ) : ?>
<span style="font-size: 14px; border: 1px solid #CDD3C8; border-radius: 999px; padding: 3px 14px; background: #FFFFFF;"><?php echo esc_html( $ta_tag->name ); ?></span>
	<?php endforeach; ?>
</span></span>
<?php endif; ?>
</section>
<?php if ( has_post_thumbnail() ) : ?>
<section class="pad" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 0 40px 64px;"><?php the_post_thumbnail( 'large', array( 'style' => 'width: 100%; height: auto; aspect-ratio: 2 / 1; object-fit: cover; object-position: center 40%; border-radius: 28px; display: block;' ) ); ?></section>
<?php endif; ?>
<section class="pad" data-sec="who" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 0 40px 96px;"><div class="post" style="gap: 48px 72px; align-items: start;"><article class="entry" style="display: flex; flex-direction: column; gap: 22px; min-width: 0; max-width: 760px;">
<?php echo $ta_html; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output. ?>
<?php
if ( $ta_is_news ) :
	$ta_prev = get_previous_post();
	$ta_next = get_next_post();
	if ( $ta_prev || $ta_next ) :
		?>
<div class="entry-nav" style="margin-top: 24px; border-top: 1px solid #22322F; padding-top: 24px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; font-size: 14.5px; font-weight: 700;">
		<?php if ( $ta_prev ) : ?>
<a href="<?php echo esc_url( get_permalink( $ta_prev ) ); ?>">← 前の記事：<?php echo esc_html( get_the_title( $ta_prev ) ); ?></a>
		<?php else : ?><span></span><?php endif; ?>
		<?php if ( $ta_next ) : ?>
<a href="<?php echo esc_url( get_permalink( $ta_next ) ); ?>">次の記事：<?php echo esc_html( get_the_title( $ta_next ) ); ?> →</a>
		<?php endif; ?>
</div>
		<?php
	endif;
endif;
?>
</article><aside style="display: flex; flex-direction: column; gap: 20px; min-width: 0;">
<?php if ( count( $ta_toc ) > 1 ) : ?>
<nav class="card" aria-label="目次" style="border: 1px solid #CDD3C8; border-radius: 24px; padding: 24px; display: flex; flex-direction: column; gap: 10px;"><span class="en" style="font-size: 12.5px; letter-spacing: 0.1em; color: #2F5A3A; font-weight: 600;">THIS ARTICLE</span><span style="font-weight: 700;">目次</span><ol style="margin: 0; padding-left: 20px; font-size: 14.5px; line-height: 1.9; color: #2E3D39;">
	<?php foreach ( $ta_toc as $ta_item ) : ?>
<li><a href="#<?php echo esc_attr( $ta_item[0] ); ?>" style="color: #2E3D39; text-decoration: none;"><?php echo esc_html( $ta_item[1] ); ?></a></li>
	<?php endforeach; ?>
</ol></nav>
<?php endif; ?>
<div class="panel" style="background: #22322F; color: #EEF2EC; border-radius: 24px; padding: 24px; display: flex; flex-direction: column; gap: 12px;"><span class="en" style="font-size: 12.5px; letter-spacing: 0.1em; color: #B5D0A8; font-weight: 600;">SERVICES</span>
<?php
foreach ( array(
	array( '01 CREATE', 'ブランドをつくる', '/services/create/' ),
	array( '02 DESIGN THE MARKET', '市場を設計する', '/services/market-design/' ),
	array( '03 CONNECT', '現地とつなぐ', '/services/connect/' ),
	array( '04 SELL', '実際に売る', '/services/sell/' ),
) as $ta_svc ) :
	?>
<a href="<?php echo ta_url( $ta_svc[2] ); ?>" style="color: #EEF2EC; text-decoration: none; display: flex; flex-direction: column; border-top: 1px solid #4A5C57; padding-top: 12px;"><span class="en" style="font-size: 12px; color: #B5D0A8;"><?php echo esc_html( $ta_svc[0] ); ?></span><span style="font-weight: 700;"><?php echo esc_html( $ta_svc[1] ); ?> →</span></a>
<?php endforeach; ?>
</div>
</aside></div></section>
<?php
if ( $ta_is_news ) :
	$ta_more = new WP_Query(
		array(
			'post_type'           => $ta_type,
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( $ta_more->have_posts() ) :
		?>
<section class="pad" data-sec="news" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 0 40px 120px; display: flex; flex-direction: column; gap: 24px;"><div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 20px 64px;"><div style="display: flex; flex-direction: column; gap: 14px;"><span class="en" style="font-size: 13px; font-weight: 600; letter-spacing: 0.12em; color: #2F5A3A; text-transform: uppercase;"><svg viewBox="0 0 64 64" aria-hidden="true" style="width: 14px; height: 14px; margin-right: 8px; vertical-align: -1px;"><path d="M14 50C14 26 26 14 50 14" fill="none" stroke="currentColor" stroke-width="6"/><circle cx="14" cy="50" r="9" fill="currentColor"/><circle cx="50" cy="14" r="8" fill="none" stroke="currentColor" stroke-width="6"/></svg>More</span><h2 class="rv" style="margin: 0; font-size: clamp(26px, 3vw, 40px); font-weight: 700; line-height: 1.5;"><span style="display: inline-block;">ほかの記事</span></h2></div><a href="<?php echo esc_url( get_post_type_archive_link( 'news' ) ); ?>" style="font-weight: 700; min-height: 44px; display: inline-flex; align-items: center;">すべての記事 →</a></div><div style="border-top: 1px solid #22322F;">
		<?php
		while ( $ta_more->have_posts() ) {
			$ta_more->the_post();
			ta_news_row();
		}
		wp_reset_postdata();
		?>
</div></section>
		<?php
	endif;
endif;
ta_cta( 'post' === $ta_type ? array( '海外でのブランドづくりを、', '相談する。' ) : array( 'まずは、', '相談する。' ) );
