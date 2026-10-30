</main>
<footer style="border-top: 1px solid #CDD3C8;">
<div class="pad" style="max-width: 1320px; margin: 0 auto; padding: 56px 40px 40px; box-sizing: border-box; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 32px 64px; font-size: 14px; color: #2E3D39;">
<div style="display: flex; flex-direction: column; gap: 4px;">
<span style="font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: #22322F;">Traverse Asia</span>
<span><?php echo esc_html( ta_t( 'Traverse Asia合同会社', 'Traverse Asia LLC' ) ); ?></span>
<a href="mailto:sales@traverse-asia.com" style="color: #2E3D39; text-decoration: none;">sales@traverse-asia.com</a>
</div>
<nav aria-label="<?php echo esc_attr( ta_t( 'フッターメニュー', 'Footer menu' ) ); ?>" style="display: flex; flex-wrap: wrap; gap: 8px 28px; align-content: flex-start;">
<?php ta_menu_links( ta_is_en() ? 'footer_en' : 'footer', '#2E3D39' ); ?>
</nav>
<span class="en" style="width: 100%; font-size: 13px; color: #45524E;">© <?php echo esc_html( wp_date( 'Y' ) ); ?> Traverse Asia LLC</span>
</div>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
