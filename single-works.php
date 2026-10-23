<?php
/**
 * Single case study: design section blocks, or the article layout for a case
 * written as plain text.
 */
get_header();
while ( have_posts() ) {
	the_post();
	$ta_content = get_the_content();
	$ta_key     = 'work-' . get_post_field( 'post_name' );
	if ( ta_has_sections( $ta_content ) || ta_design_content( $ta_key ) ) {
		ta_render_design( $ta_content, $ta_key );
	} else {
		get_template_part( 'template-parts/article' );
	}
}
get_footer();
