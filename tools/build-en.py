# Builds the English pages' initial content (blocks/content/en*.html) from the
# Japanese pages' section blocks: texts are replaced from tools/en/translations.json,
# links point to the /en/ pages, and sections aimed at Japanese companies only
# (subsidies, UK VAT, municipalities, Japanese news) are left out.
# Run after tools/build-theme.py:  python3 tools/build-en.py
import json, os, re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
T = ROOT + '/wp-content/themes/traverse-asia/blocks'
TR = json.load(open(ROOT + '/tools/en/translations.json'))
JP = re.compile(r'[\u3040-\u30ff\u4e00-\u9fff]')

# English page (path under the site) -> Japanese content it is built from.
PAGES = {
    'en': 'front', 'en/services': 'services', 'en/services/create': 'create',
    'en/services/market-design': 'market-design', 'en/services/connect': 'connect',
    'en/services/sell': 'sell', 'en/works': 'works', 'en/shizune': 'shizune',
    'en/about': 'company', 'en/contact': 'contact', 'en/contact/thanks': 'thanks',
}
LINKS = {
    '/': '/en/', '/contact/': '/en/contact/', '/services/': '/en/services/',
    '/services/create/': '/en/services/create/', '/services/market-design/': '/en/services/market-design/',
    '/services/connect/': '/en/services/connect/', '/services/sell/': '/en/services/sell/',
    '/works/': '/en/works/', '/brands/shizune/': '/en/shizune/', '/company/': '/en/about/', '/#area': '/en/#area',
}
DROP_BLOCKS = {'ta/front-10', 'ta/sell-6'}  # Japanese news list; UK VAT for Japanese sellers
REMOVE = object()

ICON_STYLE = {  # the three partner cards keep the three icon variants of the design
    'eyebrow': 'font-size: 13px; font-weight: 700; color: #2F5A3A; letter-spacing: 0.08em;',
    'head': 'font-size: 21px; font-weight: 700; line-height: 1.6;',
    'text': 'font-size: 14.5px; color: #2E3D39; line-height: 1.9;',
}


def partner_cards():
    cards = [
        ('/en/contact/', 'Distributors & importers', 'Carry Japanese products in your market',
         'We introduce Japanese makers and brands, and support samples, pricing and export.', 'Talk to us', 0),
        ('/en/contact/', 'Retailers & e-commerce', 'Stock Japanese brands, online or in store',
         'From product selection to steady supply and local promotion.', 'Talk to us', 1),
        ('/en/services/', 'Japanese brands & regions', 'Take products overseas, step by step',
         'Brand building, market design, local partners and sales channels.', 'Our services', 2),
    ]
    return [{'f1': link, 'f2': [
        {'f1': ICON_STYLE['eyebrow'], 'f2': a, '_v': 0},
        {'f1': ICON_STYLE['head'], 'f2': b, '_v': 0},
        {'f1': ICON_STYLE['text'], 'f2': c, '_v': 0}], 'f3': d, '_v': v} for link, a, b, c, d, v in cards]


def case_rows():
    S = 'font-size: 19px; font-weight: 700;'
    D = 'font-size: 14.5px; color: #2E3D39;'
    rows = [
        ('kewpie-china-digital-marketing', 'kewpie-cp.jpg', 'Kewpie campaign screen for China', 'CHINA', 'Kewpie', 'Integrated communication in China: WeChat, Weibo, website and fan club', 'Create / Connect'),
        ('event-promotion-beijing', 'embassy.jpg', 'Screen at the Emperor’s Birthday reception', 'BEIJING', 'Embassy of Japan in China', 'Showcasing regional Japan at the Emperor’s Birthday reception', 'Connect'),
        ('shopee-singapore-philippines', 'shopee-metrics.jpg', 'Shopee store dashboard', 'SINGAPORE / PHILIPPINES', 'Our own Shopee stores', 'Growing Shopee sales with ads, affiliates and campaigns', 'Sell'),
        ('hokkaido-seafood-singaporetaiwan', 'fair-sg.jpg', 'Notice for a Hokkaido shellfish tasting', 'SINGAPORE / TAIWAN', 'Hokkaido Government', 'Hokkaido live shellfish fairs in Singapore and Taiwan', 'Connect'),
        ('hokkaido-seafood-hongkong-taiwan', 'hk-booth.jpg', 'Hokkaido seafood products showcased in a Hong Kong store', 'HONG KONG / TAIWAN', 'Hokkaido Government', 'Expanding sales of Hokkaido processed seafood', 'Design the Market / Connect'),
        ('pony-canyon-philippines', 'pony.jpg', 'Video by a Philippine influencer', 'PHILIPPINES', 'Pony Canyon', 'Music promotion with local influencers', 'Create / Connect'),
        ('matcha-launch-singapore', 'yamato.jpg', 'Yamato Matcha packaging and whisk', 'SINGAPORE', 'Yamato Matcha', 'Launching a new matcha product', 'Design the Market / Create / Sell'),
        ('web-ads-singapore', 'sg-v.jpg', 'Singapore cityscape', 'SINGAPORE', 'Japan National Tourism Organization', 'Web advertising in Singapore to grow visits to Japan', 'Connect'),
    ]
    return [{'f1': '/works/%s/' % slug, 'f2': img, 'f3': alt, 'f4': area,
             'f5': '<span style="%s">%s</span><span style="%s">%s</span>' % (S, who, D, what), 'f6': steps, '_v': 0}
            for slug, img, alt, area, who, what, steps in rows]


def translate(v):
    if not isinstance(v, str):
        return v
    if v in TR:
        return REMOVE if TR[v] is None else TR[v]
    if v in LINKS:
        return LINKS[v]
    if 'Amazonのレビュー 約40件' in v:
        return v.replace('Amazonのレビュー 約40件', 'About 40 Amazon reviews')
    return v


def walk(o):
    """Translate values; a list item holding a removed text is dropped."""
    if isinstance(o, dict):
        out = {}
        for k, v in o.items():
            nv = walk(v)
            if nv is REMOVE:
                return REMOVE
            out[k] = nv
        return out
    if isinstance(o, list):
        return [x for x in (walk(i) for i in o) if x is not REMOVE]
    return translate(o)


def comment(name, data):
    j = json.dumps({'data': data}, ensure_ascii=False, separators=(',', ':'))
    j = j.replace('--', '\\u002d\\u002d').replace('<', '\\u003c').replace('>', '\\u003e').replace('&', '\\u0026')
    return '<!-- wp:%s %s /-->' % (name, j)


def build(path, key):
    src = open('%s/content/%s.html' % (T, key)).read()
    blocks = []
    for m in re.finditer(r'<!-- wp:(\S+) (\{.*?\}) /-->', src):
        name, data = m.group(1), json.loads(m.group(2))['data']
        if name in DROP_BLOCKS:
            continue
        if name == 'ta/front-5':
            data = {'f1': 'PARTNER WITH US', 'f2': 'Work with us.', 'f3': partner_cards()}
        elif name == 'ta/works-3':  # automatic Japanese list → the cases in English
            name = 'ta/front-9'
            data = {'f1': 'CASE STUDIES', 'f2': 'Case studies', 'f3': '/en/contact/', 'f4': 'Ask about our work →', 'f5': case_rows()}
        elif name == 'ta/market-design-7':  # drop the row without a case page
            data['f3'] = [r for r in data['f3'] if '食品メーカー' not in json.dumps(r, ensure_ascii=False)]
        data = walk(data)
        if data is REMOVE:
            continue
        blocks.append(comment(name, data))
    out = '\n\n'.join(blocks) + '\n'
    left = sorted(set(re.findall(r'[^"\\]*[\u3040-\u30ff\u4e00-\u9fff][^"\\]*', out.encode().decode('unicode_escape', 'ignore') if False else out)))
    left = [s for s in left if 'SHIZUNE 静音' not in s and '静音' not in s and 'Traverse Asia合同会社' not in s]
    fname = path.replace('/', '-')
    open('%s/content/%s.html' % (T, fname), 'w').write(out)
    return fname, left


if __name__ == '__main__':
    for path, key in PAGES.items():
        fname, left = build(path, key)
        print('%-28s %s' % (fname, ('untranslated: %s' % left[:6]) if left else 'ok'))
