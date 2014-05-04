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
        $relation = $this->getPrefix('prerequisite');
        $post_types = [
            $this->getPrefix('course') => $this->__('Course'),
            $this->getPrefix('lesson') => $this->__('Lesson')
        ];

        foreach ($post_types as $post_type => $label)
        {
            // Courses -> Prerequisites CRUD Relationship
            CRUD::make($this->prefix, $post_type, $relation)
                ->setForm($relation, function($post) { return $this->getForm($post); })
                ->validateForm($relation, function($instance) { return $this->validateForm($instance); })
                ->setFormFields($relation, [])
                ->setFormButtons($relation, ['save'])
                ->setListFields($relation, $this->getListFields())
                ->register();

            CRUD::setPostTypeLabel($post_type, $label);
        }

        CRUD::setPostTypeLabel($this->getPrefix('prerequisite'), $this->__('Prerequisite'));
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
                return ['field' => $this->__('Prerequisite'), 'value' => $value];
            }
        ];
    }

    /**
     * Create the Meta Boxes
     */
    private function metaboxes()
    {
        $post_types = [
            $this->getPrefix('course') => $this->__('Course Prerequisites'),
            $this->getPrefix('lesson') => $this->__('Lesson Prerequisites')
        ];

        foreach ($post_types as $post_type => $label)
        {
            // Course -> Prerequisites Meta Box
            MetaBox::make($this->prefix, 'prerequisites', $label)
                ->setPostType($post_type)
                ->setFields(['enforce_prerequisites'])
                ->setDisplay([ $this->getTabs() ])
                ->register();
        }
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
                ->addNav($html->link('#prerequisites-list-tab', $this->__('Prerequisites'), ['data-toggle' => 'tab']))->isActive()
                ->addNav($html->link('#prerequisites-settings-tab', $this->__('Settings'), ['data-toggle' => 'tab']));

            $tabs_pane_list = [
                CRUD::getListContainer([$this->getPrefix('prerequisite')]),
                ThickBox::register_iframe( 'thickbox_quizez', $this->__('Assign Prerequisites'), 'admin-ajax.php',
                    ['action' => 'list_' . $post->post_type . '_' . $this->getPrefix('prerequisite')] )->render(),
                ThickBox::register_iframe( 'thickbox_quizez', $this->__('New Prerequisite'), 'admin-ajax.php',
                    ['action' => 'edit_' . $post->post_type . '_' . $this->getPrefix('prerequisite')] )
                    ->setButtonAttributes(['class' => 'button thickbox button-primary'])->render()
            ];

            $enforce_attributes = [];
            if ($post->post_type == $this->getPrefix('lesson'))
            {
                $courses_with_lesson = CRUD::find_connected_with_post($post, $this->getPrefix('course'));
                if (count($courses_with_lesson) > 0)
                    $enforce_attributes = ['disabled'];
            }

            $tab_pane_settings = Grid::make();
            $tab_pane_settings->addColumn(4, $formbuilder->get_radio(
                'enforce_prerequisites',
                $this->__('Enforce Prerequisites'),
                !empty($post->enforce_prerequisites) ? esc_attr($post->enforce_prerequisites) : 'false',
                ['true' => 'On', 'false' => 'Off'],
                $enforce_attributes
            ));
            $tab_pane_settings->addColumn(8,
                $this->__('<p><strong>If Enforce Prerequisites is set to ON</strong>, the Student isn\'t allowed to take the Course until all the prerequisite Courses and/or Lessons are finalized first;</p>
                <p><strong>NOT</strong> applicable on Prerequisites from external sources.</p>')
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
        $lessons = FormBuilder::select_values($this->getPrefix('lesson'), $this->__('-- Select a lesson --'));
        $courses = FormBuilder::select_values($this->getPrefix('course'), $this->__('-- Select a course --'));
        $prerequisite_types = [ 'url' => $this->__('External URL'), 'lesson' => $this->__('Lesson'), 'course' => $this->__('Course') ];
        if (empty($post))
        {
            $post = new \stdClass();
            $post->type = 'url';
            foreach ($prerequisite_types as $pt => $pv) $post->$pt = '';
        }
        ?>

        <div id="prerequisite_type"><?php FormBuilder::radio('meta[type]', $this->__('Prerequisite type'), esc_attr($post->type), $prerequisite_types); ?></div>
        <div id="prerequisite_url"><?php FormBuilder::input('meta[url]', $this->__('Provide an external prerequisite URL'), esc_attr($post->url)); ?></div>
        <div id="prerequisite_lesson"><?php FormBuilder::select('meta[lesson]', $this->__('Select a Lesson'), $post->lesson, $lessons); ?></div>
        <div id="prerequisite_course"><?php FormBuilder::select('meta[course]', $this->__('Select a Course'), $post->course, $courses); ?></div>

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
            return $instance->setFormValidationError($this->__('Please select a prerequisite type.'));

        $type = $meta['type'];
        if (empty($meta[$type]))
            return $instance->setFormValidationError( sprintf( $this->__('Please provide a %1$s prerequisite or select a different type.'), $type ) );

        return true;
    }
}