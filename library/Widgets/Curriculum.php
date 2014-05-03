<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\ListGroup\ListGroup;
use Mosaicpro\WP\Plugins\Quiz\QuizResults;
use Mosaicpro\WpCore\Date;
use WP_Widget;

/**
 * Class Curriculum_Widget
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Curriculum_Widget extends WP_Widget
{
    /**
     * Construct the Widget
     */
    function __construct()
    {
        $options = array(
            'name' => '(LMS) Course Curriculum',
            'description' => 'Display Course Curriculum on Single Course and Lesson pages'
        );
        parent::__construct(strtolower(class_basename(__CLASS__)), '', $options);
    }

    /**
     * The Widget Form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        extract($instance);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($title)) echo esc_attr($title); ?>" />
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

        $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;
        $curriculum_id = get_the_ID();
        $post_type = get_post_type();

        $courses = Courses::getInstance();
        $quizzes = $courses->getQuizzes();

        if ($post_type != $courses->getPrefix('course') && $post_type != $courses->getPrefix('lesson'))
            return;

        if ($post_type == $courses->getPrefix('course'))
        {
            $course_id = get_the_ID();
            $curriculum_id = 0;
        }
        if (!$course_id) return;

        $curriculum = $courses->get_curriculum($course_id);
        if ($curriculum->have_posts())
        {
            $curriculum_list = ListGroup::make();
            while ($curriculum->have_posts())
            {
                $curriculum->the_post();
                if ($quizzes && get_post_type() == $quizzes->getPrefix('quiz'))
                    $curriculum_type = $quizzes->__('Quiz');

                if (get_post_type() == $courses->getPrefix('lesson'))
                    $curriculum_type = $courses->__('Lesson');

                $curriculum_type_label = '<span class="label label-default">' . $curriculum_type . '</span>';
                $active = get_the_ID() == $curriculum_id;
                if ($active) $curriculum_type_label = '<span class="label">' . $curriculum_type . '</span>';

                $curriculum_list->addLink(get_the_permalink() . '?course_id=' . $course_id, get_the_title() . '<br/>' . $curriculum_type_label)
                    ->isActive($active);

                if (get_post_type() == $courses->getPrefix('lesson'))
                {
                    $duration = Date::time_format(implode(":", get_post_meta(get_the_ID(), 'duration', true)));
                    $curriculum_list->setBadge($duration);
                }
                if ($quizzes && get_post_type() == $quizzes->getPrefix('quiz'))
                {
                    $open = QuizResults::getInstance()->get_open_timer(get_the_ID());
                    if ($open)
                    {
                        $duration = Date::time_format(implode(":", $open));
                        $curriculum_list->setBadge($duration . ' remaining');
                    }
                }
            }

            if (empty($title)) $title = $courses->__('Course Curriculum');

            echo $before_widget;
                echo $before_title . $title . $after_title;
                echo $curriculum_list;
            echo $after_widget;

            wp_reset_query();
        }
    }
}