<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\PluginGeneric;

/**
 * Class Settings
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Settings extends PluginGeneric
{
    /**
     * Create a new Settings instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->initSettings();
    }

    /**
     * Add actions for initializing the Settings menus, sections and fields
     */
    private function initSettings()
    {
        add_action('admin_menu', [__CLASS__, 'menus']);
        add_action('admin_init', [__CLASS__, 'sections']);
        add_action('admin_init', [__CLASS__, 'fields']);
    }

    /**
     * Initialize menu callback
     */
    public static function menus()
    {
        add_menu_page(
            'LMS Settings',
            'LMS Settings',
            'manage_options',
            'mp-lms-settings',
            function() { return self::getPage(); }
        );

        add_submenu_page(
            'mp-lms-settings',
            'Header Options',
            'Header Options',
            'manage_options',
            'mp-lms-header-options',
            function() { return self::getPage(); }
        );

        add_submenu_page(
            'mp-lms-settings',
            'Footer Options',
            'Footer Options',
            'manage_options',
            'mp-lms-footer-options',
            function() { return self::getPage(); }
        );

    }

    /**
     * Initialize sections callback
     */
    public static function sections()
    {
        add_settings_section(
            'header_section',
            'Header Options',
            function() { echo "These options are designed to help you control what's displayed in your header."; },
            'mp-lms-header-options'
        );

        add_settings_section(
            'footer_section',
            'Footer Options',
            function() { echo "These options are designed to help you control what's displayed in your footer."; },
            'mp-lms-footer-options'
        );

    }

    /**
     * Initialize fields callback
     */
    public static function fields()
    {
        add_settings_field(
            'header_options',
            'The header options',
            function()
            {
                $options = (array) get_option('header_options');
                ?>
                <input type="text" name="header_options[message]" id="header_options_message" value="<?php echo $options['message']; ?>" />
                <?php
            },
            'mp-lms-header-options',
            'header_section'
        );

        add_settings_field(
            'footer_options',
            'The footer options',
            function()
            {
                $options = (array) get_option('footer_options');
                ?>
                <input type="text" name="footer_options[message]" id="footer_options_message" value="<?php echo $options['message']; ?>" />
                <?php
            },
            'mp-lms-footer-options',
            'footer_section'
        );

        register_setting(
            'header_section',
            'header_options'
        );

        register_setting(
            'footer_section',
            'footer_options'
        );
    }

    /**
     * Get the settings page content
     */
    public static function getPage()
    {
        ?>
        <div class="wrap">
            <h2>LMS Settings</h2>
            <?php settings_errors(); ?>

            <?php
            $active_tab = 'mp-lms-header-options';
            if (isset($_GET['page']) && $_GET['page'] !== 'mp-lms-settings') {
                $active_tab = $_GET['page'];
            }
            ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=mp-lms-header-options" class="nav-tab <?php echo 'mp-lms-header-options' == $active_tab || 'mp-lms-settings' == $active_tab ? 'nav-tab-active' : '' ?>">Header Options</a>
                <a href="?page=mp-lms-footer-options" class="nav-tab <?php echo 'mp-lms-footer-options' == $active_tab ? 'nav-tab-active' : '' ?>">Footer Options</a>
            </h2>

            <form action="options.php" method="post">
                <?php

                if ( 'mp-lms-header-options' == $active_tab )
                {
                    // Render the settings for the header_section
                    settings_fields('header_section');

                    // Renders all of the settings for sections
                    do_settings_sections('mp-lms-header-options');
                }

                if ( 'mp-lms-footer-options' == $active_tab )
                {
                    // Render the settings for the header_section
                    settings_fields('footer_section');

                    // Renders all of the settings for sections
                    do_settings_sections('mp-lms-footer-options');
                }

                // Add the submit button
                submit_button();

                ?>
            </form>
        </div>
        <?php
    }
} 