<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Core\IoC;
use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\Date;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\PostType;
use Mosaicpro\WpCore\Taxonomy;
use Mosaicpro\WpCore\ThickBox;
use WP_Query;

/**
 * Class Courses
 * @package Mosaicpro\WP\Plugins\LMS
 */
class Courses extends PluginGeneric
{
    /**
     * Create a new Courses instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->post_types();
        $this->taxonomies();
        $this->metaboxes();
        $this->crud();
        $this->admin_post_list();
    }

    /**
     * Create the Courses post type
     */
    private function post_types()
    {
        PostType::make('course', $this->prefix)
            ->setOptions(['show_in_menu' => $this->prefix, 'supports' => ['title', 'thumbnail']])
            ->register();
    }

    /**
     * Create the Taxonomies
     */
    private function taxonomies()
    {
        Taxonomy::make('topic')
            ->setPostType([$this->getPrefix('lesson'), $this->getPrefix('course')])
            ->setOption('args', ['show_admin_column' => true])
            ->register();
    }

    /**
     * Create the Meta Boxes
     */
    private function metaboxes()
    {
        // Course Description Editor
        add_action('edit_form_after_title', function($post)
        {
            if ($post->post_type !== $this->getPrefix('course')) return;
            echo '<h2>' . $this->__('Course Description') . '</h2>';
            wp_editor($post->post_content, 'editpost', ['textarea_name' => 'post_content', 'textarea_rows' => 5]);
            echo "<br/>";
        });

        // Course -> Lessons Meta Box
        MetaBox::make($this->prefix, 'lesson_quiz', $this->__('Course Lessons'))
            ->setPostType($this->getPrefix('course'))
            ->setDisplay([
                CRUD::getListContainer([$this->getPrefix('lesson'), $this->getPrefix('quiz')]),
                ThickBox::register_iframe( 'thickbox_lessons', $this->__('Add Lessons'), 'admin-ajax.php',
                    ['action' => 'list_' . $this->getPrefix('course') . '_' . $this->getPrefix('lesson')] )->render(),
                ThickBox::register_iframe( 'thickbox_quizez', $this->__('Add Quizez'), 'admin-ajax.php',
                    ['action' => 'list_' . $this->getPrefix('course') . '_' . $this->getPrefix('quiz')] )->render()
            ])
            ->register();
    }

    /**
     * Create CRUD Relationships
     */
    private function crud()
    {
        // Courses -> Lessons & Quiz Mixed CRUD Relationship
        CRUD::make($this->prefix, $this->getPrefix('course'), [$this->getPrefix('lesson'), $this->getPrefix('quiz')])
            ->setListFields($this->getPrefix('lesson'), [
                'ID',
                'post_thumbnail',
                // Custom Title & Duration Column
                function($post)
                {
                    return [
                        'field' => $this->__('Title'),
                        'value' => $post->post_title .
                            '<p><small class="label label-default">' .
                            Date::time_format(implode(":", $post->duration)) . '</small></p>'
                    ];
                }
            ])
            ->setListFields($this->getPrefix('quiz'), [
                'ID',
                'post_title',
                'count_' . $this->getPrefix('quiz_unit')
            ])
            ->setForm($this->getPrefix('lesson'), function($post)
            {
                FormBuilder::select_hhmmss('duration', $this->__('Duration'), $post->duration);
            })
            ->register();

        CRUD::setPostTypeLabel($this->getPrefix('lesson'), $this->__('Lesson'));
    }

    /**
     * Customize the WP Admin post list
     */
    private function admin_post_list()
    {
        // Add Courses Listing Custom Columns
        PostList::add_columns($this->getPrefix('course'), [
            ['thumbnail', $this->__('Thumbnail'), 1],
            ['lessons', $this->__('Lessons'), 3],
            ['quizez', $this->__('Quizez'), 4],
            ['duration', $this->__('Duration'), 5],
            ['author', $this->__('Instructor'), 6]
        ]);

        // Display Courses Listing Custom Columns
        PostList::bind_column($this->getPrefix('course'), function($column, $post_id)
        {
            if ($column == 'duration')
                echo self::get_duration($post_id);

            if ($column == 'lessons')
            {
                $lessons = get_post_meta($post_id, $this->getPrefix('lesson'));
                echo count($lessons);
            }
            if ($column == 'quizez')
            {
                $quizez = get_post_meta($post_id, $this->getPrefix('quiz'));
                echo count($quizez);
            }
        });
    }

    /**
     * Get the duration of all Lessons within a Course
     * @param $course_id
     * @return string
     */
    public static function get_duration($course_id)
    {
        $lessons = get_post_meta($course_id, IoC::getContainer('plugin')->getPrefix('lesson'));
        $duration = [];
        foreach($lessons as $lesson_id)
            $duration[] = Date::time_to_seconds(implode(":", get_post_meta($lesson_id, 'duration', true)));

        return Date::seconds_to_time(array_sum($duration));
    }

    /**
     * Get curriculum by $course_id
     * @param $course_id
     * @return WP_Query
     */
    public static function get_curriculum($course_id)
    {
        $plugin = IoC::getContainer('plugin');
        $related_types = [$plugin->getPrefix('lesson'), $plugin->getPrefix('quiz')];
        $list = [];
        foreach ($related_types as $related_type)
        {
            $values = get_post_custom_values($related_type, $course_id);
            if (!is_null($values)) $list = array_merge($list, $values);
        }

        if (empty($list)) return false;

        $related_posts_args = [
            'post_type' => $related_types,
            'numberposts' => -1,
            'post__in' => $list
        ];

        $related_posts = new WP_Query($related_posts_args);

        $order_key = '_order_' . $plugin->getPrefix('lesson') . '_' . $plugin->getPrefix('quiz');
        $order = get_post_meta($course_id, $order_key, true);
        $posts = CRUD::order_sortables($related_posts->posts, $order);
        $related_posts->posts = $posts;

        wp_reset_postdata();
        return $related_posts;
    }
} 