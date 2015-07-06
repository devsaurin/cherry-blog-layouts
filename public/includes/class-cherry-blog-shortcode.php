<?php
/**
 * Cherry Blog Shortcode.
 *
 * @package   Cherry_Blog_Layouts
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @link      http://www.cherryframework.com/
 * @copyright 2014 Cherry Team
 */

/**
 * Class for Blog shortcode.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Cherry_Blog_Layout_Shortcode' ) ) {

	class Cherry_Blog_Layout_Shortcode {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Shortcode name.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		public $name = 'blog';

		function __construct() {

			// Register shortcode on 'init'.
			add_action( 'init', array( $this, 'register_shortcode' ) );

			// Add shortcode to editor
			add_filter( 'cherry_shortcodes/data/shortcodes', array( $this, 'add_to_editor' ) );

		}

		/**
		 * Registers the [$this->name] shortcode.
		 *
		 * @since 1.0.0
		 */
		public function register_shortcode() {

			/**
			 * Filters a shortcode name.
			 *
			 * @since 1.0.0
			 * @param string $this->name Shortcode name.
			 */
			$tag = apply_filters( $this->name . '_shortcode_name', $this->name );

			add_shortcode( $tag, array( $this, 'do_shortcode' ) );
		}

		/**
		 * Add blog layout shortcode to Cherry Shortcodes editor
		 *
		 * @since  1.0.0
		 * @param  array  $shortcodes  already added shortcodes
		 * @return array
		 */
		public function add_to_editor( $shortcodes ) {

			$terms = get_terms( 'category', 'slug' );

			$terms_list = array();
			if ( ! is_wp_error( $terms ) ) {
				$terms_list = wp_list_pluck( $terms, 'name', 'slug' );
			}

			$sizes_list = array();
			if ( class_exists( 'Cherry_Shortcodes_Tools' ) && method_exists( 'Cherry_Shortcodes_Tools', 'image_sizes' ) ) {
				$sizes_list = Cherry_Shortcodes_Tools::image_sizes();
			}

			$shortcodes[ $this->name ] = apply_filters( 'cherry_blog_layout_shortcode_settings',
				array(
					'name'  => __( 'Blog Layout', 'cherry-blog-layouts' ), // Shortcode name.
					'desc'  => __( 'Cherry blog layout shortcode', 'cherry-blog-layouts' ),
					'type'  => 'single', // Can be 'wrap' or 'single'. Example: [b]this is wrapped[/b], [this_is_single]
					'group' => 'content', // Can be 'content', 'box', 'media' or 'other'. Groups can be mixed
					'atts'  => array( // List of shortcode params (attributes).
						'posts_per_page' => array(
							'type'    => 'slider',
							'min'     => -1,
							'max'     => 100,
							'step'    => 1,
							'default' => 3,
							'name'    => __( 'Limit', 'cherry-blog-layouts' ),
							'desc'    => __( 'Maximum number of posts.', 'cherry-blog-layouts' )
						),
						'order' => array(
							'type' => 'select',
							'values' => array(
								'desc' => __( 'Descending', 'cherry-blog-layouts' ),
								'asc'  => __( 'Ascending', 'cherry-blog-layout' )
							),
							'default' => 'DESC',
							'name' => __( 'Order', 'cherry-blog-layouts' ),
							'desc' => __( 'Posts order', 'cherry-blog-layouts' )
						),
						'orderby' => array(
							'type' => 'select',
							'values' => array(
								'none'          => __( 'None', 'cherry-blog-layouts' ),
								'id'            => __( 'Post ID', 'cherry-blog-layouts' ),
								'author'        => __( 'Post author', 'cherry-blog-layouts' ),
								'title'         => __( 'Post title', 'cherry-blog-layouts' ),
								'name'          => __( 'Post slug', 'cherry-blog-layouts' ),
								'date'          => __( 'Date', 'cherry-blog-layouts' ),
								'modified'      => __( 'Last modified date', 'cherry-blog-layouts' ),
								'rand'          => __( 'Random', 'cherry-blog-layouts' ),
								'comment_count' => __( 'Comments number', 'cherry-blog-layouts' ),
							),
							'default' => 'date',
							'name'    => __( 'Order by', 'cherry-blog-layouts' ),
							'desc'    => __( 'Order posts by', 'cherry-blog-layouts' )
						),
						'category' => array(
							'type'     => 'select',
							'multiple' => true,
							'values'   => $terms_list,
							'default'  => '',
							'name'     => __( 'Category', 'cherry-blog-layouts' ),
							'desc'     => __( 'Select categories to show posts from', 'cherry-blog-layouts' ),
						),
						'paged' => array(
							'type'    => 'bool',
							'default' => 'no',
							'name'    => __( 'Show pager', 'cherry-blog-layouts' ),
							'desc'    => __( 'Show paged navigation or not', 'cherry-blog-layouts' ),
						),
						'layout_type' => array(
							'type' => 'select',
							'values' => array(
								'grid'     => __( 'Grid', 'cherry-blog-layouts' ),
								'masonry'  => __( 'Masonry', 'cherry-blog-layouts' ),
								'timeline' => __( 'Timeline', 'cherry-blog-layouts' )
							),
							'default' => 'date',
							'name'    => __( 'Layout', 'cherry-blog-layouts' ),
							'desc'    => __( 'Select output layout format', 'cherry-blog-layouts' )
						),
						'class'   => array(
							'default' => '',
							'name'    => __( 'Class', 'cherry-blog-layouts' ),
							'desc'    => __( 'Extra CSS class', 'cherry-blog-layouts' )
						),
					),
					'icon'     => 'th', // Custom icon (font-awesome).
					'function' => array( $this, 'do_shortcode' ) // Name of shortcode function.
				)
			);

			return $shortcodes;

		}

		/*
		public static function get_blog_template(){
				$template_list = array();

				$theme_path = get_stylesheet_directory() . '/blog-layouts/tmpl/';

				if ( file_exists( $theme_path ) && is_dir( $theme_path ) ) {
					$template_list = scandir( $theme_path );
					$template_list = array_diff( $template_list, array( '.', '..', 'index.php' ) );
				}

				foreach ( $template_list as $key => $value) {
					$result_array[ str_replace( '.tmpl', '', $value ) ] = $value;
				}

				return $result_array;
			}
		 */
		/**
		 * Callback function for blog shortcode
		 *
		 * @since  1.0.0
		 * @param  array  $atts    shortcode attributes array
		 * @param  string $content shortcode inner content
		 * @return string
		 */
		public function do_shortcode( $atts, $content = null ) {

			// Set up the default arguments.
			$defaults = array(
				'posts_per_page'	=> 3,
				'orderby'			=> 'date',
				'order'				=> 'DESC',
				'category'			=> '',
				'paged'				=> 0,
				'layout_type'		=> 'masonry',
				'template_type'		=> '',
				'class'				=> '',
			);

			/**
			 * Parse the arguments.
			 *
			 * @link http://codex.wordpress.org/Function_Reference/shortcode_atts
			 */

			$atts = shortcode_atts( $defaults, $atts, $this->name );

			$parsed_options = Cherry_Blog_Layouts_Data::get_parsed_options( $atts );

			$query_args = array();
			$query_args['posts_per_page']   = $parsed_options['posts_per_page'];
			$query_args['orderby']          = $parsed_options['orderby'];
			$query_args['order']            = $parsed_options['order'];
			$query_args['suppress_filters'] = false;

			if ( ! empty( $args['category'] ) ) {
				$cat = str_replace( ' ', ',', $args['category'] );
				$cat = explode( ',', $cat );
				if ( is_array( $cat ) ) {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'category',
							'field'    => 'slug',
							'terms'    => $cat
						)
					);
				}
			} else {
				$query_args['tax_query'] = false;
			}

			if ( $atts['paged'] ) {
				if ( get_query_var('paged') ) {
					$query_args['paged'] = get_query_var('paged');
				} elseif ( get_query_var('page') ) {
					$query_args['paged'] = get_query_var('page');
				} else {
					$query_args['paged'] = 1;
				}
			}

			$posts_query = new WP_Query( $query_args );

			if ( ! $posts_query->have_posts() ) {
				return __( 'No posts found', 'cherry-blog-layouts' );
			}

			$allowed_layouts = array( 'grid', 'masonry', 'timeline' );
			$layout = ( in_array( $parsed_options['layout_type'], $allowed_layouts ) ) ? $parsed_options['layout_type'] : 'grid';

			ob_start();

			$post_counter = 0;
			$break_point_date = '';

			while ( $posts_query->have_posts() ) {
				$posts_query->the_post();
				$template_file = Cherry_Blog_Template_Loader::get_template( 'layout-' . $layout, 'content' );
				include $template_file;
			}
			$posts = ob_get_clean();

			switch ( $layout ) {
				case 'grid':
					switch ( $parsed_options['grid_column'] ) {
						case 'grid-2':
							$columns = 2;
							break;
						case 'grid-3':
							$columns = 3;
							break;
						case 'grid-4':
							$columns = 4;
							break;
						case 'grid-6':
							$columns = 6;
							break;
					}
					$attrs = 'data-columns="' . $columns . '"';
					return sprintf( '<div class="%2$s-layout" %3$s>%4$s<div class="grid-wpapper">%1$s<div class="clear"></div></div></div>', $posts, $layout, $attrs, Cherry_Blog_Layouts_Data::filter_render() );
					break;
				case 'masonry':
					$attrs = 'data-columns="' . $parsed_options['columns'] . '"';
					$attrs .= 'data-gutter="' . $parsed_options['columns_gutter'] . '"';
					return sprintf( '<div class="%2$s-layout" %3$s>%4$s<div class="masonry-wpapper">%1$s<div class="clear"></div></div></div>', $posts, $layout, $attrs, Cherry_Blog_Layouts_Data::filter_render() );
					break;
				case 'timeline':
					$attrs = 'data-timeline-item-width="' . $parsed_options['timeline_item_width'] . '"';
					return sprintf( '<div class="%2$s-layout" %3$s>%4$s<div class="timeline-wpapper"><span class="timeline-line"></span>%1$s<div class="clear"></div></div></div>', $posts, $layout, $attrs, Cherry_Blog_Layouts_Data::filter_render() );
					break;
			}
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance )
				self::$instance = new self;

			return self::$instance;

		}

	}

	Cherry_Blog_Layout_Shortcode::get_instance();
}

add_filter('cherry_blog_layout_shortcode_settings', 'blog_layout_shortcode_settings');

/*function blog_layout_shortcode_settings( $settings ){
	$settings['atts']['template_type'] = array(
		'type'     => 'select',
		'values'   => array(
						'type-1'  => __( 'Type 1', 'cherry-blog-layouts' ),
						'type-2'  => __( 'Type 2', 'cherry-blog-layouts' ),
						'type-3'  => __( 'Type 3', 'cherry-blog-layouts' )
					),
		'default'  => '',
		'name'     => __( 'Template', 'cherry-blog-layouts' ),
		'desc'     => __( 'Select template to show posts from', 'cherry-blog-layouts' ),
	);
	return $settings;
}*/