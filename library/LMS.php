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

        // i18n
        $this->loadTextDomain();

        // Load Plugin Templates into the current Theme
        $this->plugin->initPluginTemplates();

        // initialize admin resources
        $this->initAdmin();

        // initialize front resources
        $this->initFront();
    }

    /**
     * Initialize LMS Admin
     * @return bool
     */
    private function initAdmin()
    {
        if (!is_admin()) return false;
        $this->admin_menu();
    }

    /**
     * Initialize LMS Front
     * @return bool
     */
    private function initFront()
    {
        if (is_admin()) return false;
        $this->register_front_assets();
    }

    /**
     * Register Assets
     */
    private function register_front_assets()
    {
        add_action('wp_enqueue_scripts', function()
        {
            wp_register_style('owl-carousel', plugin_dir_url(plugin_dir_path(__FILE__)) . 'assets/lib/owl-carousel/owl.carousel.css');
            wp_register_style('owl-carousel-theme', plugin_dir_url(plugin_dir_path(__FILE__)) . 'assets/lib/owl-carousel/owl.theme.css');
            wp_register_script('owl-carousel', plugin_dir_url(plugin_dir_path(__FILE__)) . 'assets/lib/owl-carousel/owl.carousel.js', ['jquery'], false, true);
            wp_register_script('owl-carousel-init', plugin_dir_url(plugin_dir_path(__FILE__)) . 'assets/plugin/owl.carousel.init.js', ['owl-carousel'], false, true);
        });
    }

    /**
     * Create the admin menu
     */
    private function admin_menu()
    {
        add_action('admin_menu', function()
        {
            add_menu_page($this->__('Learning Management System'), $this->__('LMS'), 'edit_posts', $this->prefix, '', 'dashicons-welcome-learn-more', '27.001');
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