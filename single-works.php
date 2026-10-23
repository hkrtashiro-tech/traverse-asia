<?php
/**
 * Single case study. The launch cases have designed layouts in
 * template-parts/works/{slug}.php; new cases use the article layout.
 */
get_header();
while ( have_posts() ) {
	the_post();
	$ta_slug = get_post_field( 'post_name' );
	if ( ta_part_exists( 'works/' . $ta_slug ) ) {
		get_template_part( 'template-parts/works/' . $ta_slug );
	} else {
		get_template_part( 'template-parts/article' );
	}
}
get_footer();
