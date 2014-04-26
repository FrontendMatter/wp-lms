<?php
/**
 * The template for displaying a single Lesson
 *
 * You can edit the single lesson template by creating a single-mp_lms_course.php template
 * in your theme. You can use this template as a guide or starting point.
 *
 * For a list of available custom functions to use inside this template,
 * please refer to the Developer's Guide or the Documentation
 *
 ***************** NOTICE: *****************
 * Do not make changes to this file. Any changes made to this file
 * will be overwritten if the plugin is updated.
 *
 * To overwrite this template with your own, make a copy of it (with the same name)
 * in your theme directory. WordPress will automatically load the template you create
 * in your theme directory instead of this one.
 *
 * See Theme Integration Guide for more information
 ***************** NOTICE: *****************
 */

use Mosaicpro\Media\Media;

$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;
$post_type = get_post_type();

get_header();
?>

    <div class="row">
        <div class="col-md-9">

            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <h1><?php the_title(); ?></h1>

                <?php echo get_the_term_list( get_the_ID(), 'topic', 'Topics: ', ', ', '' ); ?>

                <?php
                echo Media::make()
                    ->addObjectLeft(get_the_post_thumbnail())
                    ->addBody(null, get_the_content());
                ?>

            <?php endwhile; endif; ?>

            <hr/>

            <?php get_template_part('mp_lms', 'alert-forum'); ?>

            <?php get_template_part('mp_lms', 'curriculum'); ?>

        </div>
        <div class="col-md-3">

            <?php get_template_part('mp_lms', 'about-instructor'); ?>
            <hr/>

            <?php if ($course_id): ?>

                <?php get_template_part('mp_lms', 'course-information'); ?>
                <hr/>

            <?php endif; ?>

            <div class="text-center">
                <p><?php echo Button::success('Download Lesson Files'); ?></p>
                <?php if ($course_id): ?>
                    <p>You can also <a href="">get all the course files</a>.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <hr/>

<?php get_footer(); ?>