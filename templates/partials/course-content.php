<?php
use Mosaicpro\Media\Media;
use Mosaicpro\WP\Plugins\LMS\Courses;
$courses = Courses::getInstance();
?>

<h1><?php the_title(); ?></h1>

<?php echo get_the_term_list( get_the_ID(), 'topic', $courses->__('Topics') . ': ', ', ', '' ); ?>

<?php
echo Media::make()
    ->addObjectLeft(get_the_post_thumbnail())
    ->addBody(null, get_the_content());
?>

<hr/>

<?php get_template_part($courses->getPrefix(), 'alert-forum'); ?>

<?php if ( is_active_sidebar( 'single-course-content' ) ) : ?>

    <?php dynamic_sidebar( 'single-course-content' ); ?>

<?php endif; ?>