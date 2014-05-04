<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\CRUD;
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
     * Holds a Lessons instance
     * @var
     */
    protected static $instance;

    /**
     * Create a Lessons Singleton instance
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Initialize the Lessons Plugin
     */
    public static function init()
    {
        $instance = self::getInstance();

        // add lessons custom templates
        $instance->initTemplates();

        // initialize lessons sidebars
        $instance->initSidebars();

        // initialize lessons admin
        $instance->initAdmin();

        // initialize lessons shared resources
        $instance->initShared();
    }

    /**
     * Activate Lessons plugin
     */
    public static function activate()
    {
        $instance = self::getInstance();
        $instance->post_types();
        flush_rewrite_rules();
    }

    /**
     * Initialize Lessons Admin
     * @return bool
     */
    private function initAdmin()
    {
        if (!is_admin()) return false;
        $this->metaboxes();
        $this->admin_post_list();
    }

    /**
     * Initialize Lessons Shared resources
     */
    private function initShared()
    {
        $this->post_types();
    }

    /**
     * Initialize Lessons custom page templates
     */
    private function initTemplates()
    {
        // Set Custom page templates
        $this->plugin->setPageTemplates([
            'single-' . $this->getPrefix('lesson') . '-full_width.php' => $this->__('Single Lesson Full Width'),
            'single-' . $this->getPrefix('lesson') . '-sidebar_left.php' => $this->__('Single Lesson Left Sidebar'),
            'single-' . $this->getPrefix('lesson') . '-sidebar_right.php' => $this->__('Single Lesson Right Sidebar')
        ]);

        // Display Custom page templates in WP Admin Post screen
        $this->plugin->initPostTemplates($this->getPrefix('lesson'));
    }

    /**
     * Initialize Lessons sidebars
     */
    private function initSidebars()
    {
        add_action('widgets_init', function()
        {
            register_sidebar([
                'name'          => $this->__( 'Single Lesson Sidebar' ),
                'id'            => 'single-lesson-sidebar',
                'description'   => $this->__('Sidebar or Widget Area used on the single Lesson pages'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div><hr/>',
                'before_title'  => '<h4 class="widgettitle">',
                'after_title'   => '</h4>',
            ]);

            register_sidebar([
                'name'          => $this->__( 'Single Lesson Content' ),
                'id'            => 'single-lesson-content',
                'description'   => $this->__('Widget Area used on the single Lesson page for displaying widgets within the context of the lesson content.'),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div><hr/>',
                'before_title'  => '<h4 class="widgettitle">',
                'after_title'   => '</h4>',
            ]);
        });
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
            ->setPostType($this->getPrefix('lesson'))
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
        PostList::add_columns($this->getPrefix('lesson'), [
            ['thumbnail', $this->__('Thumbnail'), 1],
            ['duration_format', $this->__('Duration'), 3],
            ['author', $this->__('Instructor'), 4]
        ]);

        // Display Lessons Listing Custom Columns
        PostList::bind_column($this->getPrefix('lesson'), function($column, $post_id)
        {
            if ($column == 'duration_format')
            {
                $duration = Date::time_to_seconds(implode(":", get_post_meta($post_id, 'duration', true)));
                echo Date::seconds_to_time($duration);
            }
        });
    }
}