<?php
/**
 * =====================================================
 * 管理画面での設定を
 * @package   siteSupports
 * @author    Grow Group
 * @license   gpl v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
namespace siteSupports\modules\cleanup;

use siteSupports\config;
use siteSupports\inc\helper;

class cleanup {

	public $templates = array();

	/**
	 * 初期化
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'head_cleaner' ), 10 );
		add_action( 'registered_taxonomy', array( $this, 'init' ), 1 );

		add_action( 'load_template', array( $this, 'log_template_load' ), 10, 1 );
		add_action( 'template_include', array( $this, 'log_template_load' ), 10, 1 );
		add_action( 'locate_template', array( $this, 'log_template_load' ), 10, 1 );
	}


	public function init() {

		$options = config::get( 'options' );

		foreach ( $options as $option_key => $option ) {
			/**
			 * サイトの設定なおかつ、対応したメソッドが存在する場合発動
			 */
			if ( method_exists( $this, $option_key ) ) {
				$this->{$option_key}( $option );
			}

		}
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
	public function feed_links( $option ) {
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
	public function wp_generator( $option ) {
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
	public function wp_shortlink_wp_head( $option ) {
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
	public function wpautop( $option ) {
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

	public function revision( $option ) {
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
	public function jquery( $option ) {
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
	public function xmlrpc( $option ) {
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

	public function author_archive( $option ) {

		if ( ! $option ) {
			return;
		}
		/**
		 * 著者ページヘのアクセスをリダイレクト
		 */
		add_action(
			'template_redirect',
			function () {
				global $post;
				$authorrequest = false;
				if ( is_404() && ( get_query_var( 'author' ) || get_query_var( 'author_name' ) ) ) {
					$authorrequest = true;
				}

				if ( is_404() && ! ( get_query_var( 'author' ) || get_query_var( 'author_name' ) ) ) {
					return;
				}

				if ( ( is_author() || $authorrequest ) ) {
					$author_can = false;

					if ( ! is_404() ) {
						if ( is_object( $post ) ) {
							$author_can = author_can( get_the_ID(), 'administrator' );
						}
					}

					if ( $author_can === true || ! is_404() || is_404() ) {

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
			function ( $content ) {
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
	public function bootstrap( $option ) {
		if ( intval( $option ) ) {
			add_action( 'wp_enqueue_scripts', function () {
				if ( ! is_admin() ) {
					wp_enqueue_style( 'bootstrap-css', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/css/bootstrap.min.css', array(), false, null );
					wp_register_script( 'bootstrap', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js', array( 'jquery' ), false, null );
					wp_enqueue_script( 'bootstrap' );
				}
			}, 99 );
		}
	}

	/**
	 * 更新を非表示に
	 *
	 * @param $option
	 */
	public function disable_update( $option ) {
		if ( intval( $option ) ) {

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
	 *
	 * @param $option
	 */
	public function show_current_template( $option ) {
		if ( intval( $option )
		     && ! is_admin()
		) {

			add_action( 'admin_bar_menu', array( $this, 'admin_bar_template' ), 9999 );
		}
	}


	/**
	 * 管理バーにテンプレートを追加
	 *
	 * @param $wp_admin_bar
	 *
	 * @return void
	 */
	public function admin_bar_template( $wp_admin_bar ) {
		global $template;

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'admin_bar_template',
				'meta'  => array(),
				'title' => __( '<span class="ab-icon"></span> テンプレート名 : ', 'ggsupports' ) . basename( $template ),
				'href'  => admin_url( '/theme-editor.php?file=' . basename( $template ) . '&theme=' . get_template() )
			)
		);

		$wp_admin_bar->add_group(
			array(
				'parent' => 'admin_bar_template',
				'id'     => 'admin_bar_template_name',
			)
		);

		/**
		 * テーマまでのパスを削除
		 */
		$templates = str_replace( get_template_directory() . '/', '', $this->templates );
		$i         = 0;
		foreach ( $templates as $template_key => $template_name ) {
			if ( isset( $template_name )
			     && $template_name
			) {

				$wp_admin_bar->add_menu(
					array(
						'parent' => 'admin_bar_template_name',
						'id'     => 'admin_bar_template_' . $i,
						'meta'   => array(),
						'title'  => $i . '. ' . $template_name,
						'href'   => admin_url( '/theme-editor.php?file=' . $template_name . '&theme=' . get_template() )
					)
				);
			}
			$i ++;
		}

	}

	/**
	 * テンプレートロード時にログを残す
	 *
	 * @param $template
	 *
	 * @return mixed
	 */
	public function log_template_load( $template ) {
		$this->templates[] = $template;

		return $template;
	}

}