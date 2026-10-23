<?php
/**
 * Pages. A page built from design section blocks prints them as they are.
 * A designed page (services, company …) that has not been converted yet shows
 * the design's initial content; any other page shows its text in the article layout.
 */
get_header();
while ( have_posts() ) {
	the_post();
	$ta_content = get_the_content();
	if ( ta_has_sections( $ta_content ) || ta_design_content( get_post_field( 'post_name' ) ) ) {
		ta_render_design( $ta_content, get_post_field( 'post_name' ) );
	} else {
		get_template_part( 'template-parts/article' );
	}
}
get_footer();
