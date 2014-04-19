<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\Date;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\ThickBox;

class Courses extends PluginGeneric
{
    public function __construct()
    {
        parent::__construct();
        $this->metaboxes();
        $this->crud();
        $this->admin_post_list();
    }

    private function metaboxes()
    {
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
    }

    private function crud()
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
                            Date::time_format(implode(":", $post->duration)) . '</small></p>'
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
                FormBuilder::select_hhmmss('duration', 'Duration', $post->duration);
            })
            ->register();

        CRUD::setPostTypeLabel('mp_lms_lesson', 'Lesson');
    }

    private function admin_post_list()
    {
        // Add Courses Listing Custom Columns
        PostList::add_columns($this->prefix . '_course', [
            ['thumbnail', 'Thumbnail', 1],
            ['lessons', 'Lessons', 3],
            ['quizez', 'Quizez', 4],
            ['duration', 'Duration', 5],
            ['author', 'Instructor', 6]
        ]);

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
                    $duration[] = Date::time_to_seconds(implode(":", get_post_meta($lesson_id, 'duration', true)));

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
    }
} 