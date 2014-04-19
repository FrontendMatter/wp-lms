<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\ThickBox;
use Mosaicpro\WpCore\Utility;

class QuizUnits extends PluginGeneric
{
    public function __construct()
    {
        parent::__construct();
        $this->metaboxes();
        $this->crud();
        $this->admin_post_list();
    }

    private function metaboxes()
    {
        // Quiz Units -> Multiple Choice -> Quiz Answers Meta Box
        MetaBox::make($this->prefix, 'quiz_answer', 'Multiple Choice Answers')
            ->setPostType('quiz_unit')
            ->setDisplay([
                '<div id="' . $this->prefix . '_quiz_answer_list"></div>',
                ThickBox::register_iframe( 'thickbox_answers', 'Add Answers', 'admin-ajax.php',
                    ['action' => $this->prefix . '_list_quiz_answer'] )->render()
            ])
            ->register();

        // Quiz Units -> True or False -> Correct Answer Meta Box
        MetaBox::make($this->prefix, 'correct_answer_true_false', 'True of False Answer')
            ->setPostType('quiz_unit')
            ->setField('correct_answer_true_false', 'Select the correct answer', ['true', 'false'], 'radio')
            ->register();

        // Quiz Units -> One Word -> Correct Answer Meta Box
        MetaBox::make($this->prefix, 'correct_answer_one_word', 'One Word Correct Answer')
            ->setPostType('quiz_unit')
            ->setField('correct_answer_one_word', 'Provide the correct answer', 'input')
            ->setDisplay([
                'fields',
                '<p><strong>Note:</strong> You can provide a list of multiple words or word variations separated with a comma; If you provide a list, then any word from the list will be treated as the correct answer.</p>'
            ])
            ->register();

        // Only show the Multiple Choice Quiz Answers Meta Box if the Quiz Unit Type is multiple_choice
        Utility::show_hide([
                'when' => '#quiz_unit_typechecklist',
                'is_value' => 'multiple_choice',
                'show_target' => '#mp_lms_quiz_answer'
            ],['mp_lms_quiz_unit']
        );

        // Only show the True or False Correct Answer Meta Box if the Quiz Unit Type is true_false
        Utility::show_hide([
                'when' => '#quiz_unit_typechecklist',
                'is_value' => 'true_false',
                'show_target' => '#mp_lms_correct_answer_true_false'
            ],['mp_lms_quiz_unit']
        );

        // Only show the One Word Correct Answer Meta Box if the Quiz Unit Type is one_word
        Utility::show_hide([
                'when' => '#quiz_unit_typechecklist',
                'is_value' => 'one_word',
                'show_target' => '#mp_lms_correct_answer_one_word'
            ],['mp_lms_quiz_unit']
        );
    }

    private function crud()
    {
        // Quiz Units -> Quiz Answers CRUD Relationship
        CRUD::make($this->prefix, 'quiz_unit', 'quiz_answer')
            ->setPostTypeOptions('mp_lms_quiz_answer', [
                'name' => 'quiz answer',
                'args' => [
                    'supports' => ['title'],
                    'show_in_menu' => $this->prefix
                ]
            ])
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
}