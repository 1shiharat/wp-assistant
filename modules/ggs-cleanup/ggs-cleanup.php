<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Ggs_Cleanup' ) ) {
	class Ggs_Cleanup {

		/**
		 * 初期化
		 */
		public function __construct() {
			add_action( 'wp_head', array( $this, 'head_cleaner' ), 10 );
//			add_action( 'wp_head', array( $this, 'link_tag_cleaner' ), 10 );
			add_action( 'registered_taxonomy', array( $this, 'init' ), 1 );
		}

		public function init() {
			$options = $this->get_options();

			foreach ( $options as $option_key => $option ) {

				/**
				 * サイトの設定なおかつ、対応したメソッドが存在する場合発動
				 */
				if ( strpos( $option_key, 'general' )
				     && method_exists( $this, $option_key )
				) {
					$this->{$option_key}( $option );
				}
			}
		}

		public function get_options() {
			$options = Ggs_Helper::get_ggs_options();

			return $options;
		}

		/**
		 * wp_headから余計な記述を削除
		 *
		 * @return void
		 */
		public function head_cleaner() {
			remove_action( 'wp_head', 'wlwmanifest_link' );
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
			remove_action( 'wp_head', 'wp_generator' );
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
			global $wp_widget_factory;
			remove_action( 'wp_head',
				array(
					$wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
					'recent_comments_style',
				)
			);
		}

		/**
		 * link タグの余計な記述を削除
		 *
		 * @param  string $input
		 *
		 * @return string <link> tag
		 */
		public function link_tag_cleaner( $input ) {
			preg_match_all( "!<link rel='stylesheet'\s?(id='[^']+')?\s+href='(.*)' type='text/css' media='(.*)' />!", $input, $matches );
			$media = $matches[3][0] !== '' && $matches[3][0] !== 'all' ? ' media="' . $matches[3][0] . '"' : '';
			return '<link rel="stylesheet" href="' . $matches[2][0] . '"' . $media . '>' . "\n";
		}

		/**
		 * フィードリンクの出力
		 *
		 * @param $option
		 */
		public function ggsupports_general_feed_links( $option ) {
			if ( ! intval( $option ) ) {
				remove_action( 'wp_head', 'feed_links', 2 );
				remove_action( 'wp_head', 'feed_links_extra', 3 );
				remove_action( 'wp_head', 'rsd_link' );
			}
		}

		/**
		 * WordPressバージョン情報の出力
		 *
		 * @param $option
		 */
		public function ggsupports_general_wp_generator( $option ) {
			if ( ! intval( $option ) ) {
				remove_action( 'wp_head', 'wp_generator' );

				return false;
			}
		}

		/**
		 * ショートリンクの出力
		 *
		 * @param $option
		 */
		public function ggsupports_general_wp_shortlink_wp_head( $option ) {
			if ( ! intval( $option ) ) {
				remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );

				return false;
			}
		}

		/**
		 * 自動整形の停止
		 *
		 * @param $option
		 */
		public function ggsupports_general_wpautop( $option ) {
			if ( ! intval( $option ) ) {
				/**
				 * 通常コンテンツ
				 */
				remove_filter( 'the_excerpt', 'wpautop' );
				remove_filter( 'the_content', 'wpautop' );

				/**
				 * contact form 7
				 */
				if ( ! defined( 'WPCF7_AUTOP' ) ) {
					define( 'WPCF7_AUTOP', false );
				}

				/**
				 * Advanced Custom Field
				 */
				if ( function_exists( 'get_field' ) ) {
					remove_filter( 'acf_the_content', 'wpautop' );
				}

				return false;
			}
		}

		public function ggsupports_general_revision( $option ) {
			if ( ! intval( $option ) ) {
				// リビジョンの停止
				if ( ! defined( 'WP_POST_REVISIONS' ) ) {
					define( 'WP_POST_REVISIONS', false );
				}
				// 自動保存の停止
				add_action( 'wp_print_scripts', function () {
					wp_deregister_script( 'autosave' );
				} );

				return false;
			}
		}

		/**
		 * jQueryの読み込み
		 *
		 * @param $option
		 *
		 * @return bool
		 */
		public function ggsupports_general_jquery( $option ) {
			if ( intval( $option ) ) {
				if ( ! is_admin() ) {
					add_action( 'wp_enqueue_scripts', function () {
						wp_deregister_script( 'jquery' );
						wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', array(), null, false );
						wp_enqueue_script( 'jquery' );
					}, 10 );
				}

				return true;
			}
		}

		/**
		 * xmlrpc の停止
		 *
		 * @param $option
		 */
		public function ggsupports_general_xmlrpc( $option ) {
			if ( intval( $option ) ) {
				add_filter(
					'xmlrpc_methods',
					function ( $methods ) {
						unset( $methods['pingback.ping'] );
						unset( $methods['pingback.extensions.getPingbacks'] );

						return $methods;
					}, 10, 1 );

				add_filter(
					'wp_headers',
					function ( $headers ) {
						unset( $headers['X-Pingback'] );

						return $headers;
					}, 10, 1 );
			}
		}

		public function ggsupports_general_author_archive( $option ){

			if ( ! $option ) return;
			/**
			 * 著者ページヘのアクセスをリダイレクト
			 */
			add_action(
				'template_redirect',
				function() {
					global $post;
					$authorrequest = FALSE;
					if ( is_404() && ( get_query_var( 'author' ) || get_query_var( 'author_name' ) ) ) {
						$authorrequest = true;
					}

					if ( is_404() && ! ( get_query_var( 'author' ) || get_query_var( 'author_name' ) ) ) {
						return;
					}

					if ( ( is_author() || $authorrequest ) ) {
						$author_can = false;

						if ( ! is_404() ) {
							if( is_object( $post ) ) {
								$author_can = author_can( get_the_ID(), 'administrator' );
							}
						}

						if ( $author_can===true || !is_404() || is_404() ) {

							if ( $url == '' ) {
								$url = home_url();
							}
							wp_redirect( $url, "302" );
							exit;
						}
					}
				}
			);
			/**
			 * 著者ページへのリンクを削除
			 */
			add_filter( 'author_link',
			    function( $content ) {
					return '';
				},
				10,
				1
			);
		}

		/**
		 * Bootstrap を読み込み
		 *
		 * @param $option
		 *
		 * @return void
		 */
		public function ggsupports_general_bootstrap( $option ){
			if ( intval( $option ) ){
				add_action( 'wp_enqueue_scripts', function(){
					if ( ! is_admin() ){
						wp_enqueue_style( 'bootstrap-css', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/css/bootstrap.min.css', array(), false, null );
						wp_register_script( 'bootstrap', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js', array( 'jquery' ), false, null );
						wp_enqueue_script( 'bootstrap' );
					}
				}, 99 );
			}
		}

		/**
		 * 更新を非表示に
		 * @param $option
		 */
		public function ggsupports_general_disable_update( $option ){
			if ( intval( $option ) ){

				// コア
				add_filter( 'pre_site_transient_update_core', '__return_zero' );
				remove_action( 'wp_version_check', 'wp_version_check' );
				remove_action( 'admin_init', '_maybe_update_core' );

				// プラグイン
				remove_action( 'load-update-core.php', 'wp_update_plugins' );
				add_filter( 'pre_site_transient_update_plugins', create_function( '$a', "return null;" ) );

				// テーマ
				remove_action( 'load-update-core.php', 'wp_update_themes' );
				add_filter( 'pre_site_transient_update_themes', create_function( '$a', "return null;" ) );

			}

		}

		/**
		 * 現在のテンプレートを表示
		 * @param $option
		 */
		public function ggsupports_general_show_current_template( $option ){
			if ( intval( $option )
			 && ! is_admin() ){
				add_action( 'admin_bar_menu', function( $wp_admin_bar ){
					global $template;
					$title = sprintf(
						'<span class="" style="font-size:13px;">テンプレート : </span> <span class="ab-label">%s</span>',
						basename( $template )
					);
					$wp_admin_bar->add_menu(
						array(
							'id'    => 'admin_bar_template_name',
							'meta'  => array(),
							'title' => $title,
							'href'  => admin_url( '/theme-editor.php?file=' . basename( $template ) . '&theme=' . get_template() )
						)
					);
				}, 9999 );
			}
		}

		public function ggsupports_general_jetpack_dev_mode( $option ){
			if ( intval( $option ) ){
				if ( ! defined( 'JETPACK_DEV_DEBUG' ) ) {
					define( 'JETPACK_DEV_DEBUG', true );
				}
			}
		}

		public function ggsupports_general_show_current_tempalte( $option ){
			if ( intval( $option )
			     && ! is_admin() ){
				add_action( 'admin_bar_menu', function( $wp_admin_bar ){
					global $template;
					$title = sprintf(
						'<span class="" style="font-size:13px;">テンプレート : </span> <span class="ab-label">%s</span>',
						basename( $template )
					);
					$wp_admin_bar->add_menu(
						array(
							'id'    => 'admin_bar_template_name',
							'meta'  => array(),
							'title' => $title,
							'href'  => admin_url( '/theme-editor.php?file=' . basename( $template ) . '&theme=' . get_template() )
						)
					);
				}, 9999 );
			}
		}

	}
}