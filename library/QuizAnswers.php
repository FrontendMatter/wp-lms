<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;

class QuizAnswers extends PluginGeneric
{
    public function __construct()
    {
        parent::__construct();
        $this->metaboxes();
    }

    private function metaboxes()
    {
        // Quiz Answer Attributes
        MetaBox::make($this->prefix, 'quiz_answer_attributes', 'Answer Attributes')
            ->setPostType('quiz_answer')
            ->setField('correct', 'The answer is correct', 'checkbox')
            ->register();
    }
} 