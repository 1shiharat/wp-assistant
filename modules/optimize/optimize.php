<?php

namespace siteSupports\modules\optimize;

use siteSupports\config;
use siteSupports\inc\helper;

class optimize {

	public function __construct() {
		add_action( 'ggs_settings_fields_after', array( $this, 'optimize_fields' ), 10, 1 );
		add_action( 'wp_ajax_run_optimize', array( $this, 'run_optimize' ), 10, 1 );
	}

	/**
	 * フィールドを追加
	 *
	 * @param $admin
	 */
	public function optimize_fields( $admin ) {
		global $wpdb;
		$admin->add_section( 'optimize', function () {
			echo 'データベース最適化';
		} );

		$revision_posts_count = wp_count_posts( 'revision' );
		$admin->add_field(
			'optimize_revision',
			__( 'すべてのリビジョンの削除', 'ggsupports' ) . ' <label class="label">リビジョン数 : <span class="post-count post-count-auto-draft">' . esc_attr( $revision_posts_count->inherit  ). '</label> ',
			function () {
				?>
				<div>
				<?php
				$args = array(
					'id'      => 'optimize_revision',
					'default' => 0,
					'desc'    => __( 'すべてのリビジョンを削除します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
				?>
				</div>
				<?php
			},
			'optimize',
			0
		);
		$draft_results = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = \"auto-draft\"" );
		$admin->add_field(
			'optimize_auto_draft',
			__( 'すべての自動下書きの削除', 'ggsupports' ). ' <label class="label"> 下書き数 : <span class="post-count post-count-auto-draft">'. $draft_results .'</span></label>',
			function () {
				$args = array(
					'id'      => 'optimize_auto_draft',
					'default' => 0,
					'desc'    => __( 'すべての自動下書きを削除します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'optimize',
			0
		);

		$admin->add_field(
			'optimize_trash',
			__( 'すべてのゴミ箱内の記事の削除', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'optimize_trash',
					'default' => 0,
					'desc'    => __( 'すべての投稿タイプのゴミ箱内の記事を削除します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'optimize',
			0
		);

		$admin->add_field(
			'optimize_trash',
			__( 'スパムコメントとゴミ箱内のコメントを削除', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'optimize_trash',
					'default' => 0,
					'desc'    => __( 'スパムと判断されたコメントと、ゴミ箱に移されているコメントを削除します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'optimize',
			0
		);

		$admin->add_field(
			'optimize_submit',
			__( '最適化の実行', 'ggsupports' ),
			function () {
				$nonce = wp_create_nonce(__FILE__);
				?>
				<div class="run_optimize">
					<button class="button-primary button-hero" id="optimize_submit"><?php _e( '最適化を実行', 'ggsupports' ); ?></button>
					<input type="hidden" id="optimize_nonce" name="_wp_optimize_nonce" value="<?php echo $nonce ?>" />
					<span class="spinner"></span>
				</div>
			<?php
			},
			'optimize',
			0
		);

	}

	/**
	 * 最適化を実行
	 */
	public function run_optimize(){
		global $wpdb;
		$wp_nonce = $_REQUEST['_wp_optimize_nonce'];
		$verify = wp_verify_nonce( $wp_nonce, __FILE__ );
		if( ! $verify ){
			echo 'nonce is not defined.';
			exit();
		}

		/**
		 * リビジョンの削除
		 */
		if ( '1' === config::get_option('optimize_revision') ){
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
			if( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post_revision( intval( $id ) );
				}
				$message['optimize_revision'] = __( '<p>リビジョンを削除しました。</p>' );
				$message['status'] = 'success';
			}
		}

		/**
		 * 自動下書きの削除
		 */
		if ( '1' === config::get_option('optimize_auto_draft') ){
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
			if( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post( intval( $id ), true );
				}
				$message['optimize_auto_draft'] = __( '<p>自動下書き記事を削除しました。</p>' );
				$message['status'] = 'success';
			}
		}

		/**
		 * ゴミ箱内の記事の削除
		 */
		if ( '1' === config::get_option('optimize_trash') ){
			$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
			if( $query ) {
				foreach ( $query as $id ) {
					wp_delete_post( intval( $id ), true );
				}
				$message['optimize_trash'] = __( '<p>ゴミ箱内の記事を削除しました。</p>' );
				$message['status'] = 'success';
			}
		}


		if ( is_array( $message ) && $message ){
			echo wp_send_json( $message );
			exit();
		}

		$faild_message = array(
			'html'=> __( '<p>削除できませんでした。</p>', 'ggsupports' ),
			'status' => 'faild',
		);

		echo wp_send_json( $faild_message );
		exit();


	}

}