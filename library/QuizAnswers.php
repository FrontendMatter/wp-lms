<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Core\IoC;
use Mosaicpro\WpCore\CRUD;
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
        $this->initShared();
        $this->initAdmin();
    }

    /**
     * Initialize QuizAnswers Shared Resources
     */
    private function initShared()
    {
        $this->post_types();
    }

    /**
     * Initialize QuizAnswers Admin
     * @return bool
     */
    private function initAdmin()
    {
        if (!is_admin()) return false;
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
        MetaBox::make($this->prefix, 'quiz_answer_attributes', $this->__('Answer Attributes'))
            ->setPostType($this->getPrefix('quiz_answer'))
            ->setField('correct', $this->__('The answer is correct'), 'checkbox')
            ->register();
    }

    /**
     * Get all Quiz Answers by $quiz_unit_id
     * @param $quiz_unit_id
     * @return array
     */
    public static function get($quiz_unit_id)
    {
        $plugin = IoC::getContainer('plugin');
        $quiz_answers = get_post_meta($quiz_unit_id, $plugin->getPrefix('quiz_answer'));
        $answers = get_posts([
            'post_type' => [$plugin->getPrefix('quiz_answer')],
            'post__in' => $quiz_answers
        ]);

        $order = self::get_order($quiz_unit_id);
        $answers = CRUD::order_sortables($answers, $order);

        return $answers;
    }

    /**
     * Get the order of Quiz Answers associated with $quiz_unit_id
     * @param $quiz_unit_id
     * @return mixed
     */
    public static function get_order($quiz_unit_id)
    {
        $plugin = IoC::getContainer('plugin');
        $order_key = '_order_' . $plugin->getPrefix('quiz_answer');
        $order = get_post_meta($quiz_unit_id, $order_key, true);
        return !empty($order) ? $order : get_post_meta($quiz_unit_id, $plugin->getPrefix('quiz_answer'));
    }
} 