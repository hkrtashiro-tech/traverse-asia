<?php
/**
 * Site header. Markup follows the design canvas header.
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php echo esc_html( ta_t( '本文へ移動', 'Skip to content' ) ); ?></a>
<div class="site" style="background: #F4F5F1; color: #22322F; font-family: 'Noto Sans JP', 'Hiragino Sans', 'Yu Gothic', sans-serif; font-size: 16px; line-height: 2; letter-spacing: 0.02em;">
<header class="site-header">
<div class="pad hdr-in" style="max-width: 1320px; margin: 0 auto; padding: 20px 40px; display: flex; flex-wrap: wrap; align-items: center; gap: 10px 32px; box-sizing: border-box;">
<a href="<?php echo esc_url( ta_home() ); ?>" rel="home" style="text-decoration: none; color: #22322F; display: inline-flex; align-items: baseline; gap: 12px; margin-right: auto;"><span style="font-family: 'Cormorant Garamond', serif; font-size: 27px; font-weight: 600; letter-spacing: 0;">Traverse Asia</span><span class="en hide-sm" style="font-size: 12px; letter-spacing: 0.14em; color: #45524E;">JAPAN TO ASIA</span></a>
<button class="menu-btn" type="button" aria-expanded="false" aria-controls="c-nav" style="font: inherit; font-size: 14px; font-weight: 700; background: transparent; border: 1px solid #22322F; border-radius: 999px; color: #22322F; padding: 0 20px; min-height: 44px; align-items: center; cursor: pointer;" data-open="<?php echo esc_attr( ta_t( '閉じる', 'Close' ) ); ?>" data-closed="<?php echo esc_attr( ta_t( 'メニュー', 'Menu' ) ); ?>"><?php echo esc_html( ta_t( 'メニュー', 'Menu' ) ); ?></button>
<nav id="c-nav" class="nav" aria-label="<?php echo esc_attr( ta_t( 'メインメニュー', 'Main menu' ) ); ?>" style="display: flex; flex-wrap: wrap; align-items: center; gap: 6px 28px; font-size: 14.5px;">
<?php ta_menu_links( ta_is_en() ? 'header_en' : 'header', '#22322F' ); ?>
<span class="en" style="font-size: 14px; color: #45524E;"><a href="<?php echo esc_url( ta_ja_url() ); ?>" hreflang="ja" lang="ja"<?php echo ta_is_en() ? '' : ' aria-current="true"'; ?> style="color: <?php echo ta_is_en() ? '#45524E' : '#22322F; font-weight: 600'; ?>; text-decoration: none;">JP</a> / <a href="<?php echo esc_url( ta_en_url() ); ?>" hreflang="en" lang="en"<?php echo ta_is_en() ? ' aria-current="true"' : ''; ?> style="color: <?php echo ta_is_en() ? '#22322F; font-weight: 600' : '#45524E'; ?>; text-decoration: none;">EN</a></span>
<a href="<?php echo esc_url( ta_is_en() ? home_url( '/en/contact/' ) : home_url( '/contact/' ) ); ?>" class="pill" style="background: #22322F; color: #FFFFFF; text-decoration: none; border-radius: 999px; padding: 0 8px 0 22px; min-height: 46px; display: inline-flex; align-items: center; gap: 12px; font-weight: 700;"><?php echo esc_html( ta_t( '相談する', 'Contact us' ) ); ?><span class="ic" aria-hidden="true" style="width: 30px; height: 30px; border-radius: 50%; background: #F4F5F1; color: #22322F; display: inline-grid; place-items: center; font-size: 14px;">→</span></a>
</nav>
</div>
</header>
<main id="main">
