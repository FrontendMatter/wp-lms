<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\ThickBox;
use Mosaicpro\WpCore\Utility;

/**
 * Class Prerequisites
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Prerequisites extends PluginGeneric
{
    /**
     * Create a new Prerequisites instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->crud();
        $this->metaboxes();
    }

    /**
     * Create CRUD Relationships
     */
    private function crud()
    {
        // Courses -> Prerequisites CRUD Relationship
        CRUD::make($this->prefix, 'course', 'prerequisite')
            ->setForm('mp_lms_prerequisite', function($post) { return $this->getForm($post); })
            ->validateForm('mp_lms_prerequisite', function($instance) { return $this->validateForm($instance); })
            ->setFormFields('mp_lms_prerequisite', [])
            ->setFormButtons('mp_lms_prerequisite', ['save'])
            ->setListFields('mp_lms_prerequisite', [ 'ID', function($post)
            {
                $value = '';
                if ($post->type == 'url') $value .= $post->url;
                if ($post->type == 'lesson') {
                    $lesson = get_post($post->lesson);
                    $value .= $lesson->post_title;
                }
                if ($post->type == 'course') {
                    $course = get_post($post->course);
                    $value .= $course->post_title;
                }
                $value .= ' <em>(' . $post->type . ')</em>';
                return ['field' => 'Prerequisite', 'value' => $value];
            } ])
            ->setPostTypeOptions('mp_lms_prerequisite', [
                'args' => [
                    'show_ui' => false,
                    'supports' => false
                ]
            ])
            ->register();

        CRUD::setPostTypeLabel('mp_lms_prerequisite', 'Prerequisite');
    }

    /**
     * Create the Meta Boxes
     */
    private function metaboxes()
    {
        // Course -> Prerequisites Meta Box
        MetaBox::make($this->prefix, 'prerequisites', 'Course Prerequisites')
            ->setPostType('course')
            ->setDisplay([
                '<div id="' . $this->prefix . '_prerequisite_list"></div>',
                ThickBox::register_iframe( 'thickbox_quizez', 'Assign Prerequisites', 'admin-ajax.php',
                    ['action' => $this->prefix . '_list_prerequisite'] )->render(),
                ThickBox::register_iframe( 'thickbox_quizez', 'New Prerequisite', 'admin-ajax.php',
                    ['action' => $this->prefix . '_edit_mp_lms_prerequisite'] )
                    ->setButtonAttributes(['class' => 'button thickbox button-primary'])->render()
            ])
            ->register();
    }

    /**
     * Get the CRUD form content
     * @param $post
     */
    private function getForm($post)
    {
        if (empty($post)) $post = new \stdClass();
        $lessons = FormBuilder::select_values('mp_lms_lesson', '-- Select a lesson --');
        $courses = FormBuilder::select_values('mp_lms_course', '-- Select a course --');
        $prerequisite_types = [ 'url' => 'External URL', 'lesson' => 'Lesson', 'course' => 'Course' ];
        ?>

        <div id="prerequisite_type"><?php FormBuilder::radio('meta[type]', 'Prerequisite type', esc_attr($post->type), $prerequisite_types); ?></div>
        <div id="prerequisite_url"><?php FormBuilder::input('meta[url]', 'Provide an external prerequisite URL', esc_attr($post->url)); ?></div>
        <div id="prerequisite_lesson"><?php FormBuilder::select('meta[lesson]', 'Select a Lesson', $post->lesson, $lessons); ?></div>
        <div id="prerequisite_course"><?php FormBuilder::select('meta[course]', 'Select a Course', $post->course, $courses); ?></div>

        <?php
        foreach ($prerequisite_types as $type => $label)
        {
            // Only show the $type field if the Prerequisite type is $type
            Utility::enqueue_show_hide([
                'when' => '#prerequisite_type',
                'attribute' => 'value',
                'is_value' => $type,
                'show_target' => '#prerequisite_' . $type
            ]);
        }
    }

    /**
     * Validate the CRUD form data
     * @param $instance
     * @return bool
     */
    private function validateForm($instance)
    {
        $meta = $_POST['meta'];
        if (!$meta)
            return $instance->setFormValidationError();

        if (empty($meta['type']))
            return $instance->setFormValidationError('Please select a prerequisite type.');

        $type = $meta['type'];
        if (empty($meta[$type]))
            return $instance->setFormValidationError('Please provide a ' . $type . ' prerequisite or select a different type.');

        return true;
    }
}