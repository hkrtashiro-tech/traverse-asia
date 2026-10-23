<?php
/**
 * Pages. A page whose slug has a designed layout in template-parts/pages/
 * (services, company, contact …) renders that layout; other pages render
 * their editor content in the article layout.
 */
get_header();
while ( have_posts() ) {
	the_post();
	$ta_slug = get_post_field( 'post_name' );
	if ( ta_part_exists( 'pages/' . $ta_slug ) ) {
		get_template_part( 'template-parts/pages/' . $ta_slug );
	} else {
		get_template_part( 'template-parts/article' );
	}
}
get_footer();
