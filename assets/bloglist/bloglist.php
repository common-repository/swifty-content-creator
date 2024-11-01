<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Bloglist_Asset' ) ) {

    /**
     * Class SWC_Bloglist_Asset create and display bloglist
     */
    class SWC_Bloglist_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_bloglist';

        /**
         * constructor, make sure scripts are loaded
         */
        public function __construct()
        {
            parent::__construct();

            // dorh Temp fix for SSD to make sure a blog image can be added to each blogpost
            if ( function_exists( 'add_theme_support' ) ) {
                add_theme_support( 'post-thumbnails' );
            }
        }

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * Create a bloglist from all blogposts
         *
         * @return string|void
         */
        public function get_shortcode_html( $atts )
        {
            global $swifty_lib_dir;
            if( isset( $swifty_lib_dir ) ) {
                require_once $swifty_lib_dir . '/php/lib/swifty-image-functions.php';
            }

            global $post;

            // do not show in blog posts to prevent recursive calls
            if( $post && $post->post_type && $post->post_type === 'post' ) {
                return '';
            }

            ob_start();

            $args = array( 'post_status' => 'publish', 'posts_per_page' => -1 /*'posts_per_page' => 5, 'offset'=> 0,*/ );
            if( $atts[ 'show_categories' ] === 'specific' ) {
                $args[ 'category_name' ] = $atts[ 'categories' ];
            }
            if( intval( $atts[ 'maxnr' ] ) > 0 ) {
                $args[ 'posts_per_page' ] = $atts[ 'maxnr' ];
            }

            $show_img = false;
            $title_tag = '';
            $show_summary = false;
            $show_date = false;
            $show_author = false;
            $show_category = false;
            $class_add = '';

            $tmpl_nr = 1;
            if( intval( $atts[ 'template' ] ) > 0 ) {
                $tmpl_nr = intval( $atts[ 'template' ] );
            }

            if( $tmpl_nr === 1 ) {
                $show_img = true;
                $title_tag = 'h2';
                $show_summary = true;
                $show_date = true;
                $show_author = true;
                $show_category = true;
            }
            if( $tmpl_nr === 2 ) {
                $class_add .= ' swc_blog_single_line';
            }
            if( $tmpl_nr === 3 ) {
                $title_tag = 'h2';
                $show_summary = true;
                $show_date = true;
                $show_author = true;
                $show_category = true;
            }
            if( $tmpl_nr === 4 ) {
                $show_date = true;
                $class_add .= ' swc_blog_single_line';
            }
            if( $tmpl_nr === 5 ) {
                $title_tag = 'h2';
                $show_summary = true;
            }
            if( $tmpl_nr === 6 ) {
                $show_img = true;
                $title_tag = 'h2';
                $show_summary = true;
            }

            ?>
                <div class="swc_blog_template swc_blog_template<?php echo $tmpl_nr; ?>">
            <?php

            // store the current wp_filter var, this is currently used in a loop and when the_excerpt is called this loop
            // is run another time which put the position in the loop at the end and prevends further handling of the_content filter
            global $wp_filter;
            $old_wp_filter = $wp_filter;

            global $previousday;

            $post_id = $post ? $post->ID : -1;

            $blogs_query = new WP_Query( $args );

            while( $blogs_query->have_posts()  ) {
                $blogs_query->the_post();

                // forget that this date was already printed so the next blog item will show its date
                $previousday = '';
//                print_r( $post );
?>
                <div class="swc_blog_post <?php echo $class_add; ?>">
                    <div class="swc_blog_title">
                        <?php if( $title_tag !== '' ) { echo '<' . $title_tag . '>'; } ?>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <?php if( $title_tag !== '' ) { echo '</' . $title_tag . '>'; } ?>
                    </div>

                    <?php if( $show_img && has_post_thumbnail() ) : ?>
                        <div class="swc_blog_image">
                            <?php echo SwiftyImageFunctions::get_img_tag( wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) ), '', false, '', '', 'nothing', $atts, '', $post_id ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if( $show_summary ) : ?>
                        <div class="swc_blog_summary">
                            <?php the_excerpt(); ?>
                            <p class="swc_blog_readmore"><a href="<?php the_permalink(); ?>"><?php echo __( 'Read more...', 'swifty-content-creator' ); ?></a></p>
                        </div>
                    <?php endif; ?>

                    <?php if( $show_date || $show_author || $show_category ) : ?>
                        <div class="swc_blog_details">
                            <?php if( $show_date ) : ?>
                                <div class="swc_blog_date"><?php the_date(); ?></div>
                                <?php if( $show_author || $show_category ) : ?>
                                    <div class="swc_blog_bull">&bull;</div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if( $show_author ) : ?>
                                <div class="swc_blog_author"><?php the_author(); ?></div>
                                <?php if( $show_category ) : ?>
                                    <div class="swc_blog_bull">&bull;</div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if( $show_category ) : ?>
                                <div class="swc_blog_category">
                                    <?php
                                        $categories = wp_get_post_categories( $post->ID );
                                        foreach( $categories as $i => $category_id ) {
                                            $category = get_category( $category_id );

                                            if( $i > 0 ) {
                                                echo ', ';
                                            }
                                            echo $category->name;
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php }

            if( $post_id > 0 ) {
                global $wp_query;

                $wp_query->post = get_post( $post_id );
            }
            wp_reset_postdata();

            $wp_filter = $old_wp_filter;

            ?>
                </div>
            <?php

            return str_replace( array( "\r", "\n"/*, '  '*/ ), '', ob_get_clean() );
        }
    }

    new SWC_Bloglist_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_bloglist',
        'name' => __( 'Blog post list', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'others',
        'icon' => '&#xe607;',
        'order' => '30',
        'width' => '100',
        'onchange' => 'checkimages',
        'vars' => array(
            'show_categories' => array(
                'default' => 'all',
                'type' => 'radiobutton',
                'values' => 'all^' . __( 'All', 'swifty-content-creator' ) . '|specific^' . __( 'Specific categories', 'swifty-content-creator' ) . ':',
                'label' => __( 'Show from categories', 'swifty-content-creator' ),
                'column' => 0,
                'width' => 300
            ),
            'maxnr' => array(
                'default' => '',
                'label' => __( 'Maximum number of blog posts', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 1
            ),
            'categories' => array(
                'default' => '',
                'type' => 'textarea',
                'label' => __( 'Specific categories', 'swifty-content-creator' ),
                'tooltip' => __( "You can type one or more existing category names, so the blog posts will be filtered. Separate multiple categories with a comma ','" ),
                'column' => 1,
                'width' => 400
            ),
            'template' => array(
                'default' => '6',
                'type' => 'radiobutton',
                'values' =>
                    '2^' . __( 'Title', 'swifty-content-creator' ) .
                    '|4^' . __( 'Title Date', 'swifty-content-creator' ) .
                    '|5^' . __( 'Summary', 'swifty-content-creator' ) .
                    '|3^' . __( 'Summary Info', 'swifty-content-creator' ) .
                    '|6^' . __( 'Visual Summary', 'swifty-content-creator' ) .
                    '|1^' . __( 'Visual Summary Info', 'swifty-content-creator' ),
                'label' => __( 'Style', 'swifty-content-creator' ),
                'column' => 2,
                'width' => 350
            )
        )
    ) );
} );
