<?php namespace Mosaicpro\WP\Plugins\LMS;

use WP_Widget;

/**
 * Class Course_Information_Widget
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Course_Information_Widget extends WP_Widget
{
    /**
     * Construct the Widget
     */
    function __construct()
    {
        $options = array(
            'name' => '(LMS) Course Information',
            'description' => 'Display information for a Course, such as the course title, total duration of lessons in the course, number of lessons in the course'
        );
        parent::__construct(strtolower(class_basename(__CLASS__)), '', $options);
    }

    /**
     * THe Widget Form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        extract($instance);
        $courses = Courses::getInstance();

        if (!isset($title)) $title = $courses->__('Course Information');
        if (!isset($course_title)) $course_title = 0;
        if (!isset($lessons)) $lessons = 0;
        if (!isset($duration)) $duration = 0;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($title)) echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('course_title'); ?>">
                <input type="checkbox"
                       id="<?php echo $this->get_field_id('course_title'); ?>"
                       name="<?php echo $this->get_field_name('course_title'); ?>"
                       value="1"<?php checked(1, $course_title); ?> /> <?php echo $courses->__('Display course title'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('duration'); ?>">
                <input type="checkbox"
                       id="<?php echo $this->get_field_id('duration'); ?>"
                       name="<?php echo $this->get_field_name('duration'); ?>"
                       value="1"<?php checked(1, $duration); ?> /> <?php echo $courses->__('Display lessons duration'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('lessons'); ?>">
                <input type="checkbox"
                       id="<?php echo $this->get_field_id('lessons'); ?>"
                       name="<?php echo $this->get_field_name('lessons'); ?>"
                       value="1"<?php checked(1, $lessons); ?> /> <?php echo $courses->__('Display lessons count'); ?>
            </label>
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

        $course_title = isset($course_title) && (boolean) $course_title === true;
        $lessons = isset($lessons) && (boolean) $lessons === true;
        $duration = isset($duration) && (boolean) $duration === true;

        if (!isset($title)) $title = $courses->__('Course Information');

        $course_id = get_the_ID();
        if ($course_title) $the_course_title = get_the_title();

        $post_type = get_post_type();
        if ($post_type == $courses->getPrefix('lesson'))
        {
            $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;
            if (!$course_id) return;

            if ($course_title)
            {
                $course = get_post($course_id);
                $the_course_title = $course->post_title;
            }
        }

        echo $before_widget;
        if ($title !== false) echo $before_title . $title . $after_title;

        if ($course_title === true)
        {
            if ($post_type == $courses->getPrefix('course')) echo $the_course_title;
            else echo '<a href="' . get_post_permalink($course_id) . '">' . $the_course_title . '</a>';
        }
        if ($duration || $lessons):
        ?>
        <p>
            <?php if ($duration): ?><strong><?php echo $courses->__('Duration'); ?>:</strong> <?php echo $courses->get_duration($course_id); ?><br/><?php endif; ?>
            <?php if ($lessons): $meta = get_post_meta($course_id); ?><strong><?php echo $courses->__('Lessons'); ?>:</strong> <?php echo count($meta[$courses->getPrefix('lesson')]); ?><?php endif; ?>
        </p>
        <?php
        endif;
        echo $after_widget;
    }
}