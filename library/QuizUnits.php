<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Core\IoC;
use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\PostType;
use Mosaicpro\WpCore\Taxonomy;
use Mosaicpro\WpCore\ThickBox;
use Mosaicpro\WpCore\Utility;
use WP_Query;

/**
 * Class QuizUnits
 * @package Mosaicpro\WP\Plugins\LMS
 */
class QuizUnits extends PluginGeneric
{
    /**
     * Create a new QuizUnits instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->initShared();
        $this->initFront();
        $this->initAdmin();
    }

    /**
     * Initialize QuizUnits Shared Resources
     */
    private function initShared()
    {
        $this->post_types();
        $this->taxonomies();
    }

    /**
     * Initialize QuizUnits Admin Resources
     * @return bool
     */
    private function initAdmin()
    {
        if (!is_admin()) return false;

        $this->metaboxes();
        $this->crud();
        $this->admin_post_list();
    }

    /**
     * Initialize QuizUnits Front Resources
     */
    private function initFront()
    {
        add_action('wp_enqueue_scripts', function()
        {
            if (!(is_single() && get_post_type() == $this->getPrefix('quiz_unit'))) return false;

            $quiz_id = isset($_REQUEST['quiz_id']) ? $_REQUEST['quiz_id'] : false;
            if (!$quiz_id) return false;

            $quiz_timer_enabled = get_post_meta($quiz_id, 'timer_enabled', true);
            $quiz_timer_limit = get_post_meta($quiz_id, 'timer_limit', true);

            if (empty($quiz_timer_enabled) || empty($quiz_timer_limit) || $quiz_timer_enabled !== 'true') return false;

            wp_enqueue_script( 'jquery-countdown', plugin_dir_url( $this->plugin->getPluginFile() ) . 'assets/jquery-countdown/jquery.countdown.js', ['jquery'], null, true );
            wp_enqueue_style( 'jquery-countdown', plugin_dir_url( $this->plugin->getPluginFile() ) . 'assets/jquery-countdown/jquery.countdown.css', null, null );
            wp_enqueue_script( 'mp-lms-quiz-unit', plugin_dir_url( $this->plugin->getPluginFile() ) . 'assets/quiz-unit/timer.js', ['jquery', 'jquery-countdown'], null, true );
            wp_enqueue_style( 'mp-lms-quiz-unit', plugin_dir_url( $this->plugin->getPluginFile() ) . 'assets/quiz-unit/timer.css', null, null );
            wp_localize_script('mp-lms-quiz-unit', 'quiz_unit_timer', [
                'duration' => $quiz_timer_limit,
                'quiz_id' => $quiz_id
            ]);
        });
    }

    /**
     * Create the QuizUnits post type
     */
    private function post_types()
    {
        PostType::make('quiz unit', $this->prefix)
            ->setOptions(['supports' => ['title'], 'show_in_menu' => $this->prefix])
            ->register();
    }

    /**
     * Create Taxonomies
     */
    public static function taxonomies()
    {
        $plugin = IoC::getContainer('plugin');
        Taxonomy::make('quiz unit type')
            ->setType('radio')
            ->setPostType($plugin->getPrefix('quiz_unit'))
            ->setOption('update_meta_box', [
                'label' => $plugin->__('Quiz Unit Type'),
                'context' => 'normal',
                'priority' => 'high'
            ])
            ->register();
    }

    /**
     * Create Meta Boxes
     */
    private function metaboxes()
    {
        // Unit Question Editor
        add_action('edit_form_after_title', function($post)
        {
            if ($post->post_type !== $this->getPrefix('quiz_unit')) return;
            echo '<h2>' . $this->__('Unit Question') . '</h2>';
            wp_editor($post->post_content, 'editpost', ['textarea_name' => 'post_content', 'textarea_rows' => 5]);
            echo "<br/>";
        });

        // Quiz Units -> Multiple Choice -> Quiz Answers Meta Box
        MetaBox::make($this->prefix, 'quiz_answer_multiple_choice', $this->__('Multiple Choice Answers'))
            ->setPostType($this->getPrefix('quiz_unit'))
            ->setDisplay([
                CRUD::getListContainer([$this->getPrefix('quiz_answer')]),
                ThickBox::register_iframe( 'thickbox_answers', $this->__('Add Answers'), 'admin-ajax.php',
                    ['action' => 'list_' . $this->getPrefix('quiz_unit') . '_' . $this->getPrefix('quiz_answer')] )->render()
            ])
            ->register();

        // Quiz Units -> True or False -> Correct Answer Meta Box
        MetaBox::make($this->prefix, 'quiz_answer_true_false', $this->__('True of False Answer'))
            ->setPostType($this->getPrefix('quiz_unit'))
            ->setField('correct_answer_true_false', $this->__('Select the correct answer'), [$this->__('True'), $this->__('False')], 'radio')
            ->register();

        // Quiz Units -> One Word -> Correct Answer Meta Box
        MetaBox::make($this->prefix, 'quiz_answer_one_word', $this->__('One Word Correct Answer'))
            ->setPostType($this->getPrefix('quiz_unit'))
            ->setField('correct_answer_one_word', $this->__('Provide the correct answer'), 'input')
            ->setDisplay([
                'fields',
                $this->__('<p><strong>Note:</strong> This is only required if the Quiz Unit should be auto-evaluated by the system. On manual evaluation by an Instructor, you can skip this option. You can also provide a list of multiple words or word variations separated with a comma; If you provide a list, then any word from the list will be treated as the correct answer.</p>')
            ])
            ->register();

        $types = ['multiple_choice', 'true_false', 'one_word'];
        foreach($types as $type)
        {
            // Only show the $type Meta Box if the Quiz Unit Type is $type
            Utility::show_hide([
                    'when' => '#quiz_unit_typechecklist',
                    'is_value' => $type,
                    'show_target' => '#' . $this->getPrefix('quiz_answer') . '_' . $type
                ],[$this->getPrefix('quiz_unit')]
            );
        }
    }

    /**
     * Create CRUD Relationships
     */
    private function crud()
    {
        // Quiz Units -> Quiz Answers CRUD Relationship
        CRUD::make($this->prefix, $this->getPrefix('quiz_unit'), $this->getPrefix('quiz_answer'))
            ->setListFields($this->getPrefix('quiz_answer'), [
                'ID',
                'crud_edit_post_title',
                'yes_no_correct'
            ])
            ->setForm($this->getPrefix('quiz_answer'), function($post)
            {
                FormBuilder::checkbox('correct', $this->__('The answer is correct'), 1, esc_attr($post->correct) == 1);
            })
            ->register();

        CRUD::setPostTypeLabel($this->getPrefix('quiz_answer'), $this->__('Quiz Answer'));
    }

    /**
     * Customize the WP Admin post listing
     */
    private function admin_post_list()
    {
        // Add Quiz Units Listing Custom Columns
        PostList::add_columns($this->getPrefix('quiz_unit'), [
            ['type', 'Unit Type', 2]
        ]);

        // Display Quiz Units Listing Custom Columns
        PostList::bind_column($this->getPrefix('quiz_unit'), function($column, $post_id)
        {
            if ($column == 'type')
            {
                $type = Taxonomy::get_term($post_id, 'quiz_unit_type');
                $output = '<strong>' . $type->name . '</strong>';
                if ($type->slug == 'multiple_choice')
                {
                    $answers = count(get_post_meta($post_id, $this->getPrefix('quiz_answer')));
                    $output .= ' <em>(' . sprintf( $this->__('%1$s Answers'), $answers ) . ')</em>';
                }
                echo $output;
            }
        });
    }

    /**
     * Method called automatically on plugin activation
     */
    public static function activate()
    {
        self::taxonomies();

        wp_insert_term('Essay','quiz_unit_type', ['slug' => 'essay']);
        wp_insert_term('Multiple choice','quiz_unit_type', ['slug' => 'multiple_choice']);
        wp_insert_term('True or False','quiz_unit_type', ['slug' => 'true_false']);
        wp_insert_term('One Word Answer','quiz_unit_type', ['slug' => 'one_word']);
    }

    /**
     * Get all Quiz Units by $quiz_id
     * @param $quiz_id
     * @return WP_Query
     */
    public static function get($quiz_id)
    {
        $plugin = IoC::getContainer('plugin');
        $quiz_units = get_post_meta($quiz_id, $plugin->getPrefix('quiz_unit'));
        $units = new WP_Query([
            'post_type' => $plugin->getPrefix('quiz_unit'),
            'post__in' => $quiz_units
        ]);

        $order = self::get_order($quiz_id);
        $posts = CRUD::order_sortables($units->posts, $order);
        $units->posts = $posts;

        wp_reset_postdata();
        return $units;
    }

    /**
     * Get the first Quiz Unit id associated with $quiz_id
     * @param $quiz_id
     * @return bool
     */
    public static function get_first($quiz_id)
    {
        $order = self::get_order($quiz_id);
        if (empty($order))
        {
            $plugin = IoC::getContainer('plugin');
            $quiz_units = get_post_meta($quiz_id, $plugin->getPrefix('quiz_unit'));
            if (empty($quiz_units)) return false;
            return $quiz_units[0];
        }
        return $order[0];
    }

    /**
     * Get the next Quiz Unit id after $current_unit_id associated with $quiz_id
     * @param $quiz_id
     * @param $current_unit_id
     * @return bool
     */
    public static function get_next($quiz_id, $current_unit_id)
    {
        $order = self::get_order($quiz_id);
        $current_key = array_search($current_unit_id, $order);
        if (!isset($order[$current_key])) return false;
        if (!isset($order[$current_key+1])) return false;
        return $order[$current_key+1];
    }

    /**
     * Get the previous Quiz Unit id before $current_unit_id associated with $quiz_id
     * @param $quiz_id
     * @param $current_unit_id
     * @return bool
     */
    public static function get_prev($quiz_id, $current_unit_id)
    {
        $order = self::get_order($quiz_id);
        $current_key = array_search($current_unit_id, $order);
        if (!isset($order[$current_key])) return false;
        if (!isset($order[$current_key-1])) return false;
        return $order[$current_key-1];
    }

    /**
     * Get the order of Quiz Units associated with $quiz_id
     * @param $quiz_id
     * @return mixed
     */
    public static function get_order($quiz_id)
    {
        $plugin = IoC::getContainer('plugin');
        $order_key = '_order_' . $plugin->getPrefix('quiz_unit');
        $order = get_post_meta($quiz_id, $order_key, true);
        return !empty($order) ? $order : get_post_meta($quiz_id, $plugin->getPrefix('quiz_unit'));
    }
}