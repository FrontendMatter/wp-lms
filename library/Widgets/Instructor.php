<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Alert\Alert;
use Mosaicpro\Media\Media;
use WP_Query;
use WP_Widget;

/**
 * Class Instructor_Widget
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Instructor_Widget extends WP_Widget
{
    /**
     * Construct the Widget
     */
    function __construct()
    {
        $options = array(
            'name' => '(LMS) Instructor',
            'description' => "Display Instructor's name, avatar image, courses and lessons by instructor."
        );
        parent::__construct(strtolower(class_basename(__CLASS__)), '', $options);

        if ( is_active_widget(false, false, $this->id_base) )
            add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue') );
    }

    /**
     * Enqueue Widget Assets
     */
    public function enqueue()
    {
        wp_enqueue_style('owl-carousel');
        wp_enqueue_style('owl-carousel-theme');
        wp_enqueue_script('owl-carousel');
        wp_enqueue_script('owl-carousel-init');
    }

    /**
     * The Widget Form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        extract($instance);
        $courses = Courses::getInstance();
        if (!isset($display_author_courses)) $display_author_courses = 0;
        if (!isset($author_courses_limit)) $author_courses_limit = 3;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('author_id'); ?>"><?php echo $courses->__('Author username or ID'); ?>: </label><br/>
            <input type="text"
                   class="widefat"
                   id="<?php echo $this->get_field_id('author_id'); ?>"
                   name="<?php echo $this->get_field_name('author_id'); ?>"
                   value="<?php if (isset($author_id)) echo esc_attr($author_id); ?>"
                   placeholder="<?php echo $courses->__('Defaults to current post author'); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('display_author_courses'); ?>">
                <input type="checkbox"
                       id="<?php echo $this->get_field_id('display_author_courses'); ?>"
                       name="<?php echo $this->get_field_name('display_author_courses'); ?>"
                       value="1"<?php checked(1, $display_author_courses); ?> /> <?php echo $courses->__('Display Author Courses'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('author_courses_limit'); ?>"><?php echo $courses->__('Author courses limit'); ?>: </label>
            <input type="text"
                   class="widefat"
                   style="width: 50px"
                   id="<?php echo $this->get_field_id('author_courses_limit'); ?>"
                   name="<?php echo $this->get_field_name('author_courses_limit'); ?>"
                   value="<?php if (isset($author_courses_limit)) echo esc_attr($author_courses_limit); ?>" />
        </p>
        <?php
    }

    /**
     * The Widget Output
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        extract($args);
        extract($instance);
        $courses = Courses::getInstance();

        $display_author_courses = isset($display_author_courses);
        $author_courses_limit = isset($author_courses_limit) ? $author_courses_limit : 3;
        $author_id = isset($author_id) ? $author_id : false;
        if (empty($title)) $title = $courses->__('Instructor');

        echo $before_widget;

        $post = get_post();
        if (!$author_id) $author_id = $post->post_author;
        $author_valid = is_numeric($author_id) ? get_user_by('id', $author_id) : get_user_by('login', $author_id);

        if (!$author_valid)
        {
            echo Alert::make()->addAlert($courses->__('Invalid author'))->isInfo();
            return;
        }

        $author_name = $author_id;
        if (is_numeric($author_id)) $author_name = get_the_author_meta('user_nicename', $author_id);

        echo Media::make()
            ->addObjectLeft(get_avatar( $author_id, 64 ))
            ->addBody($title, $author_name);

        if ($display_author_courses)
        {
            $author_courses_args = [
                'post_type' => $courses->getPrefix('course'),
                'posts_per_page' => $author_courses_limit
            ];

            if (is_numeric($author_id)) $author_courses_args['author'] = $author_id;
            else $author_courses_args['author_name'] = $author_id;

            if (get_post_type() == $courses->getPrefix('course'))
                $author_courses_args['post__not_in'] = [get_the_ID()];

            $author_courses = new WP_Query($author_courses_args);
            if ( $author_courses->have_posts() ):
            ?>
                <hr/>
                <h4><?php echo sprintf( $courses->__('Other courses by %1$s'), $author_name ); ?></h4>
                <div class="owl-carousel owl-theme owl-carousel-single">

                    <?php while ( $author_courses->have_posts() ): ?>
                        <?php $author_courses->the_post(); ?>
                        <div class="item">

                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(get_the_ID(), ['class' => 'img-responsive']); ?></a>
                            <a href="<?php the_permalink(); ?>"><h5><?php the_title(); ?></h5></a>

                        </div>
                    <?php endwhile; ?>

                </div>
            <?php
            endif;
            wp_reset_query();
        }

        echo $after_widget;
    }
}