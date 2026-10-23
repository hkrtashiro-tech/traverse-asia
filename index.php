<?php
/**
 * Fallback for any other listing (category, tag, date, search): same rows as /news/.
 */
get_header();
if ( is_search() ) {
	$ta_title = '「' . get_search_query() . '」の検索結果';
} else {
	$ta_title = wp_strip_all_tags( get_the_archive_title() );
}
ta_breadcrumb( array( array( 'お知らせ・インサイト', get_post_type_archive_link( 'news' ) ), array( $ta_title, null ) ) );
?>
<section class="pad" data-sec="fv" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 40px 40px 48px; display: flex; flex-direction: column; gap: 20px;">
<span class="en fv-a" style="font-size: 13px; font-weight: 600; letter-spacing: 0.12em; color: #2F5A3A; text-transform: uppercase;">News &amp; Insights</span>
<h1 class="serif" style="margin: 0; font-size: clamp(32px, 4vw, 52px); font-weight: 800; line-height: 1.4; color: #1B2926;"><span class="fv-l" style="display: inline-block;"><?php echo esc_html( $ta_title ); ?></span></h1></section>
<section class="pad" data-sec="news" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 0 40px 120px; display: flex; flex-direction: column; gap: 24px;">
<div style="border-top: 1px solid #22322F;"><?php ta_news_list(); ?></div>
<?php ta_pagination(); ?>
</section>
<?php
ta_cta();
get_footer();
