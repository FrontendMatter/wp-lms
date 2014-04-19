<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;

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
        $this->metaboxes();
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