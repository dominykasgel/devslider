<?php
/**
 * Plugin Name: Dev Slider
 * Plugin URI: http://www.dominykasgel.com
 * Description: A very simple slider for WordPress. Built for developers.
 * Version: 1.0
 * Author: Dominykas Gelucevičius
 * Author URI: http://www.dominykasgel.com
 * Requires at least: 4.4
 * Tested up to: 4.7.4
 *
 * Text Domain: devslider
 *
 */
namespace DevSlider;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( '\DevSlider\Slider' ) ) : 

class Slider {

    public function __construct() {
        $this->define_constants();
        $this->init_hooks();

        do_action( 'devslider_loaded' );
    }

    /**
     * Hook into actions and filters.
     *
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ), 0 );
    }

    /**
     * Define constants.
     */
    private function define_constants() {
        $this->define('DEVSLIDER_PLUGIN_FILE', __FILE__);
        $this->define('DEVSLIDER_PLUGIN_BASENAME', plugin_basename(__FILE__));
        $this->define('DEVSLIDER_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
        $this->define('DEVSLIDER_PLUGIN_URL', plugins_url( '/', __FILE__ ));
    }

    /**
     * Define constant if not already set.
     *
     * @param  string $name
     * @param  string|bool $value
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Init Dev Slider when WordPress Initialises.
     */
    public function init() {
        // Before init action.
        do_action( 'before_devslider_init' );

        $this->register_cp_and_tax();

        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

        do_action( 'after_devslider_init' );
    }

    /**
     *
     * The main JavaScript code for the slider.
     *
     * @param $slideWidth int
     * @param $minSlides int
     * @param $maxSlides int
     *
     */
    public static function slider_js( $slideWidth = 0, $minSlides = 1, $maxSlides = 1 ) {

        $args = apply_filters('devslider_slider_js', 'slideWidth: '. absint( $slideWidth ) .',
                minSlides: '. absint( $minSlides ) .',
                maxSlides: '. absint( $maxSlides ) .',
                slideMargin: 10');

        echo '<script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery(".devslider").bxSlider({
                '. $args .'
            });
        });
    </script>';

    }

    /**
     * Enqueue WP scripts.
     *
     */
    public function scripts() {

        wp_enqueue_style( 'bxslider', DEVSLIDER_PLUGIN_URL . 'assets/css/jquery.bxslider.min.css' );
        wp_enqueue_style( 'devslider', DEVSLIDER_PLUGIN_URL . 'assets/css/devslider.css' );
        wp_enqueue_script( 'devslider', DEVSLIDER_PLUGIN_URL . 'assets/js/jquery.bxslider.min.js', array('jquery') );

        do_action( 'devslider_enqueue_scripts' );

    }

    /**
     * Admin init.
     *
     */
    public function admin_init() {
        $use_wp_meta_boxes = apply_filters( 'devslider_use_wp_meta_boxes', true );

        if ( $use_wp_meta_boxes ) {
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post', array( $this, 'save_slide' ) );
        }
    }

    /**
     * Add meta boxes for slider.
     *
     */
    public function add_meta_boxes() {
        add_meta_box( 'devslider_slide_parameters', __( 'Slide Settings', 'devslider' ), array( $this, 'slide_settings_callback' ), 'dev_slide', 'advanced', 'high' );

        do_action( 'devslider_add_meta_boxes' );
    }

    /**
     * Callback for slide settings meta box.
     *
     * @param $post
     *
     */
    public function slide_settings_callback( $post ) {

        $title = get_post_meta( $post->ID, '_slide_desc', true );

        echo '<label for="slide-desc">'. __('Description', 'devslider') .'</label><br />
	    <input type="text" name="slide_desc" id="slide-desc" value="'. esc_attr( $title ) .'" /><br />';

        do_action( 'devslider_slider_settings_metabox', $post );

    }

    /**
     * Save slide.
     *
     * @param $post_id
     *
     */
    public function save_slide( $post_id ) {

        if ( get_post_type( $post_id ) == 'dev_slide' ) {
            update_post_meta( $post_id, '_slide_desc', sanitize_text_field( $_POST['slide_desc'] ) );

            do_action( 'devslider_save_slide', $post_id );
        }

    }

    /**
     * Register a custom post type and a custom taxonomy for slider.
     *
     */
    private function register_cp_and_tax() {
        // Labels
        $labels = array(
            'name'               => _x( 'Slides', 'devslider' ),
            'singular_name'      => _x( 'Slide', 'devslider' ),
            'menu_name'          => _x( 'Slides', 'devslider' ),
            'name_admin_bar'     => _x( 'Slides', 'devslider' ),
            'add_new'            => _x( 'Add New Slide', 'devslider' ),
            'add_new_item'       => __( 'Add New Slide', 'devslider' ),
            'new_item'           => __( 'New Slide', 'devslider' ),
            'edit_item'          => __( 'Edit Slide', 'devslider' ),
            'view_item'          => __( 'View Slide', 'devslider' ),
            'all_items'          => __( 'All Slides', 'devslider' ),
            'search_items'       => __( 'Search Slides', 'devslider' ),
            'parent_item_colon'  => __( 'Parent Slides:', 'devslider' ),
            'not_found'          => __( 'No slides found.', 'devslider' ),
            'not_found_in_trash' => __( 'No slides found in Trash.', 'devslider' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'ss-slide' ),
            'capability_type'    => 'page',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'thumbnail' )
        );

        register_post_type( 'dev_slide', $args );

        // Add a new taxonomy for slides
        $labels = array(
            'name'                       => __( 'Category', 'devslider' ),
            'singular_name'              => __( 'Category', 'devslider' ),
            'search_items'               => __( 'Search Categories', 'devslider' ),
            'popular_items'              => __( 'Popular Categories', 'devslider' ),
            'all_items'                  => __( 'All Categories', 'devslider' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Category', 'devslider' ),
            'update_item'                => __( 'Update Category', 'devslider' ),
            'add_new_item'               => __( 'Add New Category', 'devslider' ),
            'new_item_name'              => __( 'New Author Category', 'devslider' ),
            'separate_items_with_commas' => __( 'Separate categories with commas', 'devslider' ),
            'add_or_remove_items'        => __( 'Add or remove category', 'devslider' ),
            'choose_from_most_used'      => __( 'Choose from the most used category', 'devslider' ),
            'not_found'                  => __( 'No category found.', 'devslider' ),
            'menu_name'                  => __( 'Category', 'devslider' ),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'dev-slide-category' ),
        );

        register_taxonomy( 'dev_slide_category', 'dev_slide', $args );
    }

    /**
     *
     * Display slider.
     *
     * @param $slideWidth int
     * @param $minSlides int
     * @param $maxSlides int
     *
     */
    public static function display_slider( $slideWidth = 0, $minSlides = 1, $maxSlides = 1, $category = 'all' ) {
        $args = array( 'posts_per_page' => -1, 'post_type' => 'dev_slide', 'post_status' => 'publish' );

        if ( $category != 'all' )
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'dev_slide_category',
                    'field' => 'term_id',
                    'terms' => $category
                )
            );

        $slides = get_posts( $args );

        if ( file_exists( get_template_directory() . '/maxslider-template.php' ) ) {
            include( get_template_directory() . '/maxslider-template.php' );
        } else {
            include( DEVSLIDER_PLUGIN_PATH . 'inc/maxslider-template.php' );
        }

        self::slider_js( $slideWidth, $minSlides, $maxSlides );
    }

    /**
     *
     * Shortcode
     *
     * @param $atts array
     *
     */
    public static function devslider_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'slidewidth' => '0',
            'minslides' => '1',
            'maxslides' => '1',
            'category' => 'all'
        ), $atts, 'DevSlider' );
 
        return self::display_slider( esc_attr( $atts['slidewidth'] ), esc_attr( $atts['minslides'] ), esc_attr( $atts['maxslides'] ), esc_attr( $atts['category'] ) );
    }

}

endif;

$devslider = new \Devslider\Slider();

add_shortcode( 'DevSlider', array( '\Devslider\Slider', 'devslider_shortcode' ) );