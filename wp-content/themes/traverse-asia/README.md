# Traverse Asia テーマ

traverse-asia.com リニューアル用のオリジナルテーマです（クラシックテーマ、PHP 8.0 以上、WordPress 6.4 以上）。

## 構成

| URL | テンプレート | 表示内容 |
|---|---|---|
| `/` | `front-page.php` | `template-parts/pages/front.php`。お知らせ欄は最新3件を自動表示 |
| 固定ページ | `page.php` | スラッグと同名の `template-parts/pages/{slug}.php` があればそのデザイン、なければ本文を記事レイアウトで表示 |
| `/works/` | `archive-works.php` | 実績（投稿タイプ `works`）。ステップ・国で絞り込み（`?step=` `?area=`） |
| `/works/{slug}/` | `single-works.php` | 公開時の9件は `template-parts/works/{slug}.php` のデザイン。新しく追加した実績は本文を記事レイアウトで表示 |
| `/news/` | `archive-news.php` | お知らせ（`news`）とコラム（通常の投稿）をまとめて新しい順に表示。タブは `?kind=press-release / information / column` |
| `/news/{slug}/`, `/blog/{slug}/` | `single.php` | 記事。h2 から目次を自動生成 |
| 404 | `404.php` | |

- `template-parts/pages|design|works/*.php` と `assets/css/base.generated.css`、`assets/css/pages/*.css` は、デザインキャンバスの元データ（リポジトリの `design/*.dc.html`）から `python3 tools/build-theme.py` で生成しています。デザインを直したら再生成してください（直接編集すると次の生成で上書きされます）。
- 手書きのファイル：`header.php` `footer.php` `inc/*.php`（初期設定は `inc/site-setup.php`） `template-parts/article.php` `template-parts/cta.php` `template-parts/contact-form.php` `assets/css/main.css` `assets/js/main.js`。
- `news` 投稿タイプと `news_category` タクソノミーは旧テーマと同じ名前で登録しているので、既存のお知らせはURLもそのまま引き継がれます。

## 本番への導入手順

1. Contact Form 7 を最新版に更新して有効化しておく。
2. `wp-content/themes/traverse-asia` をサーバーにアップロードし、テーマを有効化する（先にステージングで確認するのがおすすめ）。
3. 管理画面の「外観 → 初期設定」を開き、「初期設定を実行する」ボタンを押す（WP-CLI が使える場合は `wp eval-file tools/setup-site.php` でも同じです）。実行内容は次のとおりで、何度実行しても重複しません。
   - 足りない固定ページ（`/services/` 以下、`/public-sector/`、`/brands/shizune/`、`/contact/thanks/` など）を作成
   - 事例9件（カテゴリー「事例」）を実績 `works` に移し、新しいスラッグに変更
   - Shopee のコラム3件をカテゴリー「ブログ」に移動（`/shopee/xxx/` → `/blog/xxx/`）
   - お問い合わせフォームを `cf7/contact-form.html` から作成して選択（管理者宛メールと自動返信メール）
   - パーマリンクを更新
4. 外観 → カスタマイズ → サイト設定 で、会社案内PDFのURLを入力する（未設定の間は「会社案内（PDF）」ボタンがお問い合わせページへ移動します）。
5. Redirection プラグインで `redirects-traverse-asia.csv` をインポートし、既存ルール `/aboutus/ceo-message/` の転送先を `/company/#message` に変更する。
6. フォームから試験送信し、管理者宛メールと自動返信メールが届くこと、送信後に `/contact/thanks/` に移ることを確認する（メールが届かない場合は WP Mail SMTP などで送信設定が必要です）。
