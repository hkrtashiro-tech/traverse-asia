<?php
/**
 * Contact form slot on /contact/.
 */
$ta_form_id = ta_contact_form_id();
if ( $ta_form_id ) : ?>
<div class="ta-form-wrap" data-thanks="<?php echo esc_url( home_url( ta_is_en() ? '/en/contact/thanks/' : '/contact/thanks/' ) ); ?>" style="min-width: 0;">
	<?php echo do_shortcode( '[contact-form-7 id="' . $ta_form_id . '"]' ); ?>
</div>
<?php else : ?>
<div style="border-top: 1px solid #22322F; padding: 28px 0; min-width: 0;">
	<p style="margin: 0;"><?php echo ta_is_en() ? 'The form is being prepared. Please email us at' : 'フォームを準備中です。お手数ですが'; ?> <a href="mailto:sales@traverse-asia.com">sales@traverse-asia.com</a><?php echo ta_is_en() ? '.' : ' までメールでお問い合わせください。'; ?></p>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<p style="margin: 16px 0 0; font-size: 14px; color: #8A3B2E;">管理者向け：Contact Form 7 を有効化し、外観 → カスタマイズ → サイト設定 で「お問い合わせフォーム」を選んでください（フォーム本文は テーマの cf7/contact-form.html）。</p>
	<?php endif; ?>
</div>
<?php
endif;
