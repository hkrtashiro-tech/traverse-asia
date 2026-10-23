# Design board list and link rules shared by tools/build-theme.py.
import re
PAGES = {  # board -> (kind, key)
    'C2': ('pages', 'front'),
    'S-services': ('pages', 'services'), 'S-create': ('pages', 'create'),
    'S-design': ('pages', 'market-design'), 'S-connect': ('pages', 'connect'),
    'S-sell': ('pages', 'sell'), 'S-shopee': ('pages', 'shopee'),
    'S-vat': ('pages', 'uk-vat-registration'), 'S-public': ('pages', 'public-sector'),
    'S-shizune': ('pages', 'shizune'), 'S-about': ('pages', 'company'),
    'S-recruit': ('pages', 'recruit'), 'S-privacy': ('pages', 'privacy-policy'),
    'S-contact': ('pages', 'contact'), 'S-thanks': ('pages', 'thanks'),
    'S-works': ('archive', 'works'), 'S-insights': ('archive', 'news'),
    'S-article': ('single', 'article'), 'S-404': ('pages', '404'),
}
WORKS = ['shopee-management-support', 'kewpie-china-digital-marketing', 'event-promotion-beijing',
         'shopee-singapore-philippines', 'hokkaido-seafood-singaporetaiwan', 'hokkaido-seafood-hongkong-taiwan',
         'pony-canyon-philippines', 'matcha-launch-singapore', 'web-ads-singapore']
for w in WORKS: PAGES['W-' + w] = ('works', w)

CASES = [  # (keyword in link text, slug)
    ('注文数を3年で', 'shopee-management-support'), ('Shopee越境EC運営支援', 'shopee-management-support'),
    ('広告・アフィリエイト', 'shopee-singapore-philippines'), ('活貝', 'hokkaido-seafood-singaporetaiwan'),
    ('天皇誕生日', 'event-promotion-beijing'), ('日本政府観光局', 'web-ads-singapore'), ('Web広告', 'web-ads-singapore'),
    ('キユーピー', 'kewpie-china-digital-marketing'), ('ポニーキャニオン', 'pony-canyon-philippines'),
    ('水産加工品', 'hokkaido-seafood-hongkong-taiwan'), ('抹茶の新商品', 'matcha-launch-singapore'),
    ('この実績の詳細を見る', 'shopee-management-support'),
]
POSTS = [('Shopeeの運営費用', '/blog/shopee-operation-cost/'), ('Shopeeで売れる商品', '/blog/shopee-products-on-trend/'),
         ('Shopeeで成功するための完全ガイド', '/blog/shopee-startguide/'), ('商品力', '/blog/why-product-quality-is-not-enough/'),
         ('SHIZUNE（静音）」を発表', '/news/shizune-launch/')]
STEP = [('CREATE', '/services/create/'), ('DESIGN', '/services/market-design/'),
        ('CONNECT', '/services/connect/'), ('SELL', '/services/sell/')]
EXACT = {
    'Traverse Asia JAPAN TO ASIA': '/', 'トップ': '/', 'HOME トップ →': '/', 'トップへ戻る →': '/', 'JP': '/',
    'サービス': '/services/', 'SERVICES サービス →': '/services/', 'SERVICES サービス一覧': '/services/', 'サービスを見る': '/services/',
    '対応エリア': '/#area', '実績': '/works/', '実績を見る': '/works/', '関連する実績を見る': '/works/', 'すべての実績 →': '/works/',
    '自社ブランド': '/brands/shizune/', 'ブランドページを見る →': '/brands/shizune/', '会社情報': '/company/', '会社情報を見る': '/company/',
    '採用情報': '/recruit/', 'お問い合わせ': '/contact/', 'お問い合わせ →': '/contact/', 'プライバシーポリシー': '/privacy-policy/',
    'すべての記事 →': '/news/', '英国VAT登録代行のページを見る →': '/uk-vat-registration/',
    'Shopeeのサポートを見る →': '/services/sell/shopee/', '2つのプランを比べる': '#plans', 'ご利用の流れを見る': '#flow',
    '課題から探す': '#issues',
}
EXT = {'EN': "ta_en_url()", '会社案内（PDF）': "ta_pdf_url()", 'ダウンロードする →': "ta_pdf_url()"}

def resolve(txt, board, counters):
    if txt in EXT: return ('php', EXT[txt])
    if txt in EXACT: return ('path', EXACT[txt])
    if txt == 'contact@traverse-asia.com': return ('raw', 'mailto:contact@traverse-asia.com')
    if txt.startswith('Amazonで見る'): return ('raw', 'https://www.amazon.com/dp/B0FR4M4KTC')
    if txt.startswith('公式サイト（shizunematcha.com）'): return ('raw', 'https://shizunematcha.com/')
    if txt == '詳しく見る →':
        i = counters.setdefault('more', 0); counters['more'] += 1
        return ('path', STEP[i % 4][1])
    if '相談' in txt or txt.startswith('CONTACT'): return ('path', '/contact/')
    if txt.startswith(('WORKS', '画像を手配')): return ('path', '/works/')
    if txt.startswith('COMPANY'): return ('path', '/company/')
    if txt.startswith('NEWS'): return ('path', '/news/')
    if txt.startswith(('OUR BRAND', 'SHIZUNE MATCHA')): return ('path', '/brands/shizune/')
    if txt.startswith('自治体・公的機関'): return ('path', '/public-sector/')
    if txt.startswith(('これから海外に出たい', '海外で売り始めた')): return ('path', '/services/')
    if txt.startswith(('← 前の記事', '次の記事')): return ('path', '/news/')
    for k, p in STEP:
        if re.search(r'\b0\d (%s)\b' % k, txt): return ('path', p)
    for k, slug in CASES:
        if k in txt: return ('path', '/works/%s/' % slug)
    for k, p in POSTS:
        if k in txt: return ('path', p)
    if re.match(r'\d{4}\.', txt): return ('path', '/news/')
    for k, p in STEP:
        if re.search(r'\b0\d (%s)' % k, txt) or txt.startswith(k + ' ') and '→' in txt: return ('path', p)
    return None

