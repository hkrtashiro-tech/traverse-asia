<?php
/**
 * Top page. Built from design section blocks (edit the front page in the block editor).
 */
get_header();
while ( have_posts() ) {
	the_post();
	ta_render_design( get_the_content(), 'front' );
}
get_footer();
