/* 「検索結果のタイトル」: the title shown in search results, set per page in the document settings. */
(function (wp, cfg) {
  if (!wp || !wp.plugins || !cfg) return;
  var h = wp.element.createElement;
  var Panel = (wp.editor && wp.editor.PluginDocumentSettingPanel) || (wp.editPost && wp.editPost.PluginDocumentSettingPanel);
  if (!Panel) return;
  wp.plugins.registerPlugin('ta-seo', {
    render: function () {
      var d = wp.data.useSelect(function (select) {
        var ed = select('core/editor');
        return { id: ed.getCurrentPostId(), slug: ed.getEditedPostAttribute('slug'), meta: ed.getEditedPostAttribute('meta') || {} };
      }, []);
      var edit = wp.data.useDispatch('core/editor').editPost;
      var key = d.id === cfg.front ? 'front' : (cfg.key || d.slug);
      var fallback = cfg.defaults[key] || '';
      return h(Panel, { name: 'ta-seo', title: '検索結果のタイトル' },
        h(wp.components.TextControl, {
          label: 'タイトル',
          value: d.meta._ta_seo_title || '',
          placeholder: fallback,
          help: (key === 'front' || key === 'en') ? '空のときは「' + fallback + '」を使います。' : '後ろに「' + (key.indexOf('en/') === 0 ? ' | ' : '｜') + cfg.brand + '」が付きます。空のときは ' + (fallback ? '「' + fallback + '」' : 'ページのタイトル') + ' を使います。説明文はこのページの「抜粋」に書きます。',
          onChange: function (v) { edit({ meta: { _ta_seo_title: v } }); }
        }));
    }
  });
})(window.wp, window.taSeo);
