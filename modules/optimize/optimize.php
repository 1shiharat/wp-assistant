<?php
/*
Plugin Name: Database Optimization
Description: Database Optimization module.
Text Domain: wp-assistant
Domain Path: /languages/
*/
namespace WP_Assistant\modules\optimize;

use WP_Assistant\inc\config;
use WP_Assistant\modules\module;

class optimize extends module {

	/**
	 * 初期化
	 *
	 * @param $parent module classのインスタンス
	 *
	 * @todo 適当すぎるので実装を考える
	 *
	 */
	public function __construct() {
		$this->settings = parent::get_settings();
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'wp_ajax_run_optimize', array( $this, 'run_optimize' ), 10, 1 );

	}

	/**
	 * データベースの最適化に必要な設定の追加
	 */
	public function add_settings() {
		global $wpdb;

		$revision_posts_count = wp_count_posts( 'revision' );
		$draft_results        = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
		$trash_results        = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );

		$this->settings->add_section(
			array(
				'id'        => 'optimize',
				'title'     => __( 'Database Optimization', 'wp-assistant' ),
				'desc'      => __( 'Database Optimization', 'wp-assistant' ),
				'tabs_name' => __( 'Database Optimization', 'wp-assistant' ),
			)
		)->add_field(
			array(
				'id'                => 'optimize_revision',
				'title'             => __( 'Delete all revisions', 'wp-assistant' ) . ' <label class="label">Revision  : <span class="post-count post-count-revision">' . esc_attr( $revision_posts_count->inherit ) . '</label> ',
				'type'              => 'radiobox',
				'section'           => 'optimize',
				'default'           => '1',
				'desc'              => __( 'Please specify to enable revision of the deletion.', 'wp-assistant' ),
				'size'              => '',
				'options'           => array(
					'true'  => __( 'On', 'wp-assistant' ),
					'false' => __( 'Off', 'wp-assistant' ),
				),
				'sanitize_callback' => '',
			)
		)->add_field(
			array(
				'id'                => 'optimize_auto_draft',
				'title'             => __( 'Delete all of the auto draft', 'wp-assistant' ) . ' <label class="label"> Auto Draft post : <span class="post-count post-count-auto_draft">' . $draft_results . '</span></label>',
				'type'              => 'radiobox',
				'section'           => 'optimize',
				'default'           => '1',
				'desc'              => __( 'Please specify to enable auto-draft of the deletion.', 'wp-assistant' ),
				'size'              => '',
				'options'           => array(
					'true'  => __( 'On', 'wp-assistant' ),
					'false' => __( 'Off', 'wp-assistant' ),
				),
				'sanitize_callback' => '',
			)
		)->add_field(
			array(
				'id'                => 'optimize_trash',
				'title'             => __( 'Delete trash in the post of all post type', 'wp-assistant' ) . '<label class="label">In Trash : <span class="post-count post-count-trash">' . $trash_results . '</span></label>',
				'type'              => 'radiobox',
				'section'           => 'optimize',
				'default'           => '1',
				'desc'              => __( 'Please specify to enable trash of the deletion.', 'wp-assistant' ),
				'size'              => '',
				'options'           => array(
					'true'  => __( 'On', 'wp-assistant' ),
					'false' => __( 'Off', 'wp-assistant' ),
				),
				'sanitize_callback' => '',
			)
		)->add_field(
			array(
				'id'                => 'optimize_submit',
				'title'             => __( 'Run Optimize', 'wp-assistant' ),
				'type'              => function () {
					$nonce = wp_create_nonce( __FILE__ ); ?>
						<div class="run_optimize">
							<?php _e( 'To apply the above settings, please save once.', 'wp-assistant' ); ?>
							<button class="button-primary button-hero" id="optimize_submit"><?php _e( 'Run Optimize', 'wp-assistant' ); ?></button>
							<input type="hidden" id="optimize_nonce" name="_wp_optimize_nonce" value="<?php echo $nonce ?>" />
							<span class="spinner"></span>
						</div>
					<?php
				},
				'section'           => 'optimize',
				'sanitize_callback' => '',

			)
		);
	}

	/**
	 * 最適化を実行
	 *
	 * @return void
	 */
	public function run_optimize() {
		global $wpdb;

		$wp_nonce = $_REQUEST['_wp_optimize_nonce'];

		if ( ! wp_verify_nonce( $wp_nonce, __FILE__ ) ) {
			echo 'nonce is not defined.';
			exit();
		}
		$message = array();
		$selected_action = $_REQUEST['selected_action'];

		/**
		 * リビジョンの削除
		 */
		if ( isset( $selected_action['revision'] ) && 'true' === $selected_action['revision'] ) {
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
			if ( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post_revision( intval( $id ) );
				}
				$message['revision'] = __( 'Deleted successfully revision.', 'wp-assistant' );
				$message['status']            = 'success';
			}
		}

		/**
		 * 自動下書きの削除
		 */
		if ( isset( $selected_action['auto_draft'] ) && 'true' === $selected_action['auto_draft'] ) {
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
			if ( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post( intval( $id ), true );
				}
				$message['auto_draft'] = __( 'Deleted successfully automatic draft.', 'wp-assistant' );
				$message['status']              = 'success';
			}
		}

		/**
		 * ゴミ箱内の記事の削除
		 */
		if ( isset( $selected_action['trash'] ) && 'true' === $selected_action['trash'] ) {
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );

			if ( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post( intval( $id ), true );
				}
				$message['trash'] = __( 'Deleted successfully trash in the article', 'wp-assistant' );
				$message['status']         = 'success';
			}
		}

		if ( is_array( $message ) && $message ) {
			echo wp_send_json( $message );
			exit();
		} else {
			$faild_message = array(
				'html'   => __( 'Failed to delete.', 'wp-assistant' ),
				'status' => 'faild',
			);

			echo wp_send_json( $faild_message );
			exit();
		}

	}

}
