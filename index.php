<?php
/*
Plugin Name: MP LMS
Plugin URI: http://mosaicpro.biz
Description: Learning Management System with Courses, Quizes, WooCommerce, BuddyPress and bbPress integrations.
Version: 1.0
Author: MosaicPro
Author URI: http://mosaicpro.biz
*/

use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\Date;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\Taxonomy;
use Mosaicpro\WpCore\ThickBox;
use Mosaicpro\WpCore\Utility;

class MP_LMS
{
    protected $prefix;

	public function __construct()
	{
        $this->prefix = strtolower(__CLASS__);
        $this->register_crud();
        $this->register_taxonomies();
        $this->register_meta_boxes();
        $this->admin_menus();
        $this->admin_post_lists();
	}

    private function register_crud()
    {
        // Courses -> Lessons & Quiz Mixed CRUD Relationship
        CRUD::make($this->prefix, 'course', ['lesson', 'quiz'])
            ->setPostTypeOptions('mp_lms_lesson', [
                'args' => [
                    'supports' => ['title', 'excerpt', 'editor', 'thumbnail'],
                    'show_in_menu' => $this->prefix
                ]
            ])
            ->setPostTypeOptions('mp_lms_quiz', [
                'name' => ['quiz', 'quizez']
            ])
            ->setListFields('mp_lms_lesson', [
                'ID',
                'post_thumbnail',
                // Custom Title & Duration Column
                function($post)
                {
                    return [
                        'field' => 'Title',
                        'value' => $post->post_title .
                            '<p><small class="label label-default">' .
                            Date::time_format($post->duration) . '</small></p>'
                    ];
                }
            ])
            ->setListFields('mp_lms_quiz', [
                'ID',
                'crud_edit_post_title',
                'count_mp_lms_quiz_unit'
            ])
            ->setForm('mp_lms_lesson', function($post)
            {
                FormBuilder::input('duration', 'Duration', esc_attr($post->duration));
            })
            ->register();

        // Quizez -> Quiz Units CRUD Relationship
        CRUD::make($this->prefix, 'quiz', 'quiz_unit')
            ->setPostTypeOptions('mp_lms_quiz_unit', [
                'name' => 'quiz unit',
                'args' => [
                    'supports' => ['title'],
                    'show_in_menu' => $this->prefix
                ]
            ])
            ->setListFields('mp_lms_quiz_unit', [
                'ID',
                'post_title',
                function($post)
                {
                    $postterms = get_the_terms( $post, 'quiz_unit_type' );
                    $current = ($postterms ? array_pop($postterms) : false);
                    $output = '<strong>' . $current->name . '</strong>';
                    if ($current->slug == 'multiple_choice') $output .= ' <em>(' . count($post->mp_lms_quiz_answer) . ' Answers)</em>';
                    return [
                        'field' => 'Quiz Type',
                        'value' => $output
                    ];
                }
            ])
            ->register();

        // Quiz Units -> Quiz Answers CRUD Relationship
        CRUD::make($this->prefix, 'quiz_unit', 'quiz_answer')
            ->setPostTypeOptions('mp_lms_quiz_answer', [
                'name' => 'quiz answer',
                'args' => [
                    'supports' => ['title'],
                    'show_in_menu' => $this->prefix
                ]
            ])
            ->setListFields('mp_lms_quiz_answer', [
                'ID',
                'crud_edit_post_title',
                'yes_no_correct'
            ])
            ->setForm('mp_lms_quiz_answer', function($post)
            {
                FormBuilder::checkbox('correct', 'The answer is correct', 1, esc_attr($post->correct) == 1);
            })
            ->register();

        // Only show the Quiz Answers Meta Box if the Quiz Unit Type is multiple_choice
        Utility::show_hide([
                'when' => '#quiz_unit_typechecklist',
                'is_value' => 'multiple_choice',
                'show_target' => '#mp_lms_quiz_answer'
            ],['mp_lms_quiz_unit']
        );

        CRUD::setPostTypeLabel('mp_lms_lesson', 'Lesson');
        CRUD::setPostTypeLabel('mp_lms_quiz', 'Quiz');
        CRUD::setPostTypeLabel('mp_lms_quiz_unit', 'Quiz Unit');
        CRUD::setPostTypeLabel('mp_lms_quiz_answer', 'Quiz Answer');
    }

    private function register_taxonomies()
    {
        // define taxonomies
        $taxonomies = [
            'topic' => [
                'post_type' => [ 'mp_lms_lesson', 'mp_lms_course' ],
                'args' => ['show_admin_column' => true]
            ],
            'quiz unit type' => [
                'taxonomy_type' => 'radio',
                'update_meta_box' => [
                    'label' => 'Quiz Unit Type',
                    'context' => 'normal',
                    'priority' => 'high'
                ],
                'post_type' => 'mp_lms_quiz_unit'
            ]
        ];

        // register taxonomies
        Taxonomy::registerMany($taxonomies);
    }

    private function register_meta_boxes()
    {
        // Quiz -> Quiz Units Meta Box
        MetaBox::make($this->prefix, 'quiz_unit', 'Quiz Units')
            ->setPostType('quiz')
            ->setDisplay([
                '<div id="' . $this->prefix . '_quiz_unit_list"></div>',
                ThickBox::register_iframe( 'thickbox_units', 'Add Unit to Quiz', 'admin-ajax.php',
                    ['action' => $this->prefix . '_list_quiz_unit'] )->render()
            ])
            ->register();

        // Course -> Lessons Meta Box
        MetaBox::make($this->prefix, 'lesson_quiz', 'Course Lessons')
            ->setPostType('course')
            ->setDisplay([
                '<div id="' . $this->prefix . '_lesson_quiz_list"></div>',
                ThickBox::register_iframe( 'thickbox_lessons', 'Add Lessons', 'admin-ajax.php',
                    ['action' => $this->prefix . '_list_lesson'] )->render(),
                ThickBox::register_iframe( 'thickbox_quizez', 'Add Quizez', 'admin-ajax.php',
                    ['action' => $this->prefix . '_list_quiz'] )->render()
            ])
            ->register();

        // Lessons -> Attributes Meta Box
        MetaBox::make($this->prefix, 'lesson_attributes', 'Lesson Attributes')
            ->setPostType('lesson')
            ->setContext('side')
            ->setField('duration', 'Duration', 'input')
            ->register();

        // Quiz Units -> Quiz Answers Meta Box
        MetaBox::make($this->prefix, 'quiz_answer', 'Answers')
            ->setPostType('quiz_unit')
            ->setDisplay([
                '<div id="' . $this->prefix . '_quiz_answer_list"></div>',
                ThickBox::register_iframe( 'thickbox_answers', 'Add Answers', 'admin-ajax.php',
                    ['action' => $this->prefix . '_list_quiz_answer'] )->render()
            ])
            ->register();

        // Quiz Answer Attributes
        MetaBox::make($this->prefix, 'quiz_answer_attributes', 'Answer Attributes')
            ->setPostType('quiz_answer')
            ->setField('correct', 'The answer is correct', 'checkbox')
            ->register();
    }

    private function admin_menus()
    {
        add_action('admin_menu', function()
        {
            add_menu_page('LMS', 'LMS', 'edit_posts', $this->prefix, '', admin_url() . 'images/media-button-video.gif', 27);
            add_submenu_page( $this->prefix, 'Quiz Results', 'Quiz Results', 'edit_posts', 'quiz_results', [$this, 'admin_page_quiz_results']);
            add_submenu_page( $this->prefix, 'Topics', 'Topics', 'edit_posts', 'edit-tags.php?taxonomy=topic');
        });

        add_action('parent_file', function($parent_file)
        {
            global $current_screen;
            $taxonomy = $current_screen->taxonomy;
            if ($taxonomy == 'topic') $parent_file = $this->prefix;
            return $parent_file;
        });
    }

    private function admin_post_lists()
    {
        // Add Lessons Listing Custom Columns
        PostList::add_columns($this->prefix . '_lesson', [
            ['thumbnail', 'Thumbnail', 1],
            ['duration_format', 'Duration', 3],
            ['author', 'Instructor', 4]
        ]);

        // Add Courses Listing Custom Columns
        PostList::add_columns($this->prefix . '_course', [
            ['thumbnail', 'Thumbnail', 1],
            ['lessons', 'Lessons', 3],
            ['quizez', 'Quizez', 4],
            ['duration', 'Duration', 5],
            ['author', 'Instructor', 6]
        ]);

        // Add Quizez Listing Custom Columns
        PostList::add_columns($this->prefix . '_quiz', [
            ['quiz_units', 'Quiz Units', 2]
        ]);

        // Add Quiz Units Listing Custom Columns
        PostList::add_columns($this->prefix . '_quiz_unit', [
            ['type', 'Unit Type', 2]
        ]);

        // Display Lessons Listing Custom Columns
        PostList::bind_column($this->prefix . '_lesson', function($column, $post_id)
        {
            if ($column == 'duration_format')
            {
                $duration = Date::time_to_seconds(get_post_meta($post_id, 'duration', true));
                echo Date::seconds_to_time($duration);
            }
        });

        // Display Courses Listing Custom Columns
        PostList::bind_column($this->prefix . '_course', function($column, $post_id)
        {
            if ($column == 'duration')
            {
                $data = get_post_meta($post_id, 'mp_lms_mixed', true);
                $lessons = array_where($data, function($key, $value)
                {
                    return $value['type'] == 'mp_lms_lesson';
                });

                $duration = [];
                foreach($lessons as $lesson_id => $lesson)
                    $duration[] = Date::time_to_seconds(get_post_meta($lesson_id, 'duration', true));

                echo Date::seconds_to_time(array_sum($duration));
            }
            if ($column == 'lessons')
            {
                $data = get_post_meta($post_id, 'mp_lms_mixed', true);
                $lessons = array_where($data, function($key, $value)
                {
                    return $value['type'] == 'mp_lms_lesson';
                });
                echo count($lessons);
            }
            if ($column == 'quizez')
            {
                $data = get_post_meta($post_id, 'mp_lms_mixed', true);
                $quizez = array_where($data, function($key, $value)
                {
                    return $value['type'] == 'mp_lms_quiz';
                });
                echo count($quizez);
            }
        });

        // Display Quizez Listing Custom Columns
        PostList::bind_column($this->prefix . '_quiz', function($column, $post_id)
        {
            if ($column == 'quiz_units')
            {
                $units = get_post_meta($post_id, 'mp_lms_quiz_unit', true);
                echo count($units);
            }
        });

        // Display Quiz Units Listing Custom Columns
        PostList::bind_column($this->prefix . '_quiz_unit', function($column, $post_id)
        {
            if ($column == 'type')
            {
                $postterms = get_the_terms( $post_id, 'quiz_unit_type' );
                $current = ($postterms ? array_pop($postterms) : false);
                $output = '<strong>' . $current->name . '</strong>';
                if ($current->slug == 'multiple_choice')
                {
                    $answers = get_post_meta($post_id, 'mp_lms_quiz_answer', true);
                    $output .= ' <em>(' . count($answers) . ' Answers)</em>';
                }
                echo $output;
            }
        });
    }

    public static function activate()
    {
        self::register_taxonomies();

        wp_insert_term('Multiple choice','quiz_unit_type', ['slug' => 'multiple_choice']);
        wp_insert_term('Essay','quiz_unit_type', ['slug' => 'essay']);
    }

    private function register_user_roles()
    {
        $capabilities = ['manage_courses', 'manage_lessons', 'manage_quizez'];
        $managers = ['administrator'];
        foreach ($managers as $manager)
        {
            $role = get_role($manager);
            foreach($capabilities as $capability)
                $role->add_cap($capability);
        }

        add_role( 'instructor', __( 'Instructor' ),
            array_merge([
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true
            ], $capabilities)
        );

        add_role( 'student', __( 'Student' ), [
            'read' => true
        ]);
    }
}

add_action('init', function()
{
	new MP_LMS();
}, 100);

register_activation_hook(__FILE__, ['MP_LMS', 'activate']);