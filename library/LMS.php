<?php namespace Mosaicpro\WP\Plugins\LMS;

use Mosaicpro\Core\IoC;
use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\Date;
use Mosaicpro\WpCore\FormBuilder;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\PostList;
use Mosaicpro\WpCore\Taxonomy;
use Mosaicpro\WpCore\ThickBox;
use Mosaicpro\WpCore\Utility;

class LMS extends PluginGeneric
{
    public function __construct()
    {
        parent::__construct();

        $this->taxonomies();
        $this->admin_menu();
    }

    private function taxonomies()
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

    private function admin_menu()
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

    public static function activate()
    {
        self::register_taxonomies();

        wp_insert_term('Essay','quiz_unit_type', ['slug' => 'essay']);
        wp_insert_term('Multiple choice','quiz_unit_type', ['slug' => 'multiple_choice']);
        wp_insert_term('True or False','quiz_unit_type', ['slug' => 'true_false']);
        wp_insert_term('One Word Answer','quiz_unit_type', ['slug' => 'one_word']);
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