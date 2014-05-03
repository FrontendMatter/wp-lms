<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Core\IoC;
use Mosaicpro\WpCore\PluginGeneric;

/**
 * Class Reviews
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Reviews extends PluginGeneric
{
    /**
     * Holds an Attachments instance
     * @var
     */
    protected static $instance;

    /**
     * Holds the Reviews dependency
     * @var bool|mixed
     */
    protected $reviews;

    /**
     * Create a new Reviews instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->reviews = is_plugin_active('mp-reviews/index.php') ? IoC::getContainer('reviews') : false;
    }

    /**
     * Get a Reviews Singleton instance
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
     * Initialize the Reviews plugin
     */
    public static function init()
    {
        $instance = self::getInstance();
        $instance->initAdmin();
    }

    /**
     * Initialize Admin
     * @return bool
     */
    private function initAdmin()
    {
        if (!is_admin()) return false;

        $reviews_post_types = [
            $this->getPrefix('course') => $this->__('Course Reviews'),
            $this->getPrefix('lesson') => $this->__('Lesson Reviews')
        ];

        if ($this->reviews)
            $this->reviews->register($reviews_post_types);
    }
}