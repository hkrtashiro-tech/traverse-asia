<?php
/**
 * Site settings in Customizer (外観 → カスタマイズ → サイト設定).
 */

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section( 'ta_site', array( 'title' => 'サイト設定', 'priority' => 30 ) );

		$fields = array(
			'ta_pdf_url'       => array( '会社案内PDFのURL（空ならテーマ同梱のPDF）', 'esc_url_raw', 'url' ),
			'ta_en_url'        => array( '英語版サイトのURL（未設定ならトップ）', 'esc_url_raw', 'url' ),
			'ta_featured_work' => array( '実績一覧で大きく見せる実績（スラッグ）', 'sanitize_title', 'text' ),
		);
		foreach ( $fields as $id => $f ) {
			$wp_customize->add_setting( $id, array( 'sanitize_callback' => $f[1] ) );
			$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'ta_site', 'type' => $f[2] ) );
		}

		$forms = array( 0 => '（未選択）' );
		foreach ( get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => -1 ) ) as $form ) {
			$forms[ $form->ID ] = $form->post_title;
		}
		$wp_customize->add_setting( 'ta_cf7_id', array( 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control(
			'ta_cf7_id',
			array(
				'label'   => 'お問い合わせフォーム（Contact Form 7）',
				'section' => 'ta_site',
				'type'    => 'select',
				'choices' => $forms,
			)
		);
	}
);
