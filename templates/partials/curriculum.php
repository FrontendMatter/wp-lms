<?php
use Mosaicpro\ListGroup\ListGroup;
use Mosaicpro\WP\Plugins\LMS\Courses;
use Mosaicpro\WpCore\Date;

$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;
$curriculum_id = get_the_ID();
$post_type = get_post_type();

if ($post_type == 'mp_lms_course')
{
    $course_id = get_the_ID();
    $curriculum_id = 0;
}
if (!$course_id) return;
?>

<h4>Course Curriculum</h4>

<?php
$curriculum = Courses::get_curriculum($course_id);
if ($curriculum->have_posts())
{
    $curriculum_list = ListGroup::make();
    while ($curriculum->have_posts())
    {
        $curriculum->the_post();
        if (get_post_type() == 'mp_lms_quiz')
            $curriculum_type = 'Quiz';

        if (get_post_type() == 'mp_lms_lesson')
            $curriculum_type = 'Lesson';

        $curriculum_type_label = '<span class="label label-default">' . $curriculum_type . '</span>';
        $active = get_the_ID() == $curriculum_id;
        if ($active) $curriculum_type_label = '<span class="label">' . $curriculum_type . '</span>';

        $curriculum_list->addLink(get_the_permalink() . '?course_id=' . $course_id, get_the_title() . '<br/>' . $curriculum_type_label)
            ->isActive($active);

        if (get_post_type() == 'mp_lms_lesson')
        {
            $duration = Date::time_format(implode(":", get_post_meta(get_the_ID(), 'duration', true)));
            $curriculum_list->setBadge($duration);
        }
    }
    echo $curriculum_list;
    wp_reset_query();
}