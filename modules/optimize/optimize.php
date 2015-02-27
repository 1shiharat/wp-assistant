<?php

namespace siteSupports\modules\optimize;

use siteSupports\inc\helper;

class optimize {

	public function __construct() {
		add_action( 'ggs_settings_fields_after', array( $this, 'optimize_fields' ), 10, 1 );
	}

	/**
	 * フィールドを追加
	 *
	 * @param $admin
	 */
	public function optimize_fields( $admin ) {
		$admin->add_section( 'optimize', function () {
			echo 'データベース最適化';
		} );

		$admin->add_field(
			'optimize_revision',
			__( 'すべてのリビジョンの削除', 'ggsupports' ),
			function () {
				$args = array(
					'id'      => 'optimize_revision',
					'default' => 0,
					'desc'    => __( 'すべてのリビジョンを削除します。', 'ggsupports' ),
				);
				helper::radiobox( $args );
			},
			'optimize',
			0
		);

		$admin->add_field(
			'optimize_auto_draft',
			__( 'すべての自動下書きの削除', 'ggsupports' ),
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
				?>

				<div>
					<button class="button-primary button-hero" id="optimize_submit"><?php _e( '最適化を実行', 'ggsupports' ); ?></button>
				</div>
			<?php
			},
			'optimize',
			0
		);

	}


}