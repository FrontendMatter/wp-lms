<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\Shortcode;

/**
 * Class Instructor_Shortcode
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Instructor_Shortcode extends Shortcode
{
    /**
     * Holds a Instructor_Shortcode instance
     * @var
     */
    protected static $instance;

    /**
     * Add the Shortcode to WP
     */
    public function addShortcode()
    {
        add_shortcode('instructor', function($atts)
        {
            $atts = shortcode_atts( [
                'author_id' => 1,
                'display_author_courses' => true,
                'author_courses_limit' => 3
            ], $atts );

            ob_start();
            the_widget( __NAMESPACE__ . '\Instructor_Widget', $atts, ['before_title' => '<h4>', 'after_title' => '</h4>'] );
            $widget = ob_get_clean();

            return $widget;
        });
    }
}