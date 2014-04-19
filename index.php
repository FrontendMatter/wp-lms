<?php namespace Mosaicpro\WP\Plugins\LMS;

/*
Plugin Name: LMS
Plugin URI: http://mosaicpro.biz
Description: Learning Management System with Courses, Quizes, WooCommerce, BuddyPress and bbPress integrations.
Version: 1.0
Author: MosaicPro
Author URI: http://mosaicpro.biz
*/

use Mosaicpro\Core\IoC;
use Mosaicpro\WpCore\Plugin;

// Initialize the plugin
add_action('init', function()
{
    $libraries = [
        'LMS',
        'Courses',
        'Lessons',
        'Prerequisites',
        'Quizez',
        'QuizUnits',
        'QuizAnswers',
        'Settings'
    ];

    // Get the Container from IoC
    $app = IoC::getContainer();

    // Bind the Plugin to the Container
    $app->bindShared('plugin', function()
    {
        return new Plugin('mp_lms');
    });

    // Load & Initialize libraries
    foreach ($libraries as $library)
    {
        require_once 'library/' . $library . '.php';
        forward_static_call_array([__NAMESPACE__ . '\\' . $library, 'init'], []);
    }
},
100);

// Plugin activation
register_activation_hook(__FILE__, [__NAMESPACE__ . '\LMS', 'activate']);