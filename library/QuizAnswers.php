<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostType;

/**
 * Class QuizAnswers
 * @package Mosaicpro\WP\Plugins\LMS
 */
class QuizAnswers extends PluginGeneric
{
    /**
     * Create a new QuizAnswers instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->post_types();
        $this->metaboxes();
    }

    /**
     * Create the QuizAnswers post type
     */
    private function post_types()
    {
        PostType::make('quiz answer', $this->prefix)
            ->setOptions(['supports' => ['title'], 'show_in_menu' => $this->prefix])
            ->register();
    }

    /**
     * Create the Meta Boxes
     */
    private function metaboxes()
    {
        // Quiz Answer Attributes
        MetaBox::make($this->prefix, 'quiz_answer_attributes', 'Answer Attributes')
            ->setPostType('quiz_answer')
            ->setField('correct', 'The answer is correct', 'checkbox')
            ->register();
    }
} 