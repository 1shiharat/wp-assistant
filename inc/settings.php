<?php

namespace WP_Assistant\inc;

class settings {

	/** @var string 管理画面スラッグ */
	public static $page_name = '';

	/** @var null 管理画面の設定項目名の配列 */
	public $fields = null;

	/** @var null 設定を格納するオブジェクト */
	public $settings = null;

	/** @var null シングルトン インスタンス */
	private static $instance = null;

	/**
	 * クラスの初期化
	 * * ページのスラッグのセット
	 * * 設定の登録
	 * * 管理画面メニューへの登録
	 * * 各モジュールで設定した設定の登録
	 * * スクリプトの登録
	 * * Ajax でオプションを更新
	 */
	private function __construct() {
		$this->page_slug = config::get( 'prefix' ) . 'options_page';
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'set_settings' ), 99 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_update_wpaupports_option', array( $this, 'update_wpaupports_option' ) );
	}

	/**
	 * 設定画面が2度読み込まれる事がないように
	 * シングルトンインスタンスを作成
	 *
	 * @return object このクラスのインスタンス
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new self();
		}
		return static::$instance;
	}

	/**
	 * オプションページの作成
	 * @return void
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'WP Assistant', 'wp-assistant' ),
			__( 'WP Assistant', 'wp-assistant' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'option_page' )
		);
	}

	public function option_page() {
		$nonce = wp_create_nonce( __FILE__ );
		include "views/options.php";
	}

	/**
	 * Setting API の登録
	 * @return void
	 */
	public function register_setting() {
		register_setting( config::get( 'prefix' ) . '_settings', config::get( 'prefix' ) . '_options' );
	}

	/**
	 * $settings プロパティにセクション情報を追加
	 *
	 * @param array $args セクションに必要な設定を配列として記述します。
	 *                      'id' : フィールド固有のID
	 *                      'titile' : フィールド名
	 *                      'desc' : フィールドの説明文
	 *                      'tabs_name' : タブのラベル
	 *                      'page_slug' : 設定を表示するページのスラッグ
	 *
	 *
	 * @return object このクラスインスタンス
	 */
	public function add_section( $args = array() ) {

		$defaults = array(
			'id'        => '',
			'title'     => '',
			'desc'      => '',
			'tabs_name' => '',
			'page_slug' => $this->page_slug,
		);

		$section                                     = wp_parse_args( $args, $defaults );
		$this->settings[ $section['id'] ]['section'] = $section;

		return $this;
	}

	/**
	 * $settings プロパティにフィールド情報を追加する
	 *
	 * @param array $args
	 *
	 * @internal id フィールドID $name
	 * @internal title フィールドのタイトル $title
	 * @internal type フィールドが呼び出された時に実行するコールバック $callback
	 * @internal section フィールドが属するセクション $section
	 * @internal default int $default デフォルトの値
	 * @internal size int フィールドのサイズ
	 * @internal options array フィールドのオプション
	 * @internal sanitize_callback function サニタイズ用のコールバック関数を指定
	 *
	 * @internal param string $desc
	 *
	 * @return $this
	 */
	public function add_field( $args = array() ) {

		/** @var array $defaults デフォルトの設定 */
		$defaults = array(
			'id'                => '',
			'title'             => '',
			'type'              => 'text',
			'section'           => '',
			'default'           => '',
			'desc'              => '',
			'size'              => '',
			'options'           => '',
			'sanitize_callback' => '',
			'page_slug'         => $this->page_slug,
		);


		$fields = wp_parse_args( $args, $defaults );

		/** デバッグ用にフィールドのみのプロパティに保存 */
		$this->fields[] = $fields['id'];

		/** settings プロパティにフィールドの設定を保存 */
		$this->settings[ $fields['section'] ]['fields'][] = $fields;

		return $this;

	}

	/**
	 * セクションをSetting APIを利用して登録
	 *
	 * @param $section
	 */
	public function set_section( $section ) {
		add_settings_section(
			$section['id'],
			$section['title'],
			$section['desc'],
			$section['page_slug']
		);
	}

	/**
	 * フィールドをSetting APIを利用して登録
	 *
	 * @param $field
	 */
	public function set_fields( $field ) {
		/** type をコールバックで呼び出す */
		add_settings_field(
			$field['id'],
			$field['title'],
			$this->callback( $field['type'], $field ),
			$field['page_slug'],
			$field['section'],
			array(
				'desc' => $field['desc']
			)
		);
	}

	/**
	 * フィールドのコールバック
	 *
	 * @param $type
	 * @param $field
	 *
	 * @return callable
	 */
	public function callback( $type, $field ) {
		/** 指定されたタイプのフィールドがあり、クラスが存在する時発火 */
		if ( is_callable( $type ) ) {
			return $type;
		} else if ( file_exists( __DIR__ . '/fields/' . $type . '.php' ) ) {
			$classname = '\\' . __NAMESPACE__ . '\fields\\' . $type;
			if ( class_exists( $classname ) ) {
				$instance = function () use ( $type, $field, $classname ) {
					$type_instance = new $classname( $field );

					return $type_instance;
				};

				return $instance;
			}
		}
	}

	/**
	 * $settings プロパティを展開し、
	 * セクションとフィールドをすべて登録
	 * @return mixed
	 */
	public function set_settings() {
		if ( empty( $this->settings ) ) {
			return void;
		}
		foreach ( $this->settings as $section_name => $section ) {
			if ( isset( $section['section'] ) ) {
				$this->set_section( $section['section'] );
			}
			if ( empty( $section['fields'] ) || ! $section['fields'] ) {
				continue;
			}
			foreach ( $section['fields'] as $field ) {
				if ( $field ) {
					$this->set_fields( $field );
				}
			}
		}
	}

	/**
	 * Ajax で投げられた情報を保存
	 * @return void
	 */
	public function update_wpaupports_option() {

		/** nonceの確認 */
		if ( ! wp_verify_nonce( $_REQUEST['_wp_nonce'], __FILE__ ) ) {
			echo 5;
			exit();
		}

		$form_array = $_REQUEST['form'];

		$form_save_data = array();

		/**
		 * 値が有効な場合、値を照合してサニタイズ後オプションを更新
		 */
		if ( $form_array ) {
			foreach ( $form_array as $form ) {
				if ( isset( $form['name'] ) && $form['name'] === 'admin_menu_user[]' ) {
					$form_save_data['admin_menu_user'][] = $form['value'];
				} else if( isset( $form['name']  ) && isset( $form['value'] )  ) {
					$form_save_data[ $form['name'] ] = $form['value'];
				}
			}
			$settings = array_map( array( $this, 'sanitizes_fields' ), $form_save_data );

			/**
			 * add_fieldで追加したinput以外は受け付けない
			 */

			foreach ( $settings as $setting_key => $setting ) {
				if ( ! in_array( $setting_key, $this->fields ) ) {
					unset( $settings[ $setting_key ] );
				}
			}
			$settings['dashboard_contents'] = ( $form_save_data['dashboard_contents'] );

			$update = $this->update_option( config::get( 'prefix' ) . 'options', $settings );
			echo $update;
			exit();
		}
		echo 0;
		exit();
	}

	/**
	 * オプションの更新
	 * @param $option
	 * @param $value
	 * @param null $autoload
	 *
	 * @return bool
	 */
	public function update_option( $option, $value, $autoload = null ){
		global $wpdb;

		$option = trim($option);
		if ( empty($option) )
			return 2;

		wp_protect_special_option( $option );

		if ( is_object( $value ) )
			$value = clone $value;

		$value = sanitize_option( $option, $value );
		$old_value = get_option( $option );

		$value = apply_filters( 'pre_update_option_' . $option, $value, $old_value );

		$value = apply_filters( 'pre_update_option', $value, $option, $old_value );

		// If the new and old values are the same, no need to update.
		if ( $value === $old_value )
			return 3;

		if ( apply_filters( 'default_option_' . $option, false ) === $old_value ) {
			if ( null === $autoload ) {
				$autoload = 'yes';
			}

			return add_option( $option, $value, '', $autoload );
		}

		$serialized_value = maybe_serialize( $value );

		$update_args = array(
			'option_value' => $serialized_value,
		);

		if ( null !== $autoload ) {
			$update_args['autoload'] = ( 'no' === $autoload || false === $autoload ) ? 'no' : 'yes';
		}

		$result = $wpdb->update( $wpdb->options, $update_args, array( 'option_name' => $option ) );
		if ( ! $result )
			return 4;

		$notoptions = wp_cache_get( 'notoptions', 'options' );
		if ( is_array( $notoptions ) && isset( $notoptions[$option] ) ) {
			unset( $notoptions[$option] );
			wp_cache_set( 'notoptions', $notoptions, 'options' );
		}

		if ( ! defined( 'WP_INSTALLING' ) ) {
			$alloptions = wp_load_alloptions();
			if ( isset( $alloptions[$option] ) ) {
				$alloptions[ $option ] = $serialized_value;
				wp_cache_set( 'alloptions', $alloptions, 'options' );
			} else {
				wp_cache_set( $option, $serialized_value, 'options' );
			}
		}

		return true;
	}

	/**
	 * フィールドの無害化処理
	 *
	 * @param $fields
	 *
	 * @return array|string
	 */
	public function sanitizes_fields( $fields ) {
		if ( is_array( $fields ) ) {
			return array_map( 'sanitize_text_field', $fields );
		}

		return sanitize_text_field( $fields );
	}

	/**
	 * 静的ファイルの登録
	 *
	 * @param  string $hook 呼び出されるファイル名
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		switch ( $hook ) {
			case 'index.php' :
			case 'toplevel_page_' . config::get( 'prefix' ) . 'options_page' :

				wp_enqueue_media();
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-color-picker' );

				wp_enqueue_script( config::get( 'prefix' ) . 'admin_scripts', config::get( 'plugin_url' ) . 'assets/js/plugins.min.js', array(
					'jquery',
					'jquery-ui-tabs',
					'jquery-ui-button',
					'jquery-ui-accordion',
				), config::get( 'version' ) );

				wp_localize_script( config::get( 'prefix' ) . 'admin_scripts', 'wpaSETTINGS', array(
					'action'    => 'update_wpaupports_option',
					'_wp_nonce' => wp_create_nonce( __FILE__ )
				) );
				break;

			default :
				wp_enqueue_script( config::get( 'prefix' ) . 'admin_scripts', config::get( 'plugin_url' ) . 'assets/js/plugins.min.js', array(
					'jquery',
					'jquery-ui-tabs',
					'jquery-ui-button',
					'jquery-ui-accordion',
					'underscore'
				), config::get( 'version' ) );

				break;
		}
		wp_enqueue_style( 'jquery-ui-smoothness', config::get( 'plugin_url' ) . 'assets/css/plugins.min.css', config::get( 'version' ), config::get( 'version' ) );
	}

	/**
	 * Settings API のフィールドを出力
	 *
	 * @param $page
	 * @param $section
	 */
	public function do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}

		echo '<div class="acoordion ui-accordion ui-accordion-icons ui-widget ui-helper-reset">';
		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			if ( ! empty( $field['args']['label_for'] ) ) {
				echo '<h3 class="ui-accordion-header ui-helper-reset ui-corner-top"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></h3>';
			} else {
				echo '<h3 class="ui-accordion-header ui-helper-reset ui-corner-top">' . $field['title'] . '</h3>';
			}
			echo '<div class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active">';
			echo '<p>' . $field['args']['desc'] . '</p>';
			call_user_func( $field['callback'], $field['args'] );
			echo '</div>';
		}

		echo '</div>';
	}

}

