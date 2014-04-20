<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\PostType;
use Mosaicpro\WpCore\Taxonomy;
use Mosaicpro\WpCore\ThickBox;
use Mosaicpro\WpCore\Utility;

/**
 * Class QuizUnits
 * @package Mosaicpro\WP\Plugins\LMS
 */
class QuizUnits extends PluginGeneric
{
    /**
     * Create a new QuizUnits instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->post_types();
        $this->taxonomies();
        $this->metaboxes();
        $this->crud();
        $this->admin_post_list();
    }

    /**
     * Create the QuizUnits post type
     */
    private function post_types()
    {
        PostType::make('quiz unit', $this->prefix)
            ->setOptions(['supports' => ['title'], 'show_in_menu' => $this->prefix])
            ->register();
    }

    /**
     * Create Taxonomies
     */
    public static function taxonomies()
    {
        Taxonomy::make('quiz unit type')
            ->setType('radio')
            ->setPostType('mp_lms_quiz_unit')
            ->setOption('update_meta_box', [
                'label' => 'Quiz Unit Type',
                'context' => 'normal',
                'priority' => 'high'
            ])
            ->register();
    }

    /**
     * Create Meta Boxes
     */
    private function metaboxes()
    {
        // Quiz Units -> Multiple Choice -> Quiz Answers Meta Box
        MetaBox::make($this->prefix, 'quiz_answer_multiple_choice', 'Multiple Choice Answers')
            ->setPostType('quiz_unit')
            ->setDisplay([
                '<div id="' . $this->prefix . '_quiz_answer_list"></div>',
                ThickBox::register_iframe( 'thickbox_answers', 'Add Answers', 'admin-ajax.php',
                    ['action' => $this->prefix . '_list_quiz_answer'] )->render()
            ])
            ->register();

        // Quiz Units -> True or False -> Correct Answer Meta Box
        MetaBox::make($this->prefix, 'quiz_answer_true_false', 'True of False Answer')
            ->setPostType('quiz_unit')
            ->setField('correct_answer_true_false', 'Select the correct answer', ['true', 'false'], 'radio')
            ->register();

        // Quiz Units -> One Word -> Correct Answer Meta Box
        MetaBox::make($this->prefix, 'quiz_answer_one_word', 'One Word Correct Answer')
            ->setPostType('quiz_unit')
            ->setField('correct_answer_one_word', 'Provide the correct answer', 'input')
            ->setDisplay([
                'fields',
                '<p><strong>Note:</strong> You can provide a list of multiple words or word variations separated with a comma; If you provide a list, then any word from the list will be treated as the correct answer.</p>'
            ])
            ->register();

        $types = ['multiple_choice', 'true_false', 'one_word'];
        foreach($types as $type)
        {
            // Only show the $type Meta Box if the Quiz Unit Type is $type
            Utility::show_hide([
                    'when' => '#quiz_unit_typechecklist',
                    'is_value' => $type,
                    'show_target' => '#mp_lms_quiz_answer_' . $type
                ],['mp_lms_quiz_unit']
            );
        }
    }

    /**
     * Create CRUD Relationships
     */
    private function crud()
    {
        // Quiz Units -> Quiz Answers CRUD Relationship
        CRUD::make($this->prefix, 'quiz_unit', 'quiz_answer')
            ->setListFields('mp_lms_quiz_answer', [
                'ID',
                'crud_edit_post_title',
                'yes_no_correct'
            ])
            ->setForm('mp_lms_quiz_answer', function($post)
            {
                FormBuilder::checkbox('correct', 'The answer is correct', 1, esc_attr($post->correct) == 1);
            })
            ->register();

        CRUD::setPostTypeLabel('mp_lms_quiz_answer', 'Quiz Answer');
    }

    /**
     * Customize the WP Admin post listing
     */
    private function admin_post_list()
    {
        // Add Quiz Units Listing Custom Columns
        PostList::add_columns($this->prefix . '_quiz_unit', [
            ['type', 'Unit Type', 2]
        ]);

        // Display Quiz Units Listing Custom Columns
        PostList::bind_column($this->prefix . '_quiz_unit', function($column, $post_id)
        {
            if ($column == 'type')
            {
                $postterms = get_the_terms( $post_id, 'quiz_unit_type' );
                $current = ($postterms ? array_pop($postterms) : false);
                $output = '<strong>' . $current->name . '</strong>';
                if ($current->slug == 'multiple_choice')
                {
                    $answers = get_post_meta($post_id, 'mp_lms_quiz_answer', true);
                    $output .= ' <em>(' . count($answers) . ' Answers)</em>';
                }
                echo $output;
            }
        });
    }

    /**
     * Method called automatically on plugin activation
     */
    public static function activate()
    {
        self::taxonomies();

        wp_insert_term('Essay','quiz_unit_type', ['slug' => 'essay']);
        wp_insert_term('Multiple choice','quiz_unit_type', ['slug' => 'multiple_choice']);
        wp_insert_term('True or False','quiz_unit_type', ['slug' => 'true_false']);
        wp_insert_term('One Word Answer','quiz_unit_type', ['slug' => 'one_word']);
    }
}