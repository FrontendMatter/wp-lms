<?php
use Mosaicpro\WP\Plugins\LMS\Courses;
$courses = Courses::getInstance();
?>

<?php if ( is_active_sidebar( 'single-lesson-sidebar' ) ) : ?>

    <?php dynamic_sidebar( 'single-lesson-sidebar' ); ?>

<?php else : ?>

    <?php the_widget(
        'Mosaicpro\WP\Plugins\LMS\Instructor_Widget',
        ['before_title' => '<h4 class="widgettitle">', 'after_title' => '</h4>']
    ); ?>
    <hr/>

    <?php the_widget(
        'Mosaicpro\WP\Plugins\LMS\Course_Information_Widget',
        ['before_title' => '<h4 class="widgettitle">', 'after_title' => '</h4>']
    ); ?>
    <hr/>

    <?php the_widget(
        'Mosaicpro\WP\Plugins\LMS\Download_Attachments_Widget',
        ['before_title' => '<h4 class="widgettitle">', 'after_title' => '</h4>']
    ); ?>
    <hr/>

<?php endif; ?>