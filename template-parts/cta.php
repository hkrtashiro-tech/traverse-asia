<?php
/**
 * Dark CTA panel at the bottom of pages. $args['lines'] are the heading lines.
 */
$ta_lines = $args['lines'] ?? array( 'まずは、', '相談する。' );
?>
<section class="pad" data-sec="cta" style="max-width: 1320px; margin: 0 auto; box-sizing: border-box; padding: 0 40px 120px;"><div class="cta-panel" style="position: relative; overflow: hidden; border-radius: 32px; background: #22322F; color: #FFFFFF;">
<img class="cta-img" src="<?php echo ta_img( 'hero-harbor.jpg' ); ?>" alt="" aria-hidden="true" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: right center; opacity: 0.12; mix-blend-mode: luminosity;">
<div class="panel" style="position: relative; padding: 80px 56px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 32px 48px;"><div style="display: flex; flex-direction: column; gap: 18px; max-width: 680px;"><span class="en" style="font-size: 13px; font-weight: 600; letter-spacing: 0.12em; color: #B5D0A8;">CONTACT</span><h2 class="rv" style="margin: 0; font-size: clamp(28px, 3.4vw, 44px); font-weight: 700; line-height: 1.45;">
<?php foreach ( $ta_lines as $ta_line ) : ?><span style="display: inline-block;"><?php echo esc_html( $ta_line ); ?></span><?php endforeach; ?>
</h2><p style="margin: 0; color: #E3ECDF;">対象国や商材が決まっていない段階のご相談も歓迎です。</p></div>
<div style="display: flex; flex-wrap: wrap; gap: 12px;"><a href="<?php echo ta_url( '/contact/' ); ?>" class="pill" style="background: #FFFFFF; color: #22322F; text-decoration: none; border-radius: 999px; padding: 0 8px 0 26px; min-height: 56px; display: inline-flex; align-items: center; gap: 14px; font-weight: 700;">相談する<span class="ic" aria-hidden="true" style="width: 40px; height: 40px; border-radius: 50%; background: #22322F; color: #FFFFFF; display: inline-grid; place-items: center;">→</span></a><a href="<?php echo esc_url( ta_pdf_url() ); ?>" class="pill" style="color: #FFFFFF; text-decoration: none; border-radius: 999px; padding: 0 26px; min-height: 56px; display: inline-flex; align-items: center; font-weight: 700; border: 1px solid #FFFFFF;">会社案内（PDF）</a></div></div></div></section>
