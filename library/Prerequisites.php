<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Core\IoC;
use Mosaicpro\Grid\Grid;
use Mosaicpro\Nav\Nav;
use Mosaicpro\Tab\Tab;
use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostType;
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
        $this->post_types();
        $this->crud();
        $this->metaboxes();
    }

    /**
     * Create the Prerequisites post type
     */
    private function post_types()
    {
        PostType::make('prerequisites', $this->prefix)
            ->setOptions(['show_ui' => false, 'supports' => false])
            ->register();
    }

    /**
     * Create CRUD Relationships
     */
    private function crud()
    {
        $relation = 'mp_lms_prerequisite';

        // Courses -> Prerequisites CRUD Relationship
        CRUD::make($this->prefix, 'course', 'prerequisite')
            ->setForm($relation, function($post) { return $this->getForm($post); })
            ->validateForm($relation, function($instance) { return $this->validateForm($instance); })
            ->setFormFields($relation, [])
            ->setFormButtons($relation, ['save'])
            ->setListFields($relation, $this->getListFields())
            ->register();

        // Lessons -> Prerequisites CRUD Relationship
        CRUD::make($this->prefix, 'lesson', 'prerequisite')
            ->setForm($relation, function($post) { return $this->getForm($post); })
            ->validateForm($relation, function($instance) { return $this->validateForm($instance); })
            ->setFormFields($relation, [])
            ->setFormButtons($relation, ['save'])
            ->setListFields($relation, $this->getListFields())
            ->register();

        CRUD::setPostTypeLabel('mp_lms_prerequisite', 'Prerequisite');
    }

    /**
     * Get the CRUD List Fields
     * @return array
     */
    private function getListFields()
    {
        return [
            'ID',
            function($post)
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
            }
        ];
    }

    /**
     * Create the Meta Boxes
     */
    private function metaboxes()
    {
        // Course -> Prerequisites Meta Box
        MetaBox::make($this->prefix, 'prerequisites', 'Course Prerequisites')
            ->setPostType('course')
            ->setFields(['enforce_prerequisites'])
            ->setDisplay([ $this->getTabs() ])
            ->register();

        // Lesson -> Prerequisites Meta Box
        MetaBox::make($this->prefix, 'prerequisites', 'Lesson Prerequisites')
            ->setPostType('lesson')
            ->setFields(['enforce_prerequisites'])
            ->setDisplay([ $this->getTabs() ])
            ->register();
    }

    /**
     * Get the MetaBox Tabs
     * @return callable
     */
    private function getTabs()
    {
        return function($post)
        {
            $html = IoC::getContainer('html');
            $formbuilder = new FormBuilder;

            $tabs_nav = Nav::make()
                ->isTabs()
                ->addNav($html->link('#prerequisites-list-tab', 'Prerequisites', ['data-toggle' => 'tab']))->isActive()
                ->addNav($html->link('#prerequisites-settings-tab', 'Settings', ['data-toggle' => 'tab']));

            $tabs_pane_list = [
                '<div id="' . $this->prefix . '_prerequisite_list"></div>',
                ThickBox::register_iframe( 'thickbox_quizez', 'Assign Prerequisites', 'admin-ajax.php',
                    ['action' => $this->prefix . '_list_prerequisite'] )->render(),
                ThickBox::register_iframe( 'thickbox_quizez', 'New Prerequisite', 'admin-ajax.php',
                    ['action' => $this->prefix . '_edit_mp_lms_prerequisite'] )
                    ->setButtonAttributes(['class' => 'button thickbox button-primary'])->render()
            ];

            $tab_pane_settings = Grid::make();
            $tab_pane_settings->addColumn(4, $formbuilder->get_radio(
                'enforce_prerequisites',
                'Enforce Prerequisites',
                !empty($post->enforce_prerequisites) ? esc_attr($post->enforce_prerequisites) : 'false',
                ['true' => 'On', 'false' => 'Off']
            ));
            $tab_pane_settings->addColumn(8,
                '<p><strong>If Enforce Prerequisites is set to ON</strong>, the Student isn\'t allowed to take the Course until all the prerequisite Courses and/or Lessons are finalized first;</p>
                <p><strong>NOT</strong> applicable on Prerequisites from external sources.</p>'
            );

            $tabs_panes = Tab::make()
                ->isFade()
                ->addTab('prerequisites-list-tab', implode(PHP_EOL, $tabs_pane_list))->isActive()
                ->addTab('prerequisites-settings-tab', $tab_pane_settings);

            echo implode(PHP_EOL, [$tabs_nav, $tabs_panes]);
        };
    }

    /**
     * Get the CRUD form content
     * @param $post
     */
    private function getForm($post)
    {
        $lessons = FormBuilder::select_values('mp_lms_lesson', '-- Select a lesson --');
        $courses = FormBuilder::select_values('mp_lms_course', '-- Select a course --');
        $prerequisite_types = [ 'url' => 'External URL', 'lesson' => 'Lesson', 'course' => 'Course' ];
        if (empty($post))
        {
            $post = new \stdClass();
            $post->type = 'url';
            foreach ($prerequisite_types as $pt => $pv) $post->$pt = '';
        }
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