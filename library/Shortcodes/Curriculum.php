<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\Shortcode;

/**
 * Class Curriculum_Shortcode
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Curriculum_Shortcode extends Shortcode
{
    /**
     * Holds a Curriculum_Shortcode instance
     * @var
     */
    protected static $instance;

    /**
     * Add the Shortcode to WP
     */
    public function addShortcode()
    {
        add_shortcode('curriculum', function($atts)
        {
            $atts = shortcode_atts( [], $atts );

            ob_start();
            the_widget( __NAMESPACE__ . '\Curriculum_Widget', $atts, ['before_title' => '<h4>', 'after_title' => '</h4>'] );
            $widget = ob_get_clean();

            return $widget;
        });
    }
}