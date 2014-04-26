<?php
use Mosaicpro\Alert\Alert;
use Mosaicpro\Button\Button;

$post_type = get_post_type();
$button_text = 'Course Forum';
$alert_text = 'Discuss this course in our forum!';
if ($post_type == 'mp_lms_lesson')
{
    $button_text = 'Lesson Forum';
    $alert_text = 'Discuss this lesson in our forum!';
}

echo Alert::make()
    ->addAlert(Button::primary($button_text)->isLink()->pullRight() . $alert_text)
    ->isInfo();