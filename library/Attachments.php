<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WP\Plugins\Attachments\Attachments as MP_Attachments;
use Mosaicpro\WpCore\PluginGeneric;

/**
 * Class Attachments
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Attachments extends PluginGeneric
{
    /**
     * Create a new Attachments instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->initAdmin();
    }

    /**
     * Initialize Admin
     * @return bool
     */
    private function initAdmin()
    {
        if (!is_admin()) return false;

        $post_types = [
            $this->getPrefix('course') => $this->__('Course Attachments'),
            $this->getPrefix('lesson') => $this->__('Lesson Attachments')
        ];

        MP_Attachments::getInstance()->register($post_types);
    }
}