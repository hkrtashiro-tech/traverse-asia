# Converts the design canvas boards in design/*.dc.html into the theme's generated
# parts (template-parts/pages|design|works, assets/css/base.generated.css,
# assets/css/pages/*.css).  Run from the repository root:  python3 tools/build-theme.py
import re, os, json
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
D = ROOT + '/design'
T = ROOT + '/wp-content/themes/traverse-asia'
blobs = json.load(open(D + '/blobmap.json'))  # canvas image id -> file in assets/img

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

def php_href(r):
    kind, v = r
    if kind == 'php': return '<?php echo esc_url( %s ); ?>' % v
    if kind == 'raw': return v
    if v.startswith('#'): return v
    return "<?php echo ta_url( '%s' ); ?>" % v

unresolved = []
LOG = []
def convert_links(html, board):
    counters = {}
    def rep(m):
        attrs, inner = m.groups()
        hm = re.search(r'href="([^"]*)"', attrs)
        if not hm or hm.group(1) != '#': return m.group(0)
        txt = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', inner)).strip().replace('&amp;', '&')
        r = resolve(txt, board, counters)
        LOG.append((board, txt[:60], r))
        if not r:
            unresolved.append((board, txt)); return m.group(0)
        return '<a ' + attrs.replace('href="#"', 'href="%s"' % php_href(r), 1) + '>' + inner + '</a>'
    return re.sub(r'<a ([^>]*)>([\s\S]*?)</a>', rep, html)

used_imgs = set()
def convert_imgs(html):
    def rep(m):
        fn = blobs[m.group(1)]; used_imgs.add(fn)
        return "<?php echo ta_img( '%s' ); ?>" % fn
    return re.sub(r'/_blob/([0-9a-f]{32})', rep, html)


def dynamic(b, main):
    """Swap the static sample lists in the boards for theme functions that print real posts."""
    def swap(pattern, repl, flags=0):
        nonlocal main
        new, n = re.subn(pattern, lambda m: repl, main, count=1, flags=flags)
        assert n == 1, (b, pattern[:60]); main = new
    if b == 'C2':
        swap(r'(?<=<div style="display: flex; flex-direction: column; border-top: 1px solid #CDD3C8;">)\n<a href="[^"]*" class="row"[\s\S]*?</a>\n(?=</div>\n</section>)',
             "\n<?php ta_news_rows_simple( 3 ); ?>\n")
    if b == 'S-insights':
        swap(r'<div style="display: flex; flex-wrap: wrap; gap: 8px;"><span[^>]*>すべて</span>[\s\S]*?</div>', '<?php ta_news_tabs(); ?>')
        swap(r'(?<=<div style="border-top: 1px solid #22322F;">)<a [\s\S]*?</a>(?=</div>\n<div style="display: flex; justify-content: center)', '<?php ta_news_list(); ?>')
        swap(r'<div style="display: flex; justify-content: center; gap: 8px; padding-top: 12px;" class="en">[\s\S]*?</span></div>', '<?php ta_pagination(); ?>')
    if b == 'S-works':
        swap(r'<div style="display: flex; flex-direction: column; gap: 16px; border-top: 1px solid #22322F; padding-top: 28px;">[\s\S]*?</div></div>\n', '<?php ta_works_filters(); ?>\n')
        swap(r'(?<=<div class="wk" style="gap: 20px;">)[\s\S]*?(?=</div></section>)', '<?php ta_works_grid(); ?>')
    if b == 'S-contact':
        swap(r'<form[\s\S]*?</form>', "<?php get_template_part( 'template-parts/contact-form' ); ?>")
    return main

# ---- CSS: split helmet styles into shared (in every board) and per-board rules
def css_rules(css):
    out, i, n = [], 0, len(css)
    while i < n:
        j = css.find('{', i)
        if j < 0: break
        sel = css[i:j].strip()
        depth, k = 1, j + 1
        while depth:
            if css[k] == '{': depth += 1
            elif css[k] == '}': depth -= 1
            k += 1
        body = css[j + 1:k - 1]
        if sel.startswith('@media'):
            for _, r2 in css_rules(body): out.append((sel, r2))
        else:
            out.append(('', sel + '{' + body + '}'))
        i = k
    return [(m, r) for m, r in out]

def emit_css(rules):
    out, cur = [], None
    for m, r in rules:
        if m != cur:
            if cur: out.append('}')
            if m: out.append(m + '{')
            cur = m
        out.append(r)
    if cur: out.append('}')
    return '\n'.join(out) + '\n'

boards = {}
for b in PAGES:
    src = open('%s/%s.dc.html' % (D, b)).read()
    helmet = re.search(r'<helmet>([\s\S]*?)</helmet>', src).group(1)
    css = '\n'.join(re.findall(r'<style>([\s\S]*?)</style>', helmet))
    body = re.search(r'</helmet>([\s\S]*?)</x-dc>', src).group(1)
    main = body[body.index('</header>') + 9: body.index('<footer')]
    boards[b] = (css_rules(css), main)

sets = [set(r for r in rules) for rules, _ in boards.values()]
common = set.intersection(*sets)
seen, common_list = set(), []
for r in boards['S-sell'][0]:
    if r in common and r not in seen: seen.add(r); common_list.append(r)
skip = {('', 'body{margin:0;background:#F4F5F1}')}
os.makedirs(T + '/assets/css/pages', exist_ok=True)
open(T + '/assets/css/base.generated.css', 'w').write(
    '/* Generated from the design canvas: rules shared by every board. Do not edit by hand. */\n' +
    emit_css([r for r in common_list if r not in skip]))

for b, (kind, key) in PAGES.items():
    rules, main = boards[b]
    seen, own = set(), []
    for r in rules:
        if r not in common and r not in seen: seen.add(r); own.append(r)
    fname = ('work-' + key) if kind == 'works' else key
    open('%s/assets/css/pages/%s.css' % (T, fname), 'w').write(
        '/* Generated from design board %s. */\n' % b + emit_css(own))
    main = dynamic(b, convert_imgs(convert_links(main, b)))
    main = main.replace('aria-label="パンくず"', 'aria-label="パンくず"')
    if b == 'C2':
        main = main.replace('data-sec="map"', 'id="area" data-sec="map"', 1)
    if b == 'S-services':
        # the section with "issue" entry cards is the anchor target of 課題から探す
        k = main.find('これから海外に出たい'); s = main.rfind('<section', 0, k)
        main = main[:s] + main[s:].replace('<section', '<section id="issues"', 1)
    sub = {'pages': 'template-parts/pages', 'archive': 'template-parts/design', 'single': 'template-parts/design',
           'works': 'template-parts/works'}[kind]
    os.makedirs(T + '/' + sub, exist_ok=True)
    open('%s/%s/%s.php' % (T, sub, key), 'w').write(
        "<?php\n/**\n * Generated from design board %s. Regenerate instead of editing by hand.\n */\n?>\n" % b + main.strip() + '\n')

missing = [f for f in sorted(used_imgs) if not os.path.exists(T + '/assets/img/' + f)]
print('images', len(used_imgs), 'missing', missing, 'shared css rules', len(common_list))
print('unresolved links', sorted(set(unresolved)))
