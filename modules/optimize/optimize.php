<?php
/*
Plugin Name: Database Optimization
Description: Database Optimization module.
Text Domain: wp-assistant
Domain Path: /languages/
*/
namespace WP_Assistant\modules\optimize;

use WP_Assistant\modules\module;
use WP_Assistant\inc\config;
use WP_Assistant\inc\helper;

class optimize extends module {

	/**
	 * 初期化
	 * @param $parent module classのインスタンス
	 *
	 * @todo 適当すぎるので実装を考える
	 *
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;
		add_action( 'admin_init',           array( $this, 'add_settings' ) );
		add_action( 'wp_ajax_run_optimize', array( $this, 'run_optimize' ), 10, 1 );

	}

	/**
	 * データベースの最適化に必要な設定の追加
	 */
	public function add_settings() {
		global $wpdb;

		$revision_posts_count = wp_count_posts( 'revision' );
		$draft_results = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
		$trash_results = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = %s", 'trash' ));

		$this->parent->settings->add_section(
			'optimize',
			function () {
				_e( 'Database Optimization', 'wp-assistant' );
			},
			__( 'Database Optimization', 'wp-assistant' )
		)
		->add_field(
			'optimize_revision',
			__( 'Delete all revisions', 'wp-assistant' ) . ' <label class="label">Revision  : <span class="post-count post-count-revision">' . esc_attr( $revision_posts_count->inherit  ). '</label> ',
			function () {
				?>
				<div>
				<?php
				$args = array(
					'id'      => 'optimize_revision',
					'default' => 0,
					'desc'    => __( 'Please specify to enable revision of the deletion.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
				?>
				</div>
				<?php
			},
			'optimize',
			1
		)
		->add_field(
			'optimize_auto_draft',
			__( 'Delete all of the auto draft', 'wp-assistant' ). ' <label class="label"> Auto Draft post : <span class="post-count post-count-auto_draft">'. $draft_results .'</span></label>',
			function () {
				$args = array(
					'id'      => 'optimize_auto_draft',
					'default' => 0,
					'desc'    => __( 'Please specify to enable auto-draft of the deletion.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'optimize',
			1
		)
		->add_field(
			'optimize_trash',
			__( 'Delete trash in the post of all post type', 'wp-assistant' ) . '<label class="label">In Trash : <span class="post-count post-count-trash">'.$trash_results.'</span></label>',
			function () {
				$args = array(
					'id'      => 'optimize_trash',
					'default' => 0,
					'desc'    => __( 'Please specify to enable trash of the deletion.', 'wp-assistant' ),
				);
				helper::radiobox( $args );
			},
			'optimize',
			1
		)
		->add_field(
			'optimize_submit',
			__( 'Run Optimize', 'wp-assistant' ),
			function () {
				$nonce = wp_create_nonce( __FILE__ );
				?>
				<div class="run_optimize">
					<?php _e( 'To apply the above settings, please save once.', 'wp-assistant' ); ?>
					<button class="button-primary button-hero" id="optimize_submit"><?php _e( 'Run Optimize', 'wp-assistant' ); ?></button>
					<input type="hidden" id="optimize_nonce" name="_wp_optimize_nonce" value="<?php echo $nonce ?>" />
					<span class="spinner"></span>
				</div>
				</div>

			<?php
			},
			'optimize'
		);
	}

	/**
	 * 最適化を実行
	 *
	 * @return void
	 */
	public function run_optimize(){
		global $wpdb;

		// キャッシュを再セット
		config::set( 'options', get_option( config::get( 'prefix' ) . 'options' ) );

		$wp_nonce = $_REQUEST['_wp_optimize_nonce'];

		if( ! wp_verify_nonce( $wp_nonce, __FILE__ ) ){
			echo 'nonce is not defined.';
			exit();
		}

		/**
		 * リビジョンの削除
		 */
		if ( 1 == config::get_option('optimize_revision') ){
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
			if( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post_revision( intval( $id ) );
				}
				$message['optimize_revision'] = __( 'Deleted successfully revision.', 'wp-assistant' );
				$message['status'] = 'success';
			}
		}

		/**
		 * 自動下書きの削除
		 */
		if ( 1 == config::get_option('optimize_auto_draft') ){
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
			if( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post( intval( $id ), true );
				}
				$message['optimize_auto_draft'] = __( 'Deleted successfully automatic draft.', 'wp-assistant' );
				$message['status'] = 'success';
			}
		}

		/**
		 * ゴミ箱内の記事の削除
		 */
		if ( 1 == config::get_option('optimize_trash') ){
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );
			if( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post( intval( $id ), true );
				}
				$message['optimize_trash'] = __( 'Deleted successfully trash in the article', 'wp-assistant' );
				$message['status'] = 'success';
			}
		}

		if ( is_array( $message ) && $message ){
			echo wp_send_json( $message );
			exit();
		} else {

			$faild_message = array(
				'html'=> __( 'Failed to delete.', 'wp-assistant' ),
				'status' => 'faild',
			);

			echo wp_send_json( $faild_message );
			exit();
		}

	}

}