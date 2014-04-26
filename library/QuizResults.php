<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostStatus;
use Mosaicpro\WpCore\PostType;

/**
 * Class QuizResults
 * @package Mosaicpro\WP\Plugins\LMS
 */
class QuizResults extends PluginGeneric
{
    /**
     * Create a new QuizResults instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->initShared();
        $this->initAdmin();
    }

    /**
     * Initialize QuizResults Shared Resources
     */
    private function initShared()
    {
        $this->post_type();
        $this->post_status();
    }

    /**
     * Initialize QuizResults Admin
     * @return bool
     */
    private function initAdmin()
    {
        if (!is_admin()) return false;
    }

    /**
     * Create the QuizResults post type
     */
    private function post_type()
    {
        PostType::make('quiz result', $this->prefix)
            ->setOptions([
                'public' => false,
                'show_ui' => true,
                'supports' => ['title'],
                'capability_type' => 'post',
                'capabilities' => [
                    'create_posts' => false,
                ],
                'map_meta_cap' => true,
                'show_in_menu' => $this->prefix
            ])
            ->register();
    }

    /**
     * Create the quiz results statuses
     */
    private function post_status()
    {
        $statuses = [
            'pending_evaluation',
            'evaluation_complete',
            'passed',
            'failed'
        ];

        foreach ($statuses as $status)
        {
            PostStatus::make($status, $this->prefix)
                ->setPostType($this->getPrefix('quiz_result'))
                ->register();
        }
    }
} 