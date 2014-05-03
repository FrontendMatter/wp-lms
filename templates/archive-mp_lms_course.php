<?php
use Mosaicpro\Media\Media;
use Mosaicpro\WP\Plugins\LMS\Courses;

$courses = Courses::getInstance();
get_header(); ?>

    <div class="row">

        <div class="col-md-9">

            <h1><?php echo get_post_type() == $courses->getPrefix('course') ? $courses->__('Courses') : $courses->__('Lessons'); ?></h1>
            <hr/>

            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <h1 class="h2"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>

                <p><?php printf( $courses->__( 'Posted <time class="updated" datetime="%1$s" pubdate>%2$s</time> by <span class="author">%3$s</span> under %4$s' ), get_the_time('Y-m-j'), get_the_time(get_option('date_format')), mp_get_the_author_posts_link(), get_the_term_list(get_the_ID(), 'topic', '', ', ', '')); ?></p>

                <?php
                ob_start();
                the_widget(
                    'Mosaicpro\WP\Plugins\LMS\Course_Information_Widget',
                    ['title' => false, 'display_course_lessons' => true, 'display_course_duration' => true]
                );
                $course_information = ob_get_clean();

                $media = Media::make([ 'id' => 'post-' . get_the_ID(), 'class' => implode(" ", get_post_class()) ]);
                $media->addObjectLeft('<a href="' . get_the_permalink() . '">' . get_the_post_thumbnail(get_the_ID(), [120, 120]) . '</a>');
                $media->addBody('',
                    $course_information .
                    get_the_excerpt()
                );
                echo $media;
                ?>
                <hr/>

                <?php endwhile; ?>

                <?php if ( function_exists( 'mp_page_navi' ) ) { ?>
                        <?php mp_page_navi(); ?>
                <?php } else { ?>
                        <nav class="wp-prev-next">
                                <ul class="clearfix">
                                    <li class="prev-link"><?php next_posts_link( $courses->__( '&laquo; Older Entries' )) ?></li>
                                    <li class="next-link"><?php previous_posts_link( $courses->__( 'Newer Entries &raquo;' )) ?></li>
                                </ul>
                        </nav>
                <?php } ?>

            <?php else : ?>

                <article id="post-not-found" class="hentry clearfix">
                    <header class="article-header">
                        <h1><?php echo $courses->__( 'Oops, Post Not Found!' ); ?></h1>
                    </header>
                    <section class="entry-content">
                        <p><?php echo $courses->__( 'Uh Oh. Something is missing. Try double checking things.' ); ?></p>
                    </section>
                    <footer class="article-footer">
                        <p><?php echo $courses->__( 'This is the error message in the archive-mp_lms_course.php template.' ); ?></p>
                    </footer>
                </article>

            <?php endif; ?>

        </div>

        <?php get_sidebar(); ?>

    </div>

<?php get_footer(); ?>