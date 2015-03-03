<?php
/**
 * =====================================================
 * 記事編集画面にナビゲーションを設置
 * @package   siteSupports
 * @author    Grow Group
 * @license   GPL v2 or later
 * @link      http://grow-group.jp
 * @see https://github.com/wpverse/Advanced-Post-Navigation
 * =====================================================
 */
namespace siteSupports\modules\adminPostNav;

use siteSupports\config;
use siteSupports\inc\helper;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class adminPostNav {

	private static $prev_text = '';
	private static $next_text = '';
	private static $post_statuses     = array( 'draft', 'future', 'pending', 'private', 'publish' ); // Filterable later
	private static $post_statuses_sql = '';

	/**
	 * Returns version of the plugin.
	 *
	 * @since 1.7
	 */
	public static function version() {
		return '1.8';
	}

	/**
	 * Class constructor: initializes class variables and adds actions and filters.
	 */
	public static function init() {
		add_action( 'load-post.php', array( __CLASS__, 'register_post_page_hooks' ) );
	}

	/**
	 * Filters/actions to hook on the admin post.php page.
	 *
	 * @since 1.7
	 *
	 */
	public static function register_post_page_hooks() {

		// Set translatable strings
		self::$prev_text = __( '&larr; 前の記事へ', 'ggsupports' );
		self::$next_text = __( '次の記事へ &rarr;', 'ggsupports' );

		// Register hooks
		add_action( 'admin_enqueue_scripts',      array( __CLASS__, 'add_css' ) );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'add_js' ) );
		add_action( 'do_meta_boxes',              array( __CLASS__, 'do_meta_box' ), 10, 3 );
	}

	/**
	 * Register meta box
	 *
	 * By default, the navigation is present for all post types.  Filter
	 * 'c2c_admin_post_navigation_post_types' to limit its use.
	 *
	 * @param string $post_type The post type
	 * @param string $type The mode for the meta box (normal, advanced, or side)
	 * @param WP_Post $post The post
	 * @return void
	 */
	public static function do_meta_box( $post_type, $type, $post ) {
		$post_types = apply_filters( 'c2c_admin_post_navigation_post_types', get_post_types() );
		if ( ! in_array( $post_type, $post_types ) )
			return;

		$post_statuses = apply_filters( 'c2c_admin_post_navigation_post_statuses', self::$post_statuses, $post_type, $post );
		self::$post_statuses_sql = "'" . implode( "', '", array_map( 'esc_sql', $post_statuses ) ) . "'";
		$label = self::_get_post_type_label( $post_type );
		if ( in_array( $post->post_status, $post_statuses ) )
			add_meta_box( 'adminpostnav', sprintf( __( '%s Navigation', 'ggsupports' ), ucfirst( $post_type ) ), array( __CLASS__, 'add_meta_box' ), $post_type, 'side', 'core' );
	}

	/**
	 * Adds the content for the post navigation meta_box.
	 *
	 * @param object $object
	 * @param array $box
	 * @return void (Text is echoed.)
	 */
	public static function add_meta_box( $object, $box ) {
		global $post_ID;
		$display = '';

		$context = self::_get_post_type_label( $object->post_type );

		$prev = self::previous_post();
		if ( $prev ) {
			$post_title = strip_tags( get_the_title( $prev->ID ) ); /* If only the_title_attribute() accepted post ID as arg */
			$display .= '<a href="' . get_edit_post_link( $prev->ID ) . '" id="admin-post-nav-prev" title="' .
			            esc_attr( sprintf( __( '前の記事 %1$s: %2$s', 'ggsupports' ), $context, $post_title ) ) .
			            '" class="admin-post-nav-prev add-new-h2">' . self::$prev_text . '</a>';
		}

		$next = self::next_post();
		if ( $next ) {
			if ( ! empty( $display ) )
				$display .= ' ';
			$post_title = strip_tags( get_the_title( $next->ID ) );  /* If only the_title_attribute() accepted post ID as arg */
			$display .= '<a href="' . get_edit_post_link( $next->ID ) .
			            '" id="admin-post-nav-next" title="' .
			            esc_attr( sprintf( __( '次の記事 %1$s: %2$s', 'ggsupports' ), $context, $post_title ) ).
			            '" class="admin-post-nav-next add-new-h2">' . self::$next_text . '</a>';
		}

		$display = '<span id="admin-post-nav">' . $display . '</span>';
		$display = apply_filters( 'admin_post_nav', $display ); /* Deprecated as of v1.5 */
		echo apply_filters( 'c2c_admin_post_navigation_display', $display );
	}

	/**
	 * Gets label for post type.
	 *
	 * @since 1.7
	 *
	 * @param string $post_type The post_type
	 * @return string The label for the post_type
	 */
	public static function _get_post_type_label( $post_type ) {
		$label = $post_type;
		$post_type_object = get_post_type_object( $label );
		if ( is_object( $post_type_object ) )
			$label = $post_type_object->labels->singular_name;
		return strtolower( $label );
	}

	/**
	 * Outputs CSS within style tags
	 */
	public static function add_css() {
		echo <<<HTML
	<style type="text/css">
	#admin-post-nav {margin-left:20px;}
	#adminpostnav #admin-post-nav {margin-left:0;}
	h2 #admin-post-nav {font-size:0.6em;}
	.inside #admin-post-nav a {top:0;margin-top:4px;display:inline-block;}
	</style>

HTML;
	}

	/**
	 * Outputs the JavaScript used by the plugin.
	 *
	 * For those with JS enabled, the navigation links are moved next to the
	 * "Edit Post" header and the plugin's meta_box is hidden.  The fallback
	 * for non-JS people is that the plugin's meta_box is shown and the
	 * navigation links can be found there.
	 */
	public static function add_js() {
		echo <<<JS
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#admin-post-nav').appendTo($('h2'));
		$('#adminpostnav, label[for="adminpostnav-hide"]').hide();
	});
	</script>

JS;
	}

	/**
	 * Returns the previous or next post relative to the current post.
	 *
	 * Currently, a previous/next post is determined by the next lower/higher
	 * valid post based on relative sequential post ID and which the user can
	 * edit.  Other post criteria such as post type (draft, pending, etc),
	 * publish date, post author, category, etc, are not taken into
	 * consideration when determining the previous or next post.
	 *
	 * @param string $type (optional) Either '<' or '>', indicating previous or next post, respectively. Default is '<'.
	 * @param int $offset (optional) Offset. Default is 0.
	 * @param int $limit (optional) Limit. Default is 15.
	 * @return string
	 */
	public static function query( $type = '<', $offset = 0, $limit = 15 ) {
		global $post_ID, $wpdb;

		if ( $type != '<' )
			$type = '>';
		$offset = (int) $offset;
		$limit  = (int) $limit;

		$post_type = esc_sql( get_post_type( $post_ID ) );
		$sql = "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = '$post_type' AND post_status IN (" . self::$post_statuses_sql . ') ';

		// Determine order
		if ( function_exists( 'is_post_type_hierarchical' ) && is_post_type_hierarchical( $post_type ) )
			$orderby = 'post_title';
		else
			$orderby = 'ID';
		$orderby = esc_sql( apply_filters( 'c2c_admin_post_navigation_orderby', $orderby, $post_type ) );
		$post = get_post( $post_ID );
		$sql .= "AND $orderby $type '{$post->$orderby}' ";

		$sort = $type == '<' ? 'DESC' : 'ASC';
		$sql .= "ORDER BY $orderby $sort LIMIT $offset, $limit";

		// Find the first one the user can actually edit
		$posts = $wpdb->get_results( $sql );
		$result = false;
		if ( $posts ) {
			foreach ( $posts as $post ) {
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$result = $post;
					break;
				}
			}
			if ( ! $result ) { // The fetch did not yield a post editable by user, so query again.
				$offset += $limit;
				// Double the limit each time (if haven't found a post yet, chances are we may not, so try to get through posts quicker)
				$limit += $limit;
				return self::query( $type, $offset, $limit );
			}
		}
		return $result;
	}

	/**
	 * Returns the next post relative to the current post.
	 *
	 * A convenience function that calls query().
	 *
	 * @return object The next post object.
	 */
	public static function next_post() {
		return self::query( '>' );
	}

	/**
	 * Returns the previous post relative to the current post.
	 *
	 * A convenience function that calls query().
	 *
	 * @return object The previous post object.
	 */
	public static function previous_post() {
		return self::query( '<' );
	}

}