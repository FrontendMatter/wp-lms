<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\PluginGeneric;

/**
 * Class LMS
 * @package Mosaicpro\WP\Plugins\LMS
 */
class LMS extends PluginGeneric
{
    /**
     * Create a new LMS instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->admin_menu();
    }

    /**
     * Create the admin menu
     */
    private function admin_menu()
    {
        add_action('admin_menu', function()
        {
            add_menu_page($this->__('Learning Management System'), $this->__('LMS'), 'edit_posts', $this->prefix, '', admin_url() . 'images/media-button-video.gif', 27);
            add_submenu_page( $this->prefix, $this->__('Quiz Results'), $this->__('Quiz Results'), 'edit_posts', 'quiz_results', [$this, 'admin_page_quiz_results']);
            add_submenu_page( $this->prefix, $this->__('Topics'), $this->__('Topics'), 'edit_posts', 'edit-tags.php?taxonomy=topic');
        });

        add_action('parent_file', function($parent_file)
        {
            global $current_screen;
            $taxonomy = $current_screen->taxonomy;
            if ($taxonomy == 'topic') $parent_file = $this->prefix;
            return $parent_file;
        });
    }
}