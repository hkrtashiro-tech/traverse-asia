# Traverse Asia テーマ

traverse-asia.com リニューアル用のオリジナルテーマです（クラシックテーマ、PHP 8.0 以上、WordPress 6.4 以上）。

## 構成

ページはすべて「デザインのセクションブロック」の組み合わせでできています。ブロックエディターで、セクションを選ぶと右側に文字・画像・リンク・リスト項目の入力欄が出ます。セクションの追加（＋ → 「デザイン｜…」）、並べ替え、削除、リスト項目の複製・削除ができ、新しい固定ページもこのブロックで作れます。

| URL | 編集する場所 | 補足 |
|---|---|---|
| `/` と各固定ページ | 固定ページ | 未変換のページはデザインの初期内容を表示（初期設定で変換） |
| `/works/{slug}/` | 実績 | 公開時の9件はブロック。新しい実績は本文だけでも書ける（記事レイアウト） |
| `/works/` | 固定ページ「【一覧ページ】実績（/works/）」（非公開） | 実績一覧と絞り込みは自動 |
| `/news/` | 固定ページ「【一覧ページ】お知らせ・インサイト（/news/）」（非公開） | 記事一覧・タブ・ページ送りは自動。`?kind=press-release / information / column` |
| 404 | 固定ページ「【404ページ】…」（非公開） | |
| `/news/{slug}/`, `/blog/{slug}/` | お知らせ / 投稿 | 記事レイアウト。h2 から目次を自動生成 |

- セクションブロックの定義 `blocks/blocks.json`、各ページの初期内容 `blocks/content/*.html`、CSS `assets/css/design.generated.css` は、デザインキャンバスの元データ（リポジトリの `design/*.dc.html`）から `python3 tools/build-theme.py` で生成しています（bs4 が必要）。
- 描画は `inc/blocks.php`（公開ページ）と `assets/js/blocks.js`（エディターのプレビューと入力欄）が同じ定義から行います。
- 手書きのファイル：`header.php` `footer.php` `inc/*.php` `template-parts/*.php` `assets/css/main.css` `assets/css/editor.css` `assets/js/*.js`。
- ヘッダー・フッターのメニューは 外観 → メニュー で編集できます（「相談する」ボタンと JP / EN は固定）。ヘッダーで項目の下に入れた子項目は、マウスを重ねると開くパネルに表示されます（子項目の「説明」が上の英字ラベルになります）。
- 検索結果のタイトル・説明文・SNS用の画像（OGP）は `inc/seo.php` の既定値を使います。ページごとに、エディターの「検索結果のタイトル」欄と「抜粋」欄で上書きできます。
- `news` 投稿タイプと `news_category` タクソノミーは旧テーマと同じ名前で登録しているので、既存のお知らせはURLもそのまま引き継がれます。

## 英語版（/en/）

- `/en/` とその下の固定ページが英語版です（Home、Services と4ステップ、Works、SHIZUNE、About、Contact、送信完了）。日本語版と同じセクションブロックでできていて、同じようにブロックエディターで編集できます。
- 英語ページでは、ヘッダー・フッター（外観 → メニューの「（英語版）」）、パンくず、お問い合わせフォーム（Contact Form 7「Contact (English site)」）、検索用のタイトル・説明文、`<html lang="en">` が自動で英語になり、JP / EN ボタンは対応するページに移動します。対応表は `inc/i18n.php`。
- 初期の英文は `tools/build-en.py` が日本語版のブロックと `tools/en/translations.json` から作ります。初期設定ボタンで、英語ページを「下書き」として作成します。

## 本番への導入手順

1. Contact Form 7 を最新版に更新して有効化しておく。
2. `wp-content/themes/traverse-asia` をサーバーにアップロードし、テーマを有効化する（先にステージングで確認するのがおすすめ）。
3. 管理画面の「外観 → 初期設定」を開き、「初期設定を実行する」ボタンを押す（WP-CLI が使える場合は `wp eval-file tools/setup-site.php` でも同じです）。実行内容は次のとおりで、何度実行しても重複しません。
   - 足りない固定ページ（`/services/` 以下、`/public-sector/`、`/brands/shizune/`、`/contact/thanks/` など）を作成
   - 事例9件（カテゴリー「事例」）を実績 `works` に移し、新しいスラッグに変更
   - Shopee のコラム3件をカテゴリー「ブログ」に移動（`/shopee/xxx/` → `/blog/xxx/`）
   - お問い合わせフォームを `cf7/contact-form.html` から作成して選択（管理者宛メールと自動返信メール）
   - デザインしたページ・実績・一覧ページを、ブロックで編集できる形に変換（元の本文はリビジョンに残ります）
   - パーマリンクを更新
4. 会社案内PDFはテーマに同梱しています（assets/pdf/）。別のURLにしたいときは 外観 → カスタマイズ → サイト設定 で指定します。
5. Redirection プラグインで `redirects-traverse-asia.csv` をインポートし、既存ルール `/aboutus/ceo-message/` の転送先を `/company/#message` に変更する。
6. フォームから試験送信し、管理者宛メールと自動返信メールが届くこと、送信後に `/contact/thanks/` に移ることを確認する（メールが届かない場合は WP Mail SMTP などで送信設定が必要です）。

## テーマの更新（Git Updater）

本番には Git Updater プラグインを入れ、このリポジトリの `theme-release` ブランチ（テーマフォルダだけを置いたブランチ）から更新を受け取ります。

1. `style.css` の `Version` を上げてコミットする
2. `sh tools/release-theme.sh` で `theme-release` ブランチに反映してプッシュする
3. 本番の「ダッシュボード → 更新」で「Traverse Asia」を更新する
