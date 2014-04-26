<?php
use Mosaicpro\WP\Plugins\LMS\Courses;

$course_id = get_the_ID();
$course_title = get_the_title();

$post_type = get_post_type();
if ($post_type == 'mp_lms_lesson')
{
    $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;
    $course = get_post($course_id);
    $course_title = $course->post_title;
}

$meta = get_post_meta($course_id); ?>

<h4>Course Information</h4>
<?php if ($post_type == 'mp_lms_course'): ?>
    <?php echo $course_title; ?>
<?php else: ?>
    <a href="<?php echo get_post_permalink($course_id); ?>"><?php echo $course_title; ?></a>
<?php endif; ?>
<p>
    <strong>Duration:</strong> <?php echo Courses::get_duration($course_id); ?><br/>
    <strong>Lessons:</strong> <?php echo count($meta['mp_lms_lesson']); ?>
</p>