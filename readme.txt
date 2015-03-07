=== WP Assistant ===
Contributors: ishihara takashi
Tags: Option Framework
Requires at least: 4.1.1
Tested up to: 4.0
Stable tag: 0.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

このプラグインは動作環境として、PHP 5.3以上を必要とします。

WordPressサイトを構築する上でよくカスタマイズする内容を1つのプラグインに詰め込みました。

Features:

* サイトの設定 : wp_head()で動作するアクションフックの調査
* オリジナルダッシュボードウィジェット : 「ようこそ」パネルをオリジナルのダッシュボードパネルに置換します。
* データベースの最適化 : リビジョン、自動下書き、ゴミ箱内の記事を一括削除します。それぞれの項目ごとに削除することもできます。
* Ace Editor : テーマの編集、プラグインの編集画面にAce Editorが利用できるようになります。Emmetにも対応済みです。
* パンくずショートコード : シンプルなパンくずを表示します。
* 簡易的な管理メニューの編集 : 管理画面サイドメニューをユーザーごとに表示・非表示を選択することができます。ただし、あくまでも簡易的です。アクセスされたらまずいといった場合には使用しないでください。
* アドミンバーにテンプレート名の表示 :  アドミンバーに現在読み込んでいるテンプレートを表示します。
* 投稿編集ナビゲーション : 記事編集画面に投稿ナビを表示します。
* 設定のエクスポート、インポート :  プラグインの設定をエクスポート、インポート

何か要望や修正等あれば下記のgithubリポジトリまでお知らせいただければ幸いです。

[Github](https://github.com/1shiharaT/wp-assistant/)

== Installation ==

1. Upload `wp-assistant` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. General setting
2. Customize Admin menu.
3. Database optimization.

== Changelog ==

= 0.1.3 = 
bug fix.

= 0.1.2 =
Release.
