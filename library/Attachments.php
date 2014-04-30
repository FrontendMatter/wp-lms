<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Core\IoC;
use Mosaicpro\WpCore\PluginGeneric;

/**
 * Class Attachments
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Attachments extends PluginGeneric
{
    /**
     * Holds an Attachments instance
     * @var
     */
    protected static $instance;

    /**
     * Holds the Attachments dependency
     * @var bool|mixed
     */
    protected $attachments;

    /**
     * Create a new Attachments instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->attachments = is_plugin_active('mp-attachments/index.php') ? IoC::getContainer('attachments') : false;
    }

    /**
     * Get an Attachments Singleton instance
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
     * Initialize the Attachments plugin
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

        $attachments_post_types = [
            $this->getPrefix('course') => $this->__('Course Attachments'),
            $this->getPrefix('lesson') => $this->__('Lesson Attachments')
        ];

        if ($this->attachments)
            $this->attachments->register($attachments_post_types);
    }
}