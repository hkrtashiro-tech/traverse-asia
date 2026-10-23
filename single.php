<?php
/**
 * Single column post or news post.
 */
get_header();
while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/article' );
}
get_footer();
