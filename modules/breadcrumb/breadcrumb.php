<?php
/*
Plugin Name: Breadcrumbs
Description: breadcrumbs for this site.
Text Domain: wp-assistant
*/
namespace WP_Assistant\modules\breadcrumb;

use \WP_Assistant\modules\module;
use WP_Assistant\inc;
use WP_Assistant\inc\config;

class breadcrumb extends module {

	/**
	 * The list of breadcrumb items.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $breadcrumb;
	/**
	 * Templates for link, current/standard state and before/after.
	 *
	 * @var array
	 */
	public $templates;
	/**
	 * Various strings.
	 *
	 * @var array
	 */
	public $strings;
	/**
	 * Various options.
	 *
	 * @var array
	 * @access public
	 */
	public $options;


	public function __construct(){
		$this->settings = parent::get_settings();
		add_shortcode( 'wpa_breadcrumb', array( $this, 'shortcode' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
	}

	/**
	 * パンくずの設定を追加
	 */
	public function add_settings() {

		$this->settings->add_section(
			array(
				'id'        => 'breadcrumbs',
				'title'     => __( 'Breadcrumbs', 'wp-assistant' ),
				'desc'      => __( 'Setting for Beradcrumbs', 'wp-assistant' ),
				'tabs_name' => __( 'Breadcrumbs', 'wp-assistant' ),
			)
		)->add_field(
			array(
				'id'                => 'Label Home',
				'title'             => __( 'Label Home', 'wp-assistant' ),
				'type'              => 'text',
				'section'           => 'breadcrumbs',
				'default'           => 'Home',
				'desc'              => __( 'Please input home label', 'wp-assistant' ),
				'size'              => '',
				'options'           => array(
					'true'  => __( 'On', 'wp-assistant' ),
					'false' => __( 'Off', 'wp-assistant' ),
				),
				'sanitize_callback' => '',
			)
		);
	}


	public function init( $templates = array(), $options = array(), $strings = array(), $autorun = true  ){

		/**
		 * Set for HTML of breadcrumbs.
		 */
		$this->templates = wp_parse_args(
			$templates,
			array(
				'link'     => '<a href="%s">%s</a>',
				'current'  => '<span class="c">%s</span>',
				'standard' => '<span class="s">%s</span>',
				'before'   => '<nav class="breadcrumbs">',
				'after'    => '</nav>',
			)
		);
		/**
		 * Set for Option of breadcrumbs.
		 */
		$this->options = wp_parse_args( $options, array(
			'separator'      => ' › ',
			'posts_on_front' => 'posts' == get_option( 'show_on_front' ) ? true : false,
			'page_for_posts' => get_option( 'page_for_posts' ),
			'show_pagenum'   => true, // support pagination
			'show_htfpt'     => false, // show hierarchical terms for post types,
		) );
		/**
		 * Set for String of breadcrumbs
		 * @var [type]
		 */
		$this->strings = wp_parse_args( $strings, array(
			'home'      => __( 'HOME', 'wp-assistant' ),
			'search'    => array(
				'singular' => __( 'Search results for<em>%s</em>', 'wp-assistant' ),
				'plural'   => __( '%s Search results for <em>%s</em>', 'wp-assistant' ),
			),
			'paged'     => __( 'Pages: %d ', 'wp-assistant' ),
			'404_error' => __( 'Error: Not Found 404', 'wp-assistant' ),
		) );

		// Generate breadcrumb
		if ( $autorun ) {
			echo $this->output();
			return true;
		}



		return $this->output();
	}

	/**
	 * Return the final breadcrumb.
	 *
	 * @return string
	 */
	public function output() {
		if ( empty( $this->breadcrumb ) ) {
			$this->generate();
		}
		$breadcrumb = (string) implode( $this->options['separator'], $this->breadcrumb );

		return $this->templates['before'] . $breadcrumb . $this->templates['after'];
	}

	/**
	 * Build the item based on the type.
	 *
	 * @param string|array $item
	 * @param string $type
	 *
	 * @return string
	 */
	protected function template( $item, $type = 'standard' ) {
		if ( is_array( $item ) ) {
			$type = 'link';
		}
		switch ( $type ) {
			case'link':
				return $this->template(
					sprintf(
						$this->templates['link'],
						esc_url( $item['link'] ),
						$item['title']
					)
				);
				break;
			case 'current':
				return sprintf( $this->templates['current'], $item );
				break;
			case 'standard':
				return sprintf( $this->templates['standard'], $item );
				break;
		}
	}

	/**
	 * Helper to generate taxonomy parents.
	 *
	 * @param mixed $term_id
	 * @param mixed $taxonomy
	 *
	 * @return void
	 */
	protected function generate_tax_parents( $term_id, $taxonomy ) {
		$parent_ids = array_reverse( get_ancestors( $term_id, $taxonomy ) );
		foreach ( $parent_ids as $parent_id ) {
			$term                                                 = get_term( $parent_id, $taxonomy );
			$this->breadcrumb["archive_{$taxonomy}_{$parent_id}"] = $this->template( array(
				'link'  => get_term_link( $term->slug, $taxonomy ),
				'title' => $term->name,
			) );
		}
	}

	/**
	 * Generate the breadcrumb.
	 *
	 * @return void
	 */
	public function generate() {
		$post_type                     = get_post_type();
		$queried_object                = get_queried_object();
		$this->options['show_pagenum'] = ( $this->options['show_pagenum'] && is_paged() ) ? true : false;

		// Home & Front Page
		$this->breadcrumb['home'] = $this->template( $this->strings['home'], 'current' );
		$home_linked              = $this->template( array(
			'link'  => home_url( '/' ),
			'title' => $this->strings['home'],
		) );
		if ( $this->options['posts_on_front'] ) {
			if ( ! is_home() || $this->options['show_pagenum'] ) {
				$this->breadcrumb['home'] = $home_linked;
			}
		} else {
			if ( ! is_front_page() ) {
				$this->breadcrumb['home'] = $home_linked;
			}
			if ( is_home() && ! $this->options['show_pagenum'] ) {
				$this->breadcrumb['blog'] = $this->template( get_the_title( $this->options['page_for_posts'] ), 'current' );
			}
			if ( ( 'post' == $post_type && ! is_search() && ! is_home() ) || ( 'post' == $post_type && $this->options['show_pagenum'] ) ) {
				$this->breadcrumb['blog'] = $this->template( array(
					'link'  => get_permalink( $this->options['page_for_posts'] ),
					'title' => get_the_title( $this->options['page_for_posts'] ),
				) );
			}
		}
		// Post Type Archive as index
		if ( is_singular() || ( is_archive() && ! is_post_type_archive() ) || is_search() || $this->options['show_pagenum'] ) {
			if ( $post_type_link = get_post_type_archive_link( $post_type ) ) {
				$post_type_label                          = get_post_type_object( $post_type )->labels->name;
				$this->breadcrumb["archive_{$post_type}"] = $this->template(
					array(
						'link'  => $post_type_link,
						'title' => $post_type_label,
					) );
			}
		}
		if ( is_singular() ) { // Posts, (Sub)Pages, Attachments and Custom Post Types
			if ( ! is_front_page() ) {
				if ( $this->options['show_htfpt'] ) {
					$_id        = $queried_object->ID;
					$_post_type = $post_type;
					if ( is_attachment() ) {
						// Show terms of the parent page
						$_id        = $queried_object->post_parent;
						$_post_type = get_post_type( $_id );
					}
					$taxonomies = get_object_taxonomies( $_post_type, 'objects' );
					$taxonomies = array_values( wp_list_filter( $taxonomies, array(
						'hierarchical' => true,
					) ) );
					if ( ! empty( $taxonomies ) ) {
						$taxonomy = $taxonomies[0]->name; // Get the first taxonomy
						$terms    = get_the_terms( $_id, $taxonomy );
						if ( ! empty( $terms ) ) {
							$terms = array_values( $terms );
							$term  = $terms[0]; // Get the first term
							if ( 0 != $term->parent ) {
								$this->generate_tax_parents( $term->term_id, $taxonomy );
							}
							$this->breadcrumb["archive_{$taxonomy}"] = $this->template( array(
								'link'  => get_term_link( $term->slug, $taxonomy ),
								'title' => $term->name,
							) );
						}
					}
				}
				if ( 0 != $queried_object->post_parent ) { // Get Parents
					$parents = array_reverse( get_post_ancestors( $queried_object->ID ) );
					foreach ( $parents as $parent ) {
						$this->breadcrumb["archive_{$post_type}_{$parent}"] = $this->template( array(
							'link'  => get_permalink( $parent ),
							'title' => get_the_title( $parent ),
						) );
					}
				}
				$this->breadcrumb["single_{$post_type}"] = $this->template( get_the_title(), 'current' );
			}
		} elseif ( is_search() ) { // Search
			$total                      = $GLOBALS['wp_query']->found_posts;
			$text                       = sprintf(
				_n(
					$this->strings['search']['singular'],
					$this->strings['search']['plural'],
					$total
				),
				$total,
				get_search_query()
			);
			$this->breadcrumb['search'] = $this->template( $text, 'current' );
			if ( $this->options['show_pagenum'] ) {
				$this->breadcrumb['search'] = $this->template( array(
					'link'  => home_url( '?s=' . urlencode( get_search_query( false ) ) ),
					'title' => $text,
				) );
			}
			// All archive pages
		} elseif ( is_archive() ) {
			// Categories, Tags and Custom Taxonomies
			if ( is_category() || is_tag() || is_tax() ) {
				$taxonomy = $queried_object->taxonomy;
				if ( 0 != $queried_object->parent && is_taxonomy_hierarchical( $taxonomy ) ) {
					$this->generate_tax_parents( $queried_object->term_id, $taxonomy );
				}
				$this->breadcrumb["archive_{$taxonomy}"] = $this->template( $queried_object->name, 'current' );
				if ( $this->options['show_pagenum'] ) {
					$this->breadcrumb["archive_{$taxonomy}"] = $this->template( array(
						'link'  => get_term_link( $queried_object->slug, $taxonomy ),
						'title' => $queried_object->name
					) );
				}
				/**
				 * Date Archive
				 */
			} elseif ( is_date() ) {
				// Year archive
				if ( is_year() ) {
					$this->breadcrumb['archive_year'] = $this->template( get_the_date( 'Y' ), 'current' );
					if ( $this->options['show_pagenum'] ) {
						$this->breadcrumb['archive_year'] = $this->template( array(
							'link'  => get_year_link( get_query_var( 'year' ) ),
							'title' => get_the_date( 'Y' ),
						) );
					}
					// Month archive
				} elseif ( is_month() ) {
					$this->breadcrumb['archive_year']  = $this->template( array(
						'link'  => get_year_link( get_query_var( 'year' ) ),
						'title' => get_the_date( 'Y' ),
					) );
					$this->breadcrumb['archive_month'] = $this->template( get_the_date( 'F' ), 'current' );
					if ( $this->options['show_pagenum'] ) {
						$this->breadcrumb['archive_month'] = $this->template( array(
							'link'  => get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ),
							'title' => get_the_date( 'F' )
						) );
					}
				} elseif ( is_day() ) { // Day archive
					$this->breadcrumb['archive_year']  = $this->template( array(
						'link'  => get_year_link( get_query_var( 'year' ) ),
						'title' => get_the_date( 'Y' )
					) );
					$this->breadcrumb['archive_month'] = $this->template( array(
						'link'  => get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ),
						'title' => get_the_date( 'F' )
					) );
					$this->breadcrumb['archive_day']   = $this->template( get_the_date( 'j' ) );
					if ( $this->options['show_pagenum'] ) {
						$this->breadcrumb['archive_day'] = $this->template( array(
							'link'  => get_month_link(
								get_query_var( 'year' ),
								get_query_var( 'monthnum' ),
								get_query_var( 'day' )
							),
							'title' => get_the_date( 'F' )
						) );
					}
				}
			} elseif ( is_post_type_archive() && ! is_paged() ) { // Custom Post Type Archive
				$post_type_label                          = get_post_type_object( $post_type )->labels->name;
				$this->breadcrumb["archive_{$post_type}"] = $this->template( $post_type_label, 'current' );
			} elseif ( is_author() ) { // Author archive
				$this->breadcrumb['archive_author'] = $this->template( $queried_object->display_name, 'current' );
			}
		} elseif ( is_404() ) {
			$this->breadcrumb['404'] = $this->template( $this->strings['404_error'], 'current' );
		}

		if ( $this->options['show_pagenum'] ) {
			$this->breadcrumb['paged'] = $this->template(
				sprintf(
					$this->strings['paged'],
					get_query_var( 'paged' )
				),
				'current'
			);
		}
	}

	/**
	 * ショートコード
	 * @param string $content
	 * @param array $attr
	 *
	 * @return bool|string
	 */
	public function shortcode( $content = '', $attr = array() ){
		$breadcrumbs = new breadcrumb();
		return $this->init( $templates = array(), $options = array(), $strings = array(), $autorun = false  );
	}
}

