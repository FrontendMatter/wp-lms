<?php
use Mosaicpro\WP\Plugins\LMS\Courses;
$courses = Courses::getInstance();

get_header();
if (have_posts()) : while (have_posts()) : the_post();
?>

    <?php get_template_part($courses->getPrefix(), 'course-content'); ?>

<?php endwhile; endif; ?>
<?php get_footer(); ?>