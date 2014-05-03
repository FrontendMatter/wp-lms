<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Button\Button;
use Mosaicpro\WP\Plugins\Attachments\Attachments;
use WP_Widget;

/**
 * Class Download_Attachments_Widget
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Download_Attachments_Widget extends WP_Widget
{
    /**
     * Construct the Widget
     */
    function __construct()
    {
        $options = array(
            'name' => '(LMS) Download Attachments',
            'description' => 'Display Download Buttons for Attachments.'
        );
        parent::__construct(strtolower(class_basename(__CLASS__)), '', $options);
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
        $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;

        echo $before_widget;

        if (!empty($title))
            echo $before_title . $title . $after_title;

        ?>
        <div class="text-center">
            <?php
            $mp_attachments = Attachments::getInstance();

            if (get_post_type() == $courses->getPrefix('course'))
            {
                $attachments = $mp_attachments->get_post_attachments(get_the_ID());
                foreach ($attachments as $attachment)
                    echo Button::success($courses->__('Download') . ' ' . $attachment->post_title)
                        ->addUrl($mp_attachments->download_attachment_url($attachment->ID));
            }
            if (get_post_type() == $courses->getPrefix('lesson'))
            {
                ?>
                <p><?php echo Button::success($courses->__('Download Lesson Files')); ?></p>
                <?php if ($course_id): ?>
                    <p>You can also <a href="">get all the course files</a>.</p>
                <?php endif;
            }
            ?>

        </div>

        <?php
        echo $after_widget;
    }
}