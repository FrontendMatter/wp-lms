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
        if (!isset($display_course_title)) $display_course_title = 0;
        if (!isset($display_course_lessons)) $display_course_lessons = 0;
        if (!isset($display_course_duration)) $display_course_duration = 0;
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($title)) echo esc_attr($title); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('display_course_title'); ?>">
                <input type="checkbox"
                       id="<?php echo $this->get_field_id('display_course_title'); ?>"
                       name="<?php echo $this->get_field_name('display_course_title'); ?>"
                       value="1"<?php checked(1, $display_course_title); ?> /> Display course title
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('display_course_duration'); ?>">
                <input type="checkbox"
                       id="<?php echo $this->get_field_id('display_course_duration'); ?>"
                       name="<?php echo $this->get_field_name('display_course_duration'); ?>"
                       value="1"<?php checked(1, $display_course_duration); ?> /> Display lessons duration
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('display_course_lessons'); ?>">
                <input type="checkbox"
                       id="<?php echo $this->get_field_id('display_course_lessons'); ?>"
                       name="<?php echo $this->get_field_name('display_course_lessons'); ?>"
                       value="1"<?php checked(1, $display_course_lessons); ?> /> Display lessons count
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

        $display_course_title = isset($display_course_title);
        $display_course_lessons = isset($display_course_lessons);
        $display_course_duration = isset($display_course_duration);

        if (!isset($title)) $title = $courses->__('Course Information');

        $course_id = get_the_ID();
        if ($display_course_title) $course_title = get_the_title();

        $post_type = get_post_type();
        if ($post_type == $courses->getPrefix('lesson'))
        {
            $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;
            if (!$course_id) return;

            if ($display_course_title)
            {
                $course = get_post($course_id);
                $course_title = $course->post_title;
            }
        }

        echo $before_widget;
        if ($title !== false) echo $before_title . $title . $after_title;

        if ($display_course_title === true)
        {
            if ($post_type == $courses->getPrefix('course')) echo $course_title;
            else echo '<a href="' . get_post_permalink($course_id) . '">' . $course_title . '</a>';
        }
        if ($display_course_duration === true || $display_course_lessons === true):
        ?>
        <p>
            <?php if ($display_course_duration === true): ?><strong><?php echo $courses->__('Duration'); ?>:</strong> <?php echo $courses->get_duration($course_id); ?><br/><?php endif; ?>
            <?php if ($display_course_lessons === true): $meta = get_post_meta($course_id); ?><strong><?php echo $courses->__('Lessons'); ?>:</strong> <?php echo count($meta[$courses->getPrefix('lesson')]); ?><?php endif; ?>
        </p>
        <?php
        endif;
        echo $after_widget;
    }
}