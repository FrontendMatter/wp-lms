<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\PostType;
use Mosaicpro\WpCore\Taxonomy;
use Mosaicpro\WpCore\ThickBox;
use Mosaicpro\WpCore\Utility;

/**
 * Class Quizez
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Quizez extends PluginGeneric
{
    /**
     * Create a new Quizez instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->post_types();
        $this->metaboxes();
        $this->crud();
        $this->admin_post_list();
    }

    /**
     * Create the Quizez post type
     */
    private function post_types()
    {
        PostType::make(['quiz', 'quizez'], $this->prefix)
            ->setOptions(['show_in_menu' => $this->prefix])
            ->register();
    }

    /**
     * Create the Quizez Meta Boxes
     */
    private function metaboxes()
    {
        // Quiz -> Quiz Units Meta Box
        MetaBox::make($this->prefix, 'quiz_unit', $this->__('Quiz Units'))
            ->setPostType('quiz')
            ->setDisplay([
                '<div id="' . $this->prefix . '_quiz_unit_list"></div>',
                ThickBox::register_iframe( 'thickbox_units', $this->__('Add Unit to Quiz'), 'admin-ajax.php',
                    ['action' => 'list_quiz_mp_lms_quiz_unit'] )->render()
            ])
            ->register();

        // Quiz -> Timer Meta Box
        MetaBox::make($this->prefix, 'quiz_timer', $this->__('Quiz Timer'))
            ->setPostType('quiz')
            ->setField('timer_enabled', $this->__('Enable/Disable the Quiz Timer'), ['true' => $this->__('On'), 'false' => $this->__('Off')], 'radio')
            ->setField('timer_limit', $this->__('Set the timer limit (hh:mm:ss)'), 'select_hhmmss')
            ->register();

        // Only show the Quiz Timer Limit if the Quiz Timer is enabled
        Utility::show_hide([
                'when' => '#mp_lms_quiz_timer',
                'attribute' => 'value',
                'is_value' => 'true',
                'show_target' => '#mp_lms_quiz_timer .form-group'
            ],['mp_lms_quiz']
        );
    }

    /**
     * Create Quizez CRUD Relationships
     */
    private function crud()
    {
        // Quizez -> Quiz Units CRUD Relationship
        CRUD::make($this->prefix, 'quiz', 'quiz_unit')
            ->setListFields('mp_lms_quiz_unit', [
                'ID',
                'post_title',
                function($post)
                {
                    $type = Taxonomy::get_term($post->ID, 'quiz_unit_type');
                    $output = '<strong>' . $type->name . '</strong>';
                    if ($type->slug == 'multiple_choice') {
                        $answers = count(get_post_meta($post->ID, 'mp_lms_quiz_answer'));
                        $output .= ' <em>(' . sprintf( $this->__('%1$s Answers'), $answers ) . ')</em>';
                    }
                    return [
                        'field' => $this->__('Quiz Type'),
                        'value' => $output
                    ];
                }
            ])
            ->register();

        CRUD::setPostTypeLabel('mp_lms_quiz', $this->__('Quiz'));
        CRUD::setPostTypeLabel('mp_lms_quiz_unit', $this->__('Quiz Unit'));
    }

    /**
     * Customize the WP Admin post listing for Quizez
     */
    private function admin_post_list()
    {
        // Add Quizez Listing Custom Columns
        PostList::add_columns($this->prefix . '_quiz', [
            ['quiz_units', $this->__('Quiz Units'), 2]
        ]);

        // Display Quizez Listing Custom Columns
        PostList::bind_column($this->prefix . '_quiz', function($column, $post_id)
        {
            if ($column == 'quiz_units')
            {
                $units = get_post_meta($post_id, 'mp_lms_quiz_unit');
                echo count($units) . ' ' . $this->__('Quiz Units');
            }
        });
    }
}