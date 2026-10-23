/* Traverse Asia – design section blocks in the block editor.
 * Each block previews its section in the canvas; its texts, images, links and
 * lists are edited in the sidebar. Definitions come from blocks/blocks.json
 * (window.taBlocks); the front end renders the same tree in inc/blocks.php. */
(function (wp, cfg) {
  if (!wp || !cfg) return;
  var h = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var be = wp.blockEditor;
  var c = wp.components;
  var VOID = { img: 1, br: 1, input: 1, meta: 1, hr: 1, source: 1 };

  /* ---------- preview (same output as ta_render_node in PHP) ---------- */
  function esc(s) {
    return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function linkUrl(v) {
    v = String(v == null ? '' : v).trim();
    if (v === '@pdf') return cfg.pdf;
    if (v === '@en') return cfg.en;
    if (v.charAt(0) === '/' && v.charAt(1) !== '/') return cfg.home.replace(/\/$/, '') + v;
    return v;
  }
  function imageUrl(v) {
    if (v && typeof v === 'object') return v.url || '';
    v = String(v == null ? '' : v);
    return /^https?:\/\//.test(v) ? v : cfg.img + v;
  }
  function attrs(a, data) {
    var s = '';
    Object.keys(a).forEach(function (k) {
      var v = a[k];
      if (Array.isArray(v)) {
        var val = data[v[1]];
        v = v[2] === 'link' ? linkUrl(val) : v[2] === 'image' ? imageUrl(val) : val;
      }
      s += ' ' + k + '="' + esc(v) + '"';
    });
    return s;
  }
  function render(n, data) {
    switch (n[0]) {
      case 's': return esc(n[1]);
      case 't': return esc(data[n[1]]);
      case 'r': return String(data[n[1]] == null ? '' : data[n[1]]);
      case 'l':
        return String(data[n[1]] == null ? '' : data[n[1]]).split(/\r?\n/).filter(function (l) { return l.trim() !== ''; })
          .map(function (l) { return '<span' + attrs(n[2], data) + '>' + esc(l) + '</span>'; }).join('');
      case 'x': return n[1];
      case 'c': return '<div class="ta-call">' + esc(cfg.calls[n[1]] || n[1]) + '（公開ページでは自動で表示されます）</div>';
      case 'e':
        var s = '<' + n[1] + attrs(n[2], data) + '>';
        if (VOID[n[1]]) return s;
        return s + n[3].map(function (ch) { return render(ch, data); }).join('') + '</' + n[1] + '>';
      case 'rep':
        return (data[n[1]] || []).map(function (it) {
          return render(n[2][it._v || 0] || n[2][0], it || {});
        }).join(esc(n[3]));
    }
    return '';
  }

  /* ---------- sidebar form ---------- */
  var HELP = {
    lines: '改行したところで行が分かれます。',
    rich: '<br>（改行）や <strong>（太字）などのタグも使えます。',
    link: 'サイト内は /services/ のように、外部サイトは https:// から入力します。@pdf は会社案内PDFです。'
  };

  function summary(item) {
    var keys = Object.keys(item || {});
    for (var i = 0; i < keys.length; i++) {
      var v = item[keys[i]];
      if (keys[i] !== '_v' && typeof v === 'string' && v.trim() && !/\.(jpg|png|svg|webp)$/.test(v) && v.charAt(0) !== '/' && v.indexOf('http') !== 0 && v.indexOf(':') === -1) {
        var t = v.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim();
        if (t) return t.length > 22 ? t.slice(0, 22) + '…' : t;
      }
    }
    return '';
  }

  function ImageField(p) {
    return h(c.BaseControl, { label: p.f.label, __nextHasNoMarginBottom: true },
      h('div', { style: { marginBottom: '16px' } },
        h('img', { src: imageUrl(p.value), alt: '', style: { width: '100%', height: 'auto', maxHeight: '160px', objectFit: 'cover', borderRadius: '4px', display: 'block', marginBottom: '8px', background: '#eee' } }),
        h(be.MediaUploadCheck, null,
          h(be.MediaUpload, {
            allowedTypes: ['image'],
            value: p.value && p.value.id,
            onSelect: function (m) { p.onChange({ id: m.id, url: m.url }); },
            render: function (o) { return h(c.Button, { variant: 'secondary', onClick: o.open }, '画像を変更'); }
          }))));
  }

  function Field(p) {
    var f = p.f, v = p.value == null ? '' : p.value;
    switch (f.type) {
      case 'text':
      case 'alt':
        return h(c.TextControl, { label: f.label, value: v, onChange: p.onChange, __nextHasNoMarginBottom: false });
      case 'link':
        return h(c.TextControl, { label: f.label, help: HELP.link, value: v, onChange: p.onChange });
      case 'textarea':
      case 'lines':
      case 'rich':
        return h(c.TextareaControl, { label: f.label, help: HELP[f.type], value: v, rows: f.type === 'textarea' ? 4 : 3, onChange: p.onChange });
      case 'image':
        return h(ImageField, p);
      case 'repeat':
        return h(Repeat, p);
    }
    return null; // 'var': per-item look, not edited by hand
  }

  function Form(p) {
    return h(Fragment, null, p.fields.map(function (f) {
      return h(Field, {
        key: f.k, f: f, value: p.data[f.k],
        onChange: function (v) { var d = Object.assign({}, p.data); d[f.k] = v; p.onChange(d); }
      });
    }));
  }

  function Repeat(p) {
    var items = Array.isArray(p.value) ? p.value : [];
    function set(a) { p.onChange(a); }
    function move(i, d) { var a = items.slice(), t = a[i]; a[i] = a[i + d]; a[i + d] = t; set(a); }
    return h('div', { className: 'ta-repeat', style: { margin: '8px 0 20px', border: '1px solid #ddd', borderRadius: '4px' } },
      h('div', { style: { padding: '8px 12px', fontWeight: 600, background: '#f6f7f7', borderBottom: '1px solid #ddd' } },
        p.f.label + '（' + items.length + '件）'),
      items.map(function (it, i) {
        var fields = p.f.variants[it._v || 0] || p.f.variants[0];
        return h(c.PanelBody, { key: i + ':' + items.length, title: (i + 1) + '. ' + (summary(it) || p.f.label), initialOpen: false },
          h(Form, { fields: fields, data: it, onChange: function (v) { var a = items.slice(); a[i] = v; set(a); } }),
          h('div', { style: { display: 'flex', gap: '6px', flexWrap: 'wrap', marginTop: '8px' } },
            h(c.Button, { variant: 'secondary', size: 'small', disabled: i === 0, onClick: function () { move(i, -1); } }, '↑ 上へ'),
            h(c.Button, { variant: 'secondary', size: 'small', disabled: i === items.length - 1, onClick: function () { move(i, 1); } }, '↓ 下へ'),
            h(c.Button, { variant: 'secondary', size: 'small', onClick: function () { var a = items.slice(); a.splice(i + 1, 0, JSON.parse(JSON.stringify(it))); set(a); } }, '複製して追加'),
            h(c.Button, { variant: 'secondary', size: 'small', isDestructive: true, disabled: items.length < 2, onClick: function () { var a = items.slice(); a.splice(i, 1); set(a); } }, '削除')));
      }));
  }

  /* ---------- block types ---------- */
  var catIndex = {};
  cfg.categories.forEach(function (t, i) { catIndex[t] = 'ta-' + i; });

  Object.keys(cfg.types).forEach(function (name) {
    var t = cfg.types[name];
    wp.blocks.registerBlockType(name, {
      apiVersion: 3,
      title: t.title,
      category: catIndex[t.category] || 'ta-parts',
      icon: 'layout',
      attributes: { data: { type: 'object', default: {} } },
      supports: { html: false, className: false, customClassName: false },
      edit: function (props) {
        var data = Object.assign({}, t.defaults, props.attributes.data || {});
        var blockProps = be.useBlockProps({ className: 'ta-section-preview' });
        return h(Fragment, null,
          h(be.InspectorControls, null,
            h(c.PanelBody, { title: 'このセクションの内容', initialOpen: true },
              h('p', { style: { color: '#555', marginTop: 0 } }, '文字・画像・リンクを変更できます。リストの項目は「複製して追加」で増やせます。'),
              h(Form, { fields: t.fields, data: data, onChange: function (d) { props.setAttributes({ data: d }); } }))),
          h('div', Object.assign({}, blockProps, { dangerouslySetInnerHTML: { __html: render(t.tree, data) } })));
      },
      save: function () { return null; }
    });
  });

  wp.blocks.registerBlockType('ta/breadcrumb', {
    apiVersion: 3,
    title: 'パンくずリスト',
    category: 'ta-parts',
    icon: 'arrow-right-alt2',
    attributes: { data: { type: 'object', default: {} } },
    supports: { html: false, className: false, customClassName: false },
    edit: function (props) {
      var label = (props.attributes.data || {}).label || '';
      var blockProps = be.useBlockProps({ className: 'ta-section-preview' });
      return h(Fragment, null,
        h(be.InspectorControls, null,
          h(c.PanelBody, { title: 'パンくずリスト', initialOpen: true },
            h(c.TextControl, {
              label: '最後に表示する名前', help: '空にするとページのタイトルを表示します。親ページは自動で入ります。', value: label,
              onChange: function (v) { props.setAttributes({ data: { label: v } }); }
            }))),
        h('nav', Object.assign({}, blockProps, { style: { maxWidth: '1320px', margin: '0 auto', boxSizing: 'border-box', padding: '8px 40px 0', fontSize: '13px', color: '#45524E' } }),
          'トップ　/　…　/　' + (label || '（ページのタイトル）')));
    },
    save: function () { return null; }
  });
})(window.wp, window.taBlocks);
