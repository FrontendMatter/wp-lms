<?php
use Mosaicpro\HtmlGenerators\Alert\Alert;
use Mosaicpro\WP\Plugins\LMS\Courses;
$courses = Courses::getInstance();
?>

<?php if ( is_active_sidebar( 'single-course-sidebar' ) ) : ?>

    <?php dynamic_sidebar( 'single-course-sidebar' ); ?>

<?php else : ?>

    <?php Alert::make()
        ->addAlert($courses->__('No widgets added to this sidebar yet. You can do so from the WP Admin Appearance menu, Widgets section or from the Theme Customizer.'))
        ->isInfo();
    ?>

<?php endif; ?>