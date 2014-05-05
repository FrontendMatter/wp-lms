<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\Shortcode;

/**
 * Class Course_Information_Shortcode
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Course_Information_Shortcode extends Shortcode
{
    /**
     * Holds a Course_Information_Shortcode instance
     * @var
     */
    protected static $instance;

    /**
     * Add the Shortcode to WP
     */
    public function addShortcode()
    {
        add_shortcode('course_information', function($atts)
        {
            $atts = shortcode_atts(
                array(
                    'title' => false,
                    'course_title' => true,
                    'lessons' => true,
                    'duration' => true
                ), $atts
            );

            ob_start();
            the_widget( __NAMESPACE__ . '\Course_Information_Widget', $atts, ['before_title' => '<h4>', 'after_title' => '</h4>'] );
            $widget = ob_get_clean();

            return $widget;
        });
    }
}