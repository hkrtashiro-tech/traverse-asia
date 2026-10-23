# Converts the design canvas boards in design/*.dc.html into the theme's editable
# section blocks:
#   wp-content/themes/traverse-asia/blocks/blocks.json   block types (markup tree, fields, defaults)
#   wp-content/themes/traverse-asia/blocks/content/*.html each page's initial content (serialized blocks)
#   wp-content/themes/traverse-asia/assets/css/design.generated.css  all design CSS
# Run from the repository root:  python3 tools/build-theme.py
import hashlib, json, os, re, sys
from bs4 import BeautifulSoup, NavigableString, Tag, Comment

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from designmap import PAGES, resolve  # noqa: E402

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
D = ROOT + '/design'
T = ROOT + '/wp-content/themes/traverse-asia'
blobs = json.load(open(D + '/blobmap.json'))  # canvas image id -> file in assets/img

# Boards that become editable pages, in the order of the block inserter categories.
CATEGORY = {
    'C2': 'トップ', 'S-services': 'サービス', 'S-create': 'サービス', 'S-design': 'サービス', 'S-connect': 'サービス',
    'S-sell': 'サービス', 'S-shopee': 'サービス', 'S-vat': 'サービス', 'S-public': '自治体・公的機関',
    'S-shizune': '自社ブランド', 'S-about': '会社・採用', 'S-recruit': '会社・採用', 'S-privacy': 'その他',
    'S-contact': 'お問い合わせ', 'S-thanks': 'お問い合わせ', 'S-works': '実績', 'S-insights': 'お知らせ', 'S-404': 'その他',
}
for b in PAGES:
    if b.startswith('W-'):
        CATEGORY[b] = '実績'
SKIP = {'S-article'}  # single posts keep the hand-written article template
CALL_LABEL = {
    'ta_news_rows_simple': '最新のお知らせ3件', 'ta_news_tabs': 'お知らせの種類タブ', 'ta_news_list': 'お知らせ一覧',
    'ta_pagination': 'ページ送り', 'ta_works_filters': '実績の絞り込み', 'ta_works_grid': '実績一覧',
    'ta_contact_form': 'お問い合わせフォーム',
}


# ---------------------------------------------------------------- preparation
def dynamic(b, main):
    """Swap the sample lists in the boards for markers of the theme functions that print real posts."""
    def call(fn, args=''):
        return '<ta-call data-fn="%s" data-args="%s"></ta-call>' % (fn, args)

    def swap(pattern, repl):
        nonlocal main
        new, n = re.subn(pattern, lambda m: repl, main, count=1)
        assert n == 1, (b, pattern[:60])
        main = new
    if b == 'C2':
        swap(r'(?<=<div style="display: flex; flex-direction: column; border-top: 1px solid #CDD3C8;">)\n<a href="[^"]*" class="row"[\s\S]*?</a>\n(?=</div>\n</section>)',
             call('ta_news_rows_simple', '3'))
    if b == 'S-insights':
        swap(r'<div style="display: flex; flex-wrap: wrap; gap: 8px;"><span[^>]*>すべて</span>[\s\S]*?</div>', call('ta_news_tabs'))
        swap(r'(?<=<div style="border-top: 1px solid #22322F;">)<a [\s\S]*?</a>(?=</div>\n<div style="display: flex; justify-content: center)', call('ta_news_list'))
        swap(r'<div style="display: flex; justify-content: center; gap: 8px; padding-top: 12px;" class="en">[\s\S]*?</span></div>', call('ta_pagination'))
    if b == 'S-works':
        swap(r'<div style="display: flex; flex-direction: column; gap: 16px; border-top: 1px solid #22322F; padding-top: 28px;">[\s\S]*?</div></div>\n', call('ta_works_filters'))
        swap(r'(?<=<div class="wk" style="gap: 20px;">)[\s\S]*?(?=</div></section>)', call('ta_works_grid'))
    if b == 'S-contact':
        swap(r'<form[\s\S]*?</form>', call('ta_contact_form'))
    return main


def prep(b):
    src = open('%s/%s.dc.html' % (D, b)).read()
    helmet = re.search(r'<helmet>([\s\S]*?)</helmet>', src).group(1)
    css = '\n'.join(re.findall(r'<style>([\s\S]*?)</style>', helmet))
    body = re.search(r'</helmet>([\s\S]*?)</x-dc>', src).group(1)
    main = body[body.index('</header>') + 9: body.index('<footer')]
    counters = {}

    def link(m):
        attrs, inner = m.groups()
        if 'href="#"' not in attrs:
            return m.group(0)
        txt = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', inner)).strip().replace('&amp;', '&')
        r = resolve(txt, b, counters)
        assert r, (b, txt)
        val = {'ta_pdf_url()': '@pdf', 'ta_en_url()': '@en'}.get(r[1], r[1])
        return '<a ' + attrs.replace('href="#"', 'href="%s"' % val, 1) + '>' + inner + '</a>'
    main = re.sub(r'<a ([^>]*)>([\s\S]*?)</a>', link, main)
    main = re.sub(r'/_blob/([0-9a-f]{32})', lambda m: blobs[m.group(1)], main)
    main = dynamic(b, main)
    if b == 'C2':
        main = main.replace('data-sec="map"', 'id="area" data-sec="map"', 1)
    if b == 'S-services':
        k = main.find('これから海外に出たい')
        s = main.rfind('<section', 0, k)
        main = main[:s] + main[s:].replace('<section', '<section id="issues"', 1)
    raws = []

    def raw(m):
        raws.append(m.group(0))
        return '<ta-raw data-i="%d"></ta-raw>' % (len(raws) - 1)
    main = re.sub(r'<svg[\s\S]*?</svg>', raw, main)
    return css, main, raws


# ---------------------------------------------------------------- markup -> tree
VOID = {'img', 'br', 'input', 'meta', 'hr', 'source'}
INLINE = {'a', 'strong', 'b', 'em', 'i', 'br', 'small', 'sup', 'sub', 'span'}
BLOCKY = re.compile(r'display:\s*(flex|grid|block|inline-flex|inline-grid|inline-block)')
SYMBOL = re.compile(r'^[\s→←↓↑／/・|｜—–\-+＋×✓★☆●○◎※:：.,、。()（）]*$')


def is_lines(el):
    kids = [c for c in el.children if not (isinstance(c, NavigableString) and not c.strip())]
    if not kids:
        return False
    for c in kids:
        if not (isinstance(c, Tag) and c.name == 'span' and 'inline-block' in c.get('style', '')):
            return False
        if len(c.contents) != 1 or not isinstance(c.contents[0], NavigableString) or not c.contents[0].strip():
            return False
    return True


def is_rich(el):
    has_text = has_tag = False
    for d in el.descendants:
        if isinstance(d, Comment):
            continue
        if isinstance(d, NavigableString):
            has_text = has_text or bool(d.strip())
            continue
        has_tag = True
        if d.name not in INLINE or BLOCKY.search(d.get('style', '')) or d.get('class'):
            return False
    return has_text and has_tag


def inner_html(el):
    return ''.join(str(c) for c in el.contents)


def build(node, raws, ctx):
    """Element -> tree node. Field nodes carry their default value; keys are assigned later."""
    if isinstance(node, Comment):
        return None
    if isinstance(node, NavigableString):
        t = str(node)
        if not t.strip() or SYMBOL.match(t):
            return ['s', t]
        return ['t', None, t, ctx]
    if node.name == 'ta-raw':
        return ['x', raws[int(node['data-i'])]]
    if node.name == 'ta-call':
        return ['c', node['data-fn'], node.get('data-args', '')]
    attrs = {}
    for k, v in node.attrs.items():
        if k == 'href' and not v.startswith('#'):
            attrs[k] = ['f', None, v, 'link', ctx_text(node)]
        elif k == 'src' and node.name == 'img':
            attrs[k] = ['f', None, v, 'image', node.get('alt', '')]
        elif k == 'alt' and v:
            attrs[k] = ['f', None, v, 'alt', '']
        else:
            attrs[k] = v
    cctx = role(node, ctx)
    if node.name in VOID:
        children = []
    elif is_lines(node):
        first = [c for c in node.children if isinstance(c, Tag)][0]
        lines = '\n'.join(c.get_text() for c in node.children if isinstance(c, Tag))
        children = [['l', None, lines, cctx, dict(first.attrs)]]
    elif is_rich(node):
        children = [['r', None, inner_html(node), cctx]]
    else:
        children = [c for c in (build(c, raws, cctx) for c in node.children) if c is not None]
        children = repeaters(children)
    return ['e', node.name, attrs, children]


def ctx_text(el):
    return re.sub(r'\s+', ' ', el.get_text(' ')).strip()[:24]


def role(el, ctx):
    cls = el.get('class', '') or ''
    if el.name in ('h1', 'h2', 'h3', 'h4'):
        return {'h1': '大見出し', 'h2': '見出し', 'h3': '小見出し', 'h4': '小見出し'}[el.name]
    if el.name == 'a' and 'pill' in cls:
        return 'ボタンの文字'
    if el.name in ('p', 'dd', 'blockquote', 'figcaption'):
        return {'blockquote': '引用', 'figcaption': 'キャプション'}.get(el.name, '本文')
    if el.name in ('li', 'dt'):
        return '項目'
    if el.name == 'span' and 'en' in cls.split():
        return '英字ラベル'
    return ctx


def skeleton(n):
    if n[0] == 's':
        return n
    if n[0] in ('t', 'r', 'l'):
        return [n[0]]
    if n[0] == 'e':
        # style / class values may differ between items of a list (e.g. image position); they stay per item.
        return ['e', n[1], {k: (['f', v[3]] if isinstance(v, list) else ('*' if k in ('style', 'class') else v))
                            for k, v in n[2].items()}, [skeleton(c) for c in n[3]]]
    if n[0] == 'rep':
        seen = []
        for it in n[2]:
            v = skeleton(it)
            if v not in seen:
                seen.append(v)
        return ['rep', seen]
    return n


def sk(n):
    return json.dumps(skeleton(n), ensure_ascii=False, sort_keys=True)


LIST_TAGS = {'li', 'dt', 'dd', 'tr', 'figure', 'article'}


def same_kind(a, b):
    """Siblings that belong to one list: same structure, or same tag with the same (non-empty) class."""
    if a[1] != b[1]:
        return False
    if sk(a) == sk(b):
        return True
    ca, cb = a[2].get('class'), b[2].get('class')
    if isinstance(ca, str) and ca and ca == cb:
        return True
    return a[1] in LIST_TAGS


def repeaters(children):
    """Turn runs of 2+ sibling elements of one kind into a list (repeater). Items whose structure
    differs become variants of the list; each item keeps its variant."""
    out, i = [], 0
    while i < len(children):
        n = children[i]
        if n[0] != 'e':
            out.append(n)
            i += 1
            continue
        items, seps, j = [n], [], i + 1
        while j < len(children):
            sep, k = '', j
            if children[k][0] == 's' and not children[k][1].strip():
                sep, k = children[k][1], k + 1
            if k < len(children) and children[k][0] == 'e' and same_kind(n, children[k]):
                seps.append(sep)
                items.append(children[k])
                j = k + 1
            else:
                break
        if len(items) > 1 and len(set(seps)) <= 1:
            groups = {}
            for it in items:
                groups.setdefault(sk(it), []).append(it)
            for g in groups.values():
                if len(g) > 1:
                    mark_vars(g)
            out.append(['rep', None, items, seps[0] if seps else '', n])
            i = j
        else:
            out.append(n)
            i += 1
    return out


def mark_vars(items):
    """Items share a structure; style/class values that differ between them become per-item values."""
    def walk(nodes):
        first = nodes[0]
        if first[0] == 'e':
            for a in ('style', 'class'):
                vals = [n[2].get(a) for n in nodes]
                if any(isinstance(v, list) for v in vals) or len(set(vals)) > 1:
                    for n in nodes:
                        v = n[2].get(a)
                        if not isinstance(v, list):
                            n[2][a] = ['f', None, v or '', 'var', '']
            for kids in zip(*[n[3] for n in nodes]):
                walk(list(kids))
    walk(items)


# ---------------------------------------------------------------- keys, fields, defaults
def label_for(kind, ctx, value):
    if kind == 'link':
        return 'リンク先' + ('（%s）' % ctx if ctx else '')
    if kind == 'image':
        return '画像'
    if kind == 'alt':
        return '画像の説明（代替テキスト）'
    if kind == 'lines':
        return (ctx or 'テキスト') + '（改行ごとに1行）'
    if kind == 'rich':
        return (ctx or 'テキスト')
    return ctx or 'テキスト'


class Scope:
    def __init__(self):
        self.n = 0
        self.fields = []

    def key(self):
        self.n += 1
        return 'f%d' % self.n


def assign(nodes, scope, data):
    """Give field nodes keys (in document order) and collect field definitions and values."""
    out = []
    for n in nodes:
        out.append(assign_node(n, scope, data))
    return out


def text_type(v):
    return 'textarea' if len(v) > 36 or '\n' in v else 'text'


def assign_node(n, scope, data):
    kind = n[0]
    if kind == 't':
        k = scope.key()
        scope.fields.append({'k': k, 'type': text_type(n[2]), 'label': label_for('text', n[3], n[2])})
        data[k] = n[2]
        return ['t', k]
    if kind == 'r':
        k = scope.key()
        scope.fields.append({'k': k, 'type': 'rich', 'label': label_for('rich', n[3], n[2])})
        data[k] = n[2]
        return ['r', k]
    if kind == 'l':
        k = scope.key()
        scope.fields.append({'k': k, 'type': 'lines', 'label': label_for('lines', n[3], n[2])})
        data[k] = n[2]
        return ['l', k, n[4]]
    if kind == 'e':
        attrs = {}
        for a, v in n[2].items():
            if isinstance(v, list):
                k = scope.key()
                scope.fields.append({'k': k, 'type': v[3], 'label': label_for(v[3], v[4], v[2])})
                data[k] = v[2]
                attrs[a] = ['f', k, v[3]]
            else:
                attrs[a] = v
        return ['e', n[1], attrs, assign(n[3], scope, data)]
    if kind == 'rep':
        k = scope.key()
        variants, vfields, vsk, values = [], [], [], []
        for item in n[2]:
            s2, d2 = Scope(), {}
            t = assign_node(item, s2, d2)
            key_sk = sk(item)
            if key_sk not in vsk:
                vsk.append(key_sk)
                variants.append(t)
                vfields.append(s2.fields)
            d2['_v'] = vsk.index(key_sk)
            values.append(d2)
        scope.fields.append({'k': k, 'type': 'repeat', 'label': rep_label(n[4]), 'variants': vfields})
        data[k] = values
        return ['rep', k, variants, n[3]]
    return n


def rep_label(first):
    tag, attrs = first[1], first[2]
    cls = attrs.get('class', '') if isinstance(attrs.get('class', ''), str) else ''
    if tag == 'li':
        return '項目'
    if 'card' in cls:
        return 'カード'
    if 'row' in cls:
        return '行'
    if tag == 'a':
        return 'リンク'
    if tag in ('p',):
        return '段落'
    if tag == 'img':
        return '画像'
    return '繰り返し項目'


# ---------------------------------------------------------------- titles
def block_title(root):
    texts = {'en': None, 'h': None}

    def walk(n, data):
        if n[0] == 'e':
            cls = n[2].get('class', '') if isinstance(n[2].get('class', ''), str) else ''
            if n[1] in ('h1', 'h2') and texts['h'] is None:
                v = first_text(n, data)
                if v:
                    texts['h'] = v
            if n[1] == 'span' and 'en' in cls.split() and texts['en'] is None:
                v = first_text(n, data)
                if v and re.search(r'[A-Za-z]', v):
                    texts['en'] = v
            for c in n[3]:
                walk(c, data)
    return walk, texts


def first_text(n, data):
    if n[0] in ('t', 'r', 'l'):
        return re.sub(r'<[^>]+>', '', data.get(n[1], '')).replace('\n', '')
    if n[0] == 'e':
        for c in n[3]:
            v = first_text(c, data)
            if v:
                return v
    if n[0] == 'rep':
        items = data.get(n[1]) or [{}]
        for c in n[2]:
            v = first_text(c, items[0])
            if v:
                return v
    return ''


# ---------------------------------------------------------------- CSS
def css_rules(css):
    out, i, n = [], 0, len(css)
    while i < n:
        j = css.find('{', i)
        if j < 0:
            break
        sel = css[i:j].strip()
        depth, k = 1, j + 1
        while depth:
            if css[k] == '{':
                depth += 1
            elif css[k] == '}':
                depth -= 1
            k += 1
        body = css[j + 1:k - 1]
        if sel.startswith('@media'):
            for _, r2 in css_rules(body):
                out.append((sel, r2))
        else:
            out.append(('', sel + '{' + body + '}'))
        i = k
    return out


def emit_css(rules):
    out, cur = [], None
    for m, r in rules:
        if m != cur:
            if cur:
                out.append('}')
            if m:
                out.append(m + '{')
            cur = m
        out.append(r)
    if cur:
        out.append('}')
    return '\n'.join(out) + '\n'


def block_comment(name, data):
    j = json.dumps({'data': data}, ensure_ascii=False, separators=(',', ':'))
    j = j.replace('--', '\\u002d\\u002d').replace('<', '\\u003c').replace('>', '\\u003e').replace('&', '\\u0026')
    return '<!-- wp:%s %s /-->' % (name, j)


# ---------------------------------------------------------------- main
def main():
    types, order, content, all_css, imgs = {}, [], {}, [], set()
    by_skeleton = {}
    for b, (kind, key) in PAGES.items():
        css, main_html, raws = prep(b)
        for r in css_rules(css):
            if r not in all_css and r != ('', 'body{margin:0;background:#F4F5F1}'):
                all_css.append(r)
        if b in SKIP:
            continue
        soup = BeautifulSoup(main_html, 'html.parser', multi_valued_attributes=None)
        blocks = []
        sec_no = 0
        for el in soup.children:
            if not isinstance(el, Tag):
                continue
            if el.name == 'nav' and el.get('aria-label') == 'パンくず':
                crumbs = [c for c in el.children if isinstance(c, Tag)]
                blocks.append(block_comment('ta/breadcrumb', {'label': crumbs[-1].get_text()}))
                continue
            sec_no += 1
            tree = build(el, raws, '')
            scope, data = Scope(), {}
            tree = assign_node(tree, scope, data)
            sig = hashlib.sha1(json.dumps(tree, ensure_ascii=False, sort_keys=True).encode()).hexdigest()[:10]
            if sig not in by_skeleton:
                # Block names must start with a letter (the 404 page becomes "notfound").
                base = 'notfound' if key == '404' else key
                name = 'ta/%s-%d' % (base, sec_no) if kind != 'works' else 'ta/work-%s' % sig[:6]
                while name in types:
                    name += 'x'
                walk, texts = block_title(tree)
                walk(tree, data)
                title = '｜'.join(t for t in (texts['en'], texts['h']) if t) or 'セクション'
                types[name] = {'title': title[:40], 'category': CATEGORY[b], 'tree': tree, 'fields': scope.fields,
                               'defaults': data}
                by_skeleton[sig] = name
                order.append(name)
            blocks.append(block_comment(by_skeleton[sig], data))
            imgs.update(re.findall(r'"([\w.-]+\.(?:jpg|png|svg|webp))"', json.dumps(data)))
        content[key if kind != 'works' else 'work-' + key] = '\n\n'.join(blocks) + '\n'

    os.makedirs(T + '/blocks/content', exist_ok=True)
    for k, v in content.items():
        open('%s/blocks/content/%s.html' % (T, k), 'w').write(v)
    cats = []
    for b in PAGES:
        if b in CATEGORY and CATEGORY[b] not in cats:
            cats.append(CATEGORY[b])
    json.dump({'categories': cats, 'types': {n: types[n] for n in order}, 'calls': CALL_LABEL},
              open(T + '/blocks/blocks.json', 'w'), ensure_ascii=False, separators=(',', ':'))
    open(T + '/assets/css/design.generated.css', 'w').write(
        '/* Generated from the design canvas boards (design/*.dc.html). Do not edit by hand. */\n' + emit_css(all_css))
    missing = sorted(f for f in imgs if not os.path.exists(T + '/assets/img/' + f))
    nfields = sum(len(t['fields']) for t in types.values())
    print('pages', len(content), 'block types', len(types), 'top-level fields', nfields,
          'json KB', os.path.getsize(T + '/blocks/blocks.json') // 1024, 'missing images', missing)


if __name__ == '__main__':
    main()
