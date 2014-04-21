<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\Date;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\PostType;

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
        $this->post_types();
        $this->metaboxes();
        $this->admin_post_list();
    }

    /**
     * Create the post types
     */
    private function post_types()
    {
        PostType::make('lesson', $this->prefix)
            ->setOptions([
                'supports' => ['title', 'excerpt', 'editor', 'thumbnail'],
                'show_in_menu' => $this->prefix
            ])
            ->register();
    }

    /**
     * Create the Meta Boxes
     */
    private function metaboxes()
    {
        // Lessons -> Attributes Meta Box
        MetaBox::make($this->prefix, 'lesson_attributes', $this->__('Lesson Attributes'))
            ->setPostType('lesson')
            ->setContext('side')
            ->setField('duration', $this->__('Duration (hh:mm:ss)'), 'select_hhmmss')
            ->register();
    }

    /**
     * Customize the WP Admin post list
     */
    private function admin_post_list()
    {
        // Add Lessons Listing Custom Columns
        PostList::add_columns($this->prefix . '_lesson', [
            ['thumbnail', $this->__('Thumbnail'), 1],
            ['duration_format', $this->__('Duration'), 3],
            ['author', $this->__('Instructor'), 4]
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