<?php
/**
 * One-time content updates that ship with a theme version. They run once, in the
 * admin, after the theme is updated. Changed pages keep their previous text in
 * revisions.
 */

add_action(
	'admin_init',
	function () {
		$done = (int) get_option( 'ta_content_version', 1 );
		if ( $done >= 2 || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// v2: the contact address is sales@ (was contact@) in pages, cases and the form mails.
		$from  = 'contact@traverse-asia.com';
		$to    = 'sales@traverse-asia.com';
		$posts = get_posts(
			array(
				'post_type'   => array( 'page', 'works' ),
				'post_status' => 'any',
				'numberposts' => -1,
				's'           => $from,
			)
		);
		foreach ( $posts as $post ) {
			if ( false !== strpos( $post->post_content, $from ) ) {
				wp_update_post( array( 'ID' => $post->ID, 'post_content' => wp_slash( str_replace( $from, $to, $post->post_content ) ) ) );
			}
		}
		if ( class_exists( 'WPCF7_ContactForm' ) ) {
			foreach ( WPCF7_ContactForm::find() as $form ) {
				$props   = $form->get_properties();
				$changed = false;
				foreach ( array( 'mail', 'mail_2' ) as $key ) {
					if ( empty( $props[ $key ] ) || ! is_array( $props[ $key ] ) ) {
						continue;
					}
					foreach ( $props[ $key ] as $field => $value ) {
						if ( is_string( $value ) && false !== strpos( $value, $from ) ) {
							$props[ $key ][ $field ] = str_replace( $from, $to, $value );
							$changed                 = true;
						}
					}
				}
				if ( $changed ) {
					$form->set_properties( array( 'mail' => $props['mail'], 'mail_2' => $props['mail_2'] ) );
					$form->save();
				}
			}
		}
		update_option( 'ta_content_version', 2 );
	}
);
