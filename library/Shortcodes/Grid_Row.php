<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Grid\Grid;
use Mosaicpro\WpCore\Shortcode;

/**
 * Class Grid_Row_Shortcode
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Grid_Row_Shortcode extends Shortcode
{
    /**
     * Holds a Grid_Row_Shortcode instance
     * @var
     */
    protected static $instance;

    /**
     * Add the Shortcode to WP
     */
    public function addShortcode()
    {
        add_shortcode('row', function($atts, $content)
        {
            $atts = shortcode_atts( [], $atts );
            return Grid::make()->addAttributes($atts)->add(do_shortcode($content));
        });
    }
}