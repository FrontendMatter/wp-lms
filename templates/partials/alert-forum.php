<?php
use Mosaicpro\HtmlGenerators\Alert\Alert;
use Mosaicpro\HtmlGenerators\Button\Button;
use Mosaicpro\WP\Plugins\LMS\Courses;

$courses = Courses::getInstance();
$post_type = get_post_type();
$button_text = $courses->__('Course Forum');
$alert_text = $courses->__('Discuss this course in our forum!');
if ($post_type == $courses->getPrefix('lesson'))
{
    $button_text = $courses->__('Lesson Forum');
    $alert_text = $courses->__('Discuss this lesson in our forum!');
}

echo Alert::make()
    ->addAlert(Button::primary($button_text)->isLink()->pullRight() . $alert_text)
    ->isInfo();