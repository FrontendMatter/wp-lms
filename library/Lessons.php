<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\Date;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;

/**
 * Class Lessons
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Lessons extends PluginGeneric
{
    /**
     * Create a new Lessons instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->metaboxes();
        $this->admin_post_list();
    }

    /**
     * Create the Meta Boxes
     */
    private function metaboxes()
    {
        // Lessons -> Attributes Meta Box
        MetaBox::make($this->prefix, 'lesson_attributes', 'Lesson Attributes')
            ->setPostType('lesson')
            ->setContext('side')
            ->setField('duration', 'Duration (hh:mm:ss)', 'select_hhmmss')
            ->register();
    }

    /**
     * Customize the WP Admin post list
     */
    private function admin_post_list()
    {
        // Add Lessons Listing Custom Columns
        PostList::add_columns($this->prefix . '_lesson', [
            ['thumbnail', 'Thumbnail', 1],
            ['duration_format', 'Duration', 3],
            ['author', 'Instructor', 4]
        ]);

        // Display Lessons Listing Custom Columns
        PostList::bind_column($this->prefix . '_lesson', function($column, $post_id)
        {
            if ($column == 'duration_format')
            {
                $duration = Date::time_to_seconds(implode(":", get_post_meta($post_id, 'duration', true)));
                echo Date::seconds_to_time($duration);
            }
        });
    }
}