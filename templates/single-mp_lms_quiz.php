<?php
/**
 * The template for displaying a single Quiz
 *
 * You can edit the single quiz template by creating a single-mp_lms_quiz.php template
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

use Mosaicpro\Button\Button;
use Mosaicpro\ButtonGroup\ButtonGroup;
use Mosaicpro\ListGroup\ListGroup;
use Mosaicpro\Panel\Panel;
use Mosaicpro\WP\Plugins\LMS\QuizAnswers;
use Mosaicpro\WP\Plugins\LMS\QuizUnits;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\Taxonomy;

$quiz_id = false;
$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;
if (get_post_type() == 'mp_lms_quiz')
    $quiz_id = get_the_ID();

if (get_post_type() == 'mp_lms_quiz_unit')
    $quiz_id = $_REQUEST['quiz_id'];

$page_params = http_build_query(['quiz_id' => $quiz_id, 'course_id' => $course_id]);

$post_type = get_post_type();

get_header(); ?>

    <div class="row">
        <div class="col-md-9">

            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <?php if ($post_type == 'mp_lms_quiz'): ?>

                <h2>Take Quiz Page Template</h2>
                <p><strong>The quiz introduction page template content goes here.</strong> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus ad amet animi architecto autem cumque, earum eos et eveniet excepturi, inventore ipsa nemo praesentium quisquam quo veniam, vitae. Aliquid, exercitationem!</p>

            <?php elseif ($post_type == 'mp_lms_quiz_unit'): ?>

                <h4><?php the_title(); ?></h4>
                <hr/>

                <?php echo Panel::make('default')
                        ->addTitle('Question')
                        ->addBody(get_the_content()); ?>

                <?php
                $term = Taxonomy::get_term(get_the_ID(), 'quiz_unit_type');
                $meta = get_post_meta(get_the_ID());

                $formbuilder = new FormBuilder();

                $panel = Panel::make('default');
                $panel_body = '';

                if ($term->slug == 'essay')
                    $panel_body .= $formbuilder->get_textarea('response', 'Write your answer', '');

                if ($term->slug == 'true_false')
                    $panel_body .= $formbuilder->get_radio('response', 'Select the correct answer', '', ['True', 'False']);

                if ($term->slug == 'one_word')
                    $panel_body .= $formbuilder->get_input('response', 'Provide the correct answer with a single word', '');

                if ($term->slug == 'multiple_choice')
                    $panel_body .= $formbuilder->get_checkbox_multiple('response[]', 'Select one or more answers', '', $formbuilder::select_values(QuizAnswers::get(get_the_ID()), null));

                $panel->addBody($panel_body);
                echo $panel;

                $next_post = QuizUnits::get_next($quiz_id, get_the_ID());
                $prev_post = QuizUnits::get_prev($quiz_id, get_the_ID());
                $next_post_link = $next_post ? get_post_permalink($next_post) . '?' . $page_params : false;
                $prev_post_link = $prev_post ? get_post_permalink($prev_post) . '?' . $page_params : false;

                $quiz_navigation = ButtonGroup::make();
                if ($prev_post_link)
                    $quiz_navigation->add(Button::regular('Previous Unit')->addUrl($prev_post_link));
                if ($next_post_link)
                    $quiz_navigation->add(Button::regular('Next Unit')->addUrl($next_post_link));

                $buttons_grid = Grid::make();
                $buttons_grid->addColumn(6, Button::success('Save Answer'));
                $buttons_grid->addColumn(6, $quiz_navigation,
                    ['class' => 'text-right']
                );

                echo $buttons_grid;
                ?>

            <?php endif; ?>

            <?php endwhile; endif; ?>

        </div>
        <div class="col-md-3">

            <?php if ($post_type == 'mp_lms_quiz_unit'): ?>

                <?php
                $quiz_timer_enabled = get_post_meta($quiz_id, 'timer_enabled', true);
                $quiz_timer_limit = get_post_meta($quiz_id, 'timer_limit', true);

                if (!empty($quiz_timer_enabled) && !empty($quiz_timer_limit) && $quiz_timer_enabled === 'true'):
                ?>

                <h4>Time Remaining</h4>
                <hr/>

                <div id="quiz-unit-timer"></div>
                <hr/>

                <?php endif; ?>

            <?php endif; ?>

            <h4>Quiz navigation</h4>

            <?php
            if ($post_type == 'mp_lms_quiz' || current_user_can('administrator'))
            {
                $button_introduction = Button::regular('Quiz Introduction')
                    ->addUrl(get_the_permalink($quiz_id) . '?' . $page_params)->isBlock()
                    ->setClass(get_post_type() == 'mp_lms_quiz' ? 'btn-primary' : '');

                $button_course = Button::regular('Back to Course')->addUrl(get_the_permalink($course_id))->isBlock();

                if ($post_type == 'mp_lms_quiz')
                {
                    echo $button_introduction;
                    echo $button_course;
                    echo "<hr/>";
                }
            }
            ?>

            <?php
            $units = QuizUnits::get($quiz_id);
            if ($post_type == 'mp_lms_quiz' && $units->have_posts())
            {
                $first_unit_id = QuizUnits::get_first($quiz_id);
                echo Button::success('Start Quiz')->isBlock()->addUrl(get_the_permalink($first_unit_id) . '?' . $page_params);
            }

            if ($post_type == 'mp_lms_quiz_unit' && $units->have_posts())
            {
                $unit_id = get_the_ID();
                $units_list = ListGroup::make();
                while ($units->have_posts())
                {
                    $units->the_post();
                    $units_list->addLink(get_the_permalink() . '?' . $page_params, get_the_title())->isActive($unit_id == get_the_ID());
                }
                echo $units_list;
            }
            ?>

            <?php
            if ($post_type != 'mp_lms_quiz' && current_user_can('administrator'))
            {
                echo "<hr/><h4>Admin Options</h4>";
                echo "<p>" . $button_introduction . $button_course . "</p>";
                echo "<small>Note: Once the quiz has started, these options are only visible to Instructors and Admins for preview purposes;</small>";
            }
            ?>

        </div>
    </div>
    <hr/>

<?php get_footer(); ?>