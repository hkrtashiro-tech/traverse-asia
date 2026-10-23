<?php
/**
 * Same as 外観 → 初期設定 in the admin, for WP-CLI:  wp eval-file tools/setup-site.php
 * The setup itself lives in the theme (inc/site-setup.php).
 */
if ( ! function_exists( 'ta_run_site_setup' ) ) {
	exit( "Activate the Traverse Asia theme first, then run: wp eval-file tools/setup-site.php\n" );
}
foreach ( ta_run_site_setup() as $line ) {
	WP_CLI::log( $line );
}
WP_CLI::success( 'done' );
