<?php

namespace WP_Assistant\inc;

use \WP_Assistant\inc\config;
use \WP_Assistant\inc\helper;

class settings{

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
  private function __construct(){
    $this->page_slug    = config::get( 'prefix' ) . 'options_page';
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
  public static function get_instance(){
    if ( null  === static::$instance ){
      static::$instance = new self();
    }
    return static::$instance;
  }

  /**
   * オプションページの作成
   * @return void
   */
  public function add_admin_menu() {
    $settings = $this->settings;
    add_menu_page(
      __( 'WP Assistant', 'wp-assistant' ),
      __( 'WP Assistant', 'wp-assistant' ),
      'manage_options',
      $this->page_slug,
      array( $this, 'option_page' )
    );
  }
  public function option_page(){
    $nonce = wp_create_nonce( __FILE__ );
    include "views/options.php";
  }

  /**
   * Setting API の登録
   * @return void
   */
  public function register_setting(){
    register_setting( config::get( 'prefix' ) . '_settings', config::get( 'prefix' ) . '_options' );
  }

  /**
   * $settings プロパティにセクション情報を追加
   *
   * @param $name
   * @param $title
   * @param $tabs_name
   *
   * @return object このクラスインスタンス
   */
  public function add_section( $name, $title, $tabs_name ) {

    $section_name = $name . '_section';

    $defaults = array(
      'section_name' => '',
      'title' => '',
      'description' => '',
      'page_slug' => $this->page_slug,
      'tabs_name' => '',
    );

    $section = wp_parse_args( array(
      'section_name' => $section_name,
      'title' => $title,
      'description' => '',
      'tabs_name' => $tabs_name,
    ), $defaults );

    $this->settings[$section_name]['section'] = $section;

    return $this;
  }

  /**
   * $settings プロパティにフィールド情報を追加する
   *
   * @param $name フィールドID
   * @param $title フィールドのタイトル
   * @param $callback フィールドが呼び出された時に実行するコールバック
   * @param $section フィールドが属するセクション
   * @param int $default デフォルトの値
   *
   * @internal param string $desc
   * @return $this
   */
  public function add_field( $name, $title, $callback, $section, $default = 0 ) {

    if ( $title ) {
      $title = '<div class="acoordion"><h3><span class="dashicons dashicons-arrow-right-alt2"></span> ' . $title . '</h3>';
    }

    /** @var array $defaults デフォルトの設定 */
    $defaults = array(
      'name' => '',
      'title' => '',
      'callback' => '',
      'page_slug' => $this->page_slug,
      'section_name' => '',
      'default' => 0,
    );

    $section_name = $section . '_section';

    $fields = wp_parse_args( array(
      'name' => $name,
      'title' => $title,
      'callback' => $callback,
      'section_name' => $section_name,
      'default' => $default,
    ) , $defaults );

    /** デバッグ用にフィールドのみのプロパティに保存 */
    $this->fields[] = $name;

    /** settings プロパティにフィールドの設定を保存 */
    $this->settings[$section_name]['fields'][] = $fields;

    if ( ! config::get( 'install' ) ) {
      $options          = get_option( config::get( 'prefix' ) . 'options' );
      $options[ $name ] = $default;
      update_option( config::get( 'prefix' ) . 'options', $options );
    }

    return $this;

  }

  /**
   * セクションをSetting APIを利用して登録
   * @param $section
   */
  public function set_section( $section ){
    add_settings_section(
      $section['section_name'],
      $section['title'],
      $section['description'],
      $section['page_slug']
    );
  }

  /**
   * フィールドをSetting APIを利用して登録
   * @param $field
   */
  public function set_fields( $field ){
    add_settings_field(
      $field['name'],
      $field['title'],
      $field['callback'],
      $field['page_slug'],
      $field['section_name']
    );
  }

  /**
   * $settings プロパティを展開し、
   * セクションとフィールドをすべて登録
   * @return mixed
   */
  public function set_settings(){
    if ( empty( $this->settings ) ){
      return void;
    }
    foreach( $this->settings as $section_name => $section ){
      $this->set_section( $section['section'] );
      if ( empty( $section['fields'] ) || ! $section['fields'] ){
        countinue;
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
      echo 0;
      exit();
    }

    $form_str = urldecode( $_REQUEST['form'] );
    parse_str( $form_str, $form_array );

    /**
     * 値が有効な場合、値を照合してサニタイズ後オプションを更新
     */
    if ( $form_array ) {
      $settings = array_map( array( $this, 'sanitizes_fields' ), $form_array );

      /**
       * add_fieldで追加したinput以外は受け付けない
       */
      foreach ( $settings as $settting_key => $setting ) {
        if ( ! in_array( $settting_key, $this->fields ) ) {
          unset( $settings[ $settting_key ] );
        }
      }

      $settings['dashboard_contents'] = esc_html( $form_array['dashboard_contents'] );
      echo update_option( config::get( 'prefix' ) . 'options', $settings );
      exit();
    }
    echo 0;
    exit();
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
    }
    wp_enqueue_style( 'jquery-ui-smoothness', config::get( 'plugin_url' ) . 'assets/css/plugins.min.css', config::get( 'version' ), config::get( 'version' ) );
  }

}

